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
            // Guard each add to avoid duplicate-column errors when the base migration already created them.
            if (! Schema::hasColumn('filter_definitions', 'name')) {
                $table->string('name')->unique()->after('id');
            }
            if (! Schema::hasColumn('filter_definitions', 'label')) {
                $table->string('label')->after('name');
            }
            if (! Schema::hasColumn('filter_definitions', 'type')) {
                $table->enum('type', ['dropdown', 'checkbox', 'radio', 'text', 'number', 'number_range', 'date', 'date_range', 'multi_select', 'autocomplete'])->after('label');
            }
            if (! Schema::hasColumn('filter_definitions', 'target_table')) {
                $table->string('target_table')->after('type');
            }
            if (! Schema::hasColumn('filter_definitions', 'target_column')) {
                $table->string('target_column')->after('target_table');
            }
            if (! Schema::hasColumn('filter_definitions', 'options_source')) {
                $table->enum('options_source', ['static', 'dynamic', 'none'])->default('none')->after('target_column');
            }
            if (! Schema::hasColumn('filter_definitions', 'options')) {
                $table->json('options')->nullable()->after('options_source');
            }
            if (! Schema::hasColumn('filter_definitions', 'options_query')) {
                $table->text('options_query')->nullable()->after('options');
            }
            if (! Schema::hasColumn('filter_definitions', 'required')) {
                $table->boolean('required')->default(false)->after('options_query');
            }
            if (! Schema::hasColumn('filter_definitions', 'placeholder')) {
                $table->string('placeholder')->nullable()->after('required');
            }
            if (! Schema::hasColumn('filter_definitions', 'description')) {
                $table->text('description')->nullable()->after('placeholder');
            }
            if (! Schema::hasColumn('filter_definitions', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filter_definitions', function (Blueprint $table) {
            $columns = [
                'name', 'label', 'type', 'target_table', 'target_column',
                'options_source', 'options', 'options_query', 'required',
                'placeholder', 'description', 'is_active',
            ];

            // Drop only the columns that still exist to keep rollback safe.
            foreach ($columns as $column) {
                if (Schema::hasColumn('filter_definitions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
