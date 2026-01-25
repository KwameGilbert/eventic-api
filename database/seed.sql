-- ========================================
-- EVENTIC DATABASE SEED DATA
-- Comprehensive test data for all tables
-- ========================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- ========================================
-- CLEANUP: Delete existing data in reverse dependency order
-- ========================================

-- Delete dependent tables first
DELETE FROM `refresh_tokens`;
DELETE FROM `password_resets`;
DELETE FROM `audit_logs`;
DELETE FROM `event_reviews`;
DELETE FROM `event_images`;
DELETE FROM `awards_images`;
DELETE FROM `organizer_followers`;
DELETE FROM `scanner_assignments`;
DELETE FROM `pos_assignments`;

-- Delete transactional data
DELETE FROM `transactions`;
DELETE FROM `award_votes`;
DELETE FROM `tickets`;
DELETE FROM `order_items`;
DELETE FROM `orders`;
DELETE FROM `payout_requests`;

-- Delete award system data
DELETE FROM `award_nominees`;
DELETE FROM `award_categories`;
DELETE FROM `awards`;

-- Delete event system data
DELETE FROM `ticket_types`;
DELETE FROM `events`;
DELETE FROM `event_types`;

-- Delete user-related data
DELETE FROM `attendees`;
DELETE FROM `organizer_balances`;
DELETE FROM `organizers`;
DELETE FROM `users`;

-- Delete configuration
DELETE FROM `platform_settings`;

-- Reset auto-increment counters (optional but recommended)
ALTER TABLE `users` AUTO_INCREMENT = 1;
ALTER TABLE `organizers` AUTO_INCREMENT = 1;
ALTER TABLE `organizer_balances` AUTO_INCREMENT = 1;
ALTER TABLE `attendees` AUTO_INCREMENT = 1;
ALTER TABLE `event_types` AUTO_INCREMENT = 1;
ALTER TABLE `events` AUTO_INCREMENT = 1;
ALTER TABLE `event_images` AUTO_INCREMENT = 1;
ALTER TABLE `event_reviews` AUTO_INCREMENT = 1;
ALTER TABLE `ticket_types` AUTO_INCREMENT = 1;
ALTER TABLE `tickets` AUTO_INCREMENT = 1;
ALTER TABLE `orders` AUTO_INCREMENT = 1;
ALTER TABLE `order_items` AUTO_INCREMENT = 1;
ALTER TABLE `awards` AUTO_INCREMENT = 1;
ALTER TABLE `awards_images` AUTO_INCREMENT = 1;
ALTER TABLE `award_categories` AUTO_INCREMENT = 1;
ALTER TABLE `award_nominees` AUTO_INCREMENT = 1;
ALTER TABLE `award_votes` AUTO_INCREMENT = 1;
ALTER TABLE `transactions` AUTO_INCREMENT = 1;
ALTER TABLE `payout_requests` AUTO_INCREMENT = 1;
ALTER TABLE `refresh_tokens` AUTO_INCREMENT = 1;
ALTER TABLE `audit_logs` AUTO_INCREMENT = 1;
ALTER TABLE `organizer_followers` AUTO_INCREMENT = 1;
ALTER TABLE `scanner_assignments` AUTO_INCREMENT = 1;
ALTER TABLE `pos_assignments` AUTO_INCREMENT = 1;
ALTER TABLE `platform_settings` AUTO_INCREMENT = 1;

