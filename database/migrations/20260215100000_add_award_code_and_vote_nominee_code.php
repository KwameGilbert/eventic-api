<?php

use Phinx\Migration\AbstractMigration;

class AddAwardCodeAndVoteNomineeCode extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        // Add award_code to awards table
        $awards = $this->table('awards');
        if (!$awards->hasColumn('award_code')) {
            $awards->addColumn('award_code', 'string', ['limit' => 10, 'null' => true, 'after' => 'slug'])
                   ->addIndex(['award_code'], ['unique' => true])
                   ->update();
        }

        // Add nominee_code to award_votes table
        $awardVotes = $this->table('award_votes');
        if (!$awardVotes->hasColumn('nominee_code')) {
            $awardVotes->addColumn('nominee_code', 'string', ['limit' => 20, 'null' => true, 'after' => 'nominee_id'])
                       ->update();
        }
    }
}
