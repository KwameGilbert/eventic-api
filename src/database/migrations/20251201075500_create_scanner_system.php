<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateScannerSystem extends AbstractMigration
{
    public function change(): void
    {
        // Create Scanner Assignments table
        $assignments = $this->table('scanner_assignments');
        $assignments->addColumn('user_id', 'integer', ['null' => false, 'signed' => false]) // The scanner user
                   ->addColumn('event_id', 'integer', ['null' => false, 'signed' => false])
                   ->addColumn('organizer_id', 'integer', ['null' => false, 'signed' => false]) // The organizer who assigned
                   ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                   ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                   ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                   ->addForeignKey('organizer_id', 'organizers', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                   ->create();

        // Update Tickets table to track admission details
        $tickets = $this->table('tickets');
        if (!$tickets->hasColumn('admitted_by')) {
            $tickets->addColumn('admitted_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'status'])
                    ->addColumn('admitted_at', 'timestamp', ['null' => true, 'after' => 'admitted_by'])
                    ->addForeignKey('admitted_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                    ->update();
        }
    }
}
