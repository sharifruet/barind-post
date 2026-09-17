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
            'dateline'       => 'permit_empty|string|max_length[255]',
            'tags'           => 'permit_empty|is_array',
            // Phase 1 never auto-publishes: reject anything other than "draft" explicitly
            // rather than silently downgrading it, so a caller notices the mistake.
            'status'         => 'permit_empty|in_list[draft]',
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

        $data = [
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
            'id'           => $newsId,
            'slug'         => $data['slug'],
            'status'       => $data['status'],
            'skipped_tags' => $skippedTags,
        ], 201);
    }

    private function json(array $data, int $status = 200)
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }
}
