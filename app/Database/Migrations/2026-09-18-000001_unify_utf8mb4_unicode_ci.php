<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * First incremental migration under the migrations-first workflow.
 *
 * roles, users, news_tags, news_views, cities were created without an explicit collation, so they took
 * whatever the database default was (utf8mb4_0900_ai_ci on the Docker DB,
 * utf8mb4_unicode_ci elsewhere) — every other table declares utf8mb4_unicode_ci.
 * The baseline now pins it; this converts databases that already exist.
 */
class UnifyUtf8mb4UnicodeCi20260918000001 extends Migration
{
    private const TABLES = ['roles', 'users', 'news_tags', 'news_views', 'cities'];

    public function up()
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            return;
        }
        foreach (self::TABLES as $table) {
            $this->db->query("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
    }

    public function down()
    {
        // Intentionally a no-op: the previous collation depended on the server default.
    }
}
