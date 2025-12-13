<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEventFormatColumn extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Add event_format column to events table to distinguish between 
     * ticketing events and awards events.
     */
    public function change(): void
    {
        $this->table("events")
            ->addColumn("event_format", "enum", [
                "values" => ["ticketing", "awards"],
                "default" => "ticketing",
                "after" => "event_type_id",
                "null" => false,
            ])
            ->update();
    }
}
