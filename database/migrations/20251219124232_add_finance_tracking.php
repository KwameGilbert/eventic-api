<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Add comprehensive finance tracking tables and columns
 * 
 * This migration adds:
 * - admin_share_percent to events and awards tables
 * - Financial tracking columns to order_items and award_votes
 * - Enhanced payout_requests table with award support
 * - New platform_settings table for global configuration
 * - New transactions table for audit trail
 * - New organizer_balances table for quick balance lookup
 */
final class AddFinanceTracking extends AbstractMigration
{
    public function up(): void
    {
        // =====================================================
        // 1. Add admin_share_percent to events table
        // =====================================================
        if ($this->table('events')->hasColumn('admin_share_percent') === false) {
            $this->table('events')
                ->addColumn('admin_share_percent', 'decimal', [
                    'precision' => 5,
                    'scale' => 2,
                    'default' => 10.00,
                    'null' => false,
                    'after' => 'is_featured',
                    'comment' => 'Admin/platform share percentage (0-100). Organizer gets remainder.'
                ])
                ->update();
        }

        // =====================================================
        // 2. Add admin_share_percent to awards table
        // =====================================================
        if ($this->table('awards')->hasColumn('admin_share_percent') === false) {
            $this->table('awards')
                ->addColumn('admin_share_percent', 'decimal', [
                    'precision' => 5,
                    'scale' => 2,
                    'default' => 15.00,
                    'null' => false,
                    'after' => 'is_featured',
                    'comment' => 'Admin/platform share percentage (0-100). Organizer gets remainder.'
                ])
                ->update();
        }

        // =====================================================
        // 3. Add financial tracking columns to order_items
        // =====================================================
        $orderItemsTable = $this->table('order_items');
        
        if ($orderItemsTable->hasColumn('admin_share_percent') === false) {
            $orderItemsTable->addColumn('admin_share_percent', 'decimal', [
                'precision' => 5,
                'scale' => 2,
                'default' => 0.00,
                'null' => false,
                'after' => 'total_price'
            ]);
        }
        
        if ($orderItemsTable->hasColumn('admin_amount') === false) {
            $orderItemsTable->addColumn('admin_amount', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'default' => 0.00,
                'null' => false,
                'after' => 'admin_share_percent'
            ]);
        }
        
