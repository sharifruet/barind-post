<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['name' => 'reporter'],
            ['name' => 'sub-editor'],
            ['name' => 'editor'],
            ['name' => 'admin'],
        ];
        $this->db->table('roles')->insertBatch($data);
    }
} 