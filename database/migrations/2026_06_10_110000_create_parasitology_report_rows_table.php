<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parasitology_report_rows', function (Blueprint $table) {
            $table->id();
            $table->string('row_key', 100)->unique();
            $table->string('row_label');
            $table->smallInteger('sort_order')->default(0);
            $table->json('service_ids')->nullable();
            $table->string('param_name', 100)->nullable();
            $table->string('required_template_name', 100)->nullable();
            $table->json('positive_statuses')->nullable();
            $table->boolean('shows_total')->default(true);
            $table->boolean('shows_positive')->default(true);
            $table->boolean('is_section_header')->default(false);
            $table->boolean('is_configurable')->default(true);
            $table->timestamps();
        });

        $now = now();

        $mrdt       = 'mRDT Malaria';
        $pbs        = 'PBS – Malaria Parasites';
        $qualitative= 'Qualitative Positive Negative';
        $urinalysis = 'Urinalysis';
        $stool      = 'Stool Analysis';
        $abnormal   = ['abnormal'];

        // [row_key, row_label, sort, service_ids, param_name, required_template_name, positive_statuses, shows_total, shows_positive, is_section_header, is_configurable]
        $rows = [
            ['section_blood',            'A:- Blood',                1,  null,   null,                       null,        null,      false, false, true,  false],
            ['mrdt',                      'MRDT',                     2,  [29],   null,                       $mrdt,       $abnormal, true,  true,  false, true],
            ['blood_slides',              'BLOOD SLIDES',             3,  [28],   'Malaria Parasites',        $pbs,        $abnormal, true,  true,  false, true],
            ['malaria_pos',               'Malaria pos',              4,  [28],   'Malaria Parasites',        $pbs,        $abnormal, false, true,  false, true],
            ['borrelia_pos',              'Borrelia pos',             5,  null,   null,                       null,        null,      false, true,  false, false],
            ['microfilaria_pos',          'Microfilaria pos',         6,  [8],    null,                       $qualitative,$abnormal, false, true,  false, true],
            ['trypanosome_pos',           'Trypanosome pos',          7,  null,   null,                       null,        null,      false, true,  false, false],

            ['section_urine',             'B:- URINE',                8,  null,   null,                       null,        null,      false, false, true,  false],
            ['urine_examination',         'Urine Examination',        9,  [42],   null,                       null,        null,      true,  false, false, true],
            ['schistosoma_haem_pos',      'Schistosoma haem pos',    10,  null,   null,                       null,        null,      false, true,  false, false],
            ['trichomonas_pos_urine',     'Trichomonas pos',         11,  null,   null,                       null,        null,      false, true,  false, false],
            ['yeast_cells_pos',           'Yeast cells pos',         12,  [42],   'Yeast',                    $urinalysis, $abnormal, false, true,  false, true],

            ['section_stool',             'C:- STOOL ANALYSIS',      13,  null,   null,                       null,        null,      false, false, true,  false],
            ['stool_examination',         'Stool Examination',       14,  [39],   null,                       null,        null,      true,  false, false, true],
            ['entamoeba_histolytica_pos', 'Entamoeba histolytica pos',15, [39],   'Amoeba cysts',             $stool,      $abnormal, false, true,  false, true],
            ['ascaris_pos',               'Ascaris pos',             16,  [39],   'Ascaris lumbricoides',     $stool,      $abnormal, false, true,  false, true],
            ['giardia_pos',               'Giardia pos',             17,  [39],   'Giardia lamblia',          $stool,      $abnormal, false, true,  false, true],
            ['hookworm_pos',              'Hookworm pos',            18,  [39],   'Hookworms',                $stool,      $abnormal, false, true,  false, true],
            ['schistosoma_mans_pos',      'Schistosoma mans pos',    19,  [39],   'Schistosomiasis mansoni',  $stool,      $abnormal, false, true,  false, true],
            ['trichuris_pos',             'Trichuris pos',           20,  [39],   'Trichuris trichiura',      $stool,      $abnormal, false, true,  false, true],
            ['enterobius_pos',            'Enterobius pos',          21,  [39],   'Enterobius vermicularis',  $stool,      $abnormal, false, true,  false, true],
            ['strongyloides_pos',         'Strongyloides pos',       22,  [39],   'Strongyloides',            $stool,      $abnormal, false, true,  false, true],
            ['taenia_pos',                'Taenia pos',              23,  [39],   'Taenia solium',            $stool,      $abnormal, false, true,  false, true],
            ['trichomonas_pos_stool',     'Trichomonas pos',         24,  [39],   'Trichomonas hominis',      $stool,      $abnormal, false, true,  false, true],
        ];

        foreach ($rows as [$key, $label, $sort, $serviceIds, $paramName, $template, $positiveStatuses, $showsTotal, $showsPositive, $isHeader, $isConfigurable]) {
            DB::table('parasitology_report_rows')->insert([
                'row_key'                => $key,
                'row_label'              => $label,
                'sort_order'             => $sort,
                'service_ids'            => $serviceIds ? json_encode($serviceIds) : null,
                'param_name'             => $paramName,
                'required_template_name' => $template,
                'positive_statuses'      => $positiveStatuses ? json_encode($positiveStatuses) : null,
                'shows_total'            => $showsTotal,
                'shows_positive'         => $showsPositive,
                'is_section_header'      => $isHeader,
                'is_configurable'        => $isConfigurable,
                'created_at'             => $now,
                'updated_at'             => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('parasitology_report_rows');
    }
};
