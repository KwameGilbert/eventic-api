<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitialSchema extends AbstractMigration
{
    public function up(): void
    {
        // Users
        if (!$this->hasTable('users')) {
            $this->table('users')
                ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('phone', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('password', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('role', 'enum', ['values' => ['admin', 'organizer', 'attendee', 'pos', 'scanner'], 'default' => 'attendee', 'null' => false])
                ->addColumn('email_verified', 'boolean', ['default' => false, 'null' => true])
                ->addColumn('status', 'enum', ['values' => ['active', 'suspended'], 'default' => 'active', 'null' => false])
                ->addColumn('first_login', 'boolean', ['default' => false, 'null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP']) // Fixed update syntax
                ->addIndex(['email'])
                ->addIndex(['phone'])
                ->create();
        }

        // Event Organizers
        if (!$this->hasTable('organizers')) {
            $this->table('organizers')
                ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false]) // Ensure signed matches PK
                ->addColumn('organization_name', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('bio', 'text', ['null' => true])
                ->addColumn('profile_image', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('social_facebook', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('social_instagram', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('social_twitter', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Event Types
        if (!$this->hasTable('event_types')) {
            $this->table('event_types')
                ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('description', 'text', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->create();
        }

        // Events
        if (!$this->hasTable('events')) {
            $this->table('events')
                ->addColumn('organizer_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('description', 'text', ['null' => true])
                ->addColumn('event_type_id', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('venue_name', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('address', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('map_url', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('banner_image', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('start_time', 'datetime', ['null' => false])
                ->addColumn('end_time', 'datetime', ['null' => false])
                ->addColumn('status', 'enum', ['values' => ['draft', 'pending', 'published', 'cancelled'], 'default' => 'draft', 'null' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->addForeignKey('event_type_id', 'event_types', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('organizer_id', 'organizers', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Event images / gallery
        if (!$this->hasTable('event_images')) {
            $this->table('event_images')
                ->addColumn('event_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('image_path', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['event_id'])
                ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Ticket Types
        if (!$this->hasTable('ticket_types')) {
            $this->table('ticket_types')
                ->addColumn('event_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('organizer_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
                ->addColumn('quantity', 'integer', ['null' => false, 'default' => 0])
                ->addColumn('remaining', 'integer', ['null' => false, 'default' => 0])
                ->addColumn('dynamic_fee', 'decimal', ['precision' => 5, 'scale' => 2, 'default' => 0.00])
                ->addColumn('sale_start', 'datetime', ['null' => true])
                ->addColumn('sale_end', 'datetime', ['null' => true])
                ->addColumn('max_per_user', 'integer', ['default' => 10])
                ->addColumn('ticket_image', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['event_id'])
                ->addIndex(['organizer_id'])
                ->addIndex(['sale_start'])
                ->addIndex(['sale_end'])
                ->addForeignKey('organizer_id', 'organizers', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Attendees
        if (!$this->hasTable('attendees')) {
            $this->table('attendees')
                ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('first_name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('last_name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('phone', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('profile_image', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['user_id'])
                ->addIndex(['email'])
                ->addIndex(['phone'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Ticket Orders
        if (!$this->hasTable('ticket_orders')) {
            $this->table('ticket_orders')
                ->addColumn('attendee_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('event_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('quantity', 'integer', ['null' => false, 'default' => 0])
                ->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
                ->addColumn('status', 'enum', ['values' => ['pending', 'completed', 'cancelled'], 'default' => 'pending', 'null' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['attendee_id'])
                ->addIndex(['event_id'])
                ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('attendee_id', 'attendees', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Ticket order items
        if (!$this->hasTable('ticket_order_items')) {
            $this->table('ticket_order_items')
                ->addColumn('ticket_order_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('ticket_type_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('ticket_code', 'string', ['limit' => 10, 'null' => false])
                ->addColumn('status', 'enum', ['values' => ['used', 'unused', 'cancelled', 'refunded', 'expired', 'invalid'], 'default' => 'unused', 'null' => false])
                ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['ticket_order_id'])
                ->addIndex(['ticket_type_id'])
                ->addIndex(['ticket_code'], ['unique' => true])
                ->addForeignKey('ticket_order_id', 'ticket_orders', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('ticket_type_id', 'ticket_types', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Payout requests
        if (!$this->hasTable('payout_requests')) {
            $this->table('payout_requests')
                ->addColumn('organizer_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('event_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
                ->addColumn('payment_method', 'enum', ['values' => ['bank_transfer', 'mobile_money'], 'default' => 'bank_transfer', 'null' => false])
                ->addColumn('account_number', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('account_name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('status', 'enum', ['values' => ['pending', 'processing', 'completed', 'rejected'], 'default' => 'pending', 'null' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['organizer_id'])
                ->addIndex(['event_id'])
                ->addForeignKey('organizer_id', 'organizers', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Organizer followers
        if (!$this->hasTable('organizer_followers')) {
            $this->table('organizer_followers')
                ->addColumn('organizer_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('follower_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['organizer_id'])
                ->addIndex(['follower_id'])
                ->addForeignKey('organizer_id', 'organizers', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('follower_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Event reviews
        if (!$this->hasTable('event_reviews')) {
            $this->table('event_reviews')
                ->addColumn('event_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('reviewer_id', 'integer', ['null' => false, 'signed' => false]) // Moved UP to be created before indexing
                ->addColumn('rating', 'integer', ['null' => false, 'default' => 0])
                ->addColumn('comment', 'text', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['event_id'])
                ->addIndex(['reviewer_id'])
                ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('reviewer_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }
    }

    public function down(): void
    {
        // Dropping tables in reverse order of creation
        // Removed unnecessary ->save() calls

        if ($this->hasTable('event_reviews')) {
            $this->table('event_reviews')->drop()->save();
        }
        if ($this->hasTable('organizer_followers')) {
            $this->table('organizer_followers')->drop()->save();
        }
        if ($this->hasTable('payout_requests')) {
            $this->table('payout_requests')->drop()->save();
        }
        if ($this->hasTable('ticket_order_items')) {
            $this->table('ticket_order_items')->drop()->save();
        }
        if ($this->hasTable('ticket_orders')) {
            $this->table('ticket_orders')->drop()->save();
        }
        if ($this->hasTable('attendees')) {
            $this->table('attendees')->drop()->save();
        }
        if ($this->hasTable('ticket_types')) {
            $this->table('ticket_types')->drop()->save();
        }
        if ($this->hasTable('event_images')) {
            $this->table('event_images')->drop()->save();
        }
        if ($this->hasTable('events')) {
            $this->table('events')->drop()->save();
        }
        if ($this->hasTable('event_types')) {
            $this->table('event_types')->drop()->save();
        }
        if ($this->hasTable('organizers')) {
            $this->table('organizers')->drop()->save();
        }
        if ($this->hasTable('users')) {
            $this->table('users')->drop()->save();
        }
    }
}