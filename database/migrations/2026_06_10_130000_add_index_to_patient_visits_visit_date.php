<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $exists = collect(DB::select("SHOW INDEX FROM patient_visits"))
            ->contains('Key_name', 'patient_visits_visit_date_index');

        if (! $exists) {
            Schema::table('patient_visits', function (Blueprint $table) {
                $table->index('visit_date', 'patient_visits_visit_date_index');
            });
        }
    }

    public function down(): void
    {
        $exists = collect(DB::select("SHOW INDEX FROM patient_visits"))
            ->contains('Key_name', 'patient_visits_visit_date_index');

        if ($exists) {
            Schema::table('patient_visits', function (Blueprint $table) {
                $table->dropIndex('patient_visits_visit_date_index');
            });
        }
    }
};
