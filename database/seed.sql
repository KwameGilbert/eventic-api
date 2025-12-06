-- ============================================
-- EVENTIC DATABASE SEED DATA
-- Run this after schema.sql
-- ============================================

-- First, let's insert some event types
INSERT INTO `event_types` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Concert / Music', 'concert-music', 'Live music performances, concerts, and music festivals'),
(2, 'Sport / Fitness', 'sport-fitness', 'Sports events, matches, fitness activities'),
(3, 'Theater / Arts', 'theater-arts', 'Theater performances, art exhibitions, cultural events'),
(4, 'Food & Drink', 'food-drink', 'Food festivals, wine tastings, culinary events'),
(5, 'Conference', 'conference', 'Business conferences, seminars, professional events'),
(6, 'Cinema', 'cinema', 'Film screenings, movie premieres, film festivals'),
(7, 'Entertainment', 'entertainment', 'Comedy shows, variety performances, entertainment events'),
(8, 'Workshop', 'workshop', 'Hands-on workshops, training sessions, skill-building events');

-- Insert a test user (organizer) - Password is: password123
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `email_verified`, `status`) VALUES
(1, 'Admin User', 'admin@eventic.com', '+233200000001', '$argon2id$v=19$m=65536,t=4,p=1$d2p6cERBaVlBcTlvWEpMYg$rjnw1tViE6n/bq+fKnHl4k6k5bTzPyJbNVxFqFk5Nxs', 'admin', 1, 'active'),
(2, 'EventPro Ghana', 'organizer@eventpro.gh', '+233201234567', '$argon2id$v=19$m=65536,t=4,p=1$d2p6cERBaVlBcTlvWEpMYg$rjnw1tViE6n/bq+fKnHl4k6k5bTzPyJbNVxFqFk5Nxs', 'organizer', 1, 'active'),
(3, 'Live Nation Africa', 'info@livenation.africa', '+233209876543', '$argon2id$v=19$m=65536,t=4,p=1$d2p6cERBaVlBcTlvWEpMYg$rjnw1tViE6n/bq+fKnHl4k6k5bTzPyJbNVxFqFk5Nxs', 'organizer', 1, 'active'),
(4, 'TechHub Accra', 'events@techhub.gh', '+233205551234', '$argon2id$v=19$m=65536,t=4,p=1$d2p6cERBaVlBcTlvWEpMYg$rjnw1tViE6n/bq+fKnHl4k6k5bTzPyJbNVxFqFk5Nxs', 'organizer', 1, 'active'),
(5, 'Test Attendee', 'attendee@test.com', '+233241234567', '$argon2id$v=19$m=65536,t=4,p=1$d2p6cERBaVlBcTlvWEpMYg$rjnw1tViE6n/bq+fKnHl4k6k5bTzPyJbNVxFqFk5Nxs', 'attendee', 1, 'active');

-- Insert organizer profiles
INSERT INTO `organizers` (`id`, `user_id`, `organization_name`, `bio`, `profile_image`, `social_facebook`, `social_instagram`, `social_twitter`) VALUES
(1, 2, 'EventPro Ghana', 'Premier event organizers in Ghana. We create unforgettable experiences through world-class entertainment, corporate events, and community gatherings.', 'https://ui-avatars.com/api/?name=EventPro+Ghana&background=FF6B35&color=fff&size=200', 'https://facebook.com/eventproghana', 'https://instagram.com/eventprogh', 'https://twitter.com/eventprogh'),
(2, 3, 'Live Nation Africa', 'Africas leading live entertainment company, producing music festivals and concerts across the continent. Bringing global artists to African stages since 2015.', 'https://ui-avatars.com/api/?name=Live+Nation&background=E91E63&color=fff&size=200', 'https://facebook.com/livenationafrica', 'https://instagram.com/livenationafrica', 'https://twitter.com/livenationafr'),
(3, 4, 'TechHub Accra', 'Ghanas premier technology community. Organizing tech conferences, workshops, and networking events to foster innovation and digital transformation.', 'https://ui-avatars.com/api/?name=TechHub+Accra&background=673AB7&color=fff&size=200', 'https://facebook.com/techhubaccra', 'https://instagram.com/techhubaccra', 'https://twitter.com/techhubaccra');

