<?php
// database/migrations/YYYY_MM_DD_create_report_filters_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->foreignId('filter_definition_id')->constrained()->onDelete('cascade');
            
            // Override settings per report (optional)
            $table->integer('display_order')->default(0); // Filter order in UI
            $table->boolean('is_required')->nullable(); // Override filter's required setting
            $table->string('custom_label')->nullable(); // Override filter's label
            
            $table->timestamps();
            
            // Ensure same filter isn't added twice to same report
            $table->unique(['report_id', 'filter_definition_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_filters');
    }
};