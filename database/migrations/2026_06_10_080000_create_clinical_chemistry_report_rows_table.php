<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_chemistry_report_rows', function (Blueprint $table) {
            $table->id();
            $table->string('row_key', 100)->unique();
            $table->string('row_label');
            $table->smallInteger('sort_order')->default(0);
            $table->json('service_ids')->nullable();
            $table->string('param_name', 100)->nullable();
            $table->string('required_template_name', 100)->nullable();
            $table->boolean('abnormal_as_high')->default(false);
            $table->boolean('track_low_high')->default(false);
            $table->boolean('is_section_header')->default(false);
            $table->boolean('is_configurable')->default(true);
            $table->timestamps();
        });

        $now = now();
        $singleNumeric = 'Single Numeric Lab Values';
        $urinalysis = 'Urinalysis';

        $rows = [
            ['blood_glucose',      'Blood glucose',                1, $singleNumeric, null,       false, true,  false, true],
            ['creatinine',         'Creatinine',                   2, $singleNumeric, null,       false, true,  false, true],
            ['urea',               'Urea',                         3, $singleNumeric, null,       false, true,  false, true],
            ['uric_acid',          'Uric Acid',                    4, $singleNumeric, null,       false, true,  false, true],
            ['alat',               'ALAT',                         5, $singleNumeric, null,       false, true,  false, true],
            ['asat',               'ASAT',                         6, $singleNumeric, null,       false, true,  false, true],
            ['cholesterol',        'Cholesterol',                  7, $singleNumeric, null,       false, true,  false, true],
            ['triglyceride',       'Triglyceride',                 8, $singleNumeric, null,       false, true,  false, true],
            ['hdl',                'HDL',                          9, $singleNumeric, null,       false, true,  false, true],
            ['ldl',                'LDL',                          10, $singleNumeric, null,      false, true,  false, true],
            ['vldl',               'VLDL',                         11, $singleNumeric, null,      false, true,  false, true],
            ['cpk',                'CPK',                          12, $singleNumeric, null,      false, true,  false, true],
            ['ldh',                'LDH',                          13, $singleNumeric, null,      false, true,  false, true],
            ['amylase',            'Amylase',                      14, $singleNumeric, null,      false, true,  false, true],
            ['bilirubin_total',    'Bilirubin Total',              15, $singleNumeric, null,      false, true,  false, true],
            ['bilirubin_direct',   'Bilirubin Direct',             16, $singleNumeric, null,      false, true,  false, true],
            ['acid_phos',          'Acid Phos',                    17, $singleNumeric, null,      false, true,  false, true],
            ['alk_phos',           'Alk Phos',                     18, $singleNumeric, null,      false, true,  false, true],
            ['albumin',            'Albumin',                      19, $singleNumeric, null,      false, true,  false, true],
            ['globulin',           'Globulin',                     20, $singleNumeric, null,      false, true,  false, true],
            ['others_chemistry',   'Others, Specify........',      21, $singleNumeric, null,      false, true,  false, true],

            ['fluids_header',      'URINE, CSF & OTHER BODY FLUIDS', 22, null,         null,       false, false, true,  false],

            ['urine_total',        'URINE Total',                  23, null,          null,       false, false, false, true],
            ['urine_glucose',      'Glucose',                       24, $urinalysis,   'Glucose',  true,  true,  false, true],
            ['urine_protein',      'Protein',                       25, $urinalysis,   'Protein',  false, true,  false, true],
            ['urine_ketones',      'Ketones',                       26, $urinalysis,   'Ketones',  true,  true,  false, true],

            ['csf_total',          'CSF Total',                    27, null,           null,       false, false, false, false],
            ['csf_glucose',        'Glucose',                       28, $urinalysis,   'Glucose',  true,  true,  false, false],
            ['csf_protein',        'Protein',                       29, $urinalysis,   'Protein',  false, true,  false, false],

            ['body_fluids_total',  'Body fluids Total',            30, null,           null,       false, false, false, false],
            ['body_fluids_glucose','Glucose',                       31, $urinalysis,   'Glucose',  true,  true,  false, false],
            ['body_fluids_protein','Protein',                       32, $urinalysis,   'Protein',  false, true,  false, false],

            ['others_fluids',      'Others, Specify........',      33, null,           null,       false, true,  false, false],
        ];

        foreach ($rows as [$key, $label, $sort, $template, $param, $abnormalAsHigh, $trackLowHigh, $isHeader, $isConfigurable]) {
            DB::table('clinical_chemistry_report_rows')->insert([
                'row_key'                => $key,
                'row_label'              => $label,
                'sort_order'             => $sort,
                'service_ids'            => null,
                'param_name'             => $param,
                'required_template_name' => $template,
                'abnormal_as_high'       => $abnormalAsHigh,
                'track_low_high'         => $trackLowHigh,
                'is_section_header'      => $isHeader,
                'is_configurable'        => $isConfigurable,
                'created_at'             => $now,
                'updated_at'             => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_chemistry_report_rows');
    }
};
