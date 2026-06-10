<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('idsr_categories');

        Schema::table('icd_diagnoses', function (Blueprint $table) {
            $table->index('icd_code', 'icd_diagnoses_icd_code_index');
        });
    }

    public function down(): void
    {
        Schema::table('icd_diagnoses', function (Blueprint $table) {
            $table->dropIndex('icd_diagnoses_icd_code_index');
        });
    }
};
