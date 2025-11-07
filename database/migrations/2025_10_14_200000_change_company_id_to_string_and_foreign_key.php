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
            // Drop the existing foreign key constraint
            $table->dropForeign(['company_id']);
        });
        Schema::table('watchlists', function (Blueprint $table) {
            // Change company_id to string
            $table->string('company_id')->change();
        });
        // Re-add foreign key constraint to companies.symbol
        Schema::table('watchlists', function (Blueprint $table) {
            $table->foreign('company_id')->references('symbol')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('watchlists', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });
        Schema::table('watchlists', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->change();
        });
        // Re-add original foreign key to companies.id
        Schema::table('watchlists', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }
};