-- ========================================
-- 1. USERS (Base table - must be first)
-- ========================================
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `email_verified`, `email_verified_at`, `status`, `created_at`) VALUES
(1, 'Admin User', 'admin@eventic.com', '+233244000001', '$argon2id$v=19$m=65536$t=4$p=1$c29tZXNhbHQ$hash1234567890abcdef', 'admin', 1, NOW(), 'active', NOW()),
(2, 'Kwame Mensah', 'kwame@eventpro.com', '+233244000002', '$argon2id$v=19$m=65536$t=4$p=1$c29tZXNhbHQ$hash1234567890abcdef', 'organizer', 1, NOW(), 'active', NOW()),
(3, 'Ama Serwaa', 'ama@musicevents.com', '+233244000003', '$argon2id$v=19$m=65536$t=4$p=1$c29tZXNhbHQ$hash1234567890abcdef', 'organizer', 1, NOW(), 'active', NOW()),
(4, 'Kofi Asante', 'kofi.asante@gmail.com', '+233244000004', '$argon2id$v=19$m=65536$t=4$p=1$c29tZXNhbHQ$hash1234567890abcdef', 'attendee', 1, NOW(), 'active', NOW()),
(5, 'Abena Owusu', 'abena.owusu@yahoo.com', '+233244000005', '$argon2id$v=19$m=65536$t=4$p=1$c29tZXNhbHQ$hash1234567890abcdef', 'attendee', 1, NOW(), 'active', NOW()),
(6, 'Yaw Boateng', 'yaw.boateng@gmail.com', '+233244000006', '$argon2id$v=19$m=65536$t=4$p=1$c29tZXNhbHQ$hash1234567890abcdef', 'attendee', 1, NOW(), 'active', NOW()),
(7, 'Adjoa Appiah', 'adjoa@gmail.com', '+233244000007', '$argon2id$v=19$m=65536$t=4$p=1$c29tZXNhbHQ$hash1234567890abcdef', 'attendee', 0, NULL, 'active', NOW()),
(8, 'Emmanuel Nkrumah', 'emmanuel@scanner.com', '+233244000008', '$argon2id$v=19$m=65536$t=4$p=1$c29tZXNhbHQ$hash1234567890abcdef', 'scanner', 1, NOW(), 'active', NOW()),
(9, 'Grace Addo', 'grace@pos.com', '+233244000009', '$argon2id$v=19$m=65536$t=4$p=1$c29tZXNhbHQ$hash1234567890abcdef', 'pos', 1, NOW(), 'active', NOW()),
(10, 'Samuel Osei', 'samuel@eventorganizer.com', '+233244000010', '$argon2id$v=19$m=65536$t=4$p=1$c29tZXNhbHQ$hash1234567890abcdef', 'organizer', 1, NOW(), 'active', NOW());

-- ========================================
-- 2. ORGANIZERS
-- ========================================
INSERT INTO `organizers` (`id`, `user_id`, `organization_name`, `bio`, `profile_image`, `social_facebook`, `social_instagram`, `social_twitter`, `created_at`) VALUES
(1, 2, 'EventPro Ghana', 'Leading event management company in Ghana. We organize corporate events, conferences, and tech summits.', 'https://ui-avatars.com/api/?name=EventPro+Ghana&size=200', 'https://facebook.com/eventproghana', 'https://instagram.com/eventproghana', 'https://twitter.com/eventprogh', NOW()),
(2, 3, 'Music Events Africa', 'Top music event organizers specializing in concerts, award shows, and entertainment events.', 'https://ui-avatars.com/api/?name=Music+Events+Africa&size=200', 'https://facebook.com/musiceventsafrica', 'https://instagram.com/musiceventsafrica', 'https://twitter.com/musiceventsaf', NOW()),
(3, 10, 'Tech Summit Organizers', 'We organize technology conferences, hackathons, and startup events across West Africa.', 'https://ui-avatars.com/api/?name=Tech+Summit&size=200', 'https://facebook.com/techsummitgh', 'https://instagram.com/techsummitgh', 'https://twitter.com/techsummitgh', NOW());

-- ========================================
-- 3. ORGANIZER BALANCES
-- ========================================
INSERT INTO `organizer_balances` (`organizer_id`, `available_balance`, `pending_balance`, `total_earned`, `total_withdrawn`, `last_payout_at`, `created_at`) VALUES
(1, 5250.00, 2300.00, 12500.00, 5000.00, '2026-01-10 10:00:00', NOW()),
(2, 8900.00, 4500.00, 25000.00, 11600.00, '2026-01-08 15:30:00', NOW()),
(3, 1200.00, 800.00, 3500.00, 1500.00, '2026-01-05 09:00:00', NOW());

-- ========================================
-- 4. ATTENDEES
-- ========================================
INSERT INTO `attendees` (`id`, `user_id`, `first_name`, `last_name`, `email`, `phone`, `bio`, `profile_image`, `created_at`) VALUES
(1, 4, 'Kofi', 'Asante', 'kofi.asante@gmail.com', '+233244000004', 'Tech enthusiast and event lover', 'https://ui-avatars.com/api/?name=Kofi+Asante&size=200', NOW()),
(2, 5, 'Abena', 'Owusu', 'abena.owusu@yahoo.com', '+233244000005', 'Music fan and concert goer', 'https://ui-avatars.com/api/?name=Abena+Owusu&size=200', NOW()),
(3, 6, 'Yaw', 'Boateng', 'yaw.boateng@gmail.com', '+233244000006', 'Sports and entertainment enthusiast', 'https://ui-avatars.com/api/?name=Yaw+Boateng&size=200', NOW()),
(4, 7, 'Adjoa', 'Appiah', 'adjoa@gmail.com', '+233244000007', 'Event photographer', 'https://ui-avatars.com/api/?name=Adjoa+Appiah&size=200', NOW());

