<?php

namespace App\Database\Migrations;

use App\Database\SqlFile;
use CodeIgniter\Database\Migration;

/**
 * Baseline: the complete schema as of 2026-09-18, replacing the earlier partial
 * migrations (which had never run anywhere) and the hand-maintained dbscript.sql.
 *
 * Every statement is CREATE TABLE IF NOT EXISTS, so running this against a
 * database that was created from the old dbscript.sql is a no-op that simply
 * records the baseline — after which new migrations apply incrementally.
 *
 * MySQL only (the DDL is MySQL 8 / utf8mb4). The `tests` group is SQLite in
 * memory and gets nothing from this; tests are database-free by convention.
 */
class BaselineSchema20260918000000 extends Migration
{
    public const SCHEMA_FILE = APPPATH . 'Database/Schema/baseline.sql';

    public function up()
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            log_message('info', 'BaselineSchema skipped: MySQL-only DDL (driver is ' . $this->db->DBDriver . ')');

            return;
        }

        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        try {
            SqlFile::run($this->db, self::SCHEMA_FILE);
        } finally {
            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    public function down()
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            return;
        }

        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        try {
            foreach (array_reverse(SqlFile::createdTables(self::SCHEMA_FILE)) as $table) {
                $this->forge->dropTable($table, true);
            }
        } finally {
            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
}
