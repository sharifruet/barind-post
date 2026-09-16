<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSourceDedupToNews20260916000000 extends Migration
{
    public function up()
    {
        $this->forge->addColumn('news', [
            'source_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'after'      => 'source',
            ],
            'content_hash' => [
                'type'       => 'CHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'source_url',
            ],
        ]);

        // Forge has no ALTER-TABLE index helper for existing tables, so add these directly.
        $this->db->query('ALTER TABLE `news` ADD UNIQUE INDEX `uq_news_source_url` (`source_url`)');
        $this->db->query('ALTER TABLE `news` ADD INDEX `idx_news_content_hash` (`content_hash`)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE `news` DROP INDEX `uq_news_source_url`');
        $this->db->query('ALTER TABLE `news` DROP INDEX `idx_news_content_hash`');
        $this->forge->dropColumn('news', ['source_url', 'content_hash']);
    }
}
