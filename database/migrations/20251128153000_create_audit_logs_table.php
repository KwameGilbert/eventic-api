<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Create Audit Logs Table
 * 
 * Tracks all authentication-related security events:
 * - Login attempts (success/failure)
 * - Logouts
 * - Password changes
 * - Account changes
 */
final class CreateAuditLogsTable extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('audit_logs')) {
            $table = $this->table('audit_logs', ['id' => 'id', 'signed' => false]);
            
            $table->addColumn('user_id', 'integer', ['null' => true, 'signed' => false]) // Nullable for failed logins
                  ->addColumn('action', 'string', ['limit' => 50, 'null' => false])
                  ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => false])
                  ->addColumn('user_agent', 'text', ['null' => true])
                  ->addColumn('metadata', 'json', ['null' => true])
                  ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                  
                  // Foreign key (SET NULL on user deletion to keep audit trail)
                  ->addForeignKey('user_id', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                  
                  // Indexes for querying
                  ->addIndex(['user_id'])
                  ->addIndex(['action'])
                  ->addIndex(['created_at'])
                  ->addIndex(['ip_address'])
                  
                  ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('audit_logs')) {
            $this->table('audit_logs')->drop()->save();
        }
    }
}
