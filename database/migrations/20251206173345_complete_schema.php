<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CompleteSchema extends AbstractMigration
{
    public function up(): void
    {
        // Users
        if (!$this->hasTable('users')) {
            $this->table('users')
                ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('password', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('remember_token', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('role', 'enum', ['values' => ['admin', 'organizer', 'attendee', 'pos', 'scanner'], 'default' => 'attendee', 'null' => false])
                ->addColumn('email_verified', 'boolean', ['default' => false, 'null' => true])
                ->addColumn('email_verified_at', 'timestamp', ['null' => true])
                ->addColumn('status', 'enum', ['values' => ['active', 'suspended'], 'default' => 'active', 'null' => false])
                ->addColumn('first_login', 'boolean', ['default' => false, 'null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('last_login_at', 'timestamp', ['null' => true])
                ->addColumn('last_login_ip', 'string', ['limit' => 45, 'null' => true])
                ->addIndex(['email'])
                ->create();
        }

        // Organizers
        if (!$this->hasTable('organizers')) {
            $this->table('organizers')
                ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('organization_name', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('bio', 'text', ['null' => true])
                ->addColumn('profile_image', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('social_facebook', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('social_instagram', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('social_twitter', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['user_id'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Event Types
        if (!$this->hasTable('event_types')) {
            $this->table('event_types')
                ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('description', 'text', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
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
                ->addColumn('is_featured', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('audience', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('language', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('tags', 'json', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['event_type_id'])
                ->addIndex(['organizer_id'])
                ->addIndex(['is_featured'])
                ->addForeignKey('event_type_id', 'event_types', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('organizer_id', 'organizers', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Event Images
        if (!$this->hasTable('event_images')) {
            $this->table('event_images')
                ->addColumn('event_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('image_path', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
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
                ->addColumn('status', 'enum', ['values' => ['active', 'deactivated'], 'default' => 'active', 'null' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['event_id'])
                ->addIndex(['organizer_id'])
                ->addIndex(['sale_start'])
                ->addIndex(['sale_end'])
                ->addForeignKey('organizer_id', 'organizers', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Attendees (profile data for attendee users)
        if (!$this->hasTable('attendees')) {
            $this->table('attendees')
                ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('first_name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('last_name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('phone', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('bio', 'text', ['null' => true])
                ->addColumn('profile_image', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['user_id'])
                ->addIndex(['email'])
                ->addIndex(['phone'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Orders
        if (!$this->hasTable('orders')) {
            $this->table('orders')
                ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('total_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
                ->addColumn('status', 'enum', ['values' => ['pending', 'paid', 'failed', 'refunded', 'cancelled'], 'default' => 'pending'])
                ->addColumn('payment_reference', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('subtotal', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
                ->addColumn('fees', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
                ->addColumn('customer_email', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('customer_name', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('customer_phone', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('paid_at', 'datetime', ['null' => true])
                ->addIndex(['user_id'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Order Items
        if (!$this->hasTable('order_items')) {
            $this->table('order_items')
                ->addColumn('order_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('event_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('ticket_type_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('quantity', 'integer', ['null' => false])
                ->addColumn('unit_price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
                ->addColumn('total_price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['order_id'])
                ->addIndex(['event_id'])
                ->addIndex(['ticket_type_id'])
                ->addForeignKey('order_id', 'orders', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('ticket_type_id', 'ticket_types', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Tickets
        if (!$this->hasTable('tickets')) {
            $this->table('tickets')
                ->addColumn('order_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('event_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('ticket_type_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('ticket_code', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('status', 'enum', ['values' => ['active', 'used', 'cancelled'], 'default' => 'active'])
                ->addColumn('admitted_by', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('admitted_at', 'timestamp', ['null' => true])
                ->addColumn('attendee_id', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['ticket_code'], ['unique' => true])
                ->addIndex(['order_id'])
                ->addIndex(['event_id'])
                ->addIndex(['ticket_type_id'])
                ->addIndex(['attendee_id'])
                ->addIndex(['admitted_by'])
                ->addForeignKey('order_id', 'orders', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('ticket_type_id', 'ticket_types', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('attendee_id', 'attendees', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('admitted_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->create();
        }

        // Event Reviews
        if (!$this->hasTable('event_reviews')) {
            $this->table('event_reviews')
                ->addColumn('event_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('reviewer_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('rating', 'integer', ['null' => false, 'default' => 0])
                ->addColumn('comment', 'text', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['event_id'])
                ->addIndex(['reviewer_id'])
                ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('reviewer_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Organizer Followers
        if (!$this->hasTable('organizer_followers')) {
            $this->table('organizer_followers')
                ->addColumn('organizer_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('follower_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['organizer_id'])
                ->addIndex(['follower_id'])
                ->addForeignKey('organizer_id', 'organizers', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('follower_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Payout Requests
        if (!$this->hasTable('payout_requests')) {
            $this->table('payout_requests')
                ->addColumn('organizer_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('event_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
                ->addColumn('payment_method', 'enum', ['values' => ['bank_transfer', 'mobile_money'], 'default' => 'bank_transfer', 'null' => false])
                ->addColumn('account_number', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('account_name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('status', 'enum', ['values' => ['pending', 'processing', 'completed', 'rejected'], 'default' => 'pending', 'null' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['organizer_id'])
                ->addIndex(['event_id'])
                ->addForeignKey('organizer_id', 'organizers', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Password Resets (no auto-increment id)
        if (!$this->hasTable('password_resets')) {
            $this->table('password_resets')
                ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('token', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['email', 'token'], ['name' => 'password_resets_email_token'])
                ->addIndex(['created_at'], ['name' => 'password_resets_created_at'])
                ->create();
        }

        // Audit Logs
        if (!$this->hasTable('audit_logs')) {
            $this->table('audit_logs')
                ->addColumn('user_id', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('action', 'string', ['limit' => 50, 'null' => false])
                ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => false])
                ->addColumn('user_agent', 'text', ['null' => true])
                ->addColumn('metadata', 'json', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['user_id'])
                ->addIndex(['action'])
                ->addIndex(['created_at'])
                ->addIndex(['ip_address'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->create();
        }

        // Refresh Tokens
        if (!$this->hasTable('refresh_tokens')) {
            $this->table('refresh_tokens')
                ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('token_hash', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('device_name', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true])
                ->addColumn('user_agent', 'text', ['null' => true])
                ->addColumn('expires_at', 'timestamp', ['null' => false])
                ->addColumn('revoked', 'boolean', ['default' => false])
                ->addColumn('revoked_at', 'timestamp', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['token_hash'], ['unique' => true])
                ->addIndex(['user_id'])
                ->addIndex(['expires_at'])
                ->addIndex(['revoked'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // Scanner Assignments
        if (!$this->hasTable('scanner_assignments')) {
            $this->table('scanner_assignments')
                ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('event_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('organizer_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['user_id'])
                ->addIndex(['event_id'])
                ->addIndex(['organizer_id'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('organizer_id', 'organizers', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // POS Assignments
        if (!$this->hasTable('pos_assignments')) {
            $this->table('pos_assignments')
                ->addColumn('user_id', 'integer', ['null' => true])
                ->addColumn('event_id', 'integer', ['null' => true])
                ->addColumn('organizer_id', 'integer', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->create();
        }
    }

    public function down(): void
    {
        // Drop tables in reverse order of creation (respecting foreign key constraints)
        if ($this->hasTable('pos_assignments')) {
            $this->table('pos_assignments')->drop()->save();
        }
        if ($this->hasTable('scanner_assignments')) {
            $this->table('scanner_assignments')->drop()->save();
        }
        if ($this->hasTable('refresh_tokens')) {
            $this->table('refresh_tokens')->drop()->save();
        }
        if ($this->hasTable('audit_logs')) {
            $this->table('audit_logs')->drop()->save();
        }
        if ($this->hasTable('password_resets')) {
            $this->table('password_resets')->drop()->save();
        }
        if ($this->hasTable('payout_requests')) {
            $this->table('payout_requests')->drop()->save();
        }
        if ($this->hasTable('organizer_followers')) {
            $this->table('organizer_followers')->drop()->save();
        }
        if ($this->hasTable('event_reviews')) {
            $this->table('event_reviews')->drop()->save();
        }
        if ($this->hasTable('tickets')) {
            $this->table('tickets')->drop()->save();
        }
        if ($this->hasTable('order_items')) {
            $this->table('order_items')->drop()->save();
        }
        if ($this->hasTable('orders')) {
            $this->table('orders')->drop()->save();
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
