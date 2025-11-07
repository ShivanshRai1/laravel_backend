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
        Schema::create('email_alert_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_id')->nullable(); // Can watch specific company or all
            $table->enum('alert_type', ['new_data', 'ratio_change', 'all'])->default('all');
            $table->decimal('threshold', 8, 2)->default(5.00); // Percentage threshold for ratio changes
            $table->json('watched_ratios')->nullable(); // Specific ratios to watch
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'enabled']);
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_alert_preferences');
    }
};
