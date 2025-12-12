-- Migration: Change next_maintenance_date to kilometers
-- Run this SQL script in phpMyAdmin or MySQL command line
-- 
-- IMPORTANT: Backup your database before running this script!

-- Step 1: Check if kilometers column exists, if not add it
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'maintenance_record' 
AND COLUMN_NAME = 'kilometers';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `maintenance_record` ADD COLUMN `kilometers` INT(11) DEFAULT NULL AFTER `cost`',
    'SELECT "Column kilometers already exists" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 2: Drop the old next_maintenance_date column if it exists
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'maintenance_record' 
AND COLUMN_NAME = 'next_maintenance_date';

SET @sql = IF(@col_exists > 0,
    'ALTER TABLE `maintenance_record` DROP COLUMN `next_maintenance_date`',
    'SELECT "Column next_maintenance_date does not exist" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify the change
DESCRIBE maintenance_record;

