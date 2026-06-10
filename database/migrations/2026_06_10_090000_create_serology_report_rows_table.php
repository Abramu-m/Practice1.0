<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serology_report_rows', function (Blueprint $table) {
            $table->id();
            $table->string('row_key', 100)->unique();
            $table->string('row_label');
            $table->smallInteger('sort_order')->default(0);
            $table->json('service_ids')->nullable();
            $table->string('required_template_name', 100)->nullable();
            $table->json('positive_statuses')->nullable();
            $table->string('cd4_filter', 20)->nullable();
            $table->boolean('is_configurable')->default(true);
            $table->timestamps();
        });

        $now = now();
        $qualitative   = 'Qualitative Positive Negative';
        $singleNumeric = 'Single Numeric Lab Values';
        $psaTemplate   = 'PSA Semi-quantitative';

        $rows = [
            ['vdrl',             'Syphilis test {VDRL}',                                              1,  $qualitative,   ['abnormal'],            null,       true],
            ['acr_urine',        'ACR (Albumin, Creatinine, Albumine/Creatinine ratio) in urine',     2,  null,           null,                    null,       false],
            ['rheumatoid_factor','Rheumatoid Factor',                                                 3,  $qualitative,   ['abnormal'],            null,       true],
            ['hba1c_control',    'HbA1c Control',                                                     4,  null,           null,                    null,       false],
            ['hepatitis_b',      'Hepatitis B Antigen test',                                          5,  $qualitative,   ['abnormal'],            null,       true],
            ['hepatitis_c',      'Hepatitis C Antigen test',                                          6,  $qualitative,   ['abnormal'],            null,       true],
            ['acr_control',      'ACR Control',                                                       7,  null,           null,                    null,       false],
            ['upt',              'Urine Pregnancy Test {UPT}',                                        8,  $qualitative,   ['abnormal'],            null,       true],
            ['cd4_total',        'CD4',                                                               9,  $singleNumeric, null,                    null,       true],
            ['cd4_gt_200',       'CD4 higher than 200',                                              10,  $singleNumeric, null,                    'gt_200',   true],
            ['cd4_lte_200',      'CD4',                                                               11,  $singleNumeric, null,                    'lte_200',  true],
            ['widal',            'Widal Test',                                                       12,  $qualitative,   ['abnormal'],            null,       true],
            ['smear_gram_stain', 'Smear for gram stain',                                             13,  null,           null,                    null,       false],
            ['smear_wet_prep',   'Smear for wet preparation (microscopy)',                           14,  null,           null,                    null,       false],
            ['psa_semiquant',    'Prostate Specific Antigen {PSA}, semiquantitative',                15,  $psaTemplate,   ['abnormal', 'critical'], null,      true],
            ['h_pylori',         'H. pylori, Rapid (Serum Antigen)',                                 16,  $qualitative,   ['abnormal'],            null,       true],
            ['schellong',        'Schellong test',                                                   17,  null,           null,                    null,       false],
            ['rectoscopy',       'Rectoscopy',                                                       18,  null,           null,                    null,       false],
            ['asot',             'Anti Streptolysin O Titer {ASOT}',                                 19,  $qualitative,   ['abnormal'],            null,       true],
        ];

        foreach ($rows as [$key, $label, $sort, $template, $positiveStatuses, $cd4Filter, $isConfigurable]) {
            DB::table('serology_report_rows')->insert([
                'row_key'                => $key,
                'row_label'              => $label,
                'sort_order'             => $sort,
                'service_ids'            => null,
                'required_template_name' => $template,
                'positive_statuses'      => $positiveStatuses ? json_encode($positiveStatuses) : null,
                'cd4_filter'             => $cd4Filter,
                'is_configurable'        => $isConfigurable,
                'created_at'             => $now,
                'updated_at'             => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('serology_report_rows');
    }
};
