<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration: Add payment_token column to award_votes table
 * 
 * This adds a payment_token column for storing ExpressPay tokens
 * and a payment_transaction_id for the transaction reference.
 */
final class AddPaymentTokenToAwardVotes extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('award_votes');
        
        if (!$table->hasColumn('payment_token')) {
            $table->addColumn('payment_token', 'string', [
                'limit' => 100,
                'null' => true
            ])
            ->addColumn('payment_transaction_id', 'string', [
                'limit' => 100,
                'null' => true
            ])
            ->addIndex(['payment_token'])
            ->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('award_votes');
        
        if ($table->hasColumn('payment_token')) {
            $table->removeColumn('payment_token');
        }
        
        if ($table->hasColumn('payment_transaction_id')) {
            $table->removeColumn('payment_transaction_id');
        }
        
        $table->update();
    }
}
