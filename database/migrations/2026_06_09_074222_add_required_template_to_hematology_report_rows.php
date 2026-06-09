<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hematology_report_rows', function (Blueprint $table) {
            $table->string('required_template_name', 100)->nullable()->after('fbp_param_name');
        });

        // template_name values as stored in investigation_template_results
        $map = [
            'haemoglobin'           => 'Single Numeric Lab Values',
            'fbp'                   => 'Full Blood Picture',
            'wbc_count'             => 'Full Blood Picture',
            'wbc_diff'              => 'Full Blood Picture',
            'platelets'             => 'Full Blood Picture',
            'reticulocytes'         => 'Full Blood Picture',
            'peripheral_blood_film' => 'Full Blood Picture',
            'pcv'                   => 'Full Blood Picture',
            'rbc_count'             => 'Full Blood Picture',
            'sickling_test'         => 'Qualitative Positive Negative',
            'sickling_test_pos'     => 'Qualitative Positive Negative',
            'clotting_time'         => 'Single Numeric Lab Values',
            'bleeding_time'         => 'Single Numeric Lab Values',
            'prothrombin_time'      => 'Single Numeric Lab Values',
            'gcpd'                  => 'Single Numeric Lab Values',
            'le_cell'               => 'Single Numeric Lab Values',
            'ptt'                   => 'Single Numeric Lab Values',
            'esr'                   => 'Single Numeric Lab Values',
        ];

        foreach ($map as $rowKey => $templateName) {
            DB::table('hematology_report_rows')
                ->where('row_key', $rowKey)
                ->update(['required_template_name' => $templateName]);
        }
    }

    public function down(): void
    {
        Schema::table('hematology_report_rows', function (Blueprint $table) {
            $table->dropColumn('required_template_name');
        });
    }
};
