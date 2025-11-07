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
            // Add company_id column if it doesn't exist
            if (!Schema::hasColumn('uploaded_financial_data', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            }
            
            // Add batch_id to group uploads together
            if (!Schema::hasColumn('uploaded_financial_data', 'batch_id')) {
                $table->string('batch_id', 100)->nullable()->after('company_id');
                $table->index('batch_id');
            }
            
            // Add is_manual flag to distinguish manual edits from uploads
            if (!Schema::hasColumn('uploaded_financial_data', 'is_manual')) {
                $table->boolean('is_manual')->default(false)->after('uploaded_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uploaded_financial_data', function (Blueprint $table) {
            if (Schema::hasColumn('uploaded_financial_data', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }
            if (Schema::hasColumn('uploaded_financial_data', 'batch_id')) {
                $table->dropIndex(['batch_id']);
                $table->dropColumn('batch_id');
            }
            if (Schema::hasColumn('uploaded_financial_data', 'is_manual')) {
                $table->dropColumn('is_manual');
            }
        });
    }
};
