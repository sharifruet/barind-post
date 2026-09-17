<?php

namespace App\Database\Seeds;

use App\Database\SqlFile;
use CodeIgniter\Database\Seeder;

/**
 * Sample content for a fresh install (categories, roles, users, kickers, tags,
 * reporter roles, cities, sample news) from app/Database/Schema/sample_data.sql.
 *
 *   php spark migrate && php spark db:seed SampleDataSeeder
 *
 * Refuses to run on a database that already has content, so it can never
 * duplicate or clobber a real newsroom's data.
 */
class SampleDataSeeder extends Seeder
{
    public const DATA_FILE = APPPATH . 'Database/Schema/sample_data.sql';

    public function run()
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            echo "SampleDataSeeder: MySQL only, skipped.\n";

            return;
        }

        // The baseline migration creates tables with raw SQL, which does not
        // refresh the connection's cached table list; re-read it before checking.
        $this->db->resetDataCache();

        if (! $this->db->tableExists('news') || ! $this->db->tableExists('users')) {
            echo "SampleDataSeeder: schema missing — run `php spark migrate` first.\n";

            return;
        }

        if ($this->db->table('news')->countAllResults() > 0 || $this->db->table('users')->countAllResults() > 0) {
            echo "SampleDataSeeder: database already has news/users — nothing seeded.\n";

            return;
        }

        $this->db->query('SET NAMES utf8mb4');
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        try {
            $n = SqlFile::run($this->db, self::DATA_FILE);
        } finally {
            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
        }

        echo "SampleDataSeeder: {$n} statements executed.\n";
    }
}
