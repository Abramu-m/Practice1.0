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
        Schema::create('cds_medication_policies', function (Blueprint $table) {
            $table->id();
            $table->string('medication_name', 200)->unique();
            $table->json('generic_names')->nullable()->comment('Array of alternative/generic names');
            $table->json('brand_names')->nullable()->comment('Array of brand names');
            $table->string('therapeutic_class', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['is_active']);
            $table->index(['therapeutic_class']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cds_medication_policies');
    }
};
