<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSocialsToEvents extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('events');
        $table->addColumn('website', 'string', ['null' => true, 'limit' => 255, 'after' => 'tags'])
            ->addColumn('facebook', 'string', ['null' => true, 'limit' => 255, 'after' => 'website'])
            ->addColumn('twitter', 'string', ['null' => true, 'limit' => 255, 'after' => 'facebook'])
            ->addColumn('instagram', 'string', ['null' => true, 'limit' => 255, 'after' => 'twitter'])
            ->addColumn('phone', 'string', ['null' => true, 'limit' => 50, 'after' => 'instagram'])
            ->addColumn('video_url', 'string', ['null' => true, 'limit' => 255, 'after' => 'phone'])
            ->update();
    }
}
