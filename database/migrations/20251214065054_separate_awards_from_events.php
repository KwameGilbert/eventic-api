<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SeparateAwardsFromEvents extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // =====================================================
        // STEP 1: Create the new 'awards' table (if not exists)
        // =====================================================
        
        if (!$this->hasTable('awards')) {
            $awards = $this->table('awards', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);

            $awards
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('organizer_id', 'integer', ['signed' => false, 'null' => false])
                ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('description', 'text', ['null' => true, 'default' => null])
                ->addColumn('banner_image', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                ->addColumn('venue_name', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                ->addColumn('address', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                ->addColumn('map_url', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                ->addColumn('ceremony_date', 'datetime', ['null' => false, 'comment' => 'Awards ceremony date'])
                ->addColumn('voting_start', 'datetime', ['null' => false, 'comment' => 'Global voting start'])
                ->addColumn('voting_end', 'datetime', ['null' => false, 'comment' => 'Global voting end'])
                ->addColumn('status', 'enum', ['values' => ['draft', 'published', 'closed', 'completed'], 'default' => 'draft', 'null' => false])
                ->addColumn('is_featured', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('country', 'string', ['limit' => 255, 'default' => 'Ghana', 'null' => false])
                ->addColumn('region', 'string', ['limit' => 255, 'default' => 'Greater Accra', 'null' => false])
                ->addColumn('city', 'string', ['limit' => 255, 'default' => 'Accra', 'null' => false])
                ->addColumn('phone', 'string', ['limit' => 50, 'null' => true, 'default' => null])
                ->addColumn('website', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                ->addColumn('facebook', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                ->addColumn('twitter', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                ->addColumn('instagram', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                ->addColumn('video_url', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                ->addColumn('views', 'integer', ['default' => 0, 'null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['slug'], ['unique' => true])
                ->addIndex(['organizer_id'])
                ->addIndex(['status'])
                ->addIndex(['is_featured'])
                ->addIndex(['voting_start'])
                ->addIndex(['voting_end'])
                ->addForeignKey('organizer_id', 'organizers', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // =====================================================
        // STEP 2: Migrate existing awards events to awards table
        // =====================================================
        // We use execute() for raw SQL when moving data with logic (DATE_SUB, etc)
        
        // Check if event_format column exists in events table
        $eventsTable = $this->table('events');
        $hasEventFormat = $eventsTable->hasColumn('event_format');
        
        if ($hasEventFormat) {
            $this->execute("
                INSERT INTO `awards` (
                    `organizer_id`, `title`, `slug`, `description`, `banner_image`,
                    `venue_name`, `address`, `map_url`, `ceremony_date`,
                    `voting_start`, `voting_end`, `status`, `is_featured`,
                    `country`, `region`, `city`, `phone`, `website`,
                    `facebook`, `twitter`, `instagram`, `video_url`, `views`,
                    `created_at`, `updated_at`
                )
                SELECT 
                    `organizer_id`, `title`, `slug`, `description`, `banner_image`,
                    `venue_name`, `address`, `map_url`, `start_time` as `ceremony_date`,
                    DATE_SUB(`start_time`, INTERVAL 2 MONTH) as `voting_start`,
                    DATE_SUB(`start_time`, INTERVAL 1 DAY) as `voting_end`,
                    `status`, `is_featured`, `country`, `region`, `city`,
                    `phone`, `website`, `facebook`, `twitter`, `instagram`,
                    `video_url`, `views`, `created_at`, `updated_at`
                FROM `events`
                WHERE `event_format` = 'awards';
            ");
        }

        // =====================================================
        // STEP 3: Update award_categories
        // =====================================================
        
        // Check if there are any awards events to migrate
        $hasAwardsEvents = ['count' => 0];
        
        if ($hasEventFormat) {
            $hasAwardsEvents = $this->fetchRow("SELECT COUNT(*) as count FROM events WHERE event_format = 'awards'");
        }
        
        $categoriesTable = $this->table('award_categories');
        $hasEventId = $categoriesTable->hasColumn('event_id');
        $hasAwardId = $categoriesTable->hasColumn('award_id');
        
        if ($hasEventId && !$hasAwardId) {
            if ($hasAwardsEvents['count'] > 0) {
                // Only proceed if there are awards events
                $categories = $this->table('award_categories');
                
                // 1. Add column as nullable first
                $categories->addColumn('award_id', 'integer', ['signed' => false, 'null' => true, 'after' => 'id'])
                           ->update();

                // 2. Populate data
                $this->execute("
                    UPDATE `award_categories` ac
                    INNER JOIN `events` e ON ac.`event_id` = e.`id`
                    INNER JOIN `awards` a ON a.`slug` = e.`slug` AND a.`title` = e.`title`
                    SET ac.`award_id` = a.`id`
                    WHERE e.`event_format` = 'awards'
                ");

                // 3. Drop old foreign key and column, add new ones
                $categories->dropForeignKey('event_id')
                           ->removeColumn('event_id')
                           ->changeColumn('award_id', 'integer', ['signed' => false, 'null' => false])
                           ->addForeignKey('award_id', 'awards', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                           ->addIndex(['award_id'])
                           ->update();
            } else {
                // No awards events exist - just swap the column
                $categories = $this->table('award_categories');
                
                // Rename event_id to award_id
                $categories->renameColumn('event_id', 'award_id')
                           ->update();
            }
        }

        // =====================================================
        // STEP 4: Update award_nominees
        // =====================================================
        
        $nomineesTable = $this->table('award_nominees');
        $hasEventIdNominees = $nomineesTable->hasColumn('event_id');
        $hasAwardIdNominees = $nomineesTable->hasColumn('award_id');
        
        if ($hasEventIdNominees && !$hasAwardIdNominees) {
            if ($hasAwardsEvents['count'] > 0) {
                $nominees = $this->table('award_nominees');
                
                // 1. Add column
                $nominees->addColumn('award_id', 'integer', ['signed' => false, 'null' => true, 'after' => 'category_id'])
                         ->update();

                // 2. Populate data
                $this->execute("
                    UPDATE `award_nominees` an
                    INNER JOIN `award_categories` ac ON an.`category_id` = ac.`id`
                    SET an.`award_id` = ac.`award_id`
                ");

                // 3. Drop old foreign key and column, add new ones
                $nominees->dropForeignKey('event_id')
                         ->removeColumn('event_id')
                         ->changeColumn('award_id', 'integer', ['signed' => false, 'null' => false])
                         ->addForeignKey('award_id', 'awards', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                         ->addIndex(['award_id'])
                         ->update();
            } else {
                $nominees = $this->table('award_nominees');
                $nominees->renameColumn('event_id', 'award_id')
                         ->update();
            }
        }

        // =====================================================
        // STEP 5: Update award_votes
        // =====================================================
        
        $votesTable = $this->table('award_votes');
        $hasEventIdVotes = $votesTable->hasColumn('event_id');
        $hasAwardIdVotes = $votesTable->hasColumn('award_id');
        
        if ($hasEventIdVotes && !$hasAwardIdVotes) {
            if ($hasAwardsEvents['count'] > 0) {
                $votes = $this->table('award_votes');

                // 1. Add column
                $votes->addColumn('award_id', 'integer', ['signed' => false, 'null' => true, 'after' => 'category_id'])
                      ->update();

                // 2. Populate data
                $this->execute("
                    UPDATE `award_votes` av
                    INNER JOIN `award_categories` ac ON av.`category_id` = ac.`id`
                    SET av.`award_id` = ac.`award_id`
                ");

                // 3. Drop old foreign key and column, add new ones
                $votes->dropForeignKey('event_id')
                      ->removeColumn('event_id')
                      ->changeColumn('award_id', 'integer', ['signed' => false, 'null' => false])
                      ->addForeignKey('award_id', 'awards', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                      ->addIndex(['award_id'])
                      ->update();
            } else {
                $votes = $this->table('award_votes');
                $votes->renameColumn('event_id', 'award_id')
                      ->update();
            }
        }

        // =====================================================
        // STEP 6 & 7: Cleanup Events Table
        // =====================================================
        
        // Delete migrated events (if event_format exists)
        if ($hasEventFormat) {
            $this->execute("DELETE FROM `events` WHERE `event_format` = 'awards'");
        }

        // Drop the format column (if it exists)
        if ($hasEventFormat) {
            $this->table('events')
                 ->removeColumn('event_format')
                 ->update();
        }

        // =====================================================
        // STEP 8: Create awards_images table (if not exists)
        // =====================================================
        
        if (!$this->hasTable('awards_images')) {
            $images = $this->table('awards_images', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);

            $images->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                   ->addColumn('award_id', 'integer', ['signed' => false, 'null' => false])
                   ->addColumn('image_path', 'string', ['limit' => 255, 'null' => false])
                   ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                   ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                   ->addIndex(['award_id'])
                   ->addForeignKey('award_id', 'awards', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                   ->create();
        }
    }

    /**
     * Migrate Down (Rollback).
     */
    public function down(): void
    {
        // =====================================================
        // ROLLBACK: Reverse the separation
        // =====================================================
        
        // STEP 1: Re-add event_format column to events
        $this->table('events')
             ->addColumn('event_format', 'enum', [
                 'values' => ['ticketing', 'awards'],
                 'default' => 'ticketing',
                 'null' => false,
                 'after' => 'event_type_id'
             ])
             ->update();

        // STEP 2: Migrate awards back to events table
        $this->execute("
            INSERT INTO `events` (
                `organizer_id`, `title`, `slug`, `description`, `event_type_id`,
                `event_format`, `venue_name`, `address`, `map_url`, `banner_image`,
                `start_time`, `end_time`, `status`, `is_featured`, `country`,
                `region`, `city`, `phone`, `website`, `facebook`, `twitter`,
                `instagram`, `video_url`, `views`, `created_at`, `updated_at`
            )
            SELECT 
                `organizer_id`, `title`, `slug`, `description`, NULL as `event_type_id`,
                'awards' as `event_format`, `venue_name`, `address`, `map_url`,
                `banner_image`, `ceremony_date` as `start_time`, 
                `ceremony_date` as `end_time`, `status`, `is_featured`,
                `country`, `region`, `city`, `phone`, `website`, `facebook`,
                `twitter`, `instagram`, `video_url`, `views`, `created_at`, `updated_at`
            FROM `awards`
        ");

        // STEP 3: Restore award_categories event_id
        $categories = $this->table('award_categories');
        
        // Add event_id column
        $categories->addColumn('event_id', 'integer', ['signed' => false, 'null' => true, 'after' => 'id'])
                   ->update();

        // Populate event_id from awards
        $this->execute("
            UPDATE `award_categories` ac
            INNER JOIN `awards` a ON ac.`award_id` = a.`id`
            INNER JOIN `events` e ON e.`slug` = a.`slug` AND e.`title` = a.`title`
            SET ac.`event_id` = e.`id`
            WHERE e.`event_format` = 'awards'
        ");

        // Make NOT NULL, drop award_id
        $categories->changeColumn('event_id', 'integer', ['signed' => false, 'null' => false])
                   ->dropForeignKey('award_id')
                   ->removeColumn('award_id')
                   ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                   ->update();

        // STEP 4: Restore award_nominees event_id
        $nominees = $this->table('award_nominees');
        
        $nominees->addColumn('event_id', 'integer', ['signed' => false, 'null' => true, 'after' => 'category_id'])
                 ->update();

        $this->execute("
            UPDATE `award_nominees` an
            INNER JOIN `award_categories` ac ON an.`category_id` = ac.`id`
            SET an.`event_id` = ac.`event_id`
        ");

        $nominees->changeColumn('event_id', 'integer', ['signed' => false, 'null' => false])
                 ->dropForeignKey('award_id')
                 ->removeColumn('award_id')
                 ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                 ->update();

        // STEP 5: Restore award_votes event_id
        $votes = $this->table('award_votes');
        
        $votes->addColumn('event_id', 'integer', ['signed' => false, 'null' => true, 'after' => 'category_id'])
              ->update();

        $this->execute("
            UPDATE `award_votes` av
            INNER JOIN `award_categories` ac ON av.`category_id` = ac.`id`
            SET av.`event_id` = ac.`event_id`
        ");

        $votes->changeColumn('event_id', 'integer', ['signed' => false, 'null' => false])
              ->dropForeignKey('award_id')
              ->removeColumn('award_id')
              ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->update();

        // STEP 6: Drop awards tables
        $this->table('awards_images')->drop()->save();
        $this->table('awards')->drop()->save();
    }
}