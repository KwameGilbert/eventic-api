<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPaymentMethodAndSource extends AbstractMigration
{
    public function change(): void
    {
        // Add payment_method and source to award_votes
        $awardVotes = $this->table('award_votes');
        $awardVotes
            ->addColumn('payment_method', 'string', [
                'limit' => 50,
                'null' => true,
                'default' => null,
                'after' => 'nominee_code',
                'comment' => 'Payment method used: expresspay, mobile_money, card, etc.',
            ])
            ->addColumn('source', 'enum', [
                'values' => ['website', 'ussd'],
                'null' => true,
                'default' => null,
                'after' => 'payment_method',
                'comment' => 'Source of the vote: website or ussd',
            ])
            ->update();

        // Add payment_method and source to transactions
        $transactions = $this->table('transactions');
        $transactions
            ->addColumn('payment_method', 'string', [
                'limit' => 50,
                'null' => true,
                'default' => null,
                'after' => 'metadata',
                'comment' => 'Payment method used: expresspay, mobile_money, card, etc.',
            ])
            ->addColumn('source', 'enum', [
                'values' => ['website', 'ussd'],
                'null' => true,
                'default' => null,
                'after' => 'payment_method',
                'comment' => 'Source of the transaction: website or ussd',
            ])
            ->update();
    }
}
