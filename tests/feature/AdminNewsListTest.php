<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;

/**
 * /admin/news is paged: 100 rows per page, newest first, stat tiles over the
 * whole set. The test builds the handful of tables the page touches in the
 * in-memory SQLite test database, so it needs no external DB and runs in CI.
 *
 * @internal
 */
final class AdminNewsListTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private const ADMIN    = ['logged_in' => true, 'user_id' => 1, 'user_name' => 'Admin', 'user_role' => 'admin'];
    private const REPORTER = ['logged_in' => true, 'user_id' => 2, 'user_name' => 'Reporter', 'user_role' => 'reporter'];

    private const TABLES = ['news', 'kickers', 'categories', 'users', 'news_views', 'news_tags'];

    /**
     * The in-memory connection is shared by every test in the process and other
     * tests build their own (narrower) `news`, so build fresh here and drop in
     * tearDown rather than relying on IF NOT EXISTS.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $db = Database::connect();
        $t  = static fn (string $name): string => $db->prefixTable($name);

        $this->dropTables();

        $db->query("CREATE TABLE {$t('news')} (
            id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, content TEXT, slug TEXT, status TEXT DEFAULT 'draft',
            author_id INTEGER DEFAULT 1, category_id INTEGER, kicker_id INTEGER, featured INTEGER DEFAULT 0,
            image_url TEXT, source_url TEXT, published_at TEXT, created_at TEXT, updated_at TEXT)");
        $db->query("CREATE TABLE {$t('kickers')} (id INTEGER PRIMARY KEY AUTOINCREMENT, text TEXT, color TEXT, usage_count INTEGER DEFAULT 0)");
        $db->query("CREATE TABLE {$t('categories')} (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, slug TEXT)");
        $db->query("CREATE TABLE {$t('users')} (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)");
        $db->query("CREATE TABLE {$t('news_views')} (id INTEGER PRIMARY KEY AUTOINCREMENT, news_id INTEGER, viewer_ip TEXT, viewed_at TEXT)");
        $db->query("CREATE TABLE {$t('news_tags')} (news_id INTEGER, tag_id INTEGER)");

        $db->table('users')->insertBatch([['id' => 1, 'name' => 'Admin'], ['id' => 2, 'name' => 'Reporter']]);
        $db->table('categories')->insert(['id' => 1, 'name' => 'জাতীয়', 'slug' => 'national']);
    }

    protected function tearDown(): void
    {
        $this->dropTables();

        parent::tearDown();
    }

    private function dropTables(): void
    {
        $db = Database::connect();
        foreach (self::TABLES as $table) {
            $db->query('DROP TABLE IF EXISTS ' . $db->prefixTable($table));
        }
    }

    /**
     * Inserts $count articles; article N is the Nth newest-from-the-bottom, i.e.
     * "Story 130" is the newest of 130. The first $reporterOwned belong to user 2.
     */
    private function seedNews(int $count, int $reporterOwned = 0): void
    {
        $rows = [];
        for ($i = 1; $i <= $count; $i++) {
            $rows[] = [
                'id'          => $i,
                'title'       => "Story {$i}",
                'slug'        => "story-{$i}",
                'status'      => $i % 3 === 0 ? 'draft' : 'published',
                'author_id'   => $i <= $reporterOwned ? 2 : 1,
                'category_id' => 1,
                'created_at'  => date('Y-m-d H:i:s', strtotime('2026-01-01') + $i * 60),
            ];
        }
        Database::connect()->table('news')->insertBatch($rows);
    }

    private function rowCount(string $html): int
    {
        return preg_match_all('/<tr data-search=/', $html);
    }

    /** Raw HTML as sent; TestResponse::getBody() would hand back DOMParser's entity-encoded copy. */
    private function page(array $session, string $uri): string
    {
        return (string) $this->withSession($session)->get($uri)->response()->getBody();
    }

    public function testFirstPageShowsTheNewestHundred(): void
    {
        $this->seedNews(130);

        $result = $this->withSession(self::ADMIN)->get('admin/news');
        $html   = (string) $result->response()->getBody();

        $result->assertStatus(200);
        $this->assertSame(100, $this->rowCount($html));
        $this->assertStringContainsString('>Story 130<', $html);
        $this->assertStringContainsString('>Story 31<', $html);
        $this->assertStringNotContainsString('>Story 30<', $html);
        $this->assertStringContainsString('Showing 1–100 of 130', $html);
        $this->assertStringContainsString('/admin/news?page=2', $html);
    }

    public function testStatTilesCountEverythingNotJustThePage(): void
    {
        $this->seedNews(130); // 43 drafts (multiples of 3), 87 published

        $html = $this->page(self::ADMIN, 'admin/news');

        $this->assertMatchesRegularExpression('~All</div><div class="stat__value">130<~', $html);
        $this->assertMatchesRegularExpression('~Published</div><div class="stat__value">87<~', $html);
        $this->assertMatchesRegularExpression('~Drafts</div><div class="stat__value">43<~', $html);
    }

    public function testSecondPageHasTheRest(): void
    {
        $this->seedNews(130);

        $html = $this->page(self::ADMIN, 'admin/news?page=2');

        $this->assertSame(30, $this->rowCount($html));
        $this->assertStringContainsString('>Story 30<', $html);
        $this->assertStringContainsString('>Story 1<', $html);
        $this->assertStringNotContainsString('>Story 31<', $html);
        $this->assertStringContainsString('Showing 101–130 of 130', $html);
    }

    public function testPagesPastTheEndClampToTheLastPage(): void
    {
        $this->seedNews(130);

        $html = $this->page(self::ADMIN, 'admin/news?page=99');

        $this->assertSame(30, $this->rowCount($html));
        $this->assertStringContainsString('Showing 101–130 of 130', $html);
    }

    public function testASinglePageHasNoPager(): void
    {
        $this->seedNews(5);

        $html = $this->page(self::ADMIN, 'admin/news');

        $this->assertSame(5, $this->rowCount($html));
        $this->assertStringNotContainsString('class="pager"', $html);
    }

    public function testReporterSeesAndCountsOnlyTheirOwnArticles(): void
    {
        $this->seedNews(130, 7);

        $html = $this->page(self::REPORTER, 'admin/news');

        $this->assertSame(7, $this->rowCount($html));
        $this->assertStringContainsString('>Story 7<', $html);
        $this->assertStringNotContainsString('>Story 8<', $html);
        $this->assertMatchesRegularExpression('~All</div><div class="stat__value">7<~', $html);
    }

    public function testDeletingFromALaterPageReturnsToThatPage(): void
    {
        $this->seedNews(130);

        $result = $this->withSession(self::ADMIN)->post('admin/news/delete/20', ['page' => 2, csrf_token() => csrf_hash()]);

        $result->assertRedirectTo('/admin/news?page=2');
        $this->assertSame(129, Database::connect()->table('news')->countAllResults());
    }
}
