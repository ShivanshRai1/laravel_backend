<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('financial_data', function (Blueprint $table) {
            if (!Schema::hasColumn('financial_data', 'revenue')) {
                $table->decimal('revenue', 20, 2)->nullable()->after('date');
            }
            if (!Schema::hasColumn('financial_data', 'profit')) {
                $table->decimal('profit', 20, 2)->nullable()->after('revenue');
            }
        });
    }

    public function down(): void
    {
        Schema::table('financial_data', function (Blueprint $table) {
            if (Schema::hasColumn('financial_data', 'revenue')) {
                $table->dropColumn('revenue');
            }
            if (Schema::hasColumn('financial_data', 'profit')) {
                $table->dropColumn('profit');
            }
        });
    }
};
