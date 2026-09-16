<?php

namespace App\Models;

use CodeIgniter\Model;

class ReporterRoleModel extends Model
{
    protected $table = 'reporter_roles';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'description', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]|is_unique[reporter_roles.name,id,{id}]',
        'description' => 'permit_empty|max_length[500]'
    ];

    /**
     * Get all active reporter roles
     */
    public function getActiveRoles()
    {
        return $this->where('is_active', 1)->findAll();
    }

    /**
     * Get reporter roles assigned to a specific user
     */
    public function getUserRoles($userId)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('user_reporter_roles urr');
        $builder->select('rr.*');
        $builder->join('reporter_roles rr', 'rr.id = urr.reporter_role_id');
        $builder->where('urr.user_id', $userId);
        $builder->where('rr.is_active', 1);
        
        return $builder->get()->getResultArray();
    }

    /**
     * Assign reporter roles to a user
     */
    public function assignRolesToUser($userId, $roleIds)
    {
        $db = \Config\Database::connect();
        
        // First, remove existing assignments
        $db->table('user_reporter_roles')->where('user_id', $userId)->delete();
        
        // Then add new assignments
        $data = [];
        foreach ($roleIds as $roleId) {
            $data[] = [
                'user_id' => $userId,
                'reporter_role_id' => $roleId
            ];
        }
        
        if (!empty($data)) {
            return $db->table('user_reporter_roles')->insertBatch($data);
        }
        
        return true;
    }

    /**
     * Get user IDs who have a specific reporter role
     */
    public function getUsersWithRole($roleId)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('user_reporter_roles urr');
        $builder->select('u.id, u.name, u.email');
        $builder->join('users u', 'u.id = urr.user_id');
        $builder->where('urr.reporter_role_id', $roleId);
        
        return $builder->get()->getResultArray();
    }

    /**
     * Check if a user has a specific reporter role
     */
    public function userHasRole($userId, $roleId)
    {
        $db = \Config\Database::connect();
        
        $result = $db->table('user_reporter_roles')
                    ->where('user_id', $userId)
                    ->where('reporter_role_id', $roleId)
                    ->get()
                    ->getRow();
        
        return $result !== null;
    }

    /**
     * Get reporter role by name
     */
    public function getRoleByName($name)
    {
        return $this->where('name', $name)->where('is_active', 1)->first();
    }
}
