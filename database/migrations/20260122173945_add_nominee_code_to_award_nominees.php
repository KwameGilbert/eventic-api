<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

/**
 * Migration: Add nominee_code column to award_nominees table
 * 
 * This adds a unique 4-character alphanumeric code to each nominee
 * that can be used for identification and voting purposes.
 */
class AddNomineeCodeToAwardNominees
{
    public function up(): void
    {
        $schema = DB::schema();

        // Add nominee_code column
        $schema->table('award_nominees', function (Blueprint $table) {
            $table->string('nominee_code', 4)->nullable()->unique()->after('award_id');
        });

        // Generate codes for existing nominees
        $this->generateCodesForExistingNominees();

        // Make column NOT NULL after populating
        $schema->table('award_nominees', function (Blueprint $table) {
            $table->string('nominee_code', 4)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        $schema = DB::schema();

        $schema->table('award_nominees', function (Blueprint $table) {
            $table->dropColumn('nominee_code');
        });
    }

    /**
     * Generate unique codes for existing nominees
     */
    private function generateCodesForExistingNominees(): void
    {
        $nominees = DB::table('award_nominees')->whereNull('nominee_code')->get();
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $existingCodes = [];

        foreach ($nominees as $nominee) {
            $code = $this->generateUniqueCode($characters, $existingCodes);
            $existingCodes[] = $code;
            
            DB::table('award_nominees')
                ->where('id', $nominee->id)
                ->update(['nominee_code' => $code]);
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
                $existsInDb = DB::table('award_nominees')
                    ->where('nominee_code', $code)
                    ->exists();
                    
                if (!$existsInDb) {
                    return $code;
                }
            }
        }
        
        // Fallback
        return strtoupper(substr(base_convert((string)(int)(microtime(true) * 1000), 10, 36), -4));
    }
}
