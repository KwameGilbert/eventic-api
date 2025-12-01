<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTagsToEvents extends AbstractMigration
{
    public function up(): void
    {
        $this->table('events')
            ->addColumn('tags', 'json', ['null' => true, 'after' => 'language'])
            ->update();
    }

    public function down(): void
    {
        $this->table('events')
            ->removeColumn('tags')
            ->update();
    }
}
