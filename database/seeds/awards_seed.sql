-- =====================================================
-- AWARDS SYSTEM SEED DATA (Updated for separate Awards table)
-- =====================================================
-- This script creates realistic seed data for the new independent Awards system
-- Includes: Awards shows, Categories, Nominees, and Votes
-- =====================================================

-- =====================================================
-- 1. AWARD SHOWS (Not Events!)
-- =====================================================

-- Award 1: Ghana Music Awards 2025
INSERT INTO awards (organizer_id, title, slug, description, venue_name, address, ceremony_date, voting_start, voting_end, status, is_featured, created_at, updated_at)
VALUES 
(1, 'Ghana Music Awards 2025', 'ghana-music-awards-2025', 'The most prestigious music awards in Ghana celebrating excellence in music', 'Accra International Conference Centre', 'Liberation Road, Accra, Ghana', '2025-03-15 19:00:00', '2025-01-01 00:00:00', '2025-03-10 23:59:59', 'published', 1, NOW(), NOW());

-- Award 2: Ghana Movie Awards 2025
INSERT INTO awards (organizer_id, title, slug, description, venue_name, address, ceremony_date, voting_start, voting_end, status, is_featured, created_at, updated_at)
VALUES 
(1, 'Ghana Movie Awards 2025', 'ghana-movie-awards-2025', 'Celebrating excellence in Ghanaian cinema and filmmaking', 'National Theatre of Ghana', 'Liberia Road, Accra, Ghana', '2025-04-20 18:00:00', '2025-02-01 00:00:00', '2025-04-15 23:59:59', 'published', 1, NOW(), NOW());

-- Award 3: Tech Innovation Awards Ghana 2025
INSERT INTO awards (organizer_id, title, slug, description, venue_name, address, ceremony_date, voting_start, voting_end, status, is_featured, created_at, updated_at)
VALUES 
(1, 'Tech Innovation Awards Ghana 2025', 'tech-innovation-awards-2025', 'Recognizing outstanding innovations and achievements in Ghana''s tech ecosystem', 'Kempinski Hotel Gold Coast City', 'Gamel Abdul Nasser Avenue, Accra, Ghana', '2025-05-10 17:00:00', '2025-03-01 00:00:00', '2025-05-05 23:59:59', 'published', 0, NOW(), NOW());

-- Award 4: Ghana Sports Personality Awards 2025
INSERT INTO awards (organizer_id, title, slug, description, venue_name, address, ceremony_date, voting_start, voting_end, status, is_featured, created_at, updated_at)
VALUES 
(1, 'Ghana Sports Personality Awards 2025', 'ghana-sports-awards-2025', 'Honoring excellence in Ghanaian sports across all disciplines', 'Accra Sports Stadium', 'Osu, Accra, Ghana', '2025-06-05 19:00:00', '2025-04-01 00:00:00', '2025-05-31 23:59:59', 'published', 0, NOW(), NOW());

-- =====================================================
-- 2. AWARD CATEGORIES (Now references awards, not events)
-- =====================================================

-- Categories for Ghana Music Awards (Award ID: 1)
INSERT INTO award_categories (award_id, name, description, image, cost_per_vote, voting_start, voting_end, status, display_order, created_at, updated_at)
VALUES 
(1, 'Artiste of the Year', 'The most outstanding artiste of the year based on overall achievement', '/images/awards/aoty.jpg', 2.00, '2025-01-01 00:00:00', '2025-03-10 23:59:59', 'active', 1, NOW(), NOW()),
(1, 'Song of the Year', 'The most popular and impactful song of the year', '/images/awards/soty.jpg', 1.50, '2025-01-01 00:00:00', '2025-03-10 23:59:59', 'active', 2, NOW(), NOW()),
(1, 'Best New Artiste', 'The most promising new artiste who debuted this year', '/images/awards/new-artiste.jpg', 1.00, '2025-01-01 00:00:00', '2025-03-10 23:59:59', 'active', 3, NOW(), NOW()),
(1, 'Best Rapper', 'Excellence in rap and hip-hop music', '/images/awards/rapper.jpg', 1.00, '2025-01-01 00:00:00', '2025-03-10 23:59:59', 'active', 4, NOW(), NOW()),
(1, 'Best Vocalist', 'Outstanding vocal performance and range', '/images/awards/vocalist.jpg', 1.00, '2025-01-01 00:00:00', '2025-03-10 23:59:59', 'active', 5, NOW(), NOW()),
(1, 'Best Music Video', 'Excellence in music video production and creativity', '/images/awards/video.jpg', 1.00, '2025-01-01 00:00:00', '2025-03-10 23:59:59', 'active', 6, NOW(), NOW());

