<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Automation drafts arrive without an image. The pipeline records the source
 * page's og:image / first article image here as a *suggestion* for the editor
 * (shown in the Incoming queue, adopted with one click) — never as the
 * published image, since rights to it are not ours.
 */
class AddSuggestedImageToNews20260917000000 extends Migration
{
    public function up()
    {
        $this->forge->addColumn('news', [
            'suggested_image_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'after'      => 'content_hash',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('news', ['suggested_image_url']);
    }
}
