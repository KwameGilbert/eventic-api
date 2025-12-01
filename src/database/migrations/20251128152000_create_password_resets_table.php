<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Create Password Resets Table
 * 
 * Stores password reset tokens for "Forgot Password" flow
 * No primary key for performance (high write, low read)
 */
final class CreatePasswordResetsTable extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('password_resets')) {
            // Note: Using table without auto-increment ID for performance
            $table = $this->table('password_resets', ['id' => false]);
            
            $table->addColumn('email', 'string', ['limit' => 255, 'null' => false])
                  ->addColumn('token', 'string', ['limit' => 255, 'null' => false])
                  ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                  
                  // Composite index for fast lookups
                  ->addIndex(['email', 'token'])
                  ->addIndex(['created_at'])
                  
                  ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('password_resets')) {
            $this->table('password_resets')->drop()->save();
        }
    }
}
