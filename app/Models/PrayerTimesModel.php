<?php

namespace App\Models;

use CodeIgniter\Model;

class PrayerTimesModel extends Model
{
    protected $table = 'prayer_times';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'city_id',
        'date',
        'fajr',
        'sunrise',
        'dhuhr',
        'asr',
        'maghrib',
        'isha'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'city_id' => 'required|integer',
        'date' => 'required|valid_date',
        'fajr' => 'required|valid_time',
        'sunrise' => 'required|valid_time',
        'dhuhr' => 'required|valid_time',
        'asr' => 'required|valid_time',
        'maghrib' => 'required|valid_time',
        'isha' => 'required|valid_time'
    ];

    protected $validationMessages = [
        'city_id' => [
            'required' => 'City ID is required',
            'integer' => 'City ID must be an integer'
        ],
        'date' => [
            'required' => 'Date is required',
            'valid_date' => 'Date must be a valid date'
        ],
        'fajr' => [
            'required' => 'Fajr time is required',
            'valid_time' => 'Fajr time must be a valid time'
        ],
        'sunrise' => [
            'required' => 'Sunrise time is required',
            'valid_time' => 'Sunrise time must be a valid time'
        ],
        'dhuhr' => [
            'required' => 'Dhuhr time is required',
            'valid_time' => 'Dhuhr time must be a valid time'
        ],
        'asr' => [
            'required' => 'Asr time is required',
            'valid_time' => 'Asr time must be a valid time'
        ],
        'maghrib' => [
            'required' => 'Maghrib time is required',
            'valid_time' => 'Maghrib time must be a valid time'
        ],
        'isha' => [
            'required' => 'Isha time is required',
            'valid_time' => 'Isha time must be a valid time'
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
     * Check if prayer times data exists for a specific city and year
     */
    public function hasDataForYear($cityId, $year)
    {
        $startDate = $year . '-01-01';
        $endDate = $year . '-12-31';
        
        $result = $this->where('city_id', $cityId)
                      ->where('date >=', $startDate)
                      ->where('date <=', $endDate)
                      ->countAllResults();
        
        return $result > 0;
    }

    /**
     * Get prayer times for a specific city and date
     */
    public function getByCityAndDate($cityId, $date)
    {
        return $this->where('city_id', $cityId)
                   ->where('date', $date)
                   ->first();
    }

    /**
     * Get prayer times for a specific city and year
     */
    public function getByCityAndYear($cityId, $year)
    {
        $startDate = $year . '-01-01';
        $endDate = $year . '-12-31';
        
        return $this->where('city_id', $cityId)
                   ->where('date >=', $startDate)
                   ->where('date <=', $endDate)
                   ->orderBy('date', 'ASC')
                   ->findAll();
    }

    /**
     * Get prayer times for a specific city and month
     */
    public function getByCityAndMonth($cityId, $year, $month)
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-%02d', $year, $month, date('t', mktime(0, 0, 0, $month, 1, $year)));
        
        return $this->where('city_id', $cityId)
                   ->where('date >=', $startDate)
                   ->where('date <=', $endDate)
                   ->orderBy('date', 'ASC')
                   ->findAll();
    }

    /**
     * Get today's prayer times for a specific city
     */
    public function getTodayByCity($cityId)
    {
        $today = date('Y-m-d');
        return $this->getByCityAndDate($cityId, $today);
    }

    /**
     * Get next prayer time for a specific city
     */
    public function getNextPrayerByCity($cityId)
    {
        $today = date('Y-m-d');
        $currentTime = date('H:i:s');
        
        $prayerTime = $this->getByCityAndDate($cityId, $today);
        
        if (!$prayerTime) {
            return null;
        }

        $prayers = [
            'fajr' => $prayerTime['fajr'],
            'sunrise' => $prayerTime['sunrise'],
            'dhuhr' => $prayerTime['dhuhr'],
            'asr' => $prayerTime['asr'],
            'maghrib' => $prayerTime['maghrib'],
            'isha' => $prayerTime['isha']
        ];

        foreach ($prayers as $prayer => $time) {
            if ($time > $currentTime) {
                return [
                    'prayer' => $prayer,
                    'time' => $time,
                    'city_id' => $cityId,
                    'date' => $today
                ];
            }
        }

        // If no prayer is found for today, get tomorrow's fajr
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $tomorrowPrayer = $this->getByCityAndDate($cityId, $tomorrow);
        
        if ($tomorrowPrayer) {
            return [
                'prayer' => 'fajr',
                'time' => $tomorrowPrayer['fajr'],
                'city_id' => $cityId,
                'date' => $tomorrow
            ];
        }

        return null;
    }

    /**
     * Get cities with prayer times data for a specific year
     */
    public function getCitiesWithDataForYear($year)
    {
        $startDate = $year . '-01-01';
        $endDate = $year . '-12-31';
        
        return $this->select('city_id')
                   ->where('date >=', $startDate)
                   ->where('date <=', $endDate)
                   ->groupBy('city_id')
                   ->findAll();
    }

    /**
     * Delete prayer times for a specific city and year
     */
    public function deleteByCityAndYear($cityId, $year)
    {
        $startDate = $year . '-01-01';
        $endDate = $year . '-12-31';
        
        return $this->where('city_id', $cityId)
                   ->where('date >=', $startDate)
                   ->where('date <=', $endDate)
                   ->delete();
    }

    /**
     * Get statistics for prayer times data
     */
    public function getDataStatistics($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        $startDate = $year . '-01-01';
        $endDate = $year . '-12-31';

        $totalRecords = $this->where('date >=', $startDate)
                            ->where('date <=', $endDate)
                            ->countAllResults();

        $citiesWithData = $this->select('city_id')
                              ->where('date >=', $startDate)
                              ->where('date <=', $endDate)
                              ->groupBy('city_id')
                              ->countAllResults();

        $dateRange = $this->select('MIN(date) as min_date, MAX(date) as max_date')
                         ->where('date >=', $startDate)
                         ->where('date <=', $endDate)
                         ->first();

        return [
            'year' => $year,
            'total_records' => $totalRecords,
            'cities_with_data' => $citiesWithData,
            'date_range' => $dateRange
        ];
    }
}
