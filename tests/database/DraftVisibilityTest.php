<?php

use App\Models\NewsModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Unpublished articles must not be reachable from the public site.
 *
 * This is load-bearing, not hygiene. The automation publish gate
 * (Api\NewsController::publishGateFailures) holds a suspect article back by
 * leaving it `draft`, and the incoming queue discards one by setting it
 * `archived`. Both are only a safety measure if "not published" also means
 * "not served". For a while it did not: PublicSite::news() loaded the article
 * through NewsModel::getNewsBySlug(), which carried no status condition, so
 * every held-back draft stayed readable by anyone with the URL — and the slug
 * comes back in the API response the automation itself records.
 *
 * Admin code that legitimately needs unpublished rows loads them by id through
 * findWithKicker(), which is left unfiltered on purpose; that path sits behind
 * AdminAuthFilter.
 *
 * The suite has no MySQL, so the two tables the query touches are built here.
 * What is under test is the query's status condition, which does not depend on
 * the rest of the real schema; SchemaBaselineTest covers schema drift.
 *
 * @internal
 */
final class DraftVisibilityTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = false;
    protected $refresh = false;

    /** @var array<string, int> status => inserted id */
    private array $ids = [];

    protected function setUp(): void
    {
        parent::setUp();

        $forge = \Config\Database::forge($this->db);

        $forge->addField([
            'id'        => ['type' => 'INTEGER', 'auto_increment' => true],
            'title'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'content'   => ['type' => 'TEXT'],
            'slug'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'    => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
            'kicker_id' => ['type' => 'INTEGER', 'null' => true],
            'author_id' => ['type' => 'INTEGER', 'default' => 1],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('news', true);

        $forge->addField([
            'id'    => ['type' => 'INTEGER', 'auto_increment' => true],
            'text'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'color' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('kickers', true);

        foreach (['published', 'draft', 'archived'] as $status) {
            $this->db->table('news')->insert([
                'title'   => 'দৃশ্যমানতা পরীক্ষা ' . $status,
                'content' => 'পরীক্ষার জন্য তৈরি সংবাদ।',
                'slug'    => 'visibility-' . $status,
                'status'  => $status,
            ]);

            $this->ids[$status] = (int) $this->db->insertID();
        }
    }

    public function testPublicSlugLookupReturnsPublishedOnly(): void
    {
        $model = new NewsModel();

        $this->assertIsArray(
            $model->getNewsBySlug('visibility-published'),
            'A published article must still be reachable by slug.'
        );

        $this->assertNull(
            $model->getNewsBySlug('visibility-draft'),
            'A draft held back by the publish gate must not be reachable by slug.'
        );

        $this->assertNull(
            $model->getNewsBySlug('visibility-archived'),
            'An article discarded from the incoming queue must not be reachable by slug.'
        );
    }

    public function testTitleFallbackAlsoReturnsPublishedOnly(): void
    {
        // PublicSite::news() falls back to a title lookup when the slug misses,
        // so that second path needs the same condition.
        $model = new NewsModel();

        $this->assertIsArray($model->getNewsByTitle('দৃশ্যমানতা পরীক্ষা published'));
        $this->assertNull($model->getNewsByTitle('দৃশ্যমানতা পরীক্ষা draft'));
        $this->assertNull($model->getNewsByTitle('দৃশ্যমানতা পরীক্ষা archived'));
    }

    public function testAdminLookupByIdStillSeesUnpublished(): void
    {
        $model = new NewsModel();

        $this->assertIsArray(
            $model->findWithKicker($this->ids['draft']),
            'The admin edit screen must still be able to load a draft by id.'
        );
    }
}
