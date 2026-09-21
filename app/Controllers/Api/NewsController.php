<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\NewsModel;
use App\Models\TagModel;

/**
 * Automation API used by the local n8n instance to create draft news articles.
 * See N8N_NEWS_AUTOMATION_PLAN.md for the design and the ApiKeyFilter that guards
 * this whole controller via the `api/v1` route group.
 *
 * Every article created here lands as status=draft; publishing stays a human action
 * in the existing /admin/news UI.
 */
class NewsController extends BaseController
{
    public function exists()
    {
        $sourceUrl = $this->request->getGet('source_url');

        if (empty($sourceUrl)) {
            return $this->json(['error' => 'source_url query parameter is required'], 422);
        }

        $news = (new NewsModel())->where('source_url', $sourceUrl)->first();

        if (! $news) {
            return $this->json(['exists' => false]);
        }

        return $this->json([
            'exists' => true,
            'id'     => (int) $news['id'],
            'slug'   => $news['slug'],
            'status' => $news['status'],
        ]);
    }

    public function show($id)
    {
        $news = (new NewsModel())
            ->select('id, title, slug, status, category_id, source, source_url, created_at, published_at')
            ->find((int) $id);

        if (! $news) {
            return $this->json(['error' => 'Not found'], 404);
        }

        return $this->json($news);
    }

    /**
     * Attach a generated "text card" PNG to an image-less article.
     *
     * The pipeline renders the card (title + subtitle + key points) in a
     * headless browser so Bangla shapes correctly — GD on this server cannot —
     * then POSTs the PNG here as base64. We store it exactly like a manual
     * upload (public/uploads/news + an images row) and set it as image_url,
     * but only when the article has no image yet, so a real photo an editor
     * added is never clobbered. This is our own generated art, which is why it
     * is allowed where a hotlinked source image is not.
     */
    public function attachCard($id)
    {
        $newsModel = new NewsModel();
        $news      = $newsModel->find((int) $id);
        if (! $news) {
            return $this->json(['error' => 'News not found'], 404);
        }

        $payload = $this->request->getJSON(true);
        if (! is_array($payload) || empty($payload['image_base64'])) {
            return $this->json(['error' => 'image_base64 is required'], 422);
        }

        $b64 = preg_replace('~^data:image/\w+;base64,~', '', (string) $payload['image_base64']);
        $raw = base64_decode($b64, true);
        if ($raw === false || strlen($raw) < 100) {
            return $this->json(['error' => 'image_base64 is not valid base64'], 422);
        }
        if (strlen($raw) > 5 * 1024 * 1024) {
            return $this->json(['error' => 'image exceeds 5MB'], 422);
        }
        $info = @getimagesizefromstring($raw);
        if ($info === false || ($info['mime'] ?? '') !== 'image/png') {
            return $this->json(['error' => 'image must be a PNG'], 422);
        }

        // Mirror ImageUpload::upload's storage exactly so the two-document-root
        // path handling stays identical to manually uploaded images.
        $uploadPath = FCPATH . 'public/uploads/news/';
        if (! is_dir($uploadPath)) {
            @mkdir($uploadPath, 0775, true);
        }
        $filename     = 'card-' . (int) $id . '-' . substr(md5($raw), 0, 10) . '.png';
        $relativePath = 'public/uploads/news/' . $filename;
        if (file_put_contents($uploadPath . $filename, $raw) === false) {
            return $this->json(['error' => 'could not store image'], 500);
        }

        // Idempotent: the filename is the content hash, and images.image_path is
        // unique, so a retry with the same PNG reuses the existing row.
        $imageModel = new \App\Models\ImageModel();
        $existing   = $imageModel->where('image_path', $relativePath)->first();
        $imageId    = $existing['id'] ?? $imageModel->insert([
            'image_name'        => 'card-' . (int) $id,
            'image_path'        => $relativePath,
            'original_filename' => $filename,
            'file_size'         => strlen($raw),
            'mime_type'         => 'image/png',
            'width'             => $info[0] ?? null,
            'height'            => $info[1] ?? null,
            'caption'           => null,
            'alt_text'          => mb_substr((string) $news['title'], 0, 240),
            'uploaded_by'       => config('Automation')->authorId ?: 1,
        ]);

        $applied = false;
        if (empty($news['image_url']) || ! empty($payload['overwrite'])) {
            $newsModel->update((int) $id, ['image_url' => $relativePath]);   // afterUpdate purges the public cache
            $applied = true;
        }

        return $this->json([
            'success'   => true,
            'image_url' => $relativePath,
            'image_id'  => $imageId,
            'applied'   => $applied,
        ]);
    }

