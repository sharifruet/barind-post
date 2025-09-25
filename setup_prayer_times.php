<?php
/**
 * Web-accessible Prayer Times Setup
 * Run this in your browser to populate prayer times data
 */

// Simple database connection (adjust these values for your setup)
$host = 'localhost';
$dbname = 'barind_post';
$username = 'root';
$password = '';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Prayer Times Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
        .btn-success { background: #28a745; }
        .btn-info { background: #17a2b8; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🕌 Prayer Times Setup</h1>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='success'>✅ Connected to database successfully</div>";
    
    // Check if cities exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cities");
    $cityCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<div class='info'>📊 Cities in database: <strong>{$cityCount}</strong></div>";
    
    if ($cityCount == 0) {
        echo "<div class='error'>❌ No cities found. Please run the dbscript.sql first.</div>";
        echo "<p>Make sure to:</p><ul><li>Import the dbscript.sql file into your database</li><li>Check your database connection settings</li></ul>";
        exit;
    }
    
    // Get Dhaka city
    $stmt = $pdo->query("SELECT id FROM cities WHERE name = 'ঢাকা' LIMIT 1");
    $dhaka = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$dhaka) {
        echo "<div class='error'>❌ Dhaka city not found in database.</div>";
        echo "<p>Available cities:</p>";
        $stmt = $pdo->query("SELECT name FROM cities LIMIT 10");
        while ($city = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<li>" . htmlspecialchars($city['name']) . "</li>";
        }
        exit;
    }
    
    $cityId = $dhaka['id'];
    echo "<div class='info'>🏙️ Using Dhaka city (ID: {$cityId})</div>";
    
    // Check existing data
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM prayer_times WHERE city_id = ? AND date = ?");
    $stmt->execute([$cityId, $today]);
    $existingCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($existingCount > 0) {
        echo "<div class='success'>✅ Prayer times already exist for today ({$today})</div>";
        echo "<p><a href='/' class='btn'>🏠 Go to Homepage</a></p>";
        echo "<p><a href='/admin/prayer-times' class='btn btn-info'>⚙️ Admin Panel</a></p>";
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
    echo "<h3>📅 Adding Prayer Times...</h3>";
    
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
            echo "<div class='success'>✅ Added prayer times for {$date}</div>";
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Error for {$date}: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
    
    echo "<div class='success'>
        <h3>🎉 Setup Complete!</h3>
        <p>Successfully added prayer times for <strong>{$insertedDays}</strong> days.</p>
    </div>";
    
    echo "<p>
        <a href='/' class='btn btn-success'>🏠 Go to Homepage</a>
        <a href='/admin/prayer-times' class='btn btn-info'>⚙️ Admin Panel</a>
    </p>";
    
} catch (PDOException $e) {
    echo "<div class='error'>
        <h3>❌ Database Error</h3>
        <p>Error: " . htmlspecialchars($e->getMessage()) . "</p>
        <p>Please check your database configuration:</p>
        <ul>
            <li>Host: {$host}</li>
            <li>Database: {$dbname}</li>
            <li>Username: {$username}</li>
        </ul>
    </div>";
} catch (Exception $e) {
    echo "<div class='error'>
        <h3>❌ Error</h3>
        <p>Error: " . htmlspecialchars($e->getMessage()) . "</p>
    </div>";
}

echo "</div></body></html>";
?>