-- Insert Events (with is_featured for carousel)
INSERT INTO `events` (`id`, `organizer_id`, `title`, `slug`, `description`, `event_type_id`, `venue_name`, `address`, `map_url`, `banner_image`, `start_time`, `end_time`, `status`, `is_featured`, `audience`, `language`, `tags`) VALUES

-- Event 1: Afro Nation Ghana (FEATURED)
(1, 2, 'Afro Nation Ghana 2025', 'afro-nation-ghana-2025', 
'Experience the biggest Afrobeats festival in West Africa! Afro Nation Ghana 2025 brings together the hottest African artists and international DJs for an unforgettable weekend of music, culture, and celebration on the beautiful beaches of Accra. Featuring performances by Burna Boy, Wizkid, Davido, Stonebwoy, and many more!', 
1, 'Laboma Beach', 'Accra, Greater Accra Region, Ghana', 
'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.773449735772!2d-0.186964!3d5.603717!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1',
'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=1920&q=80',
'2025-12-27 16:00:00', '2025-12-29 23:59:00', 'published', 1, 
'Music Lovers, Festival Goers, 18+', 'English',
'["Afrobeats", "Music Festival", "Beach Party", "Live Music", "Accra"]'),

-- Event 2: Ghana Premier League Match (FEATURED)
(2, 1, 'Hearts of Oak vs Asante Kotoko', 'hearts-vs-kotoko-2025',
'The biggest rivalry in Ghanaian football! Watch Hearts of Oak take on Asante Kotoko in this thrilling Ghana Premier League showdown. Experience the electric atmosphere as the Phobians face the Porcupine Warriors in front of thousands of passionate fans.',
2, 'Accra Sports Stadium', 'Accra, Greater Accra Region, Ghana',
'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.8!2d-0.19!3d5.55!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1',
'https://images.unsplash.com/photo-1459865264687-595d652de67e?w=1920&q=80',
'2025-12-20 15:00:00', '2025-12-20 17:30:00', 'published', 1,
'Sports Fans, Families, All Ages', 'English',
'["Football", "Soccer", "Ghana Premier League", "Sports"]'),

-- Event 3: Tech Summit Ghana (FEATURED)
(3, 3, 'Tech Summit Ghana 2025', 'tech-summit-ghana-2025',
'Join Ghanas largest technology conference bringing together innovators, entrepreneurs, investors, and industry leaders. Explore cutting-edge technologies including AI, blockchain, fintech, and sustainable tech. Network with professionals from across Africa and beyond. Featuring keynotes from Google, Microsoft, and leading African tech companies.',
5, 'Kempinski Hotel Gold Coast City', 'Accra, Greater Accra Region, Ghana',
'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.5!2d-0.17!3d5.58!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1',
'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1920&q=80',
'2026-01-15 09:00:00', '2026-01-17 18:00:00', 'published', 1,
'Tech Professionals, Entrepreneurs, Students', 'English',
'["Technology", "Conference", "Innovation", "Networking", "Startup"]'),

-- Event 4: Jazz Night Accra (FEATURED)
(4, 1, 'Jazz Night at +233 Bar', 'jazz-night-accra',
'Experience an unforgettable evening of smooth jazz with world-renowned artists. This intimate performance features a carefully curated selection of contemporary and classic jazz pieces that will transport you to a world of musical excellence. The +233 Bar provides the perfect ambiance for an evening of sophisticated entertainment with craft cocktails and fine dining.',
1, '+233 Jazz Bar & Grill', 'Osu, Accra, Ghana',
'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.773449735772!2d-0.186964!3d5.603717!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1',
'https://images.unsplash.com/photo-1511192336575-5a79af67a629?w=800&q=80',
'2025-12-15 20:00:00', '2025-12-15 23:30:00', 'published', 1,
'Adults, Jazz Enthusiasts, Music Lovers', 'English',
'["Jazz", "Live Music", "Night Event", "Concert"]'),

