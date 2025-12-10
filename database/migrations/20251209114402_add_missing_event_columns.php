<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddMissingEventColumns extends AbstractMigration
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
        $table = $this->table('events');
        
        // Check and add missing columns
        if (!$table->hasColumn('website')) {
            $table->addColumn('website', 'string', ['limit' => 255, 'null' => true, 'after' => 'tags']);
        }
        
        if (!$table->hasColumn('facebook')) {
            $table->addColumn('facebook', 'string', ['limit' => 255, 'null' => true, 'after' => 'website']);
        }
        
        if (!$table->hasColumn('twitter')) {
            $table->addColumn('twitter', 'string', ['limit' => 255, 'null' => true, 'after' => 'facebook']);
        }
        
        if (!$table->hasColumn('instagram')) {
            $table->addColumn('instagram', 'string', ['limit' => 255, 'null' => true, 'after' => 'twitter']);
        }
        
        if (!$table->hasColumn('phone')) {
            $table->addColumn('phone', 'string', ['limit' => 50, 'null' => true, 'after' => 'instagram']);
        }
        
        if (!$table->hasColumn('video_url')) {
            $table->addColumn('video_url', 'string', ['limit' => 255, 'null' => true, 'after' => 'phone']);
        }
        
        if (!$table->hasColumn('country')) {
            $table->addColumn('country', 'string', ['limit' => 100, 'null' => false, 'default' => 'Ghana', 'after' => 'video_url']);
        }
        
        if (!$table->hasColumn('region')) {
            $table->addColumn('region', 'string', ['limit' => 100, 'null' => false, 'default' => 'Greater Accra', 'after' => 'country']);
        }
        
        if (!$table->hasColumn('city')) {
            $table->addColumn('city', 'string', ['limit' => 100, 'null' => false, 'default' => 'Accra', 'after' => 'region']);
        }
        
        $table->update();
    }
}
