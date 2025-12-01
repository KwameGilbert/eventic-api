<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddStatusToTicketTypes extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('ticket_types');
        
        if (!$table->hasColumn('status')) {
            $table->addColumn('status', 'enum', [
                'values' => ['active', 'deactivated'],
                'default' => 'active',
                'null' => false,
                'after' => 'ticket_image' // Place it after ticket_image column
            ])
            ->update();
        }
    }
}
