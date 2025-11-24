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
        Schema::create('cds_rule_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('cds_rules')->onDelete('cascade');
            $table->string('parameter_name', 100);
            $table->json('parameter_value');
            $table->enum('parameter_type', ['dosage_limit', 'age_range', 'weight_range', 'renal_adjustment', 'hepatic_adjustment', 'general'])->default('general');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['rule_id', 'parameter_name']);
            $table->index(['parameter_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cds_rule_parameters');
    }
};
