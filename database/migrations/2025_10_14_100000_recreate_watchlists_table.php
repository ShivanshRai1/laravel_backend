<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop and recreate the watchlist table to fix the issues
        Schema::dropIfExists('watchlists');
        
        Schema::create('watchlists', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_id', 30); // String company ID (ticker/symbol)
            $table->string('company_name')->nullable();
            $table->string('company_ticker')->nullable();
            $table->string('alert_type')->nullable();
            $table->string('alert_value')->nullable();
            $table->timestamps();
            
            // Create a unique index on user + company to prevent duplicates
            $table->unique(['user_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlists');
    }
};