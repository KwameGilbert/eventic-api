-- ============================================
-- ADD FEATURED FLAG TO EVENTS TABLE
-- Run this migration after schema.sql
-- ============================================

-- Add is_featured column to events table
ALTER TABLE `events` 
ADD COLUMN `is_featured` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`,
ADD INDEX `is_featured` (`is_featured`);

-- Mark some existing events as featured (if they exist)
UPDATE `events` SET `is_featured` = 1 WHERE `id` IN (1, 2, 3, 4, 5);
