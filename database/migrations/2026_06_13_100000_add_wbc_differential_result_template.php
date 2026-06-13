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
        $templateId = DB::table('result_templates')->where('code', 'wbc_differential')->value('id');

        if (!$templateId) {
            $templateId = DB::table('result_templates')->insertGetId([
                'name'        => 'WBC, Total & Differential Count',
                'code'        => 'wbc_differential',
                'description' => 'Total WBC count with neutrophil, lymphocyte, monocyte, eosinophil and basophil differential',
                'sort_order'  => 8,
                'is_active'   => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        DB::table('medical_services')
            ->where('name', 'WBC, total and differential')
            ->update(['result_template_id' => $templateId, 'updated_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $fullBloodPictureId = DB::table('result_templates')->where('code', 'full_blood_picture')->value('id');

        if ($fullBloodPictureId) {
            DB::table('medical_services')
                ->where('name', 'WBC, total and differential')
                ->update(['result_template_id' => $fullBloodPictureId, 'updated_at' => now()]);
        }

        DB::table('result_templates')->where('code', 'wbc_differential')->delete();
    }
};
