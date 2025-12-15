<?php
// database/migrations/YYYY_MM_DD_remove_filters_from_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep the legacy filters column available for seeders; add it back if missing.
        if (!Schema::hasColumn('reports', 'filters')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->json('filters')->nullable()->after('report_details');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('reports', 'filters')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->dropColumn('filters');
            });
        }
    }
};