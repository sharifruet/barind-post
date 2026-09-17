<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Indexes for the queries every public page runs.
 *
 * - news (status, published_at): home, latest, tag and search listings all filter
 *   status = 'published' and sort by published_at DESC.
 * - news (category_id, status, published_at): section pages add category_id to
 *   the same shape.
 * - news_views (viewed_at): "views today" on the dashboard, "most read in N days"
 *   and the 30-minute repeat-view check in trackView().
 *
 * Note: a predicate like DATE(viewed_at) = ? cannot use an index; callers must
 * compare viewed_at against a range (see Admin::dashboard).
 */
class AddHotQueryIndexes20260918000002 extends Migration
{
    private const INDEXES = [
        'news'       => ['idx_news_status_published' => '(`status`, `published_at`)', 'idx_news_category_status_published' => '(`category_id`, `status`, `published_at`)'],
        'news_views' => ['idx_news_views_viewed_at' => '(`viewed_at`)'],
    ];

    public function up()
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            return;
        }
        foreach (self::INDEXES as $table => $indexes) {
            foreach ($indexes as $name => $columns) {
                $this->db->query("ALTER TABLE `{$table}` ADD INDEX `{$name}` {$columns}");
            }
        }
    }

    public function down()
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            return;
        }
        foreach (self::INDEXES as $table => $indexes) {
            foreach (array_keys($indexes) as $name) {
                $this->db->query("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
            }
        }
    }
}
