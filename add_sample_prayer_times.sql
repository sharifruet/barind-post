-- Add sample prayer times for Dhaka (city_id = 1)
-- Run this SQL script in your database to populate prayer times data

-- First, check if Dhaka exists
SELECT 'Checking for Dhaka city...' as status;
SELECT id, name FROM cities WHERE name = 'ঢাকা' LIMIT 1;

-- Insert sample prayer times for the next 7 days
-- These are approximate times for testing purposes
INSERT INTO prayer_times (city_id, date, fajr, sunrise, dhuhr, asr, maghrib, isha, created_at, updated_at) VALUES
(1, CURDATE(), '05:30:00', '06:45:00', '12:15:00', '15:30:00', '18:00:00', '19:15:00', NOW(), NOW()),
(1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '05:31:00', '06:46:00', '12:16:00', '15:31:00', '18:01:00', '19:16:00', NOW(), NOW()),
(1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '05:32:00', '06:47:00', '12:17:00', '15:32:00', '18:02:00', '19:17:00', NOW(), NOW()),
(1, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '05:33:00', '06:48:00', '12:18:00', '15:33:00', '18:03:00', '19:18:00', NOW(), NOW()),
(1, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '05:34:00', '06:49:00', '12:19:00', '15:34:00', '18:04:00', '19:19:00', NOW(), NOW()),
(1, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '05:35:00', '06:50:00', '12:20:00', '15:35:00', '18:05:00', '19:20:00', NOW(), NOW()),
(1, DATE_ADD(CURDATE(), INTERVAL 6 DAY), '05:36:00', '06:51:00', '12:21:00', '15:36:00', '18:06:00', '19:21:00', NOW(), NOW());

-- Verify the data was inserted
SELECT 'Verifying inserted data...' as status;
SELECT COUNT(*) as total_prayer_times FROM prayer_times WHERE city_id = 1;
SELECT date, fajr, dhuhr, maghrib FROM prayer_times WHERE city_id = 1 ORDER BY date LIMIT 3;
