<?php
// database/migrations/YYYY_MM_DD_remove_filters_from_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Remove the old filters JSON column (move to relational structure)
            $table->dropColumn('filters');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->json('filters')->nullable();
        });
    }
};