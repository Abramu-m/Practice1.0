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
        Schema::create('cds_dosage_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_policy_id')->constrained('cds_medication_policies')->onDelete('cascade');
            $table->enum('limit_type', ['max_single', 'max_daily', 'pediatric_per_kg', 'renal_adjustment', 'hepatic_adjustment']);
            $table->decimal('value_mg', 10, 3)->nullable();
            $table->decimal('mg_per_kg', 6, 3)->nullable()->comment('For pediatric dosing');
            $table->decimal('age_min_years', 4, 1)->default(0);
            $table->decimal('age_max_years', 4, 1)->default(150);
            $table->decimal('weight_min_kg', 5, 1)->nullable();
            $table->decimal('weight_max_kg', 5, 1)->nullable();
            $table->json('special_conditions')->nullable()->comment('eGFR ranges, hepatic function, etc.');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['medication_policy_id', 'limit_type']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cds_dosage_limits');
    }
};
