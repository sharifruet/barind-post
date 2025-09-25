<?php
/**
 * Quick Prayer Times Setup
 * This script adds sample prayer times data for immediate testing
 */

// Simple database connection (adjust credentials as needed)
$host = 'localhost';
$dbname = 'barind_post';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Quick Prayer Times Setup</h2>";
    
    // Check if cities exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cities");
    $cityCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>Cities in database: <strong>{$cityCount}</strong></p>";
    
    if ($cityCount == 0) {
        echo "<p style='color: red;'>No cities found. Please run the dbscript.sql first.</p>";
        exit;
    }
    
    // Get Dhaka city
    $stmt = $pdo->query("SELECT id FROM cities WHERE name = 'ঢাকা' LIMIT 1");
    $dhaka = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$dhaka) {
        echo "<p style='color: red;'>Dhaka city not found.</p>";
        exit;
    }
    
    $cityId = $dhaka['id'];
    echo "<p>Using Dhaka city (ID: {$cityId})</p>";
    
    // Check existing data
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM prayer_times WHERE city_id = ? AND date = ?");
    $stmt->execute([$cityId, $today]);
    $existingCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($existingCount > 0) {
        echo "<p style='color: green;'>Prayer times already exist for today ({$today}).</p>";
        echo "<p><a href='/'>Go to homepage to see prayer times widget</a></p>";
        exit;
    }
    
    // Sample prayer times (approximate for Dhaka)
    $baseTimes = [
        'fajr' => '05:30',
        'sunrise' => '06:45', 
        'dhuhr' => '12:15',
        'asr' => '15:30',
        'maghrib' => '18:00',
        'isha' => '19:15'
    ];
    
    // Insert prayer times for next 7 days
    $stmt = $pdo->prepare("
        INSERT INTO prayer_times (city_id, date, fajr, sunrise, dhuhr, asr, maghrib, isha, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $insertedDays = 0;
    for ($i = 0; $i < 7; $i++) {
        $date = date('Y-m-d', strtotime("+{$i} days"));
        
        // Add slight variation to times
        $variation = $i * 1; // 1 minute variation per day
        $times = [
            'fajr' => date('H:i', strtotime($baseTimes['fajr']) + $variation * 60),
            'sunrise' => date('H:i', strtotime($baseTimes['sunrise']) + $variation * 60),
            'dhuhr' => date('H:i', strtotime($baseTimes['dhuhr']) + $variation * 60),
            'asr' => date('H:i', strtotime($baseTimes['asr']) + $variation * 60),
            'maghrib' => date('H:i', strtotime($baseTimes['maghrib']) + $variation * 60),
            'isha' => date('H:i', strtotime($baseTimes['isha']) + $variation * 60)
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
            echo "<p>✓ Added prayer times for {$date}</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error for {$date}: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h3 style='color: green;'>Setup Complete!</h3>";
    echo "<p>Successfully added prayer times for <strong>{$insertedDays}</strong> days.</p>";
    echo "<p><a href='/' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Homepage</a></p>";
    echo "<p><a href='/admin/prayer-times' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Admin Prayer Times</a></p>";
    
} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Database Error</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration.</p>";
}
?>
