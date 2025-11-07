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
        if (!Schema::hasTable('financial_ratios')) {
            Schema::create('financial_ratios', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->unsignedBigInteger('metric_id');
                $table->string('quarter', 20); // e.g., 'CY_2025_Q1'
                $table->decimal('value', 20, 4)->nullable();
                $table->boolean('is_manual')->default(true); // Manual entry vs calculated
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                $table->foreign('metric_id')->references('id')->on('financial_metrics')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                
                // Unique constraint: one value per company-metric-quarter
                $table->unique(['company_id', 'metric_id', 'quarter'], 'unique_company_metric_quarter');
                
                // Indexes for faster queries
                $table->index(['company_id', 'quarter']);
                $table->index('quarter');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_ratios');
    }
};