-- Event 5: Chale Wote Festival (FEATURED)
(5, 2, 'Chale Wote Street Art Festival 2025', 'chale-wote-2025',
'Ghanas premiere street art festival returns to Jamestown! Experience a vibrant celebration of African art, music, and culture. Featuring murals, installations, performances, fashion shows, and interactive workshops. Join thousands of artists and art lovers for a weekend of creativity and cultural exchange.',
3, 'Jamestown, Accra', 'Jamestown, Accra, Ghana',
'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.9!2d-0.21!3d5.52!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1',
'https://images.unsplash.com/photo-1561214115-f2f134cc4912?w=800&q=80',
'2025-08-15 10:00:00', '2025-08-17 22:00:00', 'published', 1,
'Art Lovers, Families, All Ages', 'English',
'["Art", "Street Art", "Festival", "Culture", "Ghana"]'),

-- Event 6: Ghana Food & Wine Expo (NOT FEATURED)
(6, 1, 'Ghana Food & Wine Expo 2025', 'food-wine-expo-2025',
'Indulge in a culinary journey featuring the finest local and international cuisines. Meet renowned chefs, sample exclusive wines from around the world, and participate in live cooking demonstrations. This expo celebrates the rich flavors of Ghana and beyond, offering tastings, masterclasses, and networking opportunities for food enthusiasts.',
4, 'Accra International Conference Centre', 'Accra, Greater Accra Region, Ghana',
'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.6!2d-0.18!3d5.57!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1',
'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800&q=80',
'2025-12-22 12:00:00', '2025-12-22 20:00:00', 'published', 0,
'Food Enthusiasts, Wine Lovers, Adults', 'English',
'["Food", "Wine", "Tasting", "Culinary", "Expo"]'),

-- Event 7: Comedy Night (NOT FEATURED)
(7, 1, 'Comedy Fiesta Ghana', 'comedy-fiesta-ghana-2025',
'Get ready for a night of non-stop laughter with Ghanas top comedians! This hilarious show features stand-up performances from DKB, Clemento Suarez, OB Amponsah, and special guest comedians from Nigeria and South Africa. Perfect for a fun evening out with friends and family. Doors open at 7:30 PM for pre-show entertainment and refreshments.',
7, 'National Theatre of Ghana', 'Accra, Greater Accra Region, Ghana',
'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.7!2d-0.20!3d5.54!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1',
'https://images.unsplash.com/photo-1585699324551-f6c309eedeca?w=800&q=80',
'2025-12-18 20:00:00', '2025-12-18 23:00:00', 'published', 0,
'Adults, Families, Comedy Fans', 'English',
'["Comedy", "Stand-up", "Entertainment", "Night Out"]'),

-- Event 8: Marathon (NOT FEATURED)
(8, 1, 'Accra International Marathon 2025', 'accra-marathon-2025',
'Lace up your running shoes for the annual Accra International Marathon! Choose from the full marathon (42km), half marathon (21km), or 10K fun run. The scenic route takes you through historic Accra neighborhoods with thousands of cheering spectators. All proceeds support local charities and youth sports development programs.',
2, 'Independence Square', 'Accra, Greater Accra Region, Ghana',
'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.8!2d-0.19!3d5.55!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1',
'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?w=1920&q=80',
'2026-03-15 06:00:00', '2026-03-15 14:00:00', 'published', 0,
'Runners, Fitness Enthusiasts, All Ages', 'English',
'["Marathon", "Running", "Charity", "Fitness", "Sports"]');

-- Insert Ticket Types for each event
-- Event 1: Afro Nation Ghana
INSERT INTO `ticket_types` (`id`, `event_id`, `organizer_id`, `name`, `price`, `quantity`, `remaining`, `dynamic_fee`, `sale_start`, `sale_end`, `max_per_user`, `status`) VALUES
(1, 1, 1, 'General Admission', 250.00, 5000, 4850, 2.50, '2025-06-01 00:00:00', '2025-12-27 12:00:00', 6, 'active'),
(2, 1, 1, 'VIP Pass', 500.00, 1000, 950, 5.00, '2025-06-01 00:00:00', '2025-12-27 12:00:00', 4, 'active'),
(3, 1, 1, 'VVIP Experience', 1000.00, 200, 195, 10.00, '2025-06-01 00:00:00', '2025-12-27 12:00:00', 2, 'active');

-- Event 2: Hearts vs Kotoko
INSERT INTO `ticket_types` (`id`, `event_id`, `organizer_id`, `name`, `price`, `quantity`, `remaining`, `dynamic_fee`, `sale_start`, `sale_end`, `max_per_user`, `status`) VALUES
(4, 2, 1, 'Regular Stand', 30.00, 10000, 8500, 0.50, '2025-11-01 00:00:00', '2025-12-20 13:00:00', 10, 'active'),
(5, 2, 1, 'VIP Stand', 80.00, 2000, 1800, 1.00, '2025-11-01 00:00:00', '2025-12-20 13:00:00', 6, 'active'),
(6, 2, 1, 'Presidential Box', 200.00, 100, 95, 2.00, '2025-11-01 00:00:00', '2025-12-20 13:00:00', 4, 'active');

-- Event 3: Tech Summit Ghana
INSERT INTO `ticket_types` (`id`, `event_id`, `organizer_id`, `name`, `price`, `quantity`, `remaining`, `dynamic_fee`, `sale_start`, `sale_end`, `max_per_user`, `status`) VALUES
(7, 3, 3, 'Early Bird', 150.00, 300, 50, 2.00, '2025-09-01 00:00:00', '2025-11-30 23:59:00', 3, 'active'),
(8, 3, 3, 'Standard Pass', 250.00, 800, 750, 3.00, '2025-09-01 00:00:00', '2026-01-14 23:59:00', 5, 'active'),
(9, 3, 3, 'VIP Package', 500.00, 150, 140, 5.00, '2025-09-01 00:00:00', '2026-01-14 23:59:00', 2, 'active'),
(10, 3, 3, 'Student Pass', 75.00, 200, 180, 1.00, '2025-09-01 00:00:00', '2026-01-14 23:59:00', 1, 'active');

-- Event 4: Jazz Night
INSERT INTO `ticket_types` (`id`, `event_id`, `organizer_id`, `name`, `price`, `quantity`, `remaining`, `dynamic_fee`, `sale_start`, `sale_end`, `max_per_user`, `status`) VALUES
(11, 4, 1, 'Regular', 80.00, 150, 140, 1.00, '2025-11-01 00:00:00', '2025-12-15 18:00:00', 4, 'active'),
(12, 4, 1, 'VIP Table (2 seats)', 250.00, 30, 28, 3.00, '2025-11-01 00:00:00', '2025-12-15 18:00:00', 2, 'active'),
(13, 4, 1, 'Premium Table (4 seats)', 450.00, 15, 14, 5.00, '2025-11-01 00:00:00', '2025-12-15 18:00:00', 1, 'active');

-- Event 5: Chale Wote Festival
INSERT INTO `ticket_types` (`id`, `event_id`, `organizer_id`, `name`, `price`, `quantity`, `remaining`, `dynamic_fee`, `sale_start`, `sale_end`, `max_per_user`, `status`) VALUES
(14, 5, 1, 'Day Pass', 20.00, 5000, 4800, 0.50, '2025-05-01 00:00:00', '2025-08-17 10:00:00', 10, 'active'),
(15, 5, 1, 'Weekend Pass', 50.00, 2000, 1850, 1.00, '2025-05-01 00:00:00', '2025-08-15 08:00:00', 6, 'active'),
(16, 5, 1, 'VIP Weekend', 150.00, 300, 290, 2.00, '2025-05-01 00:00:00', '2025-08-15 08:00:00', 4, 'active');

-- Event 6: Food & Wine Expo
INSERT INTO `ticket_types` (`id`, `event_id`, `organizer_id`, `name`, `price`, `quantity`, `remaining`, `dynamic_fee`, `sale_start`, `sale_end`, `max_per_user`, `status`) VALUES
(17, 6, 1, 'Standard Entry', 60.00, 800, 750, 1.00, '2025-10-01 00:00:00', '2025-12-22 10:00:00', 5, 'active'),
(18, 6, 1, 'VIP Tasting Pass', 150.00, 200, 185, 2.00, '2025-10-01 00:00:00', '2025-12-22 10:00:00', 3, 'active'),
(19, 6, 1, 'Masterclass Bundle', 250.00, 50, 45, 3.00, '2025-10-01 00:00:00', '2025-12-22 10:00:00', 2, 'active');

-- Event 7: Comedy Fiesta
INSERT INTO `ticket_types` (`id`, `event_id`, `organizer_id`, `name`, `price`, `quantity`, `remaining`, `dynamic_fee`, `sale_start`, `sale_end`, `max_per_user`, `status`) VALUES
(20, 7, 1, 'Standard Seating', 50.00, 500, 450, 1.00, '2025-11-01 00:00:00', '2025-12-18 18:00:00', 6, 'active'),
(21, 7, 1, 'Front Row', 100.00, 100, 90, 2.00, '2025-11-01 00:00:00', '2025-12-18 18:00:00', 4, 'active'),
(22, 7, 1, 'VIP Lounge', 200.00, 40, 38, 3.00, '2025-11-01 00:00:00', '2025-12-18 18:00:00', 4, 'active');

-- Event 8: Marathon
INSERT INTO `ticket_types` (`id`, `event_id`, `organizer_id`, `name`, `price`, `quantity`, `remaining`, `dynamic_fee`, `sale_start`, `sale_end`, `max_per_user`, `status`) VALUES
(23, 8, 1, '10K Fun Run', 50.00, 3000, 2800, 1.00, '2025-11-01 00:00:00', '2026-03-14 23:59:00', 1, 'active'),
(24, 8, 1, 'Half Marathon', 100.00, 2000, 1850, 2.00, '2025-11-01 00:00:00', '2026-03-14 23:59:00', 1, 'active'),
(25, 8, 1, 'Full Marathon', 150.00, 1000, 920, 2.50, '2025-11-01 00:00:00', '2026-03-14 23:59:00', 1, 'active');

-- Insert some event images
INSERT INTO `event_images` (`id`, `event_id`, `image_path`) VALUES
(1, 1, 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=1920&q=80'),
(2, 1, 'https://images.unsplash.com/photo-1429962714451-bb934ecdc4ec?w=1920&q=80'),
(3, 2, 'https://images.unsplash.com/photo-1459865264687-595d652de67e?w=1920&q=80'),
(4, 3, 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1920&q=80'),
(5, 3, 'https://images.unsplash.com/photo-1591115765373-5207764f72e7?w=1920&q=80'),
(6, 4, 'https://images.unsplash.com/photo-1511192336575-5a79af67a629?w=800&q=80'),
(7, 5, 'https://images.unsplash.com/photo-1561214115-f2f134cc4912?w=800&q=80'),
(8, 6, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800&q=80'),
(9, 7, 'https://images.unsplash.com/photo-1585699324551-f6c309eedeca?w=800&q=80'),
(10, 8, 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?w=1920&q=80');

-- Insert test attendee profile
INSERT INTO `attendees` (`id`, `user_id`, `first_name`, `last_name`, `email`, `phone`) VALUES
(1, 5, 'Test', 'Attendee', 'attendee@test.com', '+233241234567');

-- ============================================
-- SEED DATA COMPLETE
-- ============================================
-- 
-- Login credentials:
-- Admin: admin@eventic.com / password123
-- Organizer 1: organizer@eventpro.gh / password123  
-- Organizer 2: info@livenation.africa / password123
-- Organizer 3: events@techhub.gh / password123
-- Attendee: attendee@test.com / password123
--
-- ============================================
