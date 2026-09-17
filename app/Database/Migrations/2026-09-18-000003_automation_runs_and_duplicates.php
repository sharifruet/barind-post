<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Newsroom visibility for the automation pipeline:
 *
 * - automation_runs: one row per collector run (or error), posted by n8n to
 *   POST /api/v1/automation/runs and shown at the top of the Incoming queue.
 * - news.source_title: the headline as the source published it (the AI
 *   rewrites `title`), used for cross-source duplicate detection.
 * - news.possible_duplicate_of / duplicate_score: set at intake when a recent
 *   article's title is similar enough — flagged in the queue, never auto-merged.
 */
class AutomationRunsAndDuplicates20260918000003 extends Migration
{
    public function up()
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            return;
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS `automation_runs` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `workflow` VARCHAR(120) NOT NULL,
            `status` ENUM('success','error') NOT NULL DEFAULT 'success',
            `created_count` INT UNSIGNED NOT NULL DEFAULT 0,
            `duplicate_count` INT UNSIGNED NOT NULL DEFAULT 0,
            `error_count` INT UNSIGNED NOT NULL DEFAULT 0,
            `summary` JSON NULL,
            `message` TEXT NULL,
            `execution_id` VARCHAR(64) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_automation_runs_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query('ALTER TABLE `news`
            ADD COLUMN `source_title` VARCHAR(500) NULL AFTER `source_url`,
            ADD COLUMN `possible_duplicate_of` INT UNSIGNED NULL AFTER `content_hash`,
            ADD COLUMN `duplicate_score` TINYINT UNSIGNED NULL AFTER `possible_duplicate_of`');
    }

    public function down()
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            return;
        }
        $this->db->query('ALTER TABLE `news` DROP COLUMN `source_title`, DROP COLUMN `possible_duplicate_of`, DROP COLUMN `duplicate_score`');
        $this->forge->dropTable('automation_runs', true);
    }
}
