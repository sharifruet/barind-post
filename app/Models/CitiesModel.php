<?php

namespace App\Models;

use CodeIgniter\Model;

class CitiesModel extends Model
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
            'max_length' => 'City name must not exceed 255 characters'
        ],
        'latitude' => [
            'required' => 'Latitude is required',
            'decimal' => 'Latitude must be a decimal number'
        ],
        'longitude' => [
            'required' => 'Longitude is required',
            'decimal' => 'Longitude must be a decimal number'
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
     * Get cities with prayer times data for a specific date
     */
    public function getCitiesWithPrayerTimes($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }

        $prayerTimesModel = new \App\Models\PrayerTimesModel();
        
        return $this->select('cities.*')
                   ->join('prayer_times', 'cities.id = prayer_times.city_id')
                   ->where('prayer_times.date', $date)
                   ->groupBy('cities.id')
                   ->orderBy('cities.name', 'ASC')
                   ->findAll();
    }

    /**
     * Get city with today's prayer times
     */
    public function getCityWithPrayerTimes($cityId, $date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }

        $prayerTimesModel = new \App\Models\PrayerTimesModel();
        $city = $this->find($cityId);
        
        if ($city) {
            $city['prayer_times'] = $prayerTimesModel->getByCityAndDate($cityId, $date);
        }
        
        return $city;
    }
}
