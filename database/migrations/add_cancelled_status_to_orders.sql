-- Migration to add 'cancelled' status to orders table
-- Run this SQL to add the cancelled status to the ENUM if missing

-- For MySQL/MariaDB:
-- First, check the current ENUM values:
-- SHOW COLUMNS FROM orders LIKE 'status';

-- If 'cancelled' is not in the ENUM, run:
ALTER TABLE `orders` 
MODIFY COLUMN `status` ENUM('pending', 'paid', 'failed', 'refunded', 'cancelled') 
NOT NULL DEFAULT 'pending';
