<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('allergies')) {
            // Guard against duplicate index creation
            Schema::table('allergies', function (Blueprint $table) {
                $table->unique(['patient_id','substance_name','is_active'], 'allergies_patient_substance_active_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('allergies')) {
            Schema::table('allergies', function (Blueprint $table) {
                $table->dropUnique('allergies_patient_substance_active_unique');
            });
        }
    }
};
