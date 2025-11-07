<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('watchlists', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->nullable()->after('id');
            $table->string('company_name')->nullable()->after('company_id');
            $table->string('company_ticker')->nullable()->after('company_name');
            $table->index(['uuid']);
            $table->index(['company_ticker']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('watchlists', function (Blueprint $table) {
            $table->dropIndex(['uuid']);
            $table->dropIndex(['company_ticker']);
            $table->dropColumn(['uuid', 'company_name', 'company_ticker']);
        });
    }
};
