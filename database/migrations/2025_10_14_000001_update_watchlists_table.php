<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Skip this migration if the watchlist table doesn't exist
        if (!Schema::hasTable('watchlists')) {
            return;
        }
        
        // Check if the columns already exist to avoid duplicate column errors
        if (Schema::hasColumn('watchlists', 'uuid') && 
            Schema::hasColumn('watchlists', 'company_name') && 
            Schema::hasColumn('watchlists', 'company_ticker')) {
            // Columns already exist, skip migration
            return;
        }
        
        // First, drop the foreign key constraint if it exists
        try {
            Schema::table('watchlists', function (Blueprint $table) {
                // Get all foreign keys
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $foreignKeys = $sm->listTableForeignKeys('watchlists');
                
                // Loop through foreign keys and drop the one for company_id
                foreach ($foreignKeys as $foreignKey) {
                    if (in_array('company_id', $foreignKey->getLocalColumns())) {
                        $table->dropForeign($foreignKey->getName());
                    }
                }
            });
        } catch (\Exception $e) {
            // Log error but continue
            \Log::error("Failed to drop foreign key: {$e->getMessage()}");
        }

        // Check if company_id is a foreignId before trying to modify it
        if (Schema::hasColumn('watchlists', 'company_id')) {
            // Get the column type
            $columnType = Schema::getColumnType('watchlists', 'company_id');
            
            // Only modify if it's an integer type
            if ($columnType === 'bigint') {
                // Drop and recreate the column as string
                Schema::table('watchlists', function (Blueprint $table) {
                    $table->dropColumn('company_id');
                });
                
                Schema::table('watchlists', function (Blueprint $table) {
                    $table->string('company_id', 20)->after('user_id');
                });
            }
        }
        
        // Add the other columns if they don't exist
        Schema::table('watchlists', function (Blueprint $table) {
            if (!Schema::hasColumn('watchlists', 'company_name')) {
                $table->string('company_name')->nullable()->after('company_id');
            }
            if (!Schema::hasColumn('watchlists', 'company_ticker')) {
                $table->string('company_ticker')->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('watchlists', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('watchlists')) {
            Schema::table('watchlists', function (Blueprint $table) {
                $table->dropColumn(['company_name', 'company_ticker', 'uuid']);
                $table->dropColumn('company_id');
                $table->foreignId('company_id')->constrained()->after('user_id');
            });
        }
    }
};