-- Categories for Ghana Movie Awards (Award ID: 2)
INSERT INTO award_categories (award_id, name, description, image, cost_per_vote, voting_start, voting_end, status, display_order, created_at, updated_at)
VALUES 
(2, 'Best Actor', 'Outstanding performance by a male actor', '/images/awards/best-actor.jpg', 2.00, '2025-02-01 00:00:00', '2025-04-15 23:59:59', 'active', 1, NOW(), NOW()),
(2, 'Best Actress', 'Outstanding performance by a female actress', '/images/awards/best-actress.jpg', 2.00, '2025-02-01 00:00:00', '2025-04-15 23:59:59', 'active', 2, NOW(), NOW()),
(2, 'Best Movie', 'The best movie of the year', '/images/awards/best-movie.jpg', 2.50, '2025-02-01 00:00:00', '2025-04-15 23:59:59', 'active', 3, NOW(), NOW()),
(2, 'Best Director', 'Excellence in film direction', '/images/awards/director.jpg', 1.50, '2025-02-01 00:00:00', '2025-04-15 23:59:59', 'active', 4, NOW(), NOW()),
(2, 'Best Screenplay', 'Best written screenplay or script', '/images/awards/screenplay.jpg', 1.00, '2025-02-01 00:00:00', '2025-04-15 23:59:59', 'active', 5, NOW(), NOW());

-- Categories for Tech Innovation Awards (Award ID: 3)
INSERT INTO award_categories (award_id, name, description, image, cost_per_vote, voting_start, voting_end, status, display_order, created_at, updated_at)
VALUES 
(3, 'Tech Startup of the Year', 'Most innovative startup in Ghana''s tech ecosystem', '/images/awards/startup.jpg', 3.00, '2025-03-01 00:00:00', '2025-05-05 23:59:59', 'active', 1, NOW(), NOW()),
(3, 'Tech Innovator of the Year', 'Individual making significant tech innovations', '/images/awards/innovator.jpg', 2.00, '2025-03-01 00:00:00', '2025-05-05 23:59:59', 'active', 2, NOW(), NOW()),
(3, 'Best Mobile App', 'Most impactful mobile application', '/images/awards/app.jpg', 1.50, '2025-03-01 00:00:00', '2025-05-05 23:59:59', 'active', 3, NOW(), NOW()),
(3, 'Best Fintech Solution', 'Excellence in financial technology', '/images/awards/fintech.jpg', 2.00, '2025-03-01 00:00:00', '2025-05-05 23:59:59', 'active', 4, NOW(), NOW());

-- Categories for Sports Awards (Award ID: 4)
INSERT INTO award_categories (award_id, name, description, image, cost_per_vote, voting_start, voting_end, status, display_order, created_at, updated_at)
VALUES 
(4, 'Sportsman of the Year', 'Outstanding male sports personality', '/images/awards/sportsman.jpg', 2.50, '2025-04-01 00:00:00', '2025-05-31 23:59:59', 'active', 1, NOW(), NOW()),
(4, 'Sportswoman of the Year', 'Outstanding female sports personality', '/images/awards/sportswoman.jpg', 2.50, '2025-04-01 00:00:00', '2025-05-31 23:59:59', 'active', 2, NOW(), NOW()),
(4, 'Team of the Year', 'Best performing team across all sports', '/images/awards/team.jpg', 2.00, '2025-04-01 00:00:00', '2025-05-31 23:59:59', 'active', 3, NOW(), NOW()),
(4, 'Coach of the Year', 'Excellence in sports coaching and mentorship', '/images/awards/coach.jpg', 1.50, '2025-04-01 00:00:00', '2025-05-31 23:59:59', 'active', 4, NOW(), NOW());

