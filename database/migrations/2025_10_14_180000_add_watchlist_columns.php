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
            $table->uuid('uuid')->nullable()->after('id');
            $table->string('company_name')->nullable()->after('company_id');
            $table->string('alert_type')->nullable()->after('company_name');
            $table->string('alert_value')->nullable()->after('alert_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('watchlists', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'company_name', 'alert_type', 'alert_value']);
        });
    }
};
