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
            if (! Schema::hasColumn('filter_definitions', 'is_conditional')) {
                $table->boolean('is_conditional')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('filter_definitions', 'conditional_targets')) {
                $table->json('conditional_targets')->nullable()->after('is_conditional');
            }
            if (! Schema::hasColumn('filter_definitions', 'conditional_type')) {
                $table->string('conditional_type')->nullable()->after('conditional_targets');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filter_definitions', function (Blueprint $table) {
            if (Schema::hasColumn('filter_definitions', 'is_conditional')) {
                $table->dropColumn('is_conditional');
            }
            if (Schema::hasColumn('filter_definitions', 'conditional_targets')) {
                $table->dropColumn('conditional_targets');
            }
            if (Schema::hasColumn('filter_definitions', 'conditional_type')) {
                $table->dropColumn('conditional_type');
            }
        });
    }
};
