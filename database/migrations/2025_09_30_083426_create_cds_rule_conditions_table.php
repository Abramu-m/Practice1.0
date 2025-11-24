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
        Schema::create('cds_rule_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('cds_rules')->onDelete('cascade');
            $table->string('field_name', 100)->comment('medication_name, patient_age, etc.');
            $table->enum('operator', ['equals', 'not_equals', 'contains', 'not_contains', 'greater_than', 'less_than', 'greater_equal', 'less_equal', 'in', 'not_in', 'regex']);
            $table->text('value');
            $table->enum('value_type', ['string', 'integer', 'float', 'boolean', 'array', 'json'])->default('string');
            $table->enum('logical_operator', ['AND', 'OR'])->default('AND');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['rule_id', 'is_active']);
            $table->index(['field_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cds_rule_conditions');
    }
};
