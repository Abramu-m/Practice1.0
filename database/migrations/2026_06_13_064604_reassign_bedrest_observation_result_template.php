<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $vitalObservationsId = DB::table('result_templates')->where('code', 'vital_observations')->value('id');

        if ($vitalObservationsId) {
            DB::table('medical_services')
                ->where('name', 'Bed Rest/Observation')
                ->update(['result_template_id' => $vitalObservationsId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $narrativeLabId = DB::table('result_templates')->where('code', 'narrative_lab')->value('id');

        if ($narrativeLabId) {
            DB::table('medical_services')
                ->where('name', 'Bed Rest/Observation')
                ->update(['result_template_id' => $narrativeLabId]);
        }
    }
};
