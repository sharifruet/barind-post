<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class Migration_create_news extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'subtitle' => [
                'type' => 'VARCHAR',
                'constraint' => '500',
                'null' => true,
            ],
            'lead_text' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'content' => [
                'type' => 'TEXT',
            ],
            'author_id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true,
            ],
            'category_id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['draft', 'published', 'archived'],
                'default' => 'draft',
            ],
            'featured' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'published_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'image_url' => [
                'type' => 'VARCHAR',
                'constraint' => '500',
                'null' => true,
            ],
            'slug' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'source' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'dateline' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'word_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'language' => [
                'type' => 'VARCHAR',
                'constraint' => '10',
                'default' => 'en',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('author_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('news');
    }

    public function down()
    {
        $this->forge->dropTable('news');
    }
} 