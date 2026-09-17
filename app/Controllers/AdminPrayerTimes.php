<?php
namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\NewsModel;
use App\Models\CategoryModel;
use App\Models\TagModel;
use App\Models\PrayerTimesModel;
use App\Models\CityModel;
use App\Models\KickerModel;
use Exception;

/**
 * Prayer-times data management (fetch/store/delete per city and year).
 */
class AdminPrayerTimes extends BaseAdminController
{
    /**
     * Prayer Times Management
     */
    public function prayerTimes($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        // Validate year
        if (!is_numeric($year) || $year < 2020 || $year > 2030) {
            session()->setFlashdata('error', 'Invalid year. Please provide a year between 2020 and 2030.');
            return redirect()->to('/admin/prayer-times');
        }

        $prayerTimesModel = new PrayerTimesModel();
        $cityModel = new CityModel();

        // Get all cities
        $cities = $cityModel->findAll();
        
        // Check which cities have prayer times data for the year
        $citiesWithData = [];
        $citiesWithoutData = [];

        foreach ($cities as $city) {
            $hasData = $prayerTimesModel->hasDataForYear($city['id'], $year);
            
            if ($hasData) {
                $citiesWithData[] = $city;
            } else {
                $citiesWithoutData[] = $city;
            }
        }

        // Get statistics
        $stats = $prayerTimesModel->getDataStatistics($year);

        $data = [
            'year' => $year,
            'cities_with_data' => $citiesWithData,
            'cities_without_data' => $citiesWithoutData,
            'stats' => $stats,
            'title' => "Prayer Times Management - {$year}"
        ];

        return view('admin/prayer_times_year', $data);
    }

    public function fetchPrayerTimes($year, $cityId)
    {
        if (!$year || !$cityId) {
            session()->setFlashdata('error', 'Year and city ID are required.');
            return redirect()->to('/admin/prayer-times');
        }

        // Validate year
        if (!is_numeric($year) || $year < 2020 || $year > 2030) {
            session()->setFlashdata('error', 'Invalid year. Please provide a year between 2020 and 2030.');
            return redirect()->to('/admin/prayer-times');
        }

        $prayerTimesModel = new PrayerTimesModel();
        $cityModel = new CityModel();

        // Get city information
        $city = $cityModel->find($cityId);
        if (!$city) {
            session()->setFlashdata('error', 'City not found.');
            return redirect()->to('/admin/prayer-times');
        }

        // Check if data already exists for this city and year
        if ($prayerTimesModel->hasDataForYear($cityId, $year)) {
            session()->setFlashdata('info', "Prayer times for {$city['name']} in {$year} already exist in database.");
            return redirect()->to("/admin/prayer-times/{$year}");
        }

        try {
            // Use the PrayerTimes controller method
            $prayerTimesController = new \App\Controllers\PrayerTimes();
            $prayerTimes = $prayerTimesController->fetchPrayerTimesFromAPI($city['latitude'], $city['longitude'], $year);
            
            if (empty($prayerTimes)) {
                session()->setFlashdata('error', 'Failed to fetch prayer times from API.');
                return redirect()->to("/admin/prayer-times/{$year}");
            }

            // Store prayer times in database
            $storedCount = $prayerTimesController->storePrayerTimes($cityId, $year, $prayerTimes);

            session()->setFlashdata('success', "Successfully fetched and stored {$storedCount} prayer times for {$city['name']} in {$year}.");
            return redirect()->to("/admin/prayer-times/{$year}");

        } catch (\Exception $e) {
            log_message('error', 'Prayer times fetch error: ' . $e->getMessage());
            session()->setFlashdata('error', 'An error occurred while fetching prayer times: ' . $e->getMessage());
            return redirect()->to("/admin/prayer-times/{$year}");
        }
    }

    public function deletePrayerTimes($year, $cityId)
    {
        $prayerTimesModel = new PrayerTimesModel();
        $cityModel = new CityModel();

        $city = $cityModel->find($cityId);
        if (!$city) {
            session()->setFlashdata('error', 'City not found.');
            return redirect()->to('/admin/prayer-times');
        }

        $deletedCount = $prayerTimesModel->deleteByCityAndYear($cityId, $year);
        
        if ($deletedCount > 0) {
            session()->setFlashdata('success', "Deleted {$deletedCount} prayer time records for {$city['name']} in {$year}.");
        } else {
            session()->setFlashdata('info', "No prayer time records found for {$city['name']} in {$year}.");
        }

        return redirect()->to("/admin/prayer-times/{$year}");
    }
}