        if ($orderItemsTable->hasColumn('organizer_amount') === false) {
            $orderItemsTable->addColumn('organizer_amount', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'default' => 0.00,
                'null' => false,
                'after' => 'admin_amount'
            ]);
        }
        
        if ($orderItemsTable->hasColumn('payment_fee') === false) {
            $orderItemsTable->addColumn('payment_fee', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'default' => 0.00,
                'null' => false,
                'after' => 'organizer_amount'
            ]);
        }
        
        $orderItemsTable->update();

        // =====================================================
        // 4. Add financial tracking columns to award_votes
        // =====================================================
        $awardVotesTable = $this->table('award_votes');
        
        if ($awardVotesTable->hasColumn('cost_per_vote') === false) {
            $awardVotesTable->addColumn('cost_per_vote', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'default' => 0.00,
                'null' => false,
                'after' => 'number_of_votes'
            ]);
        }
        
        if ($awardVotesTable->hasColumn('gross_amount') === false) {
            $awardVotesTable->addColumn('gross_amount', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'default' => 0.00,
                'null' => false,
                'after' => 'cost_per_vote'
            ]);
        }
        
        if ($awardVotesTable->hasColumn('admin_share_percent') === false) {
            $awardVotesTable->addColumn('admin_share_percent', 'decimal', [
                'precision' => 5,
                'scale' => 2,
                'default' => 0.00,
                'null' => false,
                'after' => 'gross_amount'
            ]);
        }
        
        if ($awardVotesTable->hasColumn('admin_amount') === false) {
            $awardVotesTable->addColumn('admin_amount', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'default' => 0.00,
                'null' => false,
                'after' => 'admin_share_percent'
            ]);
        }
        
        if ($awardVotesTable->hasColumn('organizer_amount') === false) {
            $awardVotesTable->addColumn('organizer_amount', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'default' => 0.00,
                'null' => false,
                'after' => 'admin_amount'
            ]);
        }
        
        if ($awardVotesTable->hasColumn('payment_fee') === false) {
            $awardVotesTable->addColumn('payment_fee', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'default' => 0.00,
                'null' => false,
                'after' => 'organizer_amount'
            ]);
        }
        
        $awardVotesTable->update();

        // =====================================================
        // 5. Enhance payout_requests table
        // =====================================================
        $payoutTable = $this->table('payout_requests');
        
        // Make event_id nullable
        $this->execute('ALTER TABLE `payout_requests` MODIFY COLUMN `event_id` INT UNSIGNED DEFAULT NULL');
        
        if ($payoutTable->hasColumn('award_id') === false) {
            $payoutTable->addColumn('award_id', 'integer', [
                'signed' => false,
                'null' => true,
                'after' => 'event_id'
            ]);
        }
        
        if ($payoutTable->hasColumn('payout_type') === false) {
            $payoutTable->addColumn('payout_type', 'enum', [
                'values' => ['event', 'award'],
                'default' => 'event',
                'null' => false,
                'after' => 'award_id'
            ]);
        }
        
        if ($payoutTable->hasColumn('gross_amount') === false) {
            $payoutTable->addColumn('gross_amount', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'default' => 0.00,
                'null' => false,
                'after' => 'amount'
            ]);
        }
        
        if ($payoutTable->hasColumn('admin_fee') === false) {
            $payoutTable->addColumn('admin_fee', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'default' => 0.00,
                'null' => false,
                'after' => 'gross_amount'
            ]);
        }
        
        if ($payoutTable->hasColumn('bank_name') === false) {
            $payoutTable->addColumn('bank_name', 'string', [
                'limit' => 255,
                'null' => true,
                'after' => 'account_name'
            ]);
        }
        
        if ($payoutTable->hasColumn('processed_by') === false) {
            $payoutTable->addColumn('processed_by', 'integer', [
                'signed' => false,
                'null' => true,
                'after' => 'status'
            ]);
        }
        
        if ($payoutTable->hasColumn('processed_at') === false) {
            $payoutTable->addColumn('processed_at', 'timestamp', [
                'null' => true,
                'after' => 'processed_by'
            ]);
        }
        
        if ($payoutTable->hasColumn('rejection_reason') === false) {
            $payoutTable->addColumn('rejection_reason', 'text', [
                'null' => true,
                'after' => 'processed_at'
            ]);
        }
        
        if ($payoutTable->hasColumn('notes') === false) {
            $payoutTable->addColumn('notes', 'text', [
                'null' => true,
                'after' => 'rejection_reason'
            ]);
        }
        
        $payoutTable->update();
        
        // Add foreign keys for payout_requests
        if (!$this->isForeignKeyExists('payout_requests', 'fk_payout_award')) {
            $payoutTable->addForeignKey('award_id', 'awards', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
                'constraint' => 'fk_payout_award'
            ])->update();
        }
        
        if (!$this->isForeignKeyExists('payout_requests', 'fk_payout_processor')) {
            $payoutTable->addForeignKey('processed_by', 'users', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
                'constraint' => 'fk_payout_processor'
            ])->update();
        }

        // =====================================================
        // 6. Create platform_settings table
        // =====================================================
        if (!$this->hasTable('platform_settings')) {
            $this->table('platform_settings', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('setting_key', 'string', ['limit' => 100, 'null' => false])
                ->addColumn('setting_value', 'text', ['null' => false])
                ->addColumn('setting_type', 'enum', [
                    'values' => ['string', 'number', 'boolean', 'json'],
                    'default' => 'string',
                    'null' => false
                ])
                ->addColumn('description', 'text', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', [
                    'default' => 'CURRENT_TIMESTAMP',
                    'update' => 'CURRENT_TIMESTAMP',
                    'null' => true
                ])
                ->addIndex(['setting_key'], ['unique' => true])
                ->create();
            
            // Insert default settings
            $this->execute("
                INSERT INTO `platform_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
                ('default_event_admin_share', '10', 'number', 'Default admin share % for new events'),
                ('default_award_admin_share', '15', 'number', 'Default admin share % for new awards'),
                ('payout_hold_days', '7', 'number', 'Days after event/voting ends before payout eligibility'),
                ('min_payout_amount', '50', 'number', 'Minimum amount (GHS) required for payout request'),
                ('paystack_fee_percent', '1.5', 'number', 'Paystack transaction fee percentage')
            ");
        }

        // =====================================================
        // 7. Create transactions table
        // =====================================================
        if (!$this->hasTable('transactions')) {
            $this->table('transactions', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('reference', 'string', ['limit' => 100, 'null' => false])
                ->addColumn('transaction_type', 'enum', [
                    'values' => ['ticket_sale', 'vote_purchase', 'payout', 'refund'],
                    'null' => false
                ])
                ->addColumn('organizer_id', 'integer', ['signed' => false, 'null' => true])
                ->addColumn('event_id', 'integer', ['signed' => false, 'null' => true])
                ->addColumn('award_id', 'integer', ['signed' => false, 'null' => true])
                ->addColumn('order_id', 'integer', ['signed' => false, 'null' => true])
                ->addColumn('order_item_id', 'integer', ['signed' => false, 'null' => true])
                ->addColumn('vote_id', 'integer', ['signed' => false, 'null' => true])
                ->addColumn('payout_id', 'integer', ['signed' => false, 'null' => true])
                ->addColumn('gross_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
                ->addColumn('admin_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
                ->addColumn('organizer_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
                ->addColumn('payment_fee', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
                ->addColumn('status', 'enum', [
                    'values' => ['pending', 'completed', 'failed', 'refunded'],
                    'default' => 'pending',
                    'null' => false
                ])
                ->addColumn('description', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('metadata', 'json', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', [
                    'default' => 'CURRENT_TIMESTAMP',
                    'update' => 'CURRENT_TIMESTAMP',
                    'null' => true
                ])
                ->addIndex(['reference'], ['unique' => true])
                ->addIndex(['transaction_type'])
                ->addIndex(['organizer_id'])
                ->addIndex(['event_id'])
                ->addIndex(['award_id'])
                ->addIndex(['status'])
                ->addIndex(['created_at'])
                ->addForeignKey('organizer_id', 'organizers', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('event_id', 'events', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('award_id', 'awards', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('order_id', 'orders', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('order_item_id', 'order_items', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('vote_id', 'award_votes', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('payout_id', 'payout_requests', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->create();
        }

        // =====================================================
        // 8. Create organizer_balances table
        // =====================================================
        if (!$this->hasTable('organizer_balances')) {
            $this->table('organizer_balances', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('organizer_id', 'integer', ['signed' => false, 'null' => false])
                ->addColumn('available_balance', 'decimal', [
                    'precision' => 10,
                    'scale' => 2,
                    'default' => 0.00,
                    'null' => false,
                    'comment' => 'Ready for withdrawal'
                ])
                ->addColumn('pending_balance', 'decimal', [
                    'precision' => 10,
                    'scale' => 2,
                    'default' => 0.00,
                    'null' => false,
                    'comment' => 'Within hold period'
                ])
                ->addColumn('total_earned', 'decimal', [
                    'precision' => 10,
                    'scale' => 2,
                    'default' => 0.00,
                    'null' => false,
                    'comment' => 'Lifetime earnings'
                ])
                ->addColumn('total_withdrawn', 'decimal', [
                    'precision' => 10,
                    'scale' => 2,
                    'default' => 0.00,
                    'null' => false,
                    'comment' => 'Total payouts completed'
                ])
                ->addColumn('last_payout_at', 'timestamp', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', [
                    'default' => 'CURRENT_TIMESTAMP',
                    'update' => 'CURRENT_TIMESTAMP',
                    'null' => true
                ])
                ->addIndex(['organizer_id'], ['unique' => true])
                ->addForeignKey('organizer_id', 'organizers', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }
    }

    public function down(): void
    {
        // Drop new tables
        if ($this->hasTable('organizer_balances')) {
            $this->table('organizer_balances')->drop()->save();
        }
        
        if ($this->hasTable('transactions')) {
            $this->table('transactions')->drop()->save();
        }
        
        if ($this->hasTable('platform_settings')) {
            $this->table('platform_settings')->drop()->save();
        }

        // Remove columns from payout_requests
        $payoutTable = $this->table('payout_requests');
        
        if ($this->isForeignKeyExists('payout_requests', 'fk_payout_processor')) {
            $this->execute('ALTER TABLE `payout_requests` DROP FOREIGN KEY `fk_payout_processor`');
        }
        if ($this->isForeignKeyExists('payout_requests', 'fk_payout_award')) {
            $this->execute('ALTER TABLE `payout_requests` DROP FOREIGN KEY `fk_payout_award`');
        }
        
        $columnsToRemove = ['award_id', 'payout_type', 'gross_amount', 'admin_fee', 'bank_name', 'processed_by', 'processed_at', 'rejection_reason', 'notes'];
        foreach ($columnsToRemove as $col) {
            if ($payoutTable->hasColumn($col)) {
                $payoutTable->removeColumn($col);
            }
        }
        $payoutTable->update();

        // Remove columns from award_votes
        $awardVotesTable = $this->table('award_votes');
        $columnsToRemove = ['cost_per_vote', 'gross_amount', 'admin_share_percent', 'admin_amount', 'organizer_amount', 'payment_fee'];
        foreach ($columnsToRemove as $col) {
            if ($awardVotesTable->hasColumn($col)) {
                $awardVotesTable->removeColumn($col);
            }
        }
        $awardVotesTable->update();

        // Remove columns from order_items
        $orderItemsTable = $this->table('order_items');
        $columnsToRemove = ['admin_share_percent', 'admin_amount', 'organizer_amount', 'payment_fee'];
        foreach ($columnsToRemove as $col) {
            if ($orderItemsTable->hasColumn($col)) {
                $orderItemsTable->removeColumn($col);
            }
        }
        $orderItemsTable->update();

        // Remove admin_share_percent from awards
        if ($this->table('awards')->hasColumn('admin_share_percent')) {
            $this->table('awards')->removeColumn('admin_share_percent')->update();
        }

        // Remove admin_share_percent from events
        if ($this->table('events')->hasColumn('admin_share_percent')) {
            $this->table('events')->removeColumn('admin_share_percent')->update();
        }
    }

    /**
     * Check if a foreign key exists
     */
    private function isForeignKeyExists(string $tableName, string $constraintName): bool
    {
        $rows = $this->fetchAll(sprintf(
            "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = '%s' 
             AND CONSTRAINT_NAME = '%s'",
            $tableName,
            $constraintName
        ));
        return count($rows) > 0;
    }
}
