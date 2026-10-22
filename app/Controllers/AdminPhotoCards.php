<?php

namespace App\Controllers;

/**
 * Admin → Photo Cards: social cards for Facebook, Instagram and WhatsApp.
 *
 * The card is drawn in the browser (public/assets/js/photo-card.js), where Bangla is
 * shaped correctly; this controller only lists the articles and stores the finished
 * PNG. Saving with "use as share image" makes it the article's Open Graph image
 * (public/uploads/photo_cards/og-{id}.png, served by /og/{id}.png). Route-level `role:admin`.
 */
class AdminPhotoCards extends BaseAdminController
{
    private const FONTS = [
        'NotoSerifBengali-Bold.ttf', 'NotoSerifBengali-ExtraBold.ttf',
        'NotoSansBengali-Regular.ttf', 'NotoSansBengali-SemiBold.ttf', 'NotoSansBengali-Bold.ttf',
    ];

    public function photoCardGenerator()
    {
        $rows = \Config\Database::connect()->table('news')
            ->select('news.id, news.title, news.subtitle, news.lead_text, news.image_url, news.status, news.slug, news.published_at, news.created_at, categories.name AS category_name')
            ->join('categories', 'categories.id = news.category_id', 'left')
            ->where('news.status !=', 'archived')
            ->orderBy('COALESCE(news.published_at, news.created_at)', 'DESC', false)
            ->limit(150)
            ->get()
            ->getResultArray();

        $articles = array_map(static fn (array $r): array => [
            'id'       => (int) $r['id'],
            'title'    => (string) $r['title'],
            'subtitle' => (string) ($r['subtitle'] ?? ''),
            'points'   => key_points($r['lead_text'] ?? null),
            'category' => trim((string) ($r['category_name'] ?? '')),
            'status'   => (string) $r['status'],
            'date'     => (string) ($r['published_at'] ?: $r['created_at']),
            // Site-root path; the page resolves it on its own origin so the canvas stays exportable.
            'image'    => ! empty($r['image_url']) ? (preg_match('~^https?://~i', $r['image_url']) ? $r['image_url'] : '/' . ltrim($r['image_url'], '/')) : null,
            'url'      => base_url('news/' . $r['slug']),
        ], $rows);

        $site   = config('SiteInfo');
        $host   = (string) preg_replace('/^www\./', '', (string) parse_url(base_url(), PHP_URL_HOST));
        $handle = basename(rtrim((string) $site->facebook, '/'));

        return view('admin/photo_card_generator', [
            'articles' => $articles,
            'brand'    => [
                'name'   => $site->siteName,
                'url'    => $host !== '' ? $host : 'barindpost.com',
                'handle' => '@' . ($handle !== '' ? $handle : 'barindpost'),
                'logo'   => asset_url('logo.png'),
            ],
            'fonts'    => array_combine(self::FONTS, array_map(static fn ($f) => asset_url('assets/fonts/' . $f), self::FONTS)),
            'saveUrl'  => site_url('admin/photo-card-generator/save'),
        ]);
    }

    /**
     * POST image (PNG), news_id, format, template, share_image=1
     * → {url, file, share_image_url?}
     */
    public function save()
    {
        if (session('user_role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Admin role required.']);
        }

        $file = $this->request->getFile('image');
        if ($file === null || ! $file->isValid()) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'The card image is missing.']);
        }
        if ($file->getSize() > 12 * 1024 * 1024) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'The card image is larger than 12 MB.']);
        }
        $head = (string) file_get_contents($file->getTempName(), false, null, 0, 8);
        if ($head !== "\x89PNG\r\n\x1a\n") {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Only PNG cards can be saved.']);
        }

        $format   = (string) preg_replace('/[^a-z]/', '', strtolower((string) $this->request->getPost('format')));
        $template = (string) preg_replace('/[^a-z]/', '', strtolower((string) $this->request->getPost('template')));
        $newsId   = (int) $this->request->getPost('news_id');

        $dir = ROOTPATH . 'public/uploads/photo_cards/';
        if (! is_dir($dir) && ! mkdir($dir, 0755, true)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Cannot create public/uploads/photo_cards.']);
        }
        $name = sprintf('card-%d-%s-%s-%s-%s.png', $newsId, $template ?: 'card', $format ?: 'custom', date('Ymd-His'), bin2hex(random_bytes(3)));
        if (! $file->move($dir, $name)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Could not store the card.']);
        }

        $out = ['url' => get_image_url('public/uploads/photo_cards/' . $name), 'file' => $name];

        if ($newsId > 0 && (string) $this->request->getPost('share_image') === '1') {
            copy($dir . $name, photo_card_share_path($newsId));
            foreach (glob(WRITEPATH . 'og/' . $newsId . '-*.png') ?: [] as $generated) {
                @unlink($generated);   // the GD fallback's cached card for this article
            }
            purge_public_cache();
            $out['share_image_url'] = og_image_url(['id' => $newsId, 'image_url' => null]);
        }

        return $this->response->setJSON($out);
    }
}
