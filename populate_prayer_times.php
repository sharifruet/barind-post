<?php
/**
 * Simple script to populate sample prayer times data for testing
 * Run this script to add sample prayer times for Dhaka
 */

// Include CodeIgniter bootstrap
require_once 'vendor/autoload.php';

// Create a simple database connection
$config = [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'barind_post',
    'charset'  => 'utf8mb4'
];

try {
    $pdo = new PDO(
        "mysql:host={$config['hostname']};dbname={$config['database']};charset={$config['charset']}", 
        $config['username'], 
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Check if cities exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cities");
    $cityCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Cities in database: {$cityCount}\n";
    
    if ($cityCount == 0) {
        echo "No cities found. Please run the dbscript.sql first.\n";
        exit(1);
    }
    
    // Get Dhaka city ID
    $stmt = $pdo->query("SELECT id FROM cities WHERE name = 'ঢাকা' LIMIT 1");
    $dhaka = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$dhaka) {
        echo "Dhaka city not found. Please check your cities data.\n";
        exit(1);
    }
    
    $cityId = $dhaka['id'];
    echo "Dhaka city ID: {$cityId}\n";
    
    // Check if prayer times already exist for today
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM prayer_times WHERE city_id = ? AND date = ?");
    $stmt->execute([$cityId, $today]);
    $existingCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($existingCount > 0) {
        echo "Prayer times already exist for today ({$today}).\n";
        exit(0);
    }
    
    // Sample prayer times for Dhaka (approximate times)
    $sampleTimes = [
        'fajr' => '05:30',
        'sunrise' => '06:45',
        'dhuhr' => '12:15',
        'asr' => '15:30',
        'maghrib' => '18:00',
        'isha' => '19:15'
    ];
    
    // Insert sample prayer times for the next 7 days
    $stmt = $pdo->prepare("
        INSERT INTO prayer_times (city_id, date, fajr, sunrise, dhuhr, asr, maghrib, isha, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $insertedDays = 0;
    for ($i = 0; $i < 7; $i++) {
        $date = date('Y-m-d', strtotime("+{$i} days"));
        
        // Add some variation to times (not realistic, just for testing)
        $variation = $i * 2; // 2 minutes variation per day
        $times = [
            'fajr' => date('H:i', strtotime($sampleTimes['fajr']) + $variation * 60),
            'sunrise' => date('H:i', strtotime($sampleTimes['sunrise']) + $variation * 60),
            'dhuhr' => date('H:i', strtotime($sampleTimes['dhuhr']) + $variation * 60),
            'asr' => date('H:i', strtotime($sampleTimes['asr']) + $variation * 60),
            'maghrib' => date('H:i', strtotime($sampleTimes['maghrib']) + $variation * 60),
            'isha' => date('H:i', strtotime($sampleTimes['isha']) + $variation * 60)
        ];
        
        try {
            $stmt->execute([
                $cityId,
                $date,
                $times['fajr'],
                $times['sunrise'],
                $times['dhuhr'],
                $times['asr'],
                $times['maghrib'],
                $times['isha']
            ]);
            $insertedDays++;
            echo "Inserted prayer times for {$date}\n";
        } catch (PDOException $e) {
            echo "Error inserting prayer times for {$date}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "Successfully inserted prayer times for {$insertedDays} days.\n";
    echo "You can now test the prayer times widget on your website.\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and ensure the database exists.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
