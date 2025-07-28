-- Update script to add featured column to news table if it doesn't exist
-- This script is safe to run multiple times

-- Check if featured column exists, if not add it
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'news' 
     AND COLUMN_NAME = 'featured') = 0,
    'ALTER TABLE news ADD COLUMN featured BOOLEAN NOT NULL DEFAULT FALSE AFTER status',
    'SELECT "Featured column already exists" as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing news records to have featured = FALSE if they don't have it set
UPDATE news SET featured = FALSE WHERE featured IS NULL;

-- Show the current structure of the news table
DESCRIBE news; 