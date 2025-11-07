<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('watchlists', function (Blueprint $table) {
            // First change the column type to accept string company IDs (e.g., ticker symbols)
            DB::statement('ALTER TABLE watchlists MODIFY company_id VARCHAR(30) NOT NULL');
        });
    }

    public function down(): void
    {
        Schema::table('watchlists', function (Blueprint $table) {
            // Revert to integer for downgrade (unsafe)
            DB::statement('ALTER TABLE watchlists MODIFY company_id BIGINT NOT NULL');
        });
    }
};