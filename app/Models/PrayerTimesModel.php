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
        'fajr' => 'required|regex_match[/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/]',
        'sunrise' => 'required|regex_match[/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/]',
        'dhuhr' => 'required|regex_match[/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/]',
        'asr' => 'required|regex_match[/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/]',
        'maghrib' => 'required|regex_match[/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/]',
        'isha' => 'required|regex_match[/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/]'
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
            'regex_match' => 'Fajr time must be in HH:MM format'
        ],
        'sunrise' => [
            'required' => 'Sunrise time is required',
            'regex_match' => 'Sunrise time must be in HH:MM format'
        ],
        'dhuhr' => [
            'required' => 'Dhuhr time is required',
            'regex_match' => 'Dhuhr time must be in HH:MM format'
        ],
        'asr' => [
            'required' => 'Asr time is required',
            'regex_match' => 'Asr time must be in HH:MM format'
        ],
        'maghrib' => [
            'required' => 'Maghrib time is required',
            'regex_match' => 'Maghrib time must be in HH:MM format'
        ],
        'isha' => [
            'required' => 'Isha time is required',
            'regex_match' => 'Isha time must be in HH:MM format'
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
