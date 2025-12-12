-- Simple Migration: Change next_maintenance_date to kilometers
-- Run this in phpMyAdmin SQL tab or MySQL command line
-- 
-- IMPORTANT: Backup your database first!

-- Option 1: If kilometers column doesn't exist yet, add it first
ALTER TABLE `maintenance_record` 
ADD COLUMN IF NOT EXISTS `kilometers` INT(11) DEFAULT NULL AFTER `cost`;

-- Option 2: If the above doesn't work (older MySQL versions), use this instead:
-- ALTER TABLE `maintenance_record` ADD COLUMN `kilometers` INT(11) DEFAULT NULL AFTER `cost`;

-- Then drop the old column
ALTER TABLE `maintenance_record` 
DROP COLUMN IF EXISTS `next_maintenance_date`;

-- If DROP COLUMN IF EXISTS doesn't work (older MySQL versions), use:
-- ALTER TABLE `maintenance_record` DROP COLUMN `next_maintenance_date`;

-- Verify the change
DESCRIBE maintenance_record;


