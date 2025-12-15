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
        Schema::table('filter_definitions', function (Blueprint $table) {
            $table->string('target_table')->nullable()->change();
            $table->string('target_column')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filter_definitions', function (Blueprint $table) {
            $table->string('target_table')->nullable(false)->change();
            $table->string('target_column')->nullable(false)->change();
        });
    }
};
