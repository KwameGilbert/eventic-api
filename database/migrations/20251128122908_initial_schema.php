<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitialSchema extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up(): void
    {
        // Users
        $this->table('users')
            ->addColumn('id', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('phone', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('password', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('role', 'enum', ['values' => ['admin', 'organizer', 'attendee', 'pos', 'scanner'], 'default' => 'attendee', 'null' => false])
            ->addColumn('email_verified', 'boolean', ['default' => false, 'null' => true])
            ->addColumn('status', 'enum', ['values' => ['active', 'suspended'], 'default' => 'active', 'null' => false])
            ->addColumn('first_login', 'boolean', ['default' => false, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'])
            ->create();

        // Event Organizers
        $this->table('organizers')
            ->addColumn('id', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('user_id', 'string', ['limit' => 255, 'null' => false])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addColumn('organization_name', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('bio', 'text', ['null' => true])
            ->addColumn('profile_image', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('social_facebook', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('social_instagram', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('social_twitter', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'])
            ->create();

        // Event Types
        $this->table('event_types')
            ->addColumn('id', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'])
            ->create();

        // Events
        $this->table('events')
            ->addColumn('id', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('organizer_id', 'string', ['limit' => 255, 'null' => false])
            ->addForeignKey('organizer_id', 'organizers', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('event_type_id', 'string', ['limit' => 255, 'null' => true])
            ->addForeignKey('event_type_id', 'event_types', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addColumn('venue_name', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('address', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('map_url', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('banner_image', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('start_time', 'datetime', ['null' => false])
            ->addColumn('end_time', 'datetime', ['null' => false])
            ->addColumn('status', 'enum', ['values' => ['draft', 'pending', 'published', 'cancelled'], 'default' => 'draft', 'null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'])
            ->create();

        // Event images / gallery
        $this->table('event_images')
            ->addColumn('id', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('event_id', 'string', ['limit' => 255, 'null' => false])
            ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addColumn('image_path', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'])
            ->addIndex(['event_id'])
            ->create();

        // Tickets (ticket types)
        $this->table('ticket_types')
            ->addColumn('id', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('event_id', 'string', ['limit' => 255, 'null' => false])
            ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addColumn('organizer_id', 'string', ['limit' => 255, 'null' => false])
            ->addForeignKey('organizer_id', 'organizers', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
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
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'])
            ->addIndex(['event_id'])
            ->addIndex(['organizer_id'])
            ->addIndex(['sale_start'])
            ->addIndex(['sale_end'])
            ->create();

        // Attendees
        $this->table('attendees')
            ->addColumn('id', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('user_id', 'string', ['limit' => 255, 'null' => false])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addColumn('first_name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('last_name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('phone', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('profile_image', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'])
            ->addIndex(['user_id'])
            ->addIndex(['email'])
            ->addIndex(['phone'])
            ->create();

        // Ticket Orders (purchase)
        $this->table('ticket_orders')
            ->addColumn('id', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('attendee_id', 'string', ['limit' => 255, 'null' => false])
            ->addForeignKey('attendee_id', 'attendees', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addColumn('event_id', 'string', ['limit' => 255, 'null' => false])
            ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addColumn('quantity', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
            ->addColumn('status', 'enum', ['values' => ['pending', 'completed', 'cancelled'], 'default' => 'pending', 'null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'])
            ->addIndex(['attendee_id'])
            ->addIndex(['event_id'])
            ->create();

        // Ticket order items
        $this->table('ticket_order_items')
            ->addColumn('id', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('ticket_order_id', 'string', ['limit' => 255, 'null' => false])
            ->addForeignKey('ticket_order_id', 'ticket_orders', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addColumn('ticket_type_id', 'string', ['limit' => 255, 'null' => false])
            ->addForeignKey('ticket_type_id', 'ticket_types', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addColumn('ticket_code', 'string', ['limit' => 10, 'null' => false])
            ->addColumn('status', 'enum', ['values' => ['used', 'unused', 'cancelled', 'refunded', 'expired', 'invalid'], 'default' => 'unused', 'null' => false])
            ->addColumn('price', 'decimal', [ 'precision' => 10, 'scale' => 2, 'default' => 0.00])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'])
            ->addIndex(['ticket_order_id'])
            ->addIndex(['ticket_type_id'])
            ->addIndex(['ticket_code'], ['unique' => true])
            ->create();
    
        // Payout requests
        $this->table('payout_requests')
            ->addColumn('id', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('organizer_id', 'string', ['limit' => 255, 'null' => false])
            ->addForeignKey('organizer_id', 'organizers', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addColumn('event_id', 'string', ['limit' => 255, 'null' => false])
            ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
            ->addColumn('payment_method', 'enum', ['values' => ['bank_transfer', 'mobile_money'], 'default' => 'bank_transfer', 'null' => false])
            ->addColumn('account_number', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('account_name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('status', 'enum', ['values' => ['pending', 'processing', 'completed', 'rejected'], 'default' => 'pending', 'null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'])
            ->addIndex(['organizer_id'])
            ->addIndex(['event_id'])
            ->create();

        // Organizer followers
        $this->table('organizer_followers')
            ->addColumn('id', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('organizer_id', 'string', ['limit' => 255, 'null' => false])
            ->addForeignKey('organizer_id', 'organizers', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addColumn('follower_id', 'string', ['limit' => 255, 'null' => false])
            ->addForeignKey('follower_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'])
            ->addIndex(['organizer_id'])
            ->addIndex(['follower_id'])
            ->create();
            
        // Event reviews
        $this->table('event_reviews')
            ->addColumn('id', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('event_id', 'string', ['limit' => 255, 'null' => false])
            ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addColumn('reviewer_id', 'string', ['limit' => 255, 'null' => false])
            ->addForeignKey('reviewer_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addColumn('rating', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('comment', 'text', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'])
            ->addIndex(['event_id'])
            ->addIndex(['reviewer_id'])
            ->create();
    }

    public function down(): void
    {
        $this->table('event_reviews')->drop()->save();
        $this->table('organizer_followers')->drop()->save();
        $this->table('payout_requests')->drop()->save();
        $this->table('ticket_order_items')->drop()->save();
        $this->table('ticket_orders')->drop()->save();
        $this->table('attendees')->drop()->save();
        $this->table('ticket_types')->drop()->save();
        $this->table('event_images')->drop()->save();
        $this->table('events')->drop()->save();
        $this->table('event_types')->drop()->save();
        $this->table('organizers')->drop()->save();
        $this->table('users')->drop()->save();
    }
}
