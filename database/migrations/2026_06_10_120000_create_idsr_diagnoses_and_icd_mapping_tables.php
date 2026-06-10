<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idsr_diagnoses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 90);
            $table->timestamps();
        });

        Schema::create('idsr_icd_mapping', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idsr_diagnosis_id')->constrained('idsr_diagnoses')->cascadeOnDelete();
            $table->string('icd_code', 11);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->index('icd_code', 'idsr_icd_mapping_icd_code_index');
        });

        DB::statement("
            INSERT INTO idsr_diagnoses (id, name, created_at, updated_at)
            SELECT id, catname, NOW(), NOW() FROM medcom1_0.icd_category_idsr2
        ");

        DB::statement("
            INSERT INTO idsr_icd_mapping (id, idsr_diagnosis_id, icd_code, status, created_at, updated_at)
            SELECT ig_id, category, icd, status, NOW(), NOW() FROM medcom1_0.icd_grouping_idsr2
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('idsr_icd_mapping');
        Schema::dropIfExists('idsr_diagnoses');
    }
};
