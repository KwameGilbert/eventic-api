<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Create Refresh Tokens Table
 * 
 * Stores refresh tokens in database for:
 * - Token revocation on logout
 * - Multi-device session management
 * - Security audit trail
 */
final class CreateRefreshTokensTable extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('refresh_tokens')) {
            $table = $this->table('refresh_tokens', ['id' => 'id', 'signed' => false]);
            
            $table->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
                  ->addColumn('token_hash', 'string', ['limit' => 255, 'null' => false])
                  ->addColumn('device_name', 'string', ['limit' => 255, 'null' => true])
                  ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true])
                  ->addColumn('user_agent', 'text', ['null' => true])
                  ->addColumn('expires_at', 'timestamp', ['null' => false])
                  ->addColumn('revoked', 'boolean', ['default' => false])
                  ->addColumn('revoked_at', 'timestamp', ['null' => true])
                  ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                  
                  // Foreign key
                  ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                  
                  // Indexes for performance
                  ->addIndex(['user_id'])
                  ->addIndex(['token_hash'], ['unique' => true])
                  ->addIndex(['expires_at'])
                  ->addIndex(['revoked'])
                  
                  ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('refresh_tokens')) {
            $this->table('refresh_tokens')->drop()->save();
        }
    }
}
