<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateActivityLogsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('activity_logs', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
            ->addColumn('user_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('action', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('model_type', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('model_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true])
            ->addColumn('user_agent', 'text', ['null' => true])
            ->addColumn('old_values', 'json', ['null' => true])
            ->addColumn('new_values', 'json', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
            ->addIndex(['user_id'])
            ->addIndex(['action'])
            ->addIndex(['model_type', 'model_id'])
            ->addIndex(['created_at'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