-- =====================================================
-- 3. AWARD NOMINEES (Now references award_id instead of event_id)
-- =====================================================

-- Nominees for Music Awards Categories
-- Category 1: Artiste of the Year
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(1, 1, 'Sarkodie', 'Legendary rapper with multiple hit songs and collaborations', '/images/nominees/sarkodie.jpg', 1, NOW(), NOW()),
(1, 1, 'Stonebwoy', 'Dancehall king with international recognition', '/images/nominees/stonebwoy.jpg', 2, NOW(), NOW()),
(1, 1, 'Black Sherif', 'Rising star dominating the music charts', '/images/nominees/black-sherif.jpg', 3, NOW(), NOW()),
(1, 1, 'King Promise', 'Afrobeats sensation with hit singles', '/images/nominees/king-promise.jpg', 4, NOW(), NOW()),
(1, 1, 'Amaarae', 'International breakthrough artiste', '/images/nominees/amaarae.jpg', 5, NOW(), NOW());

-- Category 2: Song of the Year
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(2, 1, 'Second Sermon - Black Sherif', 'Viral hit song that dominated charts', '/images/nominees/second-sermon.jpg', 1, NOW(), NOW()),
(2, 1, 'Terminator - Asake ft Olamide', 'Amapiano infused banger', '/images/nominees/terminator.jpg', 2, NOW(), NOW()),
(2, 1, 'Paris - Amaarae', 'International collaboration hit', '/images/nominees/paris.jpg', 3, NOW(), NOW()),
(2, 1, 'Jamz - King Promise', 'Summer anthem loved by many', '/images/nominees/jamz.jpg', 4, NOW(), NOW());

-- Category 3: Best New Artiste
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(3, 1, 'Gyakie', 'Breakthrough female vocalist', '/images/nominees/gyakie.jpg', 1, NOW(), NOW()),
(3, 1, 'Lasmid', 'New voice in Afrobeats', '/images/nominees/lasmid.jpg', 2, NOW(), NOW()),
(3, 1, 'Kweku Flick', 'Young rapper making waves', '/images/nominees/kweku-flick.jpg', 3, NOW(), NOW()),
(3, 1, 'Yaw Tog', 'Asakaa pioneer', '/images/nominees/yaw-tog.jpg', 4, NOW(), NOW());

-- Category 4: Best Rapper
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(4, 1, 'Sarkodie', 'The rap king', '/images/nominees/sark-rapper.jpg', 1, NOW(), NOW()),
(4, 1, 'Medikal', 'Consistent hit maker', '/images/nominees/medikal.jpg', 2, NOW(), NOW()),
(4, 1, 'Kwesi Arthur', 'Lyrical genius', '/images/nominees/kwesi-arthur.jpg', 3, NOW(), NOW()),
(4, 1, 'Black Sherif', 'New face of rap', '/images/nominees/blacko-rapper.jpg', 4, NOW(), NOW());

-- Category 5: Best Vocalist
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(5, 1, 'King Promise', 'Smooth vocals', '/images/nominees/kp-vocalist.jpg', 1, NOW(), NOW()),
(5, 1, 'Efya', 'Powerful voice', '/images/nominees/efya.jpg', 2, NOW(), NOW()),
(5, 1, 'Kidi', 'Hit songs with great vocals', '/images/nominees/kidi.jpg', 3, NOW(), NOW()),
(5, 1, 'Gyakie', 'Rising vocal talent', '/images/nominees/gyakie-vocalist.jpg', 4, NOW(), NOW());

-- Category 6: Best Music Video
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(6, 1, 'Second Sermon Remix - Black Sherif ft Burna Boy', 'Cinematic masterpiece', '/images/nominees/sermon-video.jpg', 1, NOW(), NOW()),
(6, 1, 'Terminator - Asake', 'Creative visuals', '/images/nominees/term-video.jpg', 2, NOW(), NOW()),
(6, 1, 'Champagne - Amaarae', 'International production', '/images/nominees/champagne-video.jpg', 3, NOW(), NOW());

