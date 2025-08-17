<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReporterFieldToNews extends Migration
{
    public function up()
    {
        $this->forge->addColumn('news', [
            'reporter' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'lead_text'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('news', 'reporter');
    }
}
