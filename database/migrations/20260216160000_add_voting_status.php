<?php

use Phinx\Migration\AbstractMigration;

class AddVotingStatus extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change(): void
    {
        // Add voting_status to awards table
        $awards = $this->table('awards');
        if (!$awards->hasColumn('voting_status')) {
            $awards->addColumn('voting_status', 'enum', [
                'values' => ['open', 'closed'],
                'default' => 'open',
                'null' => false,
                'comment' => 'Manually open or close voting for the entire award'
            ])->update();
        }

        // Add voting_status to award_categories table
        $categories = $this->table('award_categories');
        if (!$categories->hasColumn('voting_status')) {
             $categories->addColumn('voting_status', 'enum', [
                'values' => ['open', 'closed'],
                'default' => 'open',
                'null' => false,
                'comment' => 'Manually open or close voting for this specific category'
            ])->update();
        }
    }
}
