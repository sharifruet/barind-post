<?php

namespace App\Models;

use CodeIgniter\Model;

class CityModel extends Model
{
    protected $table = 'cities';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'latitude',
        'longitude'
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules = [
        'name' => 'required|max_length[255]',
        'latitude' => 'required|decimal',
        'longitude' => 'required|decimal'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'City name is required',
            'max_length' => 'City name cannot exceed 255 characters'
        ],
        'latitude' => [
            'required' => 'Latitude is required',
            'decimal' => 'Latitude must be a valid decimal number'
        ],
        'longitude' => [
            'required' => 'Longitude is required',
            'decimal' => 'Longitude must be a valid decimal number'
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
     * Get all cities ordered by name
     */
    public function getAllCities()
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Get city by name
     */
    public function getByName($name)
    {
        return $this->where('name', $name)->first();
    }

    /**
     * Get cities within a certain radius of given coordinates
     */
    public function getCitiesWithinRadius($latitude, $longitude, $radiusKm = 50)
    {
        // Using Haversine formula to calculate distance
        $sql = "SELECT *, 
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + sin(radians(?)) * 
                sin(radians(latitude)))) AS distance 
                FROM cities 
                HAVING distance < ? 
                ORDER BY distance";

        return $this->db->query($sql, [$latitude, $longitude, $latitude, $radiusKm])->getResultArray();
    }

    /**
     * Search cities by name
     */
    public function searchByName($searchTerm)
    {
        return $this->like('name', $searchTerm)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Get major cities (you can define criteria for major cities)
     */
    public function getMajorCities()
    {
        // Define major cities by ID or name - adjust as needed
        $majorCityNames = [
            'ঢাকা', 'চট্টগ্রাম', 'খুলনা', 'সিলেট', 'রাজশাহী', 
            'বরিশাল', 'রংপুর', 'কুমিল্লা', 'ময়মনসিংহ', 'গাজীপুর'
        ];

        return $this->whereIn('name', $majorCityNames)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
}