-- Nominees for Movie Awards Categories
-- Category 7: Best Actor
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(7, 2, 'John Dumelo', 'Versatile actor with powerful performances', '/images/nominees/john-dumelo.jpg', 1, NOW(), NOW()),
(7, 2, 'Majid Michel', 'Award-winning actor', '/images/nominees/majid.jpg', 2, NOW(), NOW()),
(7, 2, 'James Gardiner', 'Rising star in Ghanaian cinema', '/images/nominees/james-gardiner.jpg', 3, NOW(), NOW()),
(7, 2, 'Adjetey Anang', 'Legendary performer', '/images/nominees/adjetey.jpg', 4, NOW(), NOW());

-- Category 8: Best Actress
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(8, 2, 'Jackie Appiah', 'Queen of Ghana movies', '/images/nominees/jackie.jpg', 1, NOW(), NOW()),
(8, 2, 'Nadia Buari', 'Outstanding performances', '/images/nominees/nadia.jpg', 2, NOW(), NOW()),
(8, 2, 'Yvonne Nelson', 'Producer and actress', '/images/nominees/yvonne.jpg', 3, NOW(), NOW()),
(8, 2, 'Joselyn Dumas', 'Versatile actress', '/images/nominees/joselyn.jpg', 4, NOW(), NOW());

-- Category 9: Best Movie
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(9, 2, 'The Burial of Kojo', 'Visually stunning masterpiece', '/images/nominees/kojo.jpg', 1, NOW(), NOW()),
(9, 2, 'Gold Coast Lounge', 'Gripping storyline', '/images/nominees/goldcoast.jpg', 2, NOW(), NOW()),
(9, 2, 'Azali', 'Powerful narrative', '/images/nominees/azali.jpg', 3, NOW(), NOW());

-- Category 10: Best Director
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(10, 2, 'Blitz Bazawule', 'Visionary director', '/images/nominees/blitz.jpg', 1, NOW(), NOW()),
(10, 2, 'Pascal Amanfo', 'Creative filmmaker', '/images/nominees/pascal.jpg', 2, NOW(), NOW()),
(10, 2, 'Leila Djansi', 'Award-winning director', '/images/nominees/leila.jpg', 3, NOW(), NOW());

-- Category 11: Best Screenplay
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(11, 2, 'The Burial of Kojo - Blitz Bazawule', 'Original storytelling', '/images/nominees/kojo-script.jpg', 1, NOW(), NOW()),
(11, 2, 'Azali - Kwabena Gyansah', 'Compelling narrative', '/images/nominees/azali-script.jpg', 2, NOW(), NOW());

-- Nominees for Tech Awards Categories
-- Category 12: Tech Startup of the Year
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(12, 3, 'Hubtel', 'Leading payment solutions provider', '/images/nominees/hubtel.jpg', 1, NOW(), NOW()),
(12, 3, 'mPharma', 'Healthcare technology innovator', '/images/nominees/mpharma.jpg', 2, NOW(), NOW()),
(12, 3, 'Zeepay', 'Digital wallet and remittance platform', '/images/nominees/zeepay.jpg', 3, NOW(), NOW()),
(12, 3, 'AgroCenta', 'Agricultural technology solution', '/images/nominees/agrocenta.jpg', 4, NOW(), NOW());

-- Category 13: Tech Innovator of the Year
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(13, 3, 'Herman Chinery-Hesse', 'Father of African technology', '/images/nominees/herman.jpg', 1, NOW(), NOW()),
(13, 3, 'Charlette N''Guessan', 'AI innovator', '/images/nominees/charlette.jpg', 2, NOW(), NOW()),
(13, 3, 'Yaw Oti-Amoako', 'Tech entrepreneur', '/images/nominees/yaw-oti.jpg', 3, NOW(), NOW());

