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
            $table->string('name')->unique()->after('id');
            $table->string('label')->after('name');
            $table->enum('type', ['dropdown', 'checkbox', 'radio', 'text', 'number', 'number_range', 'date', 'date_range', 'multi_select', 'autocomplete'])->after('label');
            $table->string('target_table')->after('type');
            $table->string('target_column')->after('target_table');
            $table->enum('options_source', ['static', 'dynamic', 'none'])->default('none')->after('target_column');
            $table->json('options')->nullable()->after('options_source');
            $table->text('options_query')->nullable()->after('options');
            $table->boolean('required')->default(false)->after('options_query');
            $table->string('placeholder')->nullable()->after('required');
            $table->text('description')->nullable()->after('placeholder');
            $table->boolean('is_active')->default(true)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filter_definitions', function (Blueprint $table) {
            $table->dropColumn([
                'name', 'label', 'type', 'target_table', 'target_column',
                'options_source', 'options', 'options_query', 'required',
                'placeholder', 'description', 'is_active'
            ]);
        });
    }
};
