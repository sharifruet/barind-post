<?php

namespace App\Libraries;

use App\Models\CategoryModel;
use App\Models\NewsModel;
use App\Models\TagModel;
use Config\Automation;

/**
 * Everything machine clients may do with news: look up, list and create articles.
 *
 * Shared by the two front doors for automation — the JSON API (`Api\NewsController`,
 * used by n8n) and the MCP endpoint (`Mcp`, used by AI agents) — so validation, dedup
 * and the publish gates exist exactly once. Every method returns `[httpStatus, body]`;
 * the callers only decide how to wrap it.
 *
 * Every article is filed as status=draft unless the caller asks for "published", the
 * server has automation.autoPublish on, and the article clears every publish gate.
 */
class NewsIntake
{
    /**
     * @return array{0:int, 1:array}
     */
    public function exists(mixed $sourceUrl): array
    {
        if (! is_string($sourceUrl) || $sourceUrl === '') {
            return [422, ['error' => 'source_url query parameter is required']];
        }

        $news = (new NewsModel())->where('source_url', $sourceUrl)->first();

        if (! $news) {
            return [200, ['exists' => false]];
        }

        return [200, [
            'exists' => true,
            'id'     => (int) $news['id'],
            'slug'   => $news['slug'],
            'status' => $news['status'],
        ]];
    }

    /**
     * @return array{0:int, 1:array}
     */
    public function show(int $id): array
    {
        $news = (new NewsModel())
            ->select('id, title, slug, status, category_id, source, source_url, created_at, published_at')
            ->find($id);

        if (! $news) {
            return [404, ['error' => 'Not found']];
        }

        return [200, $news];
    }

