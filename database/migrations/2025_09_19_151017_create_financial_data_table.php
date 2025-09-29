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
        Schema::create('financial_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('open_price', 10, 4)->nullable();
            $table->decimal('high_price', 10, 4)->nullable();
            $table->decimal('low_price', 10, 4)->nullable();
            $table->decimal('close_price', 10, 4)->nullable();
            $table->decimal('adjusted_close', 10, 4)->nullable();
            $table->bigInteger('volume')->nullable();
            $table->decimal('market_cap', 20, 2)->nullable();
            $table->decimal('pe_ratio', 8, 2)->nullable();
            $table->decimal('dividend_yield', 5, 2)->nullable();
            $table->decimal('eps', 8, 2)->nullable();
            $table->timestamps();
            
            $table->unique(['company_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_data');
    }
};
