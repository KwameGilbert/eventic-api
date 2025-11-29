<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Enhance Users Table for Production Auth
 * 
 * Adds columns for:
 * - Remember tokens
 * - Email verification
 * - Login tracking
 */
final class EnhanceUsersTable extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('users')) {
            $table = $this->table('users');
            
            // Add new columns if they don't exist
            if (!$table->hasColumn('remember_token')) {
                $table->addColumn('remember_token', 'string', [
                    'limit' => 100,
                    'null' => true,
                    'after' => 'password'
                ])->update();
            }
            
            if (!$table->hasColumn('email_verified_at')) {
                $table->addColumn('email_verified_at', 'timestamp', [
                    'null' => true,
                    'after' => 'email_verified'
                ])->update();
            }
            
            if (!$table->hasColumn('last_login_at')) {
                $table->addColumn('last_login_at', 'timestamp', [
                    'null' => true
                ])->update();
            }
            
            if (!$table->hasColumn('last_login_ip')) {
                $table->addColumn('last_login_ip', 'string', [
                    'limit' => 45,
                    'null' => true
                ])->update();
            }
        }
    }

    public function down(): void
    {
        if ($this->hasTable('users')) {
            $table = $this->table('users');
            
            if ($table->hasColumn('last_login_ip')) {
                $table->removeColumn('last_login_ip')->update();
            }
            if ($table->hasColumn('last_login_at')) {
                $table->removeColumn('last_login_at')->update();
            }
            if ($table->hasColumn('email_verified_at')) {
                $table->removeColumn('email_verified_at')->update();
            }
            if ($table->hasColumn('remember_token')) {
                $table->removeColumn('remember_token')->update();
            }
        }
    }
}
