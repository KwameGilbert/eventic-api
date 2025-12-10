<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCompletedToEventStatus extends AbstractMigration
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
        $this->table('events')
        ->changeColumn('status','enum', ['values' => ['draft', 'pending', 'published', 'cancelled', 'completed'], 'default' => 'draft', 'null' => false])
        ->addColumn('country','string', ['limit'=> 255,'null'=> false, 'default'=>'Ghana'])
        ->addColumn('region','string', ['limit'=> 255,'null'=> false, 'default'=>'Greater Accra'])
        ->addColumn('city','string', ['limit'=>255, 'null'=> false, 'default'=>'Accra'])
        ->update();

        $this->table('ticket_types')
        ->addColumn('sale_price','decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
        ->update();


    }
}
