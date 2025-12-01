<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTicketingSystem extends AbstractMigration
{
    public function change(): void
    {
        // Drop old tables if they exist
        if ($this->hasTable('ticket_order_items')) {
            $this->table('ticket_order_items')->drop()->save();
        }
        if ($this->hasTable('ticket_orders')) {
            $this->table('ticket_orders')->drop()->save();
        }

        // Create Orders table
        $orders = $this->table('orders');
        $orders->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
              ->addColumn('total_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
              ->addColumn('status', 'enum', ['values' => ['pending', 'paid', 'failed', 'refunded'], 'default' => 'pending'])
              ->addColumn('payment_reference', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->create();

        // Create Order Items table
        $orderItems = $this->table('order_items');
        $orderItems->addColumn('order_id', 'integer', ['null' => false, 'signed' => false])
                   ->addColumn('event_id', 'integer', ['null' => false, 'signed' => false])
                   ->addColumn('ticket_type_id', 'integer', ['null' => false, 'signed' => false])
                   ->addColumn('quantity', 'integer', ['null' => false])
                   ->addColumn('unit_price', 'decimal', ['precision' => 10, 'scale' => 2])
                   ->addColumn('total_price', 'decimal', ['precision' => 10, 'scale' => 2])
                   ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                   ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                   ->addForeignKey('order_id', 'orders', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                   ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                   ->addForeignKey('ticket_type_id', 'ticket_types', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                   ->create();

        // Create Tickets table
        $tickets = $this->table('tickets');
        $tickets->addColumn('order_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('event_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('ticket_type_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('ticket_code', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('status', 'enum', ['values' => ['active', 'used', 'cancelled'], 'default' => 'active'])
                ->addColumn('attendee_id', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['ticket_code'], ['unique' => true])
                ->addForeignKey('order_id', 'orders', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('ticket_type_id', 'ticket_types', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('attendee_id', 'attendees', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->create();
    }
}
