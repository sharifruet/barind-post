<?php

namespace App\Models;

use CodeIgniter\Model;

class KickerModel extends Model
{
    protected $table = 'kickers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['text', 'color', 'usage_count'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'text' => 'required|max_length[255]|is_unique[kickers.text,id,{id}]',
        'color' => 'required|max_length[7]'
    ];
    protected $validationMessages = [
        'text' => [
            'required' => 'Kicker text is required',
            'max_length' => 'Kicker text cannot exceed 255 characters',
            'is_unique' => 'This kicker text already exists'
        ],
        'color' => [
            'required' => 'Kicker color is required',
            'max_length' => 'Invalid color format'
        ]
    ];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get all kickers ordered by usage count (descending) and then by text
     */
    public function getAllKickers()
    {
        return $this->orderBy('usage_count', 'DESC')
                   ->orderBy('text', 'ASC')
                   ->findAll();
    }

    /**
     * Get kicker by text
     */
    public function getByText($text)
    {
        return $this->where('text', $text)->first();
    }

    /**
     * Create or update kicker
     */
    public function createOrUpdate($text, $color)
    {
        $existing = $this->getByText($text);
        
        if ($existing) {
            // Update existing kicker
            return $this->update($existing['id'], ['color' => $color]);
        } else {
            // Create new kicker
            return $this->insert([
                'text' => $text,
                'color' => $color,
                'usage_count' => 0
            ]);
        }
    }

    /**
     * Increment usage count for a kicker
     */
    public function incrementUsage($text)
    {
        $kicker = $this->getByText($text);
        if ($kicker) {
            return $this->update($kicker['id'], [
                'usage_count' => $kicker['usage_count'] + 1
            ]);
        }
        return false;
    }

    /**
     * Decrement usage count for a kicker
     */
    public function decrementUsage($text)
    {
        $kicker = $this->getByText($text);
        if ($kicker && $kicker['usage_count'] > 0) {
            return $this->update($kicker['id'], [
                'usage_count' => $kicker['usage_count'] - 1
            ]);
        }
        return false;
    }

    /**
     * Get most used kickers
     */
    public function getMostUsed($limit = 10)
    {
        return $this->where('usage_count >', 0)
                   ->orderBy('usage_count', 'DESC')
                   ->limit($limit)
                   ->find();
    }

    /**
     * Search kickers by text
     */
    public function search($query)
    {
        return $this->like('text', $query)
                   ->orderBy('usage_count', 'DESC')
                   ->orderBy('text', 'ASC')
                   ->findAll();
    }

    /**
     * Delete kicker if usage count is 0
     */
    public function deleteIfUnused($id)
    {
        $kicker = $this->find($id);
        if ($kicker && $kicker['usage_count'] == 0) {
            return $this->delete($id);
        }
        return false;
    }
}
