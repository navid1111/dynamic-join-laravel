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
        Schema::create('report_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->foreignId('filter_definition_id')->constrained('filter_definitions')->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->string('custom_label')->nullable();
            $table->timestamps();
            
            $table->unique(['report_id', 'filter_definition_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_filters');
    }
};
