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
        Schema::table('uploaded_financial_data', function (Blueprint $table) {
            // $table->text('Company')->nullable();
            // $table->text('Currency')->nullable();
            // $table->text('CY_2025_Q1')->nullable();
            // $table->text('CY_2024_Q4')->nullable();
            // $table->text('CY_2024_Q3')->nullable();
            // $table->text('CY_2024_Q2')->nullable();
            // $table->text('CY_2024_Q1')->nullable();
            // $table->text('CY_2023_Q4')->nullable();
            // $table->text('CY_2023_Q3')->nullable();
            // $table->text('CY_2023_Q2')->nullable();
            // $table->text('CY_2023_Q1')->nullable();
            // $table->text('CY_2022_Q4')->nullable();
            // $table->text('CY_2022_Q3')->nullable();
            // $table->text('CY_2022_Q2')->nullable();
            // $table->string('original_filename', 255)->nullable();
            // $table->integer('uploaded_by')->nullable();
            // $table->dateTime('uploaded_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uploaded_financial_data', function (Blueprint $table) {
            $table->dropColumn([
                'Company', 'Currency',
                'CY_2025_Q1', 'CY_2024_Q4', 'CY_2024_Q3', 'CY_2024_Q2', 'CY_2024_Q1',
                'CY_2023_Q4', 'CY_2023_Q3', 'CY_2023_Q2', 'CY_2023_Q1',
                'CY_2022_Q4', 'CY_2022_Q3', 'CY_2022_Q2',
                'original_filename', 'uploaded_by', 'uploaded_at'
            ]);
        });
    }
};