-- Category 14: Best Mobile App
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(14, 3, 'Hubtel App', 'All-in-one payment solution', '/images/nominees/hubtel-app.jpg', 1, NOW(), NOW()),
(14, 3, 'My MTN App', 'Telecom services app', '/images/nominees/mtn-app.jpg', 2, NOW(), NOW()),
(14, 3, 'Glovo Ghana', 'Delivery service app', '/images/nominees/glovo.jpg', 3, NOW(), NOW());

-- Category 15: Best Fintech Solution
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(15, 3, 'Zeepay', 'Cross-border payment solution', '/images/nominees/zeepay-fintech.jpg', 1, NOW(), NOW()),
(15, 3, 'ExpressPay', 'Payment gateway provider', '/images/nominees/expresspay.jpg', 2, NOW(), NOW()),
(15, 3, 'Fido', 'Credit scoring platform', '/images/nominees/fido.jpg', 3, NOW(), NOW());

-- Nominees for Sports Awards Categories
-- Category 16: Sportsman of the Year
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(16, 4, 'Thomas Partey', 'Arsenal midfielder and Black Stars captain', '/images/nominees/partey.jpg', 1, NOW(), NOW()),
(16, 4, 'Mohammed Kudus', 'West Ham United star', '/images/nominees/kudus.jpg', 2, NOW(), NOW()),
(16, 4, 'Joseph Paintsil', 'LA Galaxy winger', '/images/nominees/paintsil.jpg', 3, NOW(), NOW()),
(16, 4, 'Jordan Ayew', 'Crystal Palace forward', '/images/nominees/jordan.jpg', 4, NOW(), NOW());

-- Category 17: Sportswoman of the Year
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(17, 4, 'Deborah Acquah', 'Long jump champion', '/images/nominees/deborah.jpg', 1, NOW(), NOW()),
(17, 4, 'Rose Amoanimaa Harvey', 'Sprinter', '/images/nominees/rose.jpg', 2, NOW(), NOW()),
(17, 4, 'Janet Amponsah', 'Boxer', '/images/nominees/janet.jpg', 3, NOW(), NOW());

-- Category 18: Team of the Year
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(18, 4, 'Black Stars', 'National football team', '/images/nominees/blackstars.jpg', 1, NOW(), NOW()),
(18, 4, 'Kotoko', 'Ghana Premier League champions', '/images/nominees/kotoko.jpg', 2, NOW(), NOW()),
(18, 4, 'Hearts of Oak', 'Accra giants', '/images/nominees/hearts.jpg', 3, NOW(), NOW());

-- Category 19: Coach of the Year
INSERT INTO award_nominees (category_id, award_id, name, description, image, display_order, created_at, updated_at)
VALUES 
(19, 4, 'Otto Addo', 'Black Stars coach', '/images/nominees/otto.jpg', 1, NOW(), NOW()),
(19, 4, 'Prosper Ogum', 'Kotoko coach', '/images/nominees/prosper.jpg', 2, NOW(), NOW()),
(19, 4, 'Samuel Boadu', 'Hearts coach', '/images/nominees/boadu.jpg', 3, NOW(), NOW());

-- =====================================================
-- 4. AWARD VOTES (Sample votes for testing)
-- =====================================================

