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
        if (!Schema::hasTable('financial_metrics')) {
            Schema::create('financial_metrics', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique(); // Internal name (e.g., 'current_ratio')
                $table->string('display_name'); // Display name (e.g., 'Current Ratio')
                $table->enum('type', ['currency', 'percentage', 'ratio', 'count', 'text'])->default('ratio');
                $table->string('unit', 50)->nullable(); // USD, %, x, etc.
                $table->text('formula')->nullable(); // For calculated metrics
                $table->string('category', 100)->nullable(); // Profitability, Liquidity, etc.
                $table->boolean('is_custom')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_metrics');
    }
};
