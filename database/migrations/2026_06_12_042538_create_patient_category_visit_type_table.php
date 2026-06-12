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
        Schema::create('patient_category_visit_type', function (Blueprint $table) {
            $table->foreignId('patient_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('visit_type_id')->constrained()->onDelete('cascade');
            $table->primary(['patient_category_id', 'visit_type_id']);
            $table->index('visit_type_id', 'patient_category_visit_type_visit_type_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_category_visit_type');
    }
};
