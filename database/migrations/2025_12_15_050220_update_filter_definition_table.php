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
        Schema::table('filter_definitions',function(Blueprint $table){
            $table->boolean('is_conditional')->default(false)->after('type');
            $table->json('conditional_targets')->nullable()->after('target_column');
            $table->json('conditional_type')->nullable()->after('conditional_targets');
        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filter_definitions', function (Blueprint $table) {
            $table->dropColumn(['is_conditional', 'conditional_targets', 'conditional_type']);
        });
        //
    }
};