-- Music Awards Votes (Artiste of the Year)
INSERT INTO award_votes (nominee_id, category_id, award_id, number_of_votes, status, reference, voter_name, voter_email, voter_phone, created_at, updated_at)
VALUES 
(1, 1, 1, 5, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Kwame Mensah', 'kwame@example.com', '+233241234567', NOW(), NOW()),
(2, 1, 1, 10, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Ama Serwaa', 'ama@example.com', '+233244567890', NOW(), NOW()),
(3, 1, 1, 15, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Kofi Asante', 'kofi@example.com', '+233209876543', NOW(), NOW()),
(4, 1, 1, 8, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Akosua Badu', 'akosua@example.com', '+233551234567', NOW(), NOW()),
(5, 1, 1, 12, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Yaw Boateng', 'yaw@example.com', '+233271234567', NOW(), NOW()),
(1, 1, 1, 20, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Abena Owusu', 'abena@example.com', '+233207654321', NOW(), NOW()),
(2, 1, 1, 7, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Kwabena Osei', 'kwabena@example.com', '+233240111222', NOW(), NOW()),
(3, 1, 1, 25, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Efua Mensah', 'efua@example.com', '+233552223344', NOW(), NOW());

-- Song of the Year Votes
INSERT INTO award_votes (nominee_id, category_id, award_id, number_of_votes, status, reference, voter_name, voter_email, voter_phone, created_at, updated_at)
VALUES 
(6, 2, 1, 30, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'John Doe', 'john@example.com', '+233245678901', NOW(), NOW()),
(7, 2, 1, 18, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Mary Smith', 'mary@example.com', '+233208765432', NOW(), NOW()),
(8, 2, 1, 22, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'David Mensah', 'david@example.com', '+233541112233', NOW(), NOW()),
(9, 2, 1, 15, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Grace Addo', 'grace@example.com', '+233276543210', NOW(), NOW());

-- Movie Awards Votes (Best Actor)
INSERT INTO award_votes (nominee_id, category_id, award_id, number_of_votes, status, reference, voter_name, voter_email, voter_phone, created_at, updated_at)
VALUES 
(19, 7, 2, 12, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Samuel Tetteh', 'samuel@example.com', '+233241234561', NOW(), NOW()),
(20, 7, 2, 20, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Comfort Osei', 'comfort@example.com', '+233209876541', NOW(), NOW()),
(21, 7, 2, 15, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Emmanuel Asare', 'emmanuel@example.com', '+233557654321', NOW(), NOW()),
(22, 7, 2, 18, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Beatrice Owusu', 'beatrice@example.com', '+233248765432', NOW(), NOW());

-- Tech Awards Votes
INSERT INTO award_votes (nominee_id, category_id, award_id, number_of_votes, status, reference, voter_name, voter_email, voter_phone, created_at, updated_at)
VALUES 
(27, 12, 3, 25, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Tech Enthusiast', 'tech@example.com', '+233201234567', NOW(), NOW()),
(28, 12, 3, 30, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Innovation Fan', 'innovation@example.com', '+233242345678', NOW(), NOW()),
(29, 12, 3, 20, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Startup Lover', 'startup@example.com', '+233553456789', NOW(), NOW());

-- Sports Awards Votes
INSERT INTO award_votes (nominee_id, category_id, award_id, number_of_votes, status, reference, voter_name, voter_email, voter_phone, created_at, updated_at)
VALUES 
(37, 16, 4, 40, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Football Fan 1', 'fan1@example.com', '+233204567890', NOW(), NOW()),
(38, 16, 4, 50, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Football Fan 2', 'fan2@example.com', '+233245678901', NOW(), NOW()),
(39, 16, 4, 35, 'paid', CONCAT('VOTE-', FLOOR(RAND() * 1000000)), 'Sports Lover', 'sports@example.com', '+233556789012', NOW(), NOW());

-- Some pending votes (not yet paid)
INSERT INTO award_votes (nominee_id, category_id, award_id, number_of_votes, status, reference, voter_name, voter_email, voter_phone, created_at, updated_at)
VALUES 
(1, 1, 1, 5, 'pending', 'VOTE-PENDING-001', 'Pending User 1', 'pending1@example.com', '+233240000001', NOW(), NOW()),
(7, 2, 1, 10, 'pending', 'VOTE-PENDING-002', 'Pending User 2', 'pending2@example.com', '+233240000002', NOW(), NOW()),
(19, 7, 2, 3, 'pending', 'VOTE-PENDING-003', 'Pending User 3', 'pending3@example.com', '+233240000003', NOW(), NOW());

-- =====================================================
-- END OF SEED DATA
-- =====================================================

-- Summary Statistics
-- Awards Shows: 4 (Ghana Music Awards, Movie Awards, Tech Awards, Sports Awards)
-- Categories: 19 total across all awards
-- Nominees: 47 total across all categories
-- Votes: 30+ sample votes (mix of paid and pending)
