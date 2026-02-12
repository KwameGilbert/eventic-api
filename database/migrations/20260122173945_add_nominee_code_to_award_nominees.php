<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration: Add nominee_code column to award_nominees table
 * 
 * This adds a unique 4-character alphanumeric code to each nominee
 * that can be used for identification and voting purposes.
 */
final class AddNomineeCodeToAwardNominees extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('award_nominees');
        
        // Add nominee_code column if it doesn't exist
        if (!$table->hasColumn('nominee_code')) {
            $table->addColumn('nominee_code', 'string', [
                'limit' => 10,
                'null' => true
            ])
            ->addIndex(['nominee_code'], ['unique' => true])
            ->update();
            
            // Generate codes for existing nominees
            $this->generateCodesForExistingNominees();
            
            $table->changeColumn('nominee_code', 'string', [
                'limit' => 10,
                'null' => false
            ])->update();
        };
    }

    public function down(): void
    {
        $table = $this->table('award_nominees');
        
        if ($table->hasColumn('nominee_code')) {
            $table->removeColumn('nominee_code')
                  ->update();
        }
    }

    /**
     * Generate unique codes for existing nominees
     */
    private function generateCodesForExistingNominees(): void
    {
        $rows = $this->fetchAll('SELECT id FROM award_nominees WHERE nominee_code IS NULL');
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $existingCodes = [];

        foreach ($rows as $row) {
            $code = $this->generateUniqueCode($characters, $existingCodes);
            $existingCodes[] = $code;
            
            $this->execute(
                sprintf(
                    "UPDATE award_nominees SET nominee_code = '%s' WHERE id = %d",
                    $code,
                    $row['id']
                )
            );
        }
    }

    /**
     * Generate a unique 4-character code
     */
    private function generateUniqueCode(string $characters, array $existingCodes): string
    {
        $maxAttempts = 100;
        
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $code = '';
            for ($i = 0; $i < 4; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
            
            // Check if code is unique
            if (!in_array($code, $existingCodes)) {
                $existing = $this->fetchRow(
                    sprintf("SELECT id FROM award_nominees WHERE nominee_code = '%s'", $code)
                );
                    
                if (!$existing) {
                    return $code;
                }
            }
        }
        
        // Fallback
        return strtoupper(substr(base_convert((string)(int)(microtime(true) * 1000), 10, 36), -4));
    }
}
