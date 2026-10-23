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

    /** Articles per page in the picker. */
    public const PER_PAGE = 10;

    public function photoCardGenerator()
    {
        $site   = config('SiteInfo');
        $host   = (string) preg_replace('/^www\./', '', (string) parse_url(base_url(), PHP_URL_HOST));
        $handle = basename(rtrim((string) $site->facebook, '/'));

        return view('admin/photo_card_generator', [
            'articles' => $this->articlePage(1) + ['url' => site_url('admin/photo-card-generator/articles')],
            'brand'    => [
                'name'   => $site->siteName,
                'url'    => $host !== '' ? $host : 'barindpost.com',
                'handle' => '@' . ($handle !== '' ? $handle : 'barindpost'),
                'logo'   => asset_url('logo.png'),
            ],
            'fonts'    => array_combine(self::FONTS, array_map(static fn ($f) => asset_url('assets/fonts/' . $f), self::FONTS)),
            'saveUrl'  => site_url('admin/photo-card-generator/save'),
            'facebook' => [
                'url'        => site_url('admin/photo-card-generator/facebook'),
                'configured' => config('Facebook')->isConfigured(),
                'page'       => (string) $site->facebook,
            ],
        ]);
    }

    /**
     * GET ?page=N → {items, page, pages, total}: one page of the article picker, newest first.
     */
    public function articles()
    {
        return $this->response->setJSON($this->articlePage((int) ($this->request->getGet('page') ?? 1)));
    }

    /**
     * Page $page (1-based, clamped to the last page) of non-archived articles, newest first,
     * with the fields the card needs.
     *
     * @return array{items: list<array<string, mixed>>, page: int, pages: int, total: int}
     */
    private function articlePage(int $page): array
    {
        $db    = \Config\Database::connect();
        $total = $db->table('news')->where('status !=', 'archived')->countAllResults();
        $pages = self::pageCount($total);
        $page  = min(max(1, $page), $pages);

        $rows = $db->table('news')
            ->select('news.id, news.title, news.subtitle, news.lead_text, news.image_url, news.status, news.slug, news.published_at, news.created_at, categories.name AS category_name')
            ->join('categories', 'categories.id = news.category_id', 'left')
            ->where('news.status !=', 'archived')
            ->orderBy('COALESCE(news.published_at, news.created_at)', 'DESC', false)
            ->orderBy('news.id', 'DESC')
            ->limit(self::PER_PAGE, ($page - 1) * self::PER_PAGE)
            ->get()
            ->getResultArray();

        $items = array_map(static fn (array $r): array => [
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

        return ['items' => $items, 'page' => $page, 'pages' => $pages, 'total' => $total];
    }

    /** Number of picker pages for $total articles; at least 1, so an empty list still has a page. */
    public static function pageCount(int $total): int
    {
        return max(1, (int) ceil($total / self::PER_PAGE));
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
        if (($error = $this->cardUploadError($file)) !== null) {
            return $this->response->setStatusCode(422)->setJSON(['error' => $error]);
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
            $out['rescraped']       = $this->askFacebookToRescrape($newsId);
        }

        return $this->response->setJSON($out);
    }

    /**
     * Facebook caches a link's preview (og:image) for weeks, so after a new share image is
     * saved it would keep showing the old one. Asking the Graph API to scrape the article
     * again (any valid token will do; the Page token is the one we have) makes the next
     * share — from the Share dialog or anywhere else — pick up the new card. Published
     * articles only: Facebook cannot read a draft.
     */
    private function askFacebookToRescrape(int $newsId): bool
    {
        $fb = config('Facebook');
        if (! $fb->isConfigured()) {
            return false;
        }
        $news = \Config\Database::connect()->table('news')->select('slug, status')->where('id', $newsId)->get()->getRowArray();
        if ($news === null || $news['status'] !== 'published') {
            return false;
        }

        try {
            $response = \Config\Services::curlrequest()->post(sprintf('https://graph.facebook.com/%s/', $fb->graphVersion), [
                'form_params' => [
                    'id'           => base_url('news/' . $news['slug']),
                    'scrape'       => 'true',
                    'access_token' => trim($fb->pageToken),
                ],
                'timeout'     => 20,
                'http_errors' => false,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Facebook re-scrape failed for news {id}: {msg}', ['id' => $newsId, 'msg' => $e->getMessage()]);

            return false;
        }
        if ($response->getStatusCode() >= 400) {
            log_message('error', 'Facebook re-scrape refused for news {id}: {body}', ['id' => $newsId, 'body' => substr((string) $response->getBody(), 0, 300)]);

            return false;
        }

        return true;
    }

    /**
     * POST image (PNG), news_id, message → {post_id, post_url}
     *
     * Publishes the card as a photo post on the Facebook Page (Graph API /{page}/photos,
     * the same call the n8n pipeline makes), captioned with the editor's message and the
     * article link. Server-side so the Page token never reaches the browser. Only
     * published articles: a draft's link would not open for readers.
     */
    public function postToFacebook()
    {
        if (session('user_role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Admin role required.']);
        }

        $fb = config('Facebook');
        if (! $fb->isConfigured()) {
            return $this->response->setStatusCode(503)->setJSON(['error' => 'Facebook posting is not set up: add facebook.pageId and facebook.pageToken to .env.']);
        }

        $file = $this->request->getFile('image');
        if (($error = $this->cardUploadError($file)) !== null) {
            return $this->response->setStatusCode(422)->setJSON(['error' => $error]);
        }

        $message = trim((string) $this->request->getPost('message'));
        if (mb_strlen($message) > 5000) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'The message is longer than 5,000 characters.']);
        }

        $news = \Config\Database::connect()->table('news')
            ->select('id, slug, status')
            ->where('id', (int) $this->request->getPost('news_id'))
            ->get()
            ->getRowArray();
        if ($news === null) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Pick an article first.']);
        }
        if ($news['status'] !== 'published') {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Publish the article first: its link would not open for readers yet.']);
        }

        $endpoint = sprintf('https://graph.facebook.com/%s/%s/photos', $fb->graphVersion, rawurlencode(trim($fb->pageId)));

        try {
            $response = \Config\Services::curlrequest()->post($endpoint, [
                'multipart' => [
                    'source'       => new \CURLFile($file->getTempName(), 'image/png', 'barindpost-' . $news['id'] . '.png'),
                    'message'      => self::facebookCaption($message, base_url('news/' . $news['slug'])),
                    'access_token' => trim($fb->pageToken),
                ],
                'timeout'     => 60,
                'http_errors' => false,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Facebook post failed for news {id}: {msg}', ['id' => $news['id'], 'msg' => $e->getMessage()]);

            return $this->response->setStatusCode(502)->setJSON(['error' => 'Could not reach Facebook. Try again in a moment.']);
        }

        $body = json_decode((string) $response->getBody(), true) ?: [];
        if ($response->getStatusCode() >= 400 || empty($body['post_id'] ?? $body['id'] ?? null)) {
            $reason = (string) ($body['error']['message'] ?? 'HTTP ' . $response->getStatusCode());
            log_message('error', 'Facebook rejected the post for news {id}: {msg}', ['id' => $news['id'], 'msg' => $reason]);

            return $this->response->setStatusCode(502)->setJSON(['error' => 'Facebook refused the post: ' . $reason]);
        }

        $postId = (string) ($body['post_id'] ?? $body['id']);
        log_message('info', 'Photo card for news {id} posted to Facebook as {post} by user {user}', ['id' => $news['id'], 'post' => $postId, 'user' => session('user_id')]);

        return $this->response->setJSON(['post_id' => $postId, 'post_url' => 'https://www.facebook.com/' . rawurlencode($postId)]);
    }

    /**
     * The Facebook caption: the editor's message, then the article link on its own line.
     * The link is appended here rather than typed into the message, so it is always the
     * article's real address.
     */
    public static function facebookCaption(string $message, string $articleUrl): string
    {
        $message = trim(preg_replace("/\r\n?/", "\n", $message));

        return ltrim($message . "\n\nবিস্তারিত: " . $articleUrl);
    }

    /** Why an uploaded card cannot be used, or null when it is a PNG of acceptable size. */
    private function cardUploadError(?\CodeIgniter\HTTP\Files\UploadedFile $file): ?string
    {
        if ($file === null || ! $file->isValid()) {
            return 'The card image is missing.';
        }
        if ($file->getSize() > 12 * 1024 * 1024) {
            return 'The card image is larger than 12 MB.';
        }
        $head = (string) file_get_contents($file->getTempName(), false, null, 0, 8);

        return $head === "\x89PNG\r\n\x1a\n" ? null : 'Only PNG cards can be saved.';
    }
}
