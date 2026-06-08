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
        Schema::table('medical_services', function (Blueprint $table) {
            $table->foreignId('loinc_code_id')
                ->nullable()
                ->after('service_category_id')
                ->constrained('lab_codes')
                ->nullOnDelete();

            $table->foreignId('snomed_code_id')
                ->nullable()
                ->after('loinc_code_id')
                ->constrained('lab_codes')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('snomed_code_id');
            $table->dropConstrainedForeignId('loinc_code_id');
        });
    }
};
