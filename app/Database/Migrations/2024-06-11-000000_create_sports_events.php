<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_create_sports_events extends Migration
{
    public function up()
    {
        // Sports events (tournaments / series)
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'custom_url'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'title_bn'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'title_en'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'sport_profile' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'football'],
            'description_bn' => ['type' => 'TEXT', 'null' => true],
            'banner_image' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'logo_image'  => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'news_tag_slug' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'start_date'  => ['type' => 'DATE', 'null' => true],
            'end_date'    => ['type' => 'DATE', 'null' => true],
            'status'      => ['type' => 'ENUM', 'constraint' => ['draft', 'active', 'archived'], 'default' => 'draft'],
            'show_in_nav' => ['type' => 'BOOLEAN', 'default' => false],
            'show_homepage_widget' => ['type' => 'BOOLEAN', 'default' => false],
            'config'      => ['type' => 'JSON', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addUniqueKey('custom_url');
        $this->forge->createTable('sports_events', true);

        // Participants library (teams)
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name_bn'    => ['type' => 'VARCHAR', 'constraint' => 150],
            'name_en'    => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'short_code' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'flag_url'   => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'type'       => ['type' => 'ENUM', 'constraint' => ['team', 'individual'], 'default' => 'team'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('sports_participants', true);

        // Teams registered in an event
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'event_id'       => ['type' => 'INT', 'unsigned' => true],
            'participant_id' => ['type' => 'INT', 'unsigned' => true],
            'group_name'     => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'seed'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['event_id', 'participant_id']);
        $this->forge->addForeignKey('event_id', 'sports_events', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('participant_id', 'sports_participants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sports_event_entries', true);

        // Venues / stadiums
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name_bn'    => ['type' => 'VARCHAR', 'constraint' => 200],
            'name_en'    => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'city'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'country'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'capacity'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'image_url'  => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('sports_venues', true);

        // Matches / fixtures
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'event_id'         => ['type' => 'INT', 'unsigned' => true],
            'participant_a_id' => ['type' => 'INT', 'unsigned' => true],
            'participant_b_id' => ['type' => 'INT', 'unsigned' => true],
            'venue_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'kickoff_at'       => ['type' => 'DATETIME', 'null' => true],
            'stage'            => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'group'],
            'group_name'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'match_slug'       => ['type' => 'VARCHAR', 'constraint' => 200],
            'status'           => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'scheduled'],
            'summary_bn'       => ['type' => 'TEXT', 'null' => true],
            'news_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'sport_data'       => ['type' => 'JSON', 'null' => true],
            'referee'          => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'attendance'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['event_id', 'match_slug']);
        $this->forge->addForeignKey('event_id', 'sports_events', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('participant_a_id', 'sports_participants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('participant_b_id', 'sports_participants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('venue_id', 'sports_venues', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('sports_matches', true);

        // Match timeline events (goals, cards, wickets, etc.)
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'match_id'       => ['type' => 'INT', 'unsigned' => true],
            'event_minute'   => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'period'         => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'event_type'     => ['type' => 'VARCHAR', 'constraint' => 50],
            'participant_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'player_name'    => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'detail'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'sort_order'     => ['type' => 'INT', 'default' => 0],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('match_id', 'sports_matches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('participant_id', 'sports_participants', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('sports_match_events', true);

        // Link news to events/matches
        if ($this->db->tableExists('news')) {
            $fields = [];
            if (!$this->db->fieldExists('event_id', 'news')) {
                $fields['event_id'] = ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'category_id'];
            }
            if (!$this->db->fieldExists('match_id', 'news')) {
                $fields['match_id'] = ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'event_id'];
            }
            if (!empty($fields)) {
                $this->forge->addColumn('news', $fields);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('news')) {
            if ($this->db->fieldExists('match_id', 'news')) {
                $this->forge->dropColumn('news', 'match_id');
            }
            if ($this->db->fieldExists('event_id', 'news')) {
                $this->forge->dropColumn('news', 'event_id');
            }
        }
        $this->forge->dropTable('sports_match_events', true);
        $this->forge->dropTable('sports_matches', true);
        $this->forge->dropTable('sports_venues', true);
        $this->forge->dropTable('sports_event_entries', true);
        $this->forge->dropTable('sports_participants', true);
        $this->forge->dropTable('sports_events', true);
    }
}
