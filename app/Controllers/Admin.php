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
 * Dashboard and logs. Everything else that used to live in this 1,800-line class is now in the Admin* feature controllers.
 */
class Admin extends BaseAdminController
{
    public function dashboard()
    {
        // Get view statistics
        $db = \Config\Database::connect();
        
        // Total views
        $totalViews = $db->table('news_views')->countAllResults();
        
        // Views today
        // Range, not DATE(viewed_at) = ?: a function on the column defeats idx_news_views_viewed_at.
        $todayViews = $db->table('news_views')
                         ->where('viewed_at >=', date('Y-m-d 00:00:00'))
                         ->countAllResults();
        
        // Most viewed news
        $mostViewedNews = $db->table('news_views')
                            ->select('news_id, COUNT(*) as view_count')
                            ->groupBy('news_id')
                            ->orderBy('view_count', 'DESC')
                            ->limit(5)
                            ->get()
                            ->getResultArray();
        
        // Get news details for most viewed
        $newsModel = new NewsModel();
        $topNews = [];
        foreach ($mostViewedNews as $view) {
            $news = $newsModel->find($view['news_id']);
            if ($news) {
                $topNews[] = [
                    'title' => $news['title'],
                    'views' => $view['view_count']
                ];
            }
        }
        
        return view('admin/dashboard', [
            'totalViews' => $totalViews,
            'todayViews' => $todayViews,
            'topNews' => $topNews
        ]);
    }

    /**
     * View application logs
     */
    public function viewLogs()
    {
        $todayLogFile = WRITEPATH . 'logs/log-' . date('Y-m-d') . '.log';
        $logs = [];
        $availableLogFiles = [];
        
        // Get all available log files
        $logDir = WRITEPATH . 'logs/';
        if (is_dir($logDir)) {
            $files = scandir($logDir);
            foreach ($files as $file) {
                if (strpos($file, 'log-') === 0 && strpos($file, '.log') !== false) {
                    $availableLogFiles[] = $file;
                }
            }
            rsort($availableLogFiles); // Sort by date, newest first
        }
        
        // Try to read today's log file first, then the most recent one
        $logFileToRead = $todayLogFile;
        if (!file_exists($logFileToRead) && !empty($availableLogFiles)) {
            $logFileToRead = $logDir . $availableLogFiles[0];
        }
        
        if (file_exists($logFileToRead)) {
            $content = file_get_contents($logFileToRead);
            // Parse CodeIgniter log format
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                if (strpos($line, 'INFO') !== false || strpos($line, 'ERROR') !== false || strpos($line, 'CRITICAL') !== false) {
                    $logs[] = $line;
                }
            }
            $logs = array_reverse(array_slice($logs, -100)); // Get last 100 log entries
        }
        
        return view('admin/view_logs', [
            'logs' => $logs,
            'availableLogFiles' => $availableLogFiles,
            'currentLogFile' => basename($logFileToRead),
            'title' => 'Application Logs'
        ]);
    }
}
