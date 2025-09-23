<?php

namespace App\Controllers;

use App\Controllers\BaseAdminController;
use App\Models\PrayerTimesModel;
use App\Models\CityModel;
use CodeIgniter\HTTP\ResponseInterface;

class PrayerTimes extends BaseAdminController
{
    protected $prayerTimesModel;
    protected $cityModel;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);
        $this->prayerTimesModel = new PrayerTimesModel();
        $this->cityModel = new CityModel();
    }

    /**
     * Display prayer times for a specific year
     * GET /prayer-time/2025
     */
    public function index($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        // Validate year
        if (!is_numeric($year) || $year < 2020 || $year > 2030) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Invalid year. Please provide a year between 2020 and 2030.'
            ]);
        }

        // Get all cities
        $cities = $this->cityModel->findAll();
        
        // Check which cities have prayer times data for the year
        $citiesWithData = [];
        $citiesWithoutData = [];

        foreach ($cities as $city) {
            $hasData = $this->prayerTimesModel->hasDataForYear($city['id'], $year);
            
            if ($hasData) {
                $citiesWithData[] = $city;
            } else {
                $citiesWithoutData[] = $city;
            }
        }

        $data = [
            'year' => $year,
            'cities_with_data' => $citiesWithData,
            'cities_without_data' => $citiesWithoutData,
            'title' => "Prayer Times for {$year}"
        ];

        return view('admin/prayer_times_year', $data);
    }

    /**
     * Fetch and store prayer times for a specific city and year
     * GET /prayer-time/2025/1 (where 1 is city_id for Dhaka)
     */
    public function fetchCityYear($year = null, $cityId = null)
    {
        if (!$year || !$cityId) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Year and city ID are required.'
            ]);
        }

        // Validate year
        if (!is_numeric($year) || $year < 2020 || $year > 2030) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Invalid year. Please provide a year between 2020 and 2030.'
            ]);
        }

        // Get city information
        $city = $this->cityModel->find($cityId);
        if (!$city) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'City not found.'
            ]);
        }

        // Check if data already exists for this city and year
        if ($this->prayerTimesModel->hasDataForYear($cityId, $year)) {
            return redirect()->to("/admin/prayer-times/{$year}")->with('message', 
                "Prayer times for {$city['name']} in {$year} already exist in database.");
        }

        try {
            // Fetch prayer times from Adhan API
            $prayerTimes = $this->fetchPrayerTimesFromAPI($city['latitude'], $city['longitude'], $year);
            
            if (empty($prayerTimes)) {
                return $this->response->setStatusCode(500)->setJSON([
                    'error' => 'Failed to fetch prayer times from API.'
                ]);
            }

            // Store prayer times in database
            $storedCount = $this->storePrayerTimes($cityId, $year, $prayerTimes);

            return redirect()->to("/admin/prayer-times/{$year}")->with('message', 
                "Successfully fetched and stored {$storedCount} prayer times for {$city['name']} in {$year}.");

        } catch (\Exception $e) {
            log_message('error', 'Prayer times fetch error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'An error occurred while fetching prayer times: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Fetch prayer times from Adhan API
     */
    public function fetchPrayerTimesFromAPI($latitude, $longitude, $year)
    {
        $prayerTimes = [];
        
        // Log the start of API request
        log_message('info', "Starting prayer times fetch for coordinates: {$latitude}, {$longitude}, year: {$year}");
        
        // Adhan API endpoint
        $apiUrl = "http://api.aladhan.com/v1/calendar/{$year}";
        
        $params = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'method' => 3, // Muslim World League (MWL)
            'school' => 0  // Shafi (or you can use 1 for Hanafi)
        ];

        $url = $apiUrl . '?' . http_build_query($params);
        log_message('info', "API URL: {$url}");

        $client = \Config\Services::curlrequest();
        
        try {
            log_message('info', "Making API request to Adhan API...");
            
            $response = $client->get($url, [
                'timeout' => 60, // Increased timeout for large datasets
                'headers' => [
                    'User-Agent' => 'BarindPost/1.0',
                    'Accept' => 'application/json'
                ]
            ]);

            $statusCode = $response->getStatusCode();
            log_message('info', "API Response Status: {$statusCode}");

            if ($statusCode === 200) {
                $responseBody = $response->getBody();
                $data = json_decode($responseBody, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $error = json_last_error_msg();
                    log_message('error', "JSON decode error: {$error}");
                    throw new \Exception("Failed to parse API response: {$error}");
                }
                
                if (isset($data['data']) && is_array($data['data'])) {
                    $totalDays = count($data['data']);
                    log_message('info', "Processing {$totalDays} days of prayer times data...");
                    
                    $processedCount = 0;
                    foreach ($data['data'] as $day) {
                        if (isset($day['timings'])) {
                            $prayerTimes[] = [
                                'date' => $day['date']['gregorian']['date'],
                                'fajr' => $this->parseTime($day['timings']['Fajr']),
                                'sunrise' => $this->parseTime($day['timings']['Sunrise']),
                                'dhuhr' => $this->parseTime($day['timings']['Dhuhr']),
                                'asr' => $this->parseTime($day['timings']['Asr']),
                                'maghrib' => $this->parseTime($day['timings']['Maghrib']),
                                'isha' => $this->parseTime($day['timings']['Isha'])
                            ];
                            $processedCount++;
                        }
                    }
                    
                    log_message('info', "Successfully processed {$processedCount} days of prayer times data");
                } else {
                    log_message('error', "Invalid API response structure. Expected 'data' array not found.");
                    log_message('error', "API Response: " . substr($responseBody, 0, 500));
                    throw new \Exception("Invalid API response structure");
                }
            } else {
                $errorBody = $response->getBody();
                log_message('error', "API request failed with status {$statusCode}. Response: " . substr($errorBody, 0, 500));
                throw new \Exception("API request failed with status code: {$statusCode}");
            }
        } catch (\Exception $e) {
            log_message('error', 'Adhan API request failed: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }

        log_message('info', "API fetch completed. Total prayer times records: " . count($prayerTimes));
        return $prayerTimes;
    }

    /**
     * Parse time string from API response
     */
    private function parseTime($timeString)
    {
        // Remove timezone info and extract time (e.g., "05:30 (BDT)" -> "05:30")
        $time = preg_replace('/\s*\([^)]*\)/', '', $timeString);
        return $time;
    }

    /**
     * Store prayer times in database
     */
    public function storePrayerTimes($cityId, $year, $prayerTimes)
    {
        $storedCount = 0;
        $totalRecords = count($prayerTimes);
        
        log_message('info', "Starting to store {$totalRecords} prayer times records for city ID: {$cityId}, year: {$year}");
        
        foreach ($prayerTimes as $index => $prayerTime) {
            $data = [
                'city_id' => $cityId,
                'date' => $prayerTime['date'],
                'fajr' => $prayerTime['fajr'],
                'sunrise' => $prayerTime['sunrise'],
                'dhuhr' => $prayerTime['dhuhr'],
                'asr' => $prayerTime['asr'],
                'maghrib' => $prayerTime['maghrib'],
                'isha' => $prayerTime['isha']
            ];

            try {
                if ($this->prayerTimesModel->insert($data)) {
                    $storedCount++;
                } else {
                    $errors = $this->prayerTimesModel->errors();
                    log_message('error', "Failed to insert prayer time for date {$prayerTime['date']}: " . implode(', ', $errors));
                }
            } catch (\Exception $e) {
                log_message('error', "Exception while inserting prayer time for date {$prayerTime['date']}: " . $e->getMessage());
            }
            
            // Log progress every 50 records
            if (($index + 1) % 50 === 0) {
                log_message('info', "Stored " . ($index + 1) . "/{$totalRecords} prayer times records");
            }
        }

        log_message('info', "Completed storing prayer times. Successfully stored: {$storedCount}/{$totalRecords} records");
        return $storedCount;
    }

    /**
     * Get prayer times for a specific city and date
     * GET /prayer-time/city/1/2025-01-15
     */
    public function getCityDate($cityId, $date)
    {
        $city = $this->cityModel->find($cityId);
        if (!$city) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'City not found.'
            ]);
        }

        $prayerTime = $this->prayerTimesModel->getByCityAndDate($cityId, $date);
        
        if (!$prayerTime) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'Prayer times not found for this city and date.'
            ]);
        }

        return $this->response->setJSON([
            'city' => $city['name'],
            'date' => $date,
            'prayer_times' => $prayerTime
        ]);
    }
}