    public function categories()
    {
        $categories = (new CategoryModel())
            ->select('id, name, slug')
            ->orderBy('name', 'ASC')
            ->findAll();

        return $this->json(['categories' => $categories]);
    }

    public function tags()
    {
        $tags = (new TagModel())
            ->select('id, name')
            ->orderBy('name', 'ASC')
            ->findAll();

        return $this->json(['tags' => $tags]);
    }

    public function create()
    {
        $payload = $this->request->getJSON(true);
        if (! is_array($payload)) {
            $payload = [];
        }

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
            // "published" is a *request*, not a command: it is honoured only when the server
            // has automation.autoPublish on and the article clears every publish gate below.
            // Anything else is still rejected outright rather than silently downgraded.
            'status'         => 'permit_empty|in_list[draft,published]',
        ];

        $validation = \Config\Services::validation();
        $validation->setRules($rules);

        if (! $validation->run($payload)) {
            return $this->json(['error' => 'Validation failed', 'fields' => $validation->getErrors()], 422);
        }

        $automation = config('Automation');
        if (empty($automation->authorId)) {
            return $this->json(['error' => 'automation.authorId is not configured on the server'], 500);
        }

        $categoryModel = new CategoryModel();
        if (! $categoryModel->find((int) $payload['category_id'])) {
            return $this->json(['error' => 'Validation failed', 'fields' => ['category_id' => 'Category does not exist.']], 422);
        }

        // The model sometimes emits a literal backslash-n instead of a newline; that reaches
        // the page as visible "\n" and glues words together. Repair before storing or hashing.
        foreach (['title', 'subtitle', 'lead_text', 'content'] as $field) {
            if (isset($payload[$field]) && is_string($payload[$field])) {
                $payload[$field] = \App\Libraries\BanglaText::fixEscapedNewlines($payload[$field]);
            }
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
                return $this->json([
                    'error'   => 'An article with this source_url already exists',
                    'article' => ['id' => (int) $existing['id'], 'slug' => $existing['slug'], 'status' => $existing['status']],
                ], 409);
            }
        }

        $existingByHash = $newsModel->where('content_hash', $contentHash)->first();
        if ($existingByHash) {
            return $this->json([
                'error'   => 'An article with matching content already exists',
                'article' => ['id' => (int) $existingByHash['id'], 'slug' => $existingByHash['slug'], 'status' => $existingByHash['status']],
            ], 409);
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
            'featured'       => false,
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
            return $this->json(['error' => 'Failed to create the article', 'fields' => $newsModel->errors()], 500);
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

        return $this->json([
            'id'                 => $newsId,
            'slug'               => $data['slug'],
            'status'             => $data['status'],
            'skipped_tags'       => $skippedTags,
            'possible_duplicate' => $similar ? ['id' => $similar['id'], 'title' => $similar['title'], 'score' => $similar['score']] : null,
            'publish_gates'      => $gateFailures,   // empty = published (when asked for)
        ], 201);
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
    private function publishGateFailures(array $data, int $wordCount, ?array $similar, \Config\Automation $automation): array
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
        $bodyForCheck = trim(($data['subtitle'] ?? '') . ' ' . ($data['lead_text'] ?? '') . ' ' . ($data['content'] ?? ''));
        if ($bodyForCheck !== '') {
            $grounded = \App\Libraries\TitleSimilarity::groundedness((string) ($data['title'] ?? ''), $bodyForCheck);
            if ($grounded < \App\Libraries\TitleSimilarity::GROUNDED_MIN) {
                $failures[] = sprintf('headline does not match the article (%d%% of its words appear in the body)', (int) round($grounded * 100));
            }
        }

        // Rewrites of English wires sometimes leave a clause untranslated. Publishing that
        // under the masthead is worse than holding it, so it stays a draft for an editor.
        $language = \App\Libraries\BanglaText::problems(
            trim(($data['title'] ?? '') . ' ' . ($data['subtitle'] ?? '') . ' ' . ($data['lead_text'] ?? '') . ' ' . ($data['content'] ?? ''))
        );
        foreach ($language as $problem) {
            $failures[] = 'not clean Bangla: ' . $problem;
        }

        return $failures;
    }

    private function json(array $data, int $status = 200)
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }
}