-- ========================================
-- 5. EVENT TYPES (Categories)
-- ========================================
INSERT INTO `event_types` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(1, 'Conference', 'conference', 'Professional conferences and summits', NOW()),
(2, 'Concert', 'concert', 'Music concerts and live performances', NOW()),
(3, 'Workshop', 'workshop', 'Educational workshops and training sessions', NOW()),
(4, 'Festival', 'festival', 'Cultural and entertainment festivals', NOW()),
(5, 'Networking', 'networking', 'Business networking events', NOW()),
(6, 'Sports', 'sports', 'Sports events and competitions', NOW());

-- ========================================
-- 6. EVENTS
-- ========================================
INSERT INTO `events` (`id`, `organizer_id`, `title`, `slug`, `description`, `event_type_id`, `venue_name`, `address`, `map_url`, `banner_image`, `start_time`, `end_time`, `status`, `is_featured`, `admin_share_percent`, `audience`, `language`, `tags`, `website`, `facebook`, `twitter`, `instagram`, `phone`, `video_url`, `country`, `region`, `city`, `views`, `created_at`) VALUES
(1, 1, 'Tech Summit 2026', 'tech-summit-2026', 'Annual technology summit bringing together innovators, entrepreneurs, and tech enthusiasts from across West Africa.', 1, 'Accra International Conference Centre', 'Independence Avenue, Accra', 'https://maps.google.com/?q=Accra+International+Conference+Centre', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87', '2026-03-15 09:00:00', '2026-03-15 18:00:00', 'published', 1, 10.00, 'Tech Professionals, Entrepreneurs', 'English', '["technology","innovation","startup","AI","blockchain"]', 'https://techsummit2026.com', 'https://facebook.com/techsummit2026', 'https://twitter.com/techsummit2026', 'https://instagram.com/techsummit2026', '+233302000001', 'https://youtube.com/watch?v=example1', 'Ghana', 'Greater Accra', 'Accra', 1250, NOW()),
(2, 2, 'Accra Music Awards 2026', 'accra-music-awards-2026', 'The biggest music awards ceremony celebrating the best of Ghanaian music. Featuring live performances from top artists.', 2, 'National Theatre of Ghana', 'Liberia Road, Accra', 'https://maps.google.com/?q=National+Theatre+Ghana', 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4', '2026-05-20 19:00:00', '2026-05-20 23:30:00', 'published', 1, 10.00, 'Music Lovers, General Public', 'English', '["music","awards","entertainment","concert"]', 'https://accramusicawards.com', 'https://facebook.com/accramusicawards', 'https://twitter.com/accramusicawards', 'https://instagram.com/accramusicawards', '+233302000002', 'https://youtube.com/watch?v=example2', 'Ghana', 'Greater Accra', 'Accra', 2340, NOW()),
(3, 3, 'Startup Pitch Competition', 'startup-pitch-competition', 'Pitch your startup idea to investors and win funding. Training sessions and networking opportunities included.', 5, 'Impact Hub Accra', 'Roman Ridge, Accra', 'https://maps.google.com/?q=Impact+Hub+Accra', 'https://images.unsplash.com/photo-1559136555-9303baea8ebd', '2026-04-10 14:00:00', '2026-04-10 19:00:00', 'published', 0, 10.00, 'Entrepreneurs, Investors', 'English', '["startup","entrepreneurship","investment","pitch"]', 'https://startuppitch.com', 'https://facebook.com/startuppitchgh', 'https://twitter.com/startuppitchgh', 'https://instagram.com/startuppitchgh', '+233302000003', NULL, 'Ghana', 'Greater Accra', 'Accra', 580, NOW()),
(4, 1, 'Digital Marketing Workshop', 'digital-marketing-workshop', 'Learn the latest digital marketing strategies from industry experts. Hands-on sessions included.', 3, 'Kofi Annan ICT Centre', 'Airport Residential Area, Accra', 'https://maps.google.com/?q=Kofi+Annan+ICT+Centre', 'https://images.unsplash.com/photo-1432888498266-38ffec3eaf0a', '2026-02-28 10:00:00', '2026-02-28 16:00:00', 'published', 0, 10.00, 'Marketers, Business Owners', 'English', '["marketing","digital","workshop","training"]', NULL, NULL, NULL, NULL, '+233302000004', NULL, 'Ghana', 'Greater Accra', 'Accra', 320, NOW()),
(5, 2, 'Jazz Night Live', 'jazz-night-live', 'An evening of smooth jazz with renowned local and international artists.', 2, 'Alliance Française', 'Airport Residential Area, Accra', 'https://maps.google.com/?q=Alliance+Francaise+Accra', 'https://images.unsplash.com/photo-1415201364774-f6f0bb35f28f', '2026-06-15 20:00:00', '2026-06-15 23:00:00', 'pending', 0, 10.00, 'Adults', 'English', '["jazz","music","concert","live"]', NULL, NULL, NULL, NULL, '+233302000005', NULL, 'Ghana', 'Greater Accra', 'Accra', 85, NOW()),
(6, 1, 'Future Leaders Summit', 'future-leaders-summit', 'Empowering the next generation of African leaders through mentorship and networking.', 1, 'Movenpick Ambassador Hotel', 'Independence Avenue, Accra', NULL, 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678', '2026-07-22 09:00:00', '2026-07-22 17:00:00', 'draft', 0, 10.00, 'Youth, Students', 'English', '["leadership","youth","mentorship"]', NULL, NULL, NULL, NULL, NULL, NULL, 'Ghana', 'Greater Accra', 'Accra', 12, NOW());

-- ========================================
-- 7. EVENT IMAGES
-- ========================================
INSERT INTO `event_images` (`event_id`, `image_path`, `created_at`) VALUES
(1, 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678', NOW()),
(1, 'https://images.unsplash.com/photo-1540575467063-178a50c2df87', NOW()),
(2, 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3', NOW()),
(2, 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4', NOW());

-- ========================================
-- 8. TICKET TYPES
-- ========================================
INSERT INTO `ticket_types` (`id`, `event_id`, `organizer_id`, `name`, `price`, `quantity`, `remaining`, `sale_price`, `description`, `max_per_user`, `status`, `created_at`) VALUES
(1, 1, 1, 'VIP Pass', 500.00, 50, 35, 450.00, 'Access to all sessions, VIP lounge, lunch, and networking dinner', 5, 'active', NOW()),
(2, 1, 1, 'Regular Pass', 200.00, 300, 180, NULL, 'Access to all sessions and lunch', 10, 'active', NOW()),
(3, 1, 1, 'Student Pass', 100.00, 100, 45, NULL, 'Student discount - Valid student ID required', 2, 'active', NOW()),
(4, 2, 2, 'Premium Seat', 350.00, 100, 25, NULL, 'Front row seats with meet & greet access', 4, 'active', NOW()),
(5, 2, 2, 'Standard Ticket', 150.00, 500, 220, 120.00, 'General admission', 10, 'active', NOW()),
(6, 3, 3, 'Participant Ticket', 50.00, 80, 32, NULL, 'Includes workshop materials and refreshments', 3, 'active', NOW()),
(7, 3, 3, 'Spectator Pass', 20.00, 50, 18, NULL, 'Watch pitches and network', 5, 'active', NOW()),
(8, 4, 1, 'Workshop Ticket', 120.00, 40, 12, NULL, 'Includes certificate and materials', 2, 'active', NOW()),
(9, 5, 2, 'General Admission', 80.00, 150, 92, NULL, 'Standing room', 4, 'active', NOW()),
(10, 5, 2, 'VIP Table', 400.00, 20, 8, NULL, 'Reserved table for 4 with complimentary drinks', 1, 'active', NOW());

-- ========================================
-- 9. ORDERS
-- ========================================
INSERT INTO `orders` (`id`, `user_id`, `subtotal`, `fees`, `total_amount`, `status`, `payment_reference`, `customer_email`, `customer_name`, `customer_phone`, `paid_at`, `created_at`) VALUES
(1, 4, 450.00, 13.50, 463.50, 'paid', 'PAY-TXN-001-2026', 'kofi.asante@gmail.com', 'Kofi Asante', '+233244000004', '2026-01-10 14:25:00', '2026-01-10 14:20:00'),
(2, 5, 300.00, 9.00, 309.00, 'paid', 'PAY-TXN-002-2026', 'abena.owusu@yahoo.com', 'Abena Owusu', '+233244000005', '2026-01-11 16:30:00', '2026-01-11 16:28:00'),
(3, 6, 200.00, 6.00, 206.00, 'paid', 'PAY-TXN-003-2026', 'yaw.boateng@gmail.com', 'Yaw Boateng', '+233244000006', '2026-01-12 10:15:00', '2026-01-12 10:12:00'),
(4, 4, 350.00, 10.50, 360.50, 'paid', 'PAY-TXN-004-2026', 'kofi.asante@gmail.com', 'Kofi Asante', '+233244000004', '2026-01-13 11:00:00', '2026-01-13 10:58:00'),
(5, 5, 100.00, 3.00, 103.00, 'paid', 'PAY-TXN-005-2026', 'abena.owusu@yahoo.com', 'Abena Owusu', '+233244000005', '2026-01-14 15:45:00', '2026-01-14 15:42:00'),
(6, 6, 120.00, 3.60, 123.60, 'pending', NULL, 'yaw.boateng@gmail.com', 'Yaw Boateng', '+233244000006', NULL, NOW()),
(7, 7, 240.00, 7.20, 247.20, 'failed', 'PAY-TXN-007-2026', 'adjoa@gmail.com', 'Adjoa Appiah', '+233244000007', NULL, NOW());

-- ========================================
-- 10. ORDER ITEMS
-- ========================================
INSERT INTO `order_items` (`order_id`, `event_id`, `ticket_type_id`, `quantity`, `unit_price`, `total_price`, `admin_share_percent`, `admin_amount`, `organizer_amount`, `payment_fee`, `created_at`) VALUES
(1, 1, 1, 1, 450.00, 450.00, 10.00, 40.50, 396.00, 13.50, '2026-01-10 14:20:00'),
(2, 2, 5, 2, 150.00, 300.00, 10.00, 27.00, 264.00, 9.00, '2026-01-11 16:28:00'),
(3, 1, 2, 1, 200.00, 200.00, 10.00, 18.00, 176.00, 6.00, '2026-01-12 10:12:00'),
(4, 2, 4, 1, 350.00, 350.00, 10.00, 31.50, 308.00, 10.50, '2026-01-13 10:58:00'),
(5, 1, 3, 1, 100.00, 100.00, 10.00, 9.00, 88.00, 3.00, '2026-01-14 15:42:00'),
(6, 4, 8, 1, 120.00, 120.00, 10.00, 10.80, 105.60, 3.60, NOW()),
(7, 5, 9, 3, 80.00, 240.00, 10.00, 21.60, 211.20, 7.20, NOW());

-- =========================================
-- 11. TICKETS
-- ========================================
INSERT INTO `tickets` (`id`, `order_id`, `event_id`, `ticket_type_id`, `ticket_code`, `status`, `attendee_id`, `created_at`) VALUES
(1, 1, 1, 1, 'TECH2026-VIP-0001', 'active', 1, '2026-01-10 14:25:00'),
(2, 2, 2, 5, 'MUSIC2026-STD-0001', 'active', 2, '2026-01-11 16:30:00'),
(3, 2, 2, 5, 'MUSIC2026-STD-0002', 'active', 2, '2026-01-11 16:30:00'),
(4, 3, 1, 2, 'TECH2026-REG-0001', 'used', 3, '2026-01-12 10:15:00'),
(5, 4, 2, 4, 'MUSIC2026-PREM-0001', 'active', 1, '2026-01-13 11:00:00'),
(6, 5, 1, 3, 'TECH2026-STU-0001', 'active', 2, '2026-01-14 15:45:00');

-- ========================================
-- 12. TRANSACTIONS
-- ========================================
INSERT INTO `transactions` (`reference`, `transaction_type`, `organizer_id`, `event_id`, `order_id`, `order_item_id`, `gross_amount`, `admin_amount`, `organizer_amount`, `payment_fee`, `status`, `description`, `created_at`) VALUES
('TXN-2026-0001', 'ticket_sale', 1, 1, 1, 1, 450.00, 40.50, 396.00, 13.50, 'completed', 'VIP Pass purchase for Tech Summit 2026', '2026-01-10 14:25:00'),
('TXN-2026-0002', 'ticket_sale', 2, 2, 2, 2, 300.00, 27.00, 264.00, 9.00, 'completed', 'Standard Ticket purchase for Accra Music Awards', '2026-01-11 16:30:00'),
('TXN-2026-0003', 'ticket_sale', 1, 1, 3, 3, 200.00, 18.00, 176.00, 6.00, 'completed', 'Regular Pass purchase', '2026-01-12 10:15:00'),
('TXN-2026-0004', 'ticket_sale', 2, 2, 4, 4, 350.00, 31.50, 308.00, 10.50, 'completed', 'Premium Seat purchase', '2026-01-13 11:00:00'),
('TXN-2026-0005', 'ticket_sale', 1, 1, 5, 5, 100.00, 9.00, 88.00, 3.00, 'completed', 'Student Pass purchase', '2026-01-14 15:45:00');

-- ========================================
-- 13. AWARDS
-- ========================================
INSERT INTO `awards` (`id`, `organizer_id`, `title`, `slug`, `description`, `banner_image`, `venue_name`, `address`, `ceremony_date`, `voting_start`, `voting_end`, `status`, `show_results`, `is_featured`, `admin_share_percent`, `country`, `region`, `city`, `phone`, `website`, `views`, `created_at`) VALUES
(1, 2, 'Ghana Music Awards 2026', 'ghana-music-awards-2026', 'Annual awards celebrating excellence in Ghanaian music across multiple categories.', 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30', 'National Theatre of Ghana', 'Liberia Road, Accra', '2026-08-15 20:00:00', '2026-06-01 00:00:00', '2026-08-10 23:59:59', 'published', 1, 1, 15.00, 'Ghana', 'Greater Accra', 'Accra', '+233302000010', 'https://ghanamusicawards.com', 4500, NOW()),
(2, 1, 'Tech Innovation Awards 2026', 'tech-innovation-awards-2026', 'Recognizing groundbreaking innovations in technology and entrepreneurship.', 'https://images.unsplash.com/photo-1531545514256-b1400bc00f31', 'Movenpick Ambassador Hotel', 'Independence Avenue, Accra', '2026-09-20 19:00:00', '2026-07-01 00:00:00', '2026-09-15 23:59:59', 'published', 1, 1, 15.00, 'Ghana', 'Greater Accra', 'Accra', '+233302000011', 'https://techinnoawards.com', 2100, NOW());

-- ========================================
-- 14. AWARD CATEGORIES
-- ========================================
INSERT INTO `award_categories` (`id`, `award_id`, `name`, `description`, `cost_per_vote`, `status`, `display_order`, `created_at`) VALUES
(1, 1, 'Artist of the Year', 'Best overall artist based on impact and popularity', 2.00, 'active', 1, NOW()),
(2, 1, 'Song of the Year', 'Most popular song of the year', 1.00, 'active', 2, NOW()),
(3, 1, 'Best New Artist', 'Breakthrough artist of the year', 1.50, 'active', 3, NOW()),
(4, 1, 'Best Collaboration', 'Best collaborative song', 1.00, 'active', 4, NOW()),
(5, 2, 'Startup of the Year', 'Most innovative startup', 3.00, 'active', 1, NOW()),
(6, 2, 'Best Mobile App', 'Most impactful mobile application', 2.00, 'active', 2, NOW()),
(7, 2, 'Tech Innovator Award', 'Individual making significant tech contributions', 2.50, 'active', 3, NOW());

-- ========================================
-- 15. AWARD NOMINEES
-- ========================================
INSERT INTO `award_nominees` (`id`, `category_id`, `award_id`, `name`, `description`, `image`, `display_order`, `created_at`) VALUES
-- Artist of the Year
(1, 1, 1, 'Sarkodie', 'Hip-hop legend with multiple hits this year', 'https://ui-avatars.com/api/?name=Sarkodie&size=200', 1, NOW()),
(2, 1, 1, 'King Promise', 'Afrobeats sensation with international acclaim', 'https://ui-avatars.com/api/?name=King+Promise&size=200', 2, NOW()),
(3, 1, 1, 'Stonebwoy', 'Dancehall king with massive year', 'https://ui-avatars.com/api/?name=Stonebwoy&size=200', 3, NOW()),
-- Song of the Year
(4, 2, 1, 'Terminator by Sarkodie', 'Chart-topping rap anthem', 'https://ui-avatars.com/api/?name=Terminator&size=200', 1, NOW()),
(5, 2, 1, 'Favourite Story by King Promise', 'Romantic afrobeats hit', 'https://ui-avatars.com/api/?name=Favourite+Story&size=200', 2, NOW()),
(6, 2, 1, 'Activate by Stonebwoy', 'Dancehall banger', 'https://ui-avatars.com/api/?name=Activate&size=200', 3, NOW()),
-- Best New Artist
(7, 3, 1, 'Gyakie', 'Rising star with viral hits', 'https://ui-avatars.com/api/?name=Gyakie&size=200', 1, NOW()),
(8, 3, 1, 'Black Sherif', 'Breakout rapper of the year', 'https://ui-avatars.com/api/?name=Black+Sherif&size=200', 2, NOW()),
-- Tech Awards
(9, 5, 2, 'MoMo Express', 'Revolutionary payment platform', 'https://ui-avatars.com/api/?name=MoMo+Express&size=200', 1, NOW()),
(10, 5, 2, 'FarmConnect Ghana', 'AgriTech connecting farmers to markets', 'https://ui-avatars.com/api/?name=FarmConnect&size=200', 2, NOW()),
(11, 6, 2, 'EduLearn App', 'Educational platform for African students', 'https://ui-avatars.com/api/?name=EduLearn&size=200', 1, NOW()),
(12, 6, 2, 'HealthTracker Ghana', 'Healthcare management app', 'https://ui-avatars.com/api/?name=HealthTracker&size=200', 2, NOW());

-- ========================================
-- 16. AWARD VOTES
-- ========================================
INSERT INTO `award_votes` (`nominee_id`, `category_id`, `award_id`, `number_of_votes`, `cost_per_vote`, `gross_amount`, `admin_share_percent`, `admin_amount`, `organizer_amount`, `payment_fee`, `status`, `reference`, `voter_name`, `voter_email`, `voter_phone`, `created_at`) VALUES
(1, 1, 1, 50, 2.00, 100.00, 15.00, 12.00, 85.00, 3.00, 'paid', 'VOTE-2026-0001', 'Kofi Asante', 'kofi.asante@gmail.com', '+233244000004', '2026-01-12 09:30:00'),
(2, 1, 1, 75, 2.00, 150.00, 15.00, 18.00, 127.50, 4.50, 'paid', 'VOTE-2026-0002', 'Abena Owusu', 'abena.owusu@yahoo.com', '+233244000005', '2026-01-12 10:15:00'),
(3, 1, 1, 100, 2.00, 200.00, 15.00, 24.00, 170.00, 6.00, 'paid', 'VOTE-2026-0003', 'Yaw Boateng', 'yaw.boateng@gmail.com', '+233244000006', '2026-01-12 14:20:00'),
(4, 2, 1, 25, 1.00, 25.00, 15.00, 3.00, 21.25, 0.75, 'paid', 'VOTE-2026-0004', 'Kofi Asante', 'kofi.asante@gmail.com', '+233244000004', '2026-01-13 11:00:00'),
(7, 3, 1, 30, 1.50, 45.00, 15.00, 5.40, 38.25, 1.35, 'paid', 'VOTE-2026-0005', 'Abena Owusu', 'abena.owusu@yahoo.com', '+233244000005', '2026-01-13 15:30:00'),
(9, 5, 2, 20, 3.00, 60.00, 15.00, 7.20, 51.00, 1.80, 'paid', 'VOTE-2026-0006', 'Samuel Osei', 'samuel@eventorganizer.com', '+233244000010', '2026-01-14 09:00:00');

-- ========================================
-- 17. EVENT REVIEWS
-- ========================================
INSERT INTO `event_reviews` (`event_id`, `reviewer_id`, `rating`, `comment`, `created_at`) VALUES
(1, 4, 5, 'Amazing event! Well organized and great speakers. Learned so much about AI and blockchain.', '2026-03-16 10:00:00'),
(1, 5, 4, 'Very informative sessions. The networking sessions were particularly valuable.', '2026-03-16 11:30:00'),
(2, 4, 5, 'Best music awards ever! The performances were incredible. Great venue too.', '2026-05-21 08:00:00'),
(2, 6, 4, 'Fantastic show but the seating could have been better organized.', '2026-05-21 09:15:00');

-- ========================================
-- 18. PAYOUT REQUESTS
-- ========================================
INSERT INTO `payout_requests` (`id`, `organizer_id`, `event_id`, `payout_type`, `amount`, `gross_amount`, `admin_fee`, `payment_method`, `account_number`, `account_name`, `bank_name`, `status`, `processed_by`, `processed_at`, `notes`, `created_at`) VALUES
(1, 1, 1, 'event', 2500.00, 2777.78, 277.78, 'bank_transfer', '1234567890', 'EventPro Ghana Ltd', 'Ghana Commercial Bank', 'completed', 1, '2026-01-15 10:00:00', 'Payout for Tech Summit ticket sales', '2026-01-14 16:00:00'),
(2, 2, 2, 'event', 4500.00, 5000.00, 500.00, 'mobile_money', '0244000003', 'Ama Serwaa', NULL, 'completed', 1, '2026-01-16 14:30:00', 'Payout for Music Awards', '2026-01-15 09:00:00'),
(3, 1, NULL, 'award', 1200.00, 1333.33, 133.33, 'bank_transfer', '1234567890', 'EventPro Ghana Ltd', 'Ghana Commercial Bank', 'pending', NULL, NULL, 'Pending payout for award votes', NOW());

-- ========================================
-- 19. SCANNER ASSIGNMENTS
-- ========================================
INSERT INTO `scanner_assignments` (`user_id`, `event_id`, `organizer_id`, `created_at`) VALUES
(8, 1, 1, NOW()),
(8, 4, 1, NOW());

-- ========================================
-- 20. POS ASSIGNMENTS
-- ========================================
INSERT INTO `pos_assignments` (`user_id`, `event_id`, `organizer_id`, `created_at`) VALUES
(9, 2, 2, NOW()),
(9, 5, 2, NOW());

-- ========================================
-- 21. PLATFORM SETTINGS
-- ========================================
INSERT INTO `platform_settings` (`setting_key`, `setting_value`, `setting_type`, `description`, `created_at`) VALUES
('default_event_admin_share', '10.00', 'number', 'Default admin revenue share percentage for events', NOW()),
('default_award_admin_share', '15.00', 'number', 'Default admin revenue share percentage for awards', NOW()),
('paystack_fee_percent', '3.00', 'number', 'Paystack transaction fee percentage', NOW()),
('min_payout_amount', '100.00', 'number', 'Minimum amount required for payout request', NOW()),
('payout_hold_days', '7', 'number', 'Number of days to hold funds before making available for payout', NOW()),
('platform_name', 'Eventic', 'string', 'Platform name', NOW()),
('support_email', 'support@eventic.com', 'string', 'Support contact email', NOW()),
('enable_email_notifications', 'true', 'boolean', 'Enable/disable email notifications', NOW());

-- ========================================
-- 22. ORGANIZER FOLLOWERS
-- ========================================
INSERT INTO `organizer_followers` (`organizer_id`, `follower_id`, `created_at`) VALUES
(1, 4, NOW()),
(1, 5, NOW()),
(1, 6, NOW()),
(2, 4, NOW()),
(2, 5, NOW()),
(2, 6, NOW()),
(2, 7, NOW()),
(3, 4, NOW());

-- ========================================
-- 23. AUDIT LOGS
-- ========================================
INSERT INTO `audit_logs` (`user_id`, `action`, `ip_address`, `user_agent`, `metadata`, `created_at`) VALUES
(1, 'login', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '{"success":true,"method":"password"}', NOW()),
(2, 'event_created', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', '{"event_id":1,"event_title":"Tech Summit 2026"}', '2026-01-05 10:00:00'),
(4, 'ticket_purchased', '192.168.1.102', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)', '{"order_id":1,"amount":463.50}', '2026-01-10 14:25:00'),
(5, 'vote_cast', '192.168.1.103', 'Mozilla/5.0 (Android 12; Mobile)', '{"nominee_id":2,"votes":75}', '2026-01-12 10:15:00'),
(1, 'payout_approved', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '{"payout_id":1,"amount":2500.00}', '2026-01-15 10:00:00');

-- ========================================
-- 24. REFRESH TOKENS (Example active sessions)
-- ========================================
INSERT INTO `refresh_tokens` (`user_id`, `token_hash`, `device_name`, `ip_address`, `user_agent`, `expires_at`, `revoked`, `created_at`) VALUES
(4, SHA2(CONCAT('refresh_token_', UUID()), 256), 'iPhone 13', '192.168.1.102', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', DATE_ADD(NOW(), INTERVAL 7 DAY), 0, NOW()),
(5, SHA2(CONCAT('refresh_token_', UUID()), 256), 'Samsung Galaxy S21', '192.168.1.103', 'Mozilla/5.0 (Android 12; Mobile)', DATE_ADD(NOW(), INTERVAL 7 DAY), 0, NOW()),
(2, SHA2(CONCAT('refresh_token_', UUID()), 256), 'MacBook Pro', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', DATE_ADD(NOW(), INTERVAL 7 DAY), 0, NOW());

-- ========================================
-- 25. AWARDS IMAGES
-- ========================================
INSERT INTO `awards_images` (`award_id`, `image_path`, `created_at`) VALUES
(1, 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30', NOW()),
(1, 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3', NOW()),
(2, 'https://images.unsplash.com/photo-1531545514256-b1400bc00f31', NOW());

-- ========================================
-- SUMMARY STATISTICS
-- ========================================
-- Users: 10 (1 admin, 3 organizers, 4 attendees, 1 scanner, 1 pos)
-- Organizers: 3 organizations
-- Events: 6 events (4 published, 1 pending, 1 draft)
-- Ticket Types: 10 different ticket types
-- Orders: 7 orders (5 paid, 1 pending, 1 failed)
-- Tickets: 6 individual tickets
-- Awards: 2 award ceremonies
-- Award Categories: 7 categories
-- Award Nominees: 12 nominees
-- Award Votes: 6 vote transactions
-- Transactions: 5 ticket sale transactions

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- ========================================
-- END OF SEED DATA
-- ========================================
