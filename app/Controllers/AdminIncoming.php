<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ImageModel;
use App\Models\NewsModel;

/**
 * Incoming queue: the editorial gate for automation drafts.
 *
 * Every article the n8n pipelines create arrives here as a draft with a
 * `source_url`. Nothing leaves this screen for the public site without a
 * human clicking Publish. Discarding archives the row rather than deleting
 * it, so the same source URL is recognised as already seen and never
 * re-ingested.
 */
class AdminIncoming extends BaseAdminController
{
    /** Roles allowed to triage. Reporters only ever produce drafts themselves. */
    private const TRIAGE_ROLES = ['admin', 'editor', 'sub-editor'];

    private const MAX_IMAGE_BYTES = 8 * 1024 * 1024;

    private const IMAGE_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    public function index()
    {
        if ($denied = $this->denyUnlessTriager()) {
            return $denied;
        }

        $view   = $this->request->getGet('view') === 'discarded' ? 'discarded' : 'pending';
        $status = $view === 'discarded' ? 'archived' : 'draft';
        $source = trim((string) $this->request->getGet('source'));

        $newsModel = new NewsModel();

        $builder = $newsModel->select('news.*, categories.name AS category_name')
            ->join('categories', 'categories.id = news.category_id', 'left')
            ->where('news.source_url IS NOT NULL')
            ->where('news.status', $status)
            ->orderBy('news.created_at', 'DESC');
        if ($source !== '') {
            $builder->where('news.source', $source);
        }
        $items = $builder->findAll(300);

        // Per-source counts for the filter chips (for the current view).
        $sourceRows = $newsModel->select('COALESCE(source, "") AS source, COUNT(*) AS n')
            ->where('source_url IS NOT NULL')
            ->where('status', $status)
            ->groupBy('source')
            ->orderBy('n', 'DESC')
            ->findAll();

        $counts = [
            'pending'   => $newsModel->where('source_url IS NOT NULL')->where('status', 'draft')->countAllResults(),
            'discarded' => $newsModel->where('source_url IS NOT NULL')->where('status', 'archived')->countAllResults(),
        ];

        return view('admin/incoming', [
            'items'      => $items,
            'sources'    => $sourceRows,
            'counts'     => $counts,
            'view'       => $view,
            'source'     => $source,
            'categories' => (new CategoryModel())->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function publish($id)
    {
        if ($denied = $this->denyUnlessTriager()) {
            return $denied;
        }

        $newsModel = new NewsModel();
        $news      = $newsModel->find((int) $id);
        if (! $news) {
            return $this->backWith('error', 'Draft not found.');
        }

        $data = [
            'status'       => 'published',
            'published_at' => ! empty($news['published_at']) ? $news['published_at'] : date('Y-m-d H:i:s'),
        ];

        $categoryId = (int) $this->request->getPost('category_id');
        if ($categoryId > 0 && (new CategoryModel())->find($categoryId)) {
            $data['category_id'] = $categoryId;
        }

        $newsModel->update((int) $id, $data); // NewsModel purges the public page cache

        return $this->backWith('success', 'Published: ' . esc($news['title']) . ' — <a href="/news/' . esc(rawurlencode($news['slug']), 'attr') . '" target="_blank" rel="noopener">view on site</a>.');
    }

    public function discard($id)
    {
        if ($denied = $this->denyUnlessTriager()) {
            return $denied;
        }

        $newsModel = new NewsModel();
        $news      = $newsModel->find((int) $id);
        if (! $news) {
            return $this->backWith('error', 'Draft not found.');
        }

        // Archived, not deleted: the row (and its source_url) must survive for dedup.
        $newsModel->update((int) $id, ['status' => 'archived']);

        return $this->backWith('success', 'Discarded: ' . esc($news['title']) . '. It stays in the archive so this source URL will not be ingested again.');
    }

    public function restore($id)
    {
        if ($denied = $this->denyUnlessTriager()) {
            return $denied;
        }

        $newsModel = new NewsModel();
        $news      = $newsModel->find((int) $id);
        if (! $news) {
            return $this->backWith('error', 'Draft not found.');
        }

        $newsModel->update((int) $id, ['status' => 'draft']);

        return $this->backWith('success', 'Restored to the pending queue: ' . esc($news['title']));
    }

    public function discardBulk()
    {
        if ($denied = $this->denyUnlessTriager()) {
            return $denied;
        }

        $ids = array_values(array_filter(array_map('intval', (array) $this->request->getPost('ids'))));
        if ($ids === []) {
            return $this->backWith('error', 'Nothing selected.');
        }

        $newsModel = new NewsModel();
        $newsModel->whereIn('id', $ids)
            ->where('status', 'draft')
            ->where('source_url IS NOT NULL')
            ->set(['status' => 'archived'])
            ->update();
        $affected = $newsModel->db->affectedRows();

        return $this->backWith('success', "Discarded {$affected} draft(s). They stay in the archive so their source URLs will not be ingested again.");
    }

    /**
     * Inline category change from the queue (AJAX). Returns JSON.
     */
    public function category($id)
    {
        if ($this->denyUnlessTriager()) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Not allowed'])->setStatusCode(403);
        }

        $categoryId = (int) $this->request->getPost('category_id');
        $category   = $categoryId > 0 ? (new CategoryModel())->find($categoryId) : null;
        if (! $category) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Unknown category'])->setStatusCode(422);
        }

        $newsModel = new NewsModel();
        if (! $newsModel->find((int) $id)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Draft not found'])->setStatusCode(404);
        }

        $newsModel->update((int) $id, ['category_id' => $categoryId]);

        return $this->response->setJSON(['ok' => true, 'category' => $category['name']]);
    }

    /**
     * Copy the suggested source image into our own uploads and make it the
     * lead image. Deliberately a human action — the pipeline only ever
     * records the suggestion.
     */
    public function adoptImage($id)
    {
        if ($denied = $this->denyUnlessTriager()) {
            return $denied;
        }

        $newsModel = new NewsModel();
        $news      = $newsModel->find((int) $id);
        if (! $news) {
            return $this->backWith('error', 'Draft not found.');
        }

        $url = (string) ($news['suggested_image_url'] ?? '');
        if (! preg_match('~^https?://~i', $url)) {
            return $this->backWith('error', 'This draft has no suggested image.');
        }

        try {
            [$bytes, $mime] = $this->downloadImage($url);
        } catch (\RuntimeException $e) {
            return $this->backWith('error', 'Could not fetch the suggested image: ' . esc($e->getMessage()));
        }

        $uploadPath = FCPATH . 'public/uploads/news/'; // same location ImageUpload::upload uses
        if (! is_dir($uploadPath) && ! mkdir($uploadPath, 0755, true)) {
            return $this->backWith('error', 'Upload directory is missing and could not be created.');
        }

        $filename = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . self::IMAGE_EXTENSIONS[$mime];
        if (file_put_contents($uploadPath . $filename, $bytes) === false) {
            return $this->backWith('error', 'Could not save the image file.');
        }

        $relativePath = 'public/uploads/news/' . $filename;
        $imageInfo    = @getimagesize($uploadPath . $filename);
        $caption      = ! empty($news['source']) ? 'সূত্র: ' . $news['source'] : null;

        (new ImageModel())->insert([
            'image_name'        => $news['title'],
            'image_path'        => $relativePath,
            'original_filename' => basename((string) parse_url($url, PHP_URL_PATH)) ?: $filename,
            'file_size'         => strlen($bytes),
            'mime_type'         => $mime,
            'width'             => $imageInfo[0] ?? null,
            'height'            => $imageInfo[1] ?? null,
            'caption'           => $caption,
            'alt_text'          => $news['title'],
            'uploaded_by'       => session('user_id') ?: 1,
        ]);

        $newsModel->update((int) $id, [
            'image_url'      => $relativePath,
            'image_caption'  => $news['image_caption'] ?: $caption,
            'image_alt_text' => $news['image_alt_text'] ?: $news['title'],
        ]);

        return $this->backWith('success', 'Lead image set from the source for: ' . esc($news['title']) . '. Check the caption/credit before publishing.');
    }

    // ------------------------------------------------------------------ helpers

    /**
     * @return array{0: string, 1: string} [bytes, mime]
     */
    private function downloadImage(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'timeout'         => 15,
                'follow_location' => 1,
                'max_redirects'   => 3,
                'user_agent'      => 'Mozilla/5.0 (compatible; BarindPost-Editor/1.0)',
                'ignore_errors'   => true,
            ],
        ]);

        $bytes = @file_get_contents($url, false, $context, 0, self::MAX_IMAGE_BYTES + 1);
        if ($bytes === false || $bytes === '') {
            throw new \RuntimeException('download failed');
        }
        if (strlen($bytes) > self::MAX_IMAGE_BYTES) {
            throw new \RuntimeException('image is larger than 8 MB');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes) ?: '';
        if (! isset(self::IMAGE_EXTENSIONS[$mime])) {
            throw new \RuntimeException('not an image we accept (' . ($mime ?: 'unknown type') . ')');
        }

        return [$bytes, $mime];
    }

    private function denyUnlessTriager()
    {
        if (in_array(session('user_role'), self::TRIAGE_ROLES, true)) {
            return null;
        }

        session()->setFlashdata('error', 'Only editors and administrators can review incoming drafts.');

        return redirect()->to('/admin/news');
    }

    private function backWith(string $type, string $message)
    {
        session()->setFlashdata($type, $message);

        $referer = $this->request->getHeaderLine('Referer');

        return redirect()->to($referer !== '' && str_contains($referer, '/admin/incoming') ? $referer : '/admin/incoming');
    }
}