    /**
     * Recent published articles with the fields the Facebook pipeline needs to
     * build a share card: title, subtitle, key points, section and a
     * Bangla-formatted date. Read-only; the card itself is rendered and posted
     * outside the site, so nothing here writes an image or touches the article.
     *
     * @return array{0:int, 1:array}
     */
    public function recent(int $limit): array
    {
        $limit = max(1, min(50, $limit));

        $db   = \Config\Database::connect();
        $rows = $db->table('news')
            ->select('id, slug, title, subtitle, lead_text, category_id, published_at, image_url')
            ->where('status', 'published')
            ->orderBy('published_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $cats = [];
        foreach ((new CategoryModel())->select('id, name')->findAll() as $c) {
            $cats[(int) $c['id']] = $c['name'];
        }

        $out = [];
        foreach ($rows as $r) {
            $points = array_values(array_filter(
                array_map('trim', explode("\n", (string) ($r['lead_text'] ?? ''))),
                static fn ($p) => $p !== ''
            ));
            $out[] = [
                'id'           => (int) $r['id'],
                'slug'         => $r['slug'],
                'url'          => $this->articleUrl($r['slug']),
                'section'      => $cats[(int) $r['category_id']] ?? null,
                'title'        => $r['title'],
                'subtitle'     => $r['subtitle'] ?: null,
                'key_points'   => $points,
                'date'         => bangla_date($r['published_at'] ?? null),
                'has_image'    => ! empty($r['image_url']),
                'published_at' => $r['published_at'] ?? null,
            ];
        }

        return [200, ['articles' => $out]];
    }

    /**
     * @return array{0:int, 1:array}
     */
    public function categories(): array
    {
        $categories = (new CategoryModel())
            ->select('id, name, slug')
            ->orderBy('name', 'ASC')
            ->findAll();

        return [200, ['categories' => $categories]];
    }

    /**
     * @return array{0:int, 1:array}
     */
    public function tags(): array
    {
        $tags = (new TagModel())
            ->select('id, name')
            ->orderBy('name', 'ASC')
            ->findAll();

        return [200, ['tags' => $tags]];
    }

    /**
     * Validate, dedup and store one article. 201 on success, 409 for a duplicate,
     * 422 for invalid input.
     *
     * @return array{0:int, 1:array}
     */
    public function create(array $payload): array
    {
        // `subtitle` is the one-sentence standfirst; `lead_text` holds the key
        // points, one per line. Callers may send the points as an array (or under
        // the friendlier name `key_points`); store them newline-joined.
        if (! isset($payload['lead_text']) && isset($payload['key_points'])) {
            $payload['lead_text'] = $payload['key_points'];
        }
        // Feed parsers sometimes deliver CDATA titles as objects; only a plain string is a source title.
        if (isset($payload['source_title']) && ! is_string($payload['source_title'])) {
            $payload['source_title'] = is_array($payload['source_title']) && isset($payload['source_title']['_']) && is_string($payload['source_title']['_'])
                ? $payload['source_title']['_'] : null;
        }
        // JSON true/false (the MCP tool declares `featured` as a boolean) would fail the in_list rule below.
        if (isset($payload['featured']) && is_bool($payload['featured'])) {
            $payload['featured'] = $payload['featured'] ? '1' : '0';
        }
        if (isset($payload['lead_text']) && is_array($payload['lead_text'])) {
            $points = array_filter(array_map(static fn ($p) => trim((string) $p), $payload['lead_text']), static fn ($p) => $p !== '');
            $payload['lead_text'] = $points === [] ? null : implode("\n", $points);
        }

        $rules = [
            'title'          => 'required|string|min_length[3]|max_length[255]',
            'content'        => 'required|string|min_length[20]',
            'category_id'    => 'required|integer',
            'subtitle'       => 'permit_empty|string|max_length[255]',
            'lead_text'      => 'permit_empty|string',   // key points, one per line
            'language'       => 'permit_empty|in_list[bn,en]',
            'image_url'      => 'permit_empty|string|max_length[500]',
            'image_caption'  => 'permit_empty|string',
            'image_alt_text' => 'permit_empty|string|max_length[255]',
            // A source-page image the editor may adopt from the Incoming queue; never published as-is.
            'suggested_image_url' => 'permit_empty|string|max_length[500]',
            'source'         => 'permit_empty|string|max_length[255]',
            'source_url'     => 'permit_empty|string|max_length[500]',
            'source_title'   => 'permit_empty|string|max_length[500]',   // headline as the source published it (dedup across outlets)
            'dateline'       => 'permit_empty|string|max_length[255]',
            'tags'           => 'permit_empty|is_array',
            // Importance 0-5 from the AI; the server features an article at or above
            // automation.featuredMinImportance. `featured` may also be sent directly.
            'importance'     => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[5]',
            'featured'       => 'permit_empty|in_list[0,1,true,false]',
            // "published" is a *request*, not a command: it is honoured only when the server
            // has automation.autoPublish on and the article clears every publish gate below.
            // Anything else is still rejected outright rather than silently downgraded.
            'status'         => 'permit_empty|in_list[draft,published]',
        ];

        $validation = \Config\Services::validation();
        $validation->reset();
        $validation->setRules($rules);

        if (! $validation->run($payload)) {
            return [422, ['error' => 'Validation failed', 'fields' => $validation->getErrors()]];
        }

        $automation = config('Automation');
        if (empty($automation->authorId)) {
            return [500, ['error' => 'automation.authorId is not configured on the server']];
        }

        $categoryModel = new CategoryModel();
        if (! $categoryModel->find((int) $payload['category_id'])) {
            return [422, ['error' => 'Validation failed', 'fields' => ['category_id' => 'Category does not exist.']]];
        }

        // The model sometimes emits a literal backslash-n instead of a newline; that reaches
        // the page as visible "\n" and glues words together. Repair before storing or hashing.
        foreach (['title', 'subtitle', 'lead_text', 'content'] as $field) {
            if (isset($payload[$field]) && is_string($payload[$field])) {
                $payload[$field] = BanglaText::fixEscapedNewlines($payload[$field]);
            }
        }

        // Feature the genuinely important stories. Prefer an explicit `featured`,
        // otherwise derive it from the AI importance score against the server threshold.
        if (isset($payload['featured'])) {
            $featured = filter_var($payload['featured'], FILTER_VALIDATE_BOOLEAN);
        } elseif (isset($payload['importance']) && $payload['importance'] !== '') {
            $featured = (int) $payload['importance'] >= $automation->featuredMinImportance;
        } else {
            $featured = false;
        }

        $title   = trim($payload['title']);
        $content = trim($payload['content']);

        $normalized  = mb_strtolower($title) . '|' . mb_strtolower(preg_replace('/\s+/u', ' ', $content));
        $contentHash = hash('sha256', $normalized);

        $newsModel = new NewsModel();

        $sourceUrl = $payload['source_url'] ?? null;
        if (! empty($sourceUrl)) {
            $existing = $newsModel->where('source_url', $sourceUrl)->first();
            if ($existing) {
                return [409, [
                    'error'   => 'An article with this source_url already exists',
                    'article' => ['id' => (int) $existing['id'], 'slug' => $existing['slug'], 'status' => $existing['status']],
                ]];
            }
        }

        $existingByHash = $newsModel->where('content_hash', $contentHash)->first();
        if ($existingByHash) {
            return [409, [
                'error'   => 'An article with matching content already exists',
                'article' => ['id' => (int) $existingByHash['id'], 'slug' => $existingByHash['slug'], 'status' => $existingByHash['status']],
            ]];
        }

        $wordCount = count(preg_split('/\s+/u', trim(strip_tags($content)), -1, PREG_SPLIT_NO_EMPTY));

        // Same event from another outlet? Flag it for the editor; never auto-merge.
        $sourceTitle = isset($payload['source_title']) ? trim((string) $payload['source_title']) : null;
        $similar     = $newsModel->findSimilar($sourceTitle, $title, $sourceUrl);

        $data = [
            'source_title'          => $sourceTitle !== '' ? $sourceTitle : null,
            'possible_duplicate_of' => $similar['id'] ?? null,
            'duplicate_score'       => $similar ? (int) round($similar['score'] * 100) : null,
            'title'          => $title,
            'subtitle'       => $payload['subtitle'] ?? null,
            'lead_text'      => $payload['lead_text'] ?? null,
            'content'        => $content,
            'author_id'      => (int) $automation->authorId,
            'category_id'    => (int) $payload['category_id'],
            'status'         => 'draft',
            'featured'       => $featured,
            'slug'           => generate_unique_code(),
            'image_url'      => $payload['image_url'] ?? null,
            'image_caption'  => $payload['image_caption'] ?? null,
            'image_alt_text' => $payload['image_alt_text'] ?? null,
            'suggested_image_url' => (isset($payload['suggested_image_url']) && preg_match('~^https?://~i', $payload['suggested_image_url'])) ? $payload['suggested_image_url'] : null,
            'source'         => $payload['source'] ?? null,
            'source_url'     => $sourceUrl,
            'content_hash'   => $contentHash,
            'dateline'       => $payload['dateline'] ?? null,
            'word_count'     => $wordCount,
            'language'       => $payload['language'] ?? 'bn',
        ];

        // Publishing is a request that has to earn itself: the server switch must be on and
        // the article must clear every gate. A failure is not an error — the article is
        // still filed, just as a draft, and the reasons come back in the response so the
        // run summary can show why.
        $gateFailures = [];
        if (($payload['status'] ?? 'draft') === 'published') {
            $gateFailures = $this->publishGateFailures($data, $wordCount, $similar, $automation);
            if ($gateFailures === []) {
                $data['status']       = 'published';
                $data['published_at'] = date('Y-m-d H:i:s');
            }
        }

        $newsId = $newsModel->insert($data, true);

        if (! $newsId) {
            return [500, ['error' => 'Failed to create the article', 'fields' => $newsModel->errors()]];
        }

        $skippedTags = [];
        if (! empty($payload['tags']) && is_array($payload['tags'])) {
            $tagModel = new TagModel();
            $db       = \Config\Database::connect();

            foreach ($payload['tags'] as $tagId) {
                $tagId = (int) $tagId;
                if ($tagModel->find($tagId)) {
                    $db->table('news_tags')->insert(['news_id' => $newsId, 'tag_id' => $tagId]);
                } else {
                    $skippedTags[] = $tagId;
                }
            }
        }

        return [201, [
            'id'                 => $newsId,
            'slug'               => $data['slug'],
            'status'             => $data['status'],
            'skipped_tags'       => $skippedTags,
            'possible_duplicate' => $similar ? ['id' => $similar['id'], 'title' => $similar['title'], 'score' => $similar['score']] : null,
            'publish_gates'      => $gateFailures,   // empty = published (when asked for)
        ]];
    }

    /**
     * Why this article may not be auto-published. Empty array = it may.
     *
     * These are the failure modes actually seen in this pipeline: articles generated from
     * a headline with no body behind it, wholly invented copy from a failed page fetch,
     * the same event arriving from two outlets, and half-finished AI output. An article
     * that trips any of them is still created — as a draft, for a human to look at.
     *
     * @param array{id:int,title:string,score:float}|null $similar
     *
     * @return list<string>
     */
    public function publishGateFailures(array $data, int $wordCount, ?array $similar, Automation $automation): array
    {
        $failures = [];

        if (! $automation->autoPublish) {
            $failures[] = 'auto-publish is off on this site (automation.autoPublish)';
        }

        if ($wordCount < $automation->autoPublishMinWords) {
            $failures[] = sprintf('body is %d words, minimum is %d', $wordCount, $automation->autoPublishMinWords);
        }

        if ($similar !== null) {
            $failures[] = sprintf('possible duplicate of #%d (%d%%)', $similar['id'], (int) round($similar['score'] * 100));
        }

        if (empty($data['subtitle']) && empty($data['lead_text'])) {
            $failures[] = 'no subtitle and no key points — incomplete article';
        }

        if (empty($data['source_url'])) {
            $failures[] = 'no source_url to attribute the story to';
        }

        // A model can write a correct article and head it with a fabricated headline about
        // something else — this reached the live site once. If the headline shares almost no
        // words with its own body, the article is filed for a human instead of published.
        // Text only: tag names like <strong> or <li> would otherwise count as untranslated English words.
        $content      = trim(html_entity_decode(strip_tags(str_replace('<', ' <', (string) ($data['content'] ?? ''))), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $bodyForCheck = trim(($data['subtitle'] ?? '') . ' ' . ($data['lead_text'] ?? '') . ' ' . $content);
        if ($bodyForCheck !== '') {
            $grounded = TitleSimilarity::groundedness((string) ($data['title'] ?? ''), $bodyForCheck);
            if ($grounded < TitleSimilarity::GROUNDED_MIN) {
                $failures[] = sprintf('headline does not match the article (%d%% of its words appear in the body)', (int) round($grounded * 100));
            }
        }

        // Rewrites of English wires sometimes leave a clause untranslated. Publishing that
        // under the masthead is worse than holding it, so it stays a draft for an editor.
        $language = BanglaText::problems(
            trim(($data['title'] ?? '') . ' ' . $bodyForCheck)
        );
        foreach ($language as $problem) {
            $failures[] = 'not clean Bangla: ' . $problem;
        }

        return $failures;
    }

    public function articleUrl(string $slug): string
    {
        return rtrim(base_url(), '/') . '/news/' . $slug;
    }

    public function adminEditUrl(int $id): string
    {
        return rtrim(base_url(), '/') . '/admin/news/edit/' . $id;
    }

}
