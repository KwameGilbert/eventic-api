-- =====================================================
-- MIGRATION: Separate Awards from Events
-- =====================================================
-- This migration creates a separate Awards system independent from Events
-- Execution Date: 2025-12-14
-- =====================================================

-- =====================================================
-- STEP 1: Create the new 'awards' table
-- =====================================================

DROP TABLE IF EXISTS `awards`;
CREATE TABLE IF NOT EXISTS `awards` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `organizer_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `venue_name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `map_url` varchar(255) DEFAULT NULL,
  `ceremony_date` datetime NOT NULL COMMENT 'Awards ceremony date',
  `voting_start` datetime NOT NULL COMMENT 'Global voting start (can be overridden per category)',
  `voting_end` datetime NOT NULL COMMENT 'Global voting end (can be overridden per category)',
  `status` enum('draft','published','closed','completed') NOT NULL DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `country` varchar(255) NOT NULL DEFAULT 'Ghana',
  `region` varchar(255) NOT NULL DEFAULT 'Greater Accra',
  `city` varchar(255) NOT NULL DEFAULT 'Accra',
  `phone` varchar(50) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `organizer_id` (`organizer_id`),
  KEY `status` (`status`),
  KEY `is_featured` (`is_featured`),
  KEY `voting_start` (`voting_start`),
  KEY `voting_end` (`voting_end`),
  CONSTRAINT `awards_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `organizers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 2: Migrate existing awards events to awards table
-- =====================================================

INSERT INTO `awards` (
  `organizer_id`,
  `title`,
  `slug`,
  `description`,
  `banner_image`,
  `venue_name`,
  `address`,
  `map_url`,
  `ceremony_date`,
  `voting_start`,
  `voting_end`,
  `status`,
  `is_featured`,
  `country`,
  `region`,
  `city`,
  `phone`,
  `website`,
  `facebook`,
  `twitter`,
  `instagram`,
  `video_url`,
  `views`,
  `created_at`,
  `updated_at`
)
SELECT 
  `organizer_id`,
  `title`,
  `slug`,
  `description`,
  `banner_image`,
  `venue_name`,
  `address`,
  `map_url`,
  `start_time` as `ceremony_date`,
  -- Default voting period: 2 months before ceremony
  DATE_SUB(`start_time`, INTERVAL 2 MONTH) as `voting_start`,
  -- Voting ends 1 day before ceremony
  DATE_SUB(`start_time`, INTERVAL 1 DAY) as `voting_end`,
  `status`,
  `is_featured`,
  `country`,
  `region`,
  `city`,
  `phone`,
  `website`,
  `facebook`,
  `twitter`,
  `instagram`,
  `video_url`,
  `views`,
  `created_at`,
  `updated_at`
FROM `events`
WHERE `event_format` = 'awards';

-- =====================================================
-- STEP 3: Update award_categories to reference awards
-- =====================================================

-- First, add the new award_id column
ALTER TABLE `award_categories` 
  ADD COLUMN `award_id` int(11) UNSIGNED NULL AFTER `id`;

-- Populate award_id by matching event_id to the migrated awards
UPDATE `award_categories` ac
INNER JOIN `events` e ON ac.`event_id` = e.`id`
INNER JOIN `awards` a ON a.`slug` = e.`slug` AND a.`title` = e.`title`
SET ac.`award_id` = a.`id`
WHERE e.`event_format` = 'awards';

-- Make award_id NOT NULL and add foreign key
ALTER TABLE `award_categories`
  MODIFY `award_id` int(11) UNSIGNED NOT NULL;

-- Drop the old foreign key constraint on event_id
ALTER TABLE `award_categories`
  DROP FOREIGN KEY `award_categories_ibfk_1`;

-- Drop the event_id column
ALTER TABLE `award_categories`
  DROP COLUMN `event_id`;

-- Add foreign key for award_id
ALTER TABLE `award_categories`
  ADD CONSTRAINT `award_categories_ibfk_1` 
  FOREIGN KEY (`award_id`) REFERENCES `awards` (`id`) 
  ON DELETE CASCADE ON UPDATE CASCADE;

-- Add index for award_id
ALTER TABLE `award_categories`
  ADD KEY `award_id` (`award_id`);

-- =====================================================
-- STEP 4: Update award_nominees to reference awards
-- =====================================================

-- Add the new award_id column
ALTER TABLE `award_nominees` 
  ADD COLUMN `award_id` int(11) UNSIGNED NULL AFTER `category_id`;

-- Populate award_id by matching through categories
UPDATE `award_nominees` an
INNER JOIN `award_categories` ac ON an.`category_id` = ac.`id`
SET an.`award_id` = ac.`award_id`;

-- Make award_id NOT NULL
ALTER TABLE `award_nominees`
  MODIFY `award_id` int(11) UNSIGNED NOT NULL;

-- Drop the old foreign key on event_id
ALTER TABLE `award_nominees`
  DROP FOREIGN KEY `award_nominees_ibfk_2`;

-- Drop the event_id column
ALTER TABLE `award_nominees`
  DROP COLUMN `event_id`;

-- Add foreign key for award_id
ALTER TABLE `award_nominees`
  ADD CONSTRAINT `award_nominees_ibfk_2` 
  FOREIGN KEY (`award_id`) REFERENCES `awards` (`id`) 
  ON DELETE CASCADE ON UPDATE CASCADE;

-- Add index for award_id
ALTER TABLE `award_nominees`
  ADD KEY `award_id` (`award_id`);

-- =====================================================
-- STEP 5: Update award_votes to reference awards
-- =====================================================

-- Add the new award_id column
ALTER TABLE `award_votes` 
  ADD COLUMN `award_id` int(11) UNSIGNED NULL AFTER `category_id`;

-- Populate award_id by matching through categories
UPDATE `award_votes` av
INNER JOIN `award_categories` ac ON av.`category_id` = ac.`id`
SET av.`award_id` = ac.`award_id`;

-- Make award_id NOT NULL
ALTER TABLE `award_votes`
  MODIFY `award_id` int(11) UNSIGNED NOT NULL;

-- Drop the old foreign key on event_id
ALTER TABLE `award_votes`
  DROP FOREIGN KEY `award_votes_ibfk_3`;

-- Drop the event_id column
ALTER TABLE `award_votes`
  DROP COLUMN `event_id`;

-- Add foreign key for award_id
ALTER TABLE `award_votes`
  ADD CONSTRAINT `award_votes_ibfk_3` 
  FOREIGN KEY (`award_id`) REFERENCES `awards` (`id`) 
  ON DELETE CASCADE ON UPDATE CASCADE;

-- Add index for award_id
ALTER TABLE `award_votes`
  ADD KEY `award_id` (`award_id`);

-- =====================================================
-- STEP 6: Remove awards events from events table
-- =====================================================

-- Delete events that were migrated to awards
DELETE FROM `events` WHERE `event_format` = 'awards';

-- =====================================================
-- STEP 7: Remove event_format column from events
-- =====================================================

ALTER TABLE `events` DROP COLUMN `event_format`;

-- =====================================================
-- STEP 8: Create awards_images table (similar to event_images)
-- =====================================================

DROP TABLE IF EXISTS `awards_images`;
CREATE TABLE IF NOT EXISTS `awards_images` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `award_id` int(11) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `award_id` (`award_id`),
  CONSTRAINT `awards_images_ibfk_1` FOREIGN KEY (`award_id`) REFERENCES `awards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
