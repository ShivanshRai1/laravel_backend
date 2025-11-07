<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First drop the foreign key constraint
        Schema::table('watchlists', function (Blueprint $table) {
            // Check if foreign key exists before dropping
            try {
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.TABLE_CONSTRAINTS
                    WHERE CONSTRAINT_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'watchlists'
                    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                    AND CONSTRAINT_NAME = 'watchlists_company_id_foreign'
                ");
                
                if (!empty($foreignKeys)) {
                    $table->dropForeign('watchlists_company_id_foreign');
                }
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        });
        
        // Column type might already be changed, check first
        try {
            DB::statement('ALTER TABLE watchlists MODIFY company_id VARCHAR(30) NOT NULL');
        } catch (\Exception $e) {
            // Column might already be VARCHAR
        }
    }

    public function down(): void
    {
        // This is a dangerous downgrade as it could lose data
        // Only implement if you need to revert
        Schema::table('watchlists', function (Blueprint $table) {
            // We'd need to ensure all company_ids are valid integers first
        });
    }
};