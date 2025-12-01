<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePosSystem extends AbstractMigration
{
    public function change(): void
    {
        // Cleanup if exists (idempotency for dev)
        if ($this->hasTable('pos_assignments')) {
            $this->table('pos_assignments')->drop()->save();
        }
        
        $orders = $this->table('orders');
        if ($orders->hasColumn('pos_user_id')) {
            $orders->removeColumn('pos_user_id')->update();
        }

        
    }
}
