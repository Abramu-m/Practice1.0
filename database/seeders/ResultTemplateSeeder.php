<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResultTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name'        => 'mRDT Malaria',
                'code'        => 'mrdt_malaria',
                'description' => 'Malaria Rapid Diagnostic Test result with species identification',
                'sort_order'  => 22,
                'is_active'   => 1,
            ],
            [
                'name'        => 'Stool Analysis',
                'code'        => 'stool_analysis',
                'description' => 'Stool analysis — parasites, cellular elements',
                'sort_order'  => 23,
                'is_active'   => 1,
            ],
            [
                'name'        => 'Spermiogram',
                'code'        => 'spermiogram',
                'description' => 'Semen analysis with sperm count, morphology, motility and conclusion',
                'sort_order'  => 24,
                'is_active'   => 1,
            ],
            [
                'name'        => 'Anamnesis for Sterility Patients',
                'code'        => 'sterility_anamnesis',
                'description' => 'Structured reproductive history questionnaire for sterility workup',
                'sort_order'  => 25,
                'is_active'   => 1,
            ],
        ];

        foreach ($templates as $tpl) {
            if (!DB::table('result_templates')->where('code', $tpl['code'])->exists()) {
                $id = DB::table('result_templates')->insertGetId(array_merge($tpl, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));

                // Link mRDT Malaria to medical service id=29
                if ($tpl['code'] === 'mrdt_malaria') {
                    DB::table('medical_services')
                        ->where('id', 29)
                        ->update(['result_template_id' => $id, 'updated_at' => now()]);
                }
            }
        }
    }
}
