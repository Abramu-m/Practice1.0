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
        Schema::create('cds_rule_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('cds_rule_categories')->onDelete('cascade');
            $table->string('name', 50);
            $table->string('display_name', 100);
            $table->text('description')->nullable();
            $table->string('handler_class', 255);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->unique(['category_id', 'name']);
            $table->index(['is_active', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cds_rule_types');
    }
};
