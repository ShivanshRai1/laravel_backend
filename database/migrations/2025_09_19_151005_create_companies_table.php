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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol')->unique();
            $table->string('sector')->nullable();
            $table->string('industry')->nullable();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_url')->nullable();
            $table->decimal('market_cap', 20, 2)->nullable();
            $table->integer('employees')->nullable();
            $table->string('country')->nullable();
            $table->string('exchange')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
