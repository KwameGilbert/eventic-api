<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddAudienceAndLanguageToEvents extends AbstractMigration
{
    public function up(): void
    {
        $this->table('events')
            ->addColumn('audience', 'string', ['limit' => 255, 'null' => true, 'after' => 'status'])
            ->addColumn('language', 'string', ['limit' => 255, 'null' => true, 'after' => 'audience'])
            ->update();
    }

    public function down(): void
    {
        $this->table('events')
            ->removeColumn('audience')
            ->removeColumn('language')
            ->update();
    }
}
 