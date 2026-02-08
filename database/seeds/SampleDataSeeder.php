<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Sample Data Seeder
 * 
 * Populates the database with sample data for testing:
 * - Users (organizer, attendees)
 * - Organizers
 * - Awards
 * - Award Categories
 * - Award Nominees
 * - Award Votes
 * - Events
 * - Event Types
 * - Ticket Types
 */
class SampleDataSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->seedEventTypes();
        $this->seedUsers();
        $this->seedOrganizers();
        $this->seedAwards();
        $this->seedAwardCategories();
        $this->seedAwardNominees();
        $this->seedEvents();
        $this->seedTicketTypes();
        $this->seedSampleVotes();
        
        echo "\n✅ Sample data seeded successfully!\n";
    }

    private function seedEventTypes(): void
    {
        $existing = $this->fetchRow('SELECT id FROM event_types LIMIT 1');
        if ($existing) {
            echo "Event types already exist. Skipping...\n";
            return;
        }

        $eventTypes = [
            ['name' => 'Concert', 'slug' => 'concert', 'description' => 'Live music performances'],
            ['name' => 'Conference', 'slug' => 'conference', 'description' => 'Professional conferences and seminars'],
            ['name' => 'Festival', 'slug' => 'festival', 'description' => 'Multi-day cultural festivals'],
            ['name' => 'Awards Show', 'slug' => 'awards-show', 'description' => 'Award ceremonies and galas'],
            ['name' => 'Workshop', 'slug' => 'workshop', 'description' => 'Hands-on training sessions'],
            ['name' => 'Party', 'slug' => 'party', 'description' => 'Social gatherings and celebrations'],
        ];

        foreach ($eventTypes as &$type) {
            $type['created_at'] = date('Y-m-d H:i:s');
            $type['updated_at'] = date('Y-m-d H:i:s');
        }

        $this->table('event_types')->insert($eventTypes)->save();
        echo "✅ Event types seeded\n";
    }

    private function seedUsers(): void
    {
        $existing = $this->fetchRow("SELECT id FROM users WHERE email = 'organizer@eventic.com'");
        if ($existing) {
            echo "Sample users already exist. Skipping...\n";
            return;
        }

        $passwordHash = password_hash('Password@123', PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 2
        ]);

        $users = [
            [
                'name' => 'Event Masters Ghana',
                'email' => 'organizer@eventic.com',
                'phone' => '+233541000001',
                'password' => $passwordHash,
                'role' => 'organizer',
                'status' => 'active',
                'email_verified' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Ghana Entertainment Awards',
                'email' => 'gea@eventic.com',
                'phone' => '+233541000002',
                'password' => $passwordHash,
                'role' => 'organizer',
                'status' => 'active',
                'email_verified' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'John Mensah',
                'email' => 'john@example.com',
                'phone' => '+233541000003',
                'password' => $passwordHash,
                'role' => 'attendee',
                'status' => 'active',
                'email_verified' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Ama Serwaa',
                'email' => 'ama@example.com',
                'phone' => '+233541000004',
                'password' => $passwordHash,
                'role' => 'attendee',
                'status' => 'active',
                'email_verified' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('users')->insert($users)->save();
        echo "✅ Users seeded\n";
    }

    private function seedOrganizers(): void
    {
        $existing = $this->fetchRow('SELECT id FROM organizers LIMIT 1');
        if ($existing) {
            echo "Organizers already exist. Skipping...\n";
            return;
        }

        // Get organizer user IDs
        $organizer1 = $this->fetchRow("SELECT id FROM users WHERE email = 'organizer@eventic.com'");
        $organizer2 = $this->fetchRow("SELECT id FROM users WHERE email = 'gea@eventic.com'");

        if (!$organizer1 || !$organizer2) {
            echo "Organizer users not found. Skipping...\n";
            return;
        }

        $organizers = [
            [
                'user_id' => $organizer1['id'],
                'organization_name' => 'Event Masters Ghana',
                'bio' => 'Premier event management company in Ghana, specializing in concerts, festivals, and corporate events.',
                'profile_image' => 'https://images.unsplash.com/photo-1560179707-f14e90ef3623?w=400',
                'social_facebook' => 'https://facebook.com/eventmastersgh',
                'social_instagram' => 'https://instagram.com/eventmastersgh',
                'social_twitter' => 'https://twitter.com/eventmastersgh',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => $organizer2['id'],
                'organization_name' => 'Ghana Entertainment Awards',
                'bio' => 'Celebrating excellence in Ghanaian entertainment since 2010. Home of the most prestigious awards in Ghana.',
                'profile_image' => 'https://images.unsplash.com/photo-1578269174936-2709b6aeb913?w=400',
                'social_facebook' => 'https://facebook.com/ghanaentawards',
                'social_instagram' => 'https://instagram.com/ghanaentawards',
                'social_twitter' => 'https://twitter.com/ghanaentawards',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('organizers')->insert($organizers)->save();
        
        // Create organizer balances
        $org1 = $this->fetchRow("SELECT id FROM organizers WHERE organization_name = 'Event Masters Ghana'");
        $org2 = $this->fetchRow("SELECT id FROM organizers WHERE organization_name = 'Ghana Entertainment Awards'");
        
        $balances = [
            [
                'organizer_id' => $org1['id'],
                'available_balance' => 0.00,
                'pending_balance' => 0.00,
                'total_earned' => 0.00,
                'total_withdrawn' => 0.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'organizer_id' => $org2['id'],
                'available_balance' => 0.00,
                'pending_balance' => 0.00,
                'total_earned' => 0.00,
                'total_withdrawn' => 0.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];
        
        $this->table('organizer_balances')->insert($balances)->save();
        echo "✅ Organizers seeded\n";
    }

    private function seedAwards(): void
    {
        $existing = $this->fetchRow('SELECT id FROM awards LIMIT 1');
        if ($existing) {
            echo "Awards already exist. Skipping...\n";
            return;
        }

        $organizer = $this->fetchRow("SELECT id FROM organizers WHERE organization_name = 'Ghana Entertainment Awards'");
        if (!$organizer) {
            echo "Organizer not found for awards. Skipping...\n";
            return;
        }

        $awards = [
            [
                'organizer_id' => $organizer['id'],
                'title' => 'Ghana Music Awards 2026',
                'slug' => 'ghana-music-awards-2026',
                'description' => 'The most prestigious music awards in Ghana, celebrating the best in Ghanaian music across all genres. Join us for a night of glamour, music, and celebration of our talented artists.',
                'banner_image' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=1200',
                'venue_name' => 'Accra International Conference Centre',
                'address' => 'Castle Road, Ridge, Accra',
                'map_url' => 'https://maps.google.com/?q=Accra+International+Conference+Centre',
                'ceremony_date' => '2026-05-15 19:00:00',
                'voting_start' => '2026-02-01 00:00:00',
                'voting_end' => '2026-05-01 23:59:59',
                'status' => 'published',
                'show_results' => 1,
                'is_featured' => 1,
                'admin_share_percent' => 15.00,
                'country' => 'Ghana',
                'region' => 'Greater Accra',
                'city' => 'Accra',
                'phone' => '+233302123456',
                'website' => 'https://ghanamusicawards.com',
                'facebook' => 'https://facebook.com/ghanamusicawards',
                'twitter' => 'https://twitter.com/gikiganamusicawards',
                'instagram' => 'https://instagram.com/ghanamusicawards',
                'video_url' => 'https://youtube.com/watch?v=example',
                'views' => 15420,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'organizer_id' => $organizer['id'],
                'title' => 'Ghana Movie Awards 2026',
                'slug' => 'ghana-movie-awards-2026',
                'description' => 'Celebrating excellence in Ghanaian cinema. Recognizing the best actors, directors, and films that have shaped our film industry.',
                'banner_image' => 'https://images.unsplash.com/photo-1485846234645-a62644f84728?w=1200',
                'venue_name' => 'National Theatre of Ghana',
                'address' => 'Liberia Road, Accra',
                'map_url' => 'https://maps.google.com/?q=National+Theatre+Ghana',
                'ceremony_date' => '2026-06-20 18:00:00',
                'voting_start' => '2026-03-01 00:00:00',
                'voting_end' => '2026-06-01 23:59:59',
                'status' => 'published',
                'show_results' => 1,
                'is_featured' => 0,
                'admin_share_percent' => 15.00,
                'country' => 'Ghana',
                'region' => 'Greater Accra',
                'city' => 'Accra',
                'phone' => '+233302654321',
                'website' => 'https://ghanamovieawards.com',
                'views' => 8750,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('awards')->insert($awards)->save();
        echo "✅ Awards seeded\n";
    }

    private function seedAwardCategories(): void
    {
        $existing = $this->fetchRow('SELECT id FROM award_categories LIMIT 1');
        if ($existing) {
            echo "Award categories already exist. Skipping...\n";
            return;
        }

        $musicAward = $this->fetchRow("SELECT id FROM awards WHERE slug = 'ghana-music-awards-2026'");
        $movieAward = $this->fetchRow("SELECT id FROM awards WHERE slug = 'ghana-movie-awards-2026'");

        if (!$musicAward) {
            echo "Music award not found. Skipping categories...\n";
            return;
        }

        $categories = [
            // Music Award Categories
            [
                'award_id' => $musicAward['id'],
                'name' => 'Artiste of the Year',
                'image' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=400',
                'description' => 'The highest honor recognizing the most outstanding artiste of the year',
                'cost_per_vote' => 1.00,
                'voting_start' => '2026-02-01 00:00:00',
                'voting_end' => '2026-05-01 23:59:59',
                'status' => 'active',
                'display_order' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'award_id' => $musicAward['id'],
                'name' => 'Best Hiplife/Hip-hop Song',
                'image' => 'https://images.unsplash.com/photo-1571330735066-03aaa9429d89?w=400',
                'description' => 'Best song in the Hiplife or Hip-hop genre',
                'cost_per_vote' => 0.50,
                'voting_start' => '2026-02-01 00:00:00',
                'voting_end' => '2026-05-01 23:59:59',
                'status' => 'active',
                'display_order' => 2,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'award_id' => $musicAward['id'],
                'name' => 'Best Highlife Song',
                'image' => 'https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?w=400',
                'description' => 'Best song in the Highlife genre',
                'cost_per_vote' => 0.50,
                'voting_start' => '2026-02-01 00:00:00',
                'voting_end' => '2026-05-01 23:59:59',
                'status' => 'active',
                'display_order' => 3,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'award_id' => $musicAward['id'],
                'name' => 'Best Afrobeats Song',
                'image' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=400',
                'description' => 'Best song in the Afrobeats genre',
                'cost_per_vote' => 0.50,
                'status' => 'active',
                'display_order' => 4,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'award_id' => $musicAward['id'],
                'name' => 'Best New Artiste',
                'image' => 'https://images.unsplash.com/photo-1598387993441-a364f854c3e1?w=400',
                'description' => 'Best breakthrough artiste of the year',
                'cost_per_vote' => 0.50,
                'status' => 'active',
                'display_order' => 5,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Add Movie Award Categories if exists
        if ($movieAward) {
            $movieCategories = [
                [
                    'award_id' => $movieAward['id'],
                    'name' => 'Best Actor',
                    'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400',
                    'description' => 'Best male actor in a leading role',
                    'cost_per_vote' => 1.00,
                    'voting_start' => '2026-03-01 00:00:00',
                    'voting_end' => '2026-06-01 23:59:59',
                    'status' => 'active',
                    'display_order' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'award_id' => $movieAward['id'],
                    'name' => 'Best Actress',
                    'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400',
                    'description' => 'Best female actor in a leading role',
                    'cost_per_vote' => 1.00,
                    'voting_start' => '2026-03-01 00:00:00',
                    'voting_end' => '2026-06-01 23:59:59',
                    'status' => 'active',
                    'display_order' => 2,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'award_id' => $movieAward['id'],
                    'name' => 'Best Movie',
                    'image' => 'https://images.unsplash.com/photo-1485846234645-a62644f84728?w=400',
                    'description' => 'Best overall movie production',
                    'cost_per_vote' => 1.50,
                    'voting_start' => '2026-03-01 00:00:00',
                    'voting_end' => '2026-06-01 23:59:59',
                    'status' => 'active',
                    'display_order' => 3,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
            ];
            $categories = array_merge($categories, $movieCategories);
        }

        $this->table('award_categories')->insert($categories)->save();
        echo "✅ Award categories seeded\n";
    }

    private function seedAwardNominees(): void
    {
        $existing = $this->fetchRow('SELECT id FROM award_nominees LIMIT 1');
        if ($existing) {
            echo "Award nominees already exist. Skipping...\n";
            return;
        }

        $musicAward = $this->fetchRow("SELECT id FROM awards WHERE slug = 'ghana-music-awards-2026'");
        if (!$musicAward) {
            echo "Music award not found. Skipping nominees...\n";
            return;
        }

        // Get categories
        $artisteOfYear = $this->fetchRow("SELECT id FROM award_categories WHERE name = 'Artiste of the Year' AND award_id = {$musicAward['id']}");
        $hiplifeCat = $this->fetchRow("SELECT id FROM award_categories WHERE name = 'Best Hiplife/Hip-hop Song' AND award_id = {$musicAward['id']}");
        $highlifeCat = $this->fetchRow("SELECT id FROM award_categories WHERE name = 'Best Highlife Song' AND award_id = {$musicAward['id']}");
        $afrobeatsCat = $this->fetchRow("SELECT id FROM award_categories WHERE name = 'Best Afrobeats Song' AND award_id = {$musicAward['id']}");
        $newArtisteCat = $this->fetchRow("SELECT id FROM award_categories WHERE name = 'Best New Artiste' AND award_id = {$musicAward['id']}");

        $nominees = [];
        $usedCodes = [];

        // Artiste of the Year Nominees
        if ($artisteOfYear) {
            $nominees = array_merge($nominees, [
                $this->createNominee($musicAward['id'], $artisteOfYear['id'], 'Sarkodie', 'Award-winning rapper and entrepreneur', 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=400', 1, $usedCodes),
                $this->createNominee($musicAward['id'], $artisteOfYear['id'], 'Stonebwoy', 'Dancehall and Afrobeats sensation', 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=400', 2, $usedCodes),
                $this->createNominee($musicAward['id'], $artisteOfYear['id'], 'Shatta Wale', 'King of Dancehall', 'https://images.unsplash.com/photo-1571330735066-03aaa9429d89?w=400', 3, $usedCodes),
                $this->createNominee($musicAward['id'], $artisteOfYear['id'], 'Black Sherif', 'Rising star and lyrical genius', 'https://images.unsplash.com/photo-1598387993441-a364f854c3e1?w=400', 4, $usedCodes),
                $this->createNominee($musicAward['id'], $artisteOfYear['id'], 'KiDi', 'Golden Boy of Highlife', 'https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?w=400', 5, $usedCodes),
            ]);
        }

        // Hiplife/Hip-hop Nominees
        if ($hiplifeCat) {
            $nominees = array_merge($nominees, [
                $this->createNominee($musicAward['id'], $hiplifeCat['id'], 'Sarkodie - Coachella', 'Hit single featuring global artists', 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=400', 1, $usedCodes),
                $this->createNominee($musicAward['id'], $hiplifeCat['id'], 'Medikal - Wrowroho', 'Street anthem of the year', 'https://images.unsplash.com/photo-1571330735066-03aaa9429d89?w=400', 2, $usedCodes),
                $this->createNominee($musicAward['id'], $hiplifeCat['id'], 'Kwesi Arthur - Live From Nkrumah Krom', 'Critically acclaimed album track', 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=400', 3, $usedCodes),
            ]);
        }

        // Highlife Nominees
        if ($highlifeCat) {
            $nominees = array_merge($nominees, [
                $this->createNominee($musicAward['id'], $highlifeCat['id'], 'KiDi - Touch It', 'Global viral sensation', 'https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?w=400', 1, $usedCodes),
                $this->createNominee($musicAward['id'], $highlifeCat['id'], 'Kuami Eugene - Fire Fire', 'Highlife banger', 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=400', 2, $usedCodes),
                $this->createNominee($musicAward['id'], $highlifeCat['id'], 'Akwaboah - Posti Me', 'Classic love song', 'https://images.unsplash.com/photo-1598387993441-a364f854c3e1?w=400', 3, $usedCodes),
            ]);
        }

        // Afrobeats Nominees
        if ($afrobeatsCat) {
            $nominees = array_merge($nominees, [
                $this->createNominee($musicAward['id'], $afrobeatsCat['id'], 'Stonebwoy - Jejereje', 'Dancehall meets Afrobeats', 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=400', 1, $usedCodes),
                $this->createNominee($musicAward['id'], $afrobeatsCat['id'], 'King Promise - Terminator', 'Smooth Afrobeats hit', 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=400', 2, $usedCodes),
                $this->createNominee($musicAward['id'], $afrobeatsCat['id'], 'Camidoh - Sugarcane', 'TikTok sensation', 'https://images.unsplash.com/photo-1571330735066-03aaa9429d89?w=400', 3, $usedCodes),
            ]);
        }

        // New Artiste Nominees
        if ($newArtisteCat) {
            $nominees = array_merge($nominees, [
                $this->createNominee($musicAward['id'], $newArtisteCat['id'], 'Black Sherif', 'First and Second Sermon hitmaker', 'https://images.unsplash.com/photo-1598387993441-a364f854c3e1?w=400', 1, $usedCodes),
                $this->createNominee($musicAward['id'], $newArtisteCat['id'], 'Gyakie', 'Forever hitmaker', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400', 2, $usedCodes),
                $this->createNominee($musicAward['id'], $newArtisteCat['id'], 'Yaw Tog', 'Sore hitmaker', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400', 3, $usedCodes),
                $this->createNominee($musicAward['id'], $newArtisteCat['id'], 'Kofi Jamar', 'Ekorso hitmaker', 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=400', 4, $usedCodes),
            ]);
        }

        if (!empty($nominees)) {
            $this->table('award_nominees')->insert($nominees)->save();
        }
        echo "✅ Award nominees seeded\n";
    }

    private function createNominee(int $awardId, int $categoryId, string $name, string $description, string $image, int $order, array &$usedCodes): array
    {
        $code = $this->generateUniqueCode($usedCodes);
        $usedCodes[] = $code;

        return [
            'award_id' => $awardId,
            'category_id' => $categoryId,
            'nominee_code' => $code,
            'name' => $name,
            'description' => $description,
            'image' => $image,
            'display_order' => $order,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }

    private function generateUniqueCode(array $existingCodes): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        
        do {
            $code = '';
            for ($i = 0; $i < 4; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (in_array($code, $existingCodes));

        return $code;
    }

    private function seedEvents(): void
    {
        $existing = $this->fetchRow('SELECT id FROM events LIMIT 1');
        if ($existing) {
            echo "Events already exist. Skipping...\n";
            return;
        }

        $organizer = $this->fetchRow("SELECT id FROM organizers WHERE organization_name = 'Event Masters Ghana'");
        $concertType = $this->fetchRow("SELECT id FROM event_types WHERE slug = 'concert'");
        $festivalType = $this->fetchRow("SELECT id FROM event_types WHERE slug = 'festival'");

        if (!$organizer) {
            echo "Organizer not found. Skipping events...\n";
            return;
        }

        $events = [
            [
                'organizer_id' => $organizer['id'],
                'title' => 'Afrochella 2026',
                'slug' => 'afrochella-2026',
                'description' => 'The biggest Afrobeats festival in Africa! Two days of non-stop music, culture, and entertainment featuring the biggest names in African music.',
                'event_type_id' => $festivalType ? $festivalType['id'] : null,
                'venue_name' => 'El Wak Stadium',
                'address' => 'El Wak, Accra',
                'map_url' => 'https://maps.google.com/?q=El+Wak+Stadium+Accra',
                'banner_image' => 'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=1200',
                'start_time' => '2026-12-28 16:00:00',
                'end_time' => '2026-12-29 23:59:00',
                'status' => 'published',
                'is_featured' => 1,
                'admin_share_percent' => 10.00,
                'country' => 'Ghana',
                'region' => 'Greater Accra',
                'city' => 'Accra',
                'views' => 25000,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'organizer_id' => $organizer['id'],
                'title' => 'Detty Rave 2026',
                'slug' => 'detty-rave-2026',
                'description' => 'Mr Eazi presents Detty Rave - the ultimate December experience in Ghana. Music, vibes, and unforgettable memories.',
                'event_type_id' => $concertType ? $concertType['id'] : null,
                'venue_name' => 'La Palm Royal Beach Hotel',
                'address' => 'La, Accra',
                'map_url' => 'https://maps.google.com/?q=La+Palm+Royal+Beach+Hotel',
                'banner_image' => 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=1200',
                'start_time' => '2026-12-26 20:00:00',
                'end_time' => '2026-12-27 04:00:00',
                'status' => 'published',
                'is_featured' => 1,
                'admin_share_percent' => 10.00,
                'country' => 'Ghana',
                'region' => 'Greater Accra',
                'city' => 'Accra',
                'views' => 18500,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('events')->insert($events)->save();
        echo "✅ Events seeded\n";
    }

    private function seedTicketTypes(): void
    {
        $existing = $this->fetchRow('SELECT id FROM ticket_types LIMIT 1');
        if ($existing) {
            echo "Ticket types already exist. Skipping...\n";
            return;
        }

        $afrochella = $this->fetchRow("SELECT id, organizer_id FROM events WHERE slug = 'afrochella-2026'");
        $dettyRave = $this->fetchRow("SELECT id, organizer_id FROM events WHERE slug = 'detty-rave-2026'");

        if (!$afrochella) {
            echo "Events not found. Skipping ticket types...\n";
            return;
        }

        $ticketTypes = [
            // Afrochella Tickets
            [
                'event_id' => $afrochella['id'],
                'organizer_id' => $afrochella['organizer_id'],
                'name' => 'Early Bird',
                'description' => 'Limited early bird tickets at discounted price. Two-day pass.',
                'price' => 150.00,
                'sale_price' => 120.00,
                'quantity' => 500,
                'remaining' => 250,
                'sale_start' => '2026-06-01 00:00:00',
                'sale_end' => '2026-08-31 23:59:59',
                'max_per_user' => 4,
                'ticket_image' => 'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=400',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'event_id' => $afrochella['id'],
                'organizer_id' => $afrochella['organizer_id'],
                'name' => 'Regular',
                'description' => 'Standard two-day pass with general admission.',
                'price' => 200.00,
                'sale_price' => 200.00,
                'quantity' => 2000,
                'remaining' => 1500,
                'sale_start' => '2026-09-01 00:00:00',
                'sale_end' => '2026-12-27 23:59:59',
                'max_per_user' => 6,
                'ticket_image' => 'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=400',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'event_id' => $afrochella['id'],
                'organizer_id' => $afrochella['organizer_id'],
                'name' => 'VIP',
                'description' => 'VIP experience with exclusive area access, complimentary drinks, and meet & greet opportunities.',
                'price' => 500.00,
                'sale_price' => 500.00,
                'quantity' => 200,
                'remaining' => 150,
                'sale_start' => '2026-06-01 00:00:00',
                'sale_end' => '2026-12-27 23:59:59',
                'max_per_user' => 2,
                'ticket_image' => 'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=400',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Detty Rave Tickets
        if ($dettyRave) {
            $ticketTypes = array_merge($ticketTypes, [
                [
                    'event_id' => $dettyRave['id'],
                    'organizer_id' => $dettyRave['organizer_id'],
                    'name' => 'General Admission',
                    'description' => 'Standard entry to Detty Rave.',
                    'price' => 100.00,
                    'sale_price' => 100.00,
                    'quantity' => 3000,
                    'remaining' => 2000,
                    'sale_start' => '2026-10-01 00:00:00',
                    'sale_end' => '2026-12-25 23:59:59',
                    'max_per_user' => 5,
                    'ticket_image' => 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=400',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'event_id' => $dettyRave['id'],
                    'organizer_id' => $dettyRave['organizer_id'],
                    'name' => 'VIP Table',
                    'description' => 'VIP table for 4 with bottle service.',
                    'price' => 800.00,
                    'sale_price' => 800.00,
                    'quantity' => 50,
                    'remaining' => 30,
                    'sale_start' => '2026-10-01 00:00:00',
                    'sale_end' => '2026-12-25 23:59:59',
                    'max_per_user' => 2,
                    'ticket_image' => 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=400',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
            ]);
        }

        $this->table('ticket_types')->insert($ticketTypes)->save();
        echo "✅ Ticket types seeded\n";
    }

    private function seedSampleVotes(): void
    {
        $existing = $this->fetchRow('SELECT id FROM award_votes LIMIT 1');
        if ($existing) {
            echo "Sample votes already exist. Skipping...\n";
            return;
        }

        // Get some nominees
        $nominees = $this->fetchAll('SELECT id, category_id, award_id FROM award_nominees LIMIT 10');
        if (empty($nominees)) {
            echo "No nominees found. Skipping votes...\n";
            return;
        }

        $votes = [];
        $phones = ['+233541111111', '+233542222222', '+233543333333', '+233544444444', '+233545555555'];

        foreach ($nominees as $index => $nominee) {
            // Create 2-5 votes per nominee
            $voteCount = random_int(2, 5);
            
            for ($i = 0; $i < $voteCount; $i++) {
                $numVotes = random_int(1, 20);
                $costPerVote = 0.50; // Default
                $grossAmount = $numVotes * $costPerVote;
                $adminPercent = 15.00;
                $adminAmount = $grossAmount * ($adminPercent / 100);
                $organizerAmount = $grossAmount - $adminAmount;

                $votes[] = [
                    'nominee_id' => $nominee['id'],
                    'category_id' => $nominee['category_id'],
                    'award_id' => $nominee['award_id'],
                    'number_of_votes' => $numVotes,
                    'cost_per_vote' => $costPerVote,
                    'gross_amount' => $grossAmount,
                    'admin_share_percent' => $adminPercent,
                    'admin_amount' => $adminAmount,
                    'organizer_amount' => $organizerAmount,
                    'payment_fee' => $grossAmount * 0.0195, // ~2% Paystack fee
                    'status' => 'paid',
                    'reference' => 'SEED_' . uniqid() . '_' . $index . '_' . $i,
                    'voter_phone' => $phones[array_rand($phones)],
                    'created_at' => date('Y-m-d H:i:s', strtotime("-" . random_int(1, 30) . " days")),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        if (!empty($votes)) {
            $this->table('award_votes')->insert($votes)->save();
        }
        echo "✅ Sample votes seeded\n";
    }
}
