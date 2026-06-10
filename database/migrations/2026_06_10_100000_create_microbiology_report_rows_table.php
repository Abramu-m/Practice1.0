<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('microbiology_report_rows', function (Blueprint $table) {
            $table->id();
            $table->string('row_key', 100)->unique();
            $table->string('row_label');
            $table->smallInteger('sort_order')->default(0);
            $table->json('service_ids')->nullable();
            $table->boolean('show_total')->default(true);
            $table->boolean('show_positive')->default(true);
            $table->string('required_template_name', 100)->nullable();
            $table->string('param_name', 100)->nullable();
            $table->string('match_field', 10)->default('status');
            $table->json('match_values')->nullable();
            $table->string('json_path', 200)->nullable();
            $table->json('json_path_values')->nullable();
            $table->boolean('is_bold')->default(false);
            $table->boolean('include_in_grand_total')->default(true);
            $table->boolean('is_configurable')->default(true);
            $table->timestamps();
        });

        $now = now();
        $genxpertTb    = 'TB Examination {GeneXpert MTB/RIF, ZN Stain for AFB}';
        $spermiogram   = 'Spermiogram';
        $wetPrep       = 'Wet Preparation Microscopy';
        $gramStain     = 'Gram Stain Microscopy';
        $singleNumeric = 'Single Numeric Lab Values';

        $defaults = [
            'service_ids'             => null,
            'show_total'              => true,
            'show_positive'           => true,
            'required_template_name'  => null,
            'param_name'              => null,
            'match_field'             => 'status',
            'match_values'            => null,
            'json_path'               => null,
            'json_path_values'        => null,
            'is_bold'                 => false,
            'include_in_grand_total'  => true,
            'is_configurable'         => true,
        ];

        $rows = [
            ['afb_smear', 'SPUTUM FOR AFB SMEAR', 1, [
                'required_template_name' => $genxpertTb,
                'json_path'        => '$.micro_result_A,$.micro_result_B,$.micro_result_C',
                'json_path_values' => ['scanty', '1+', '2+', '3+'],
            ]],
            ['sputum_genexpert', 'Sputum For Genexpert', 2, [
                'required_template_name' => $genxpertTb,
                'json_path'        => '$.xpert_result',
                'json_path_values' => ['positive', 'rr'],
            ]],
            ['sputum_genexpert_mdr', 'Sputum Genexpert - MDR', 3, [
                'show_total'       => false,
                'required_template_name' => $genxpertTb,
                'json_path'        => '$.xpert_result',
                'json_path_values' => ['rr'],
            ]],
            ['seminalysis', 'Seminalysis examination', 4, [
                'show_positive'    => false,
                'required_template_name' => $spermiogram,
            ]],
            ['hvs_smear', 'High Vaginal / Urethral Smear', 5, [
                'show_positive'    => false,
                'required_template_name' => $wetPrep,
            ]],
            ['tv_wet_prep', 'Positive TV by wet preparation', 6, [
                'show_total'       => false,
                'required_template_name' => $wetPrep,
                'param_name'       => 'T. vaginalis',
                'match_values'     => ['abnormal'],
            ]],
            ['candida_wet_prep', 'Positive Candida', 7, [
                'show_total'       => false,
                'required_template_name' => $wetPrep,
                'param_name'       => 'Yeast',
                'match_values'     => ['abnormal'],
            ]],
            ['hvs_gram_stain', 'HVS - GRAM STAIN', 8, [
                'show_positive'    => false,
                'required_template_name' => $gramStain,
                'is_bold'          => true,
            ]],
            ['gram_neg_diplococci', 'Gram Neg diplococci', 9, [
                'show_total'       => false,
                'required_template_name' => $gramStain,
                'param_name'       => 'Interpretation',
                'match_field'      => 'value',
                'match_values'     => ['Gram-negative diplococci'],
            ]],
            ['gram_pos_cocci', 'Gram Pos Cocci', 10, [
                'show_total'       => false,
                'required_template_name' => $gramStain,
                'param_name'       => 'Gram-positive Cocci',
                'match_values'     => ['abnormal', 'critical'],
            ]],
            ['gram_neg_rods', 'Gram Neg Rods', 11, [
                'show_total'       => false,
                'required_template_name' => $gramStain,
                'param_name'       => 'Gram-negative Bacilli',
                'match_values'     => ['abnormal', 'critical'],
            ]],
            ['gram_pos_rods', 'Gram Pos Rods', 12, [
                'show_total'       => false,
                'required_template_name' => $gramStain,
                'param_name'       => 'Gram-positive Bacilli',
                'match_values'     => ['abnormal', 'critical'],
            ]],

            ['csf_examination', 'CSF Examination', 13, [
                'show_positive'    => false,
                'is_bold'          => true,
                'is_configurable'  => false,
            ]],
            ['csf_gnr', 'GNR', 14, ['show_total' => false, 'is_configurable' => false]],
            ['csf_afb_pos', 'Acid Fast bacilli pos', 15, ['show_total' => false, 'is_configurable' => false]],
            ['csf_gpc', 'GPC', 16, ['show_total' => false, 'is_configurable' => false]],
            ['csf_cryptococci', 'Cryptococci pos', 17, ['show_total' => false, 'is_configurable' => false]],

            ['culture_examination', 'CULTURE EXAMINATION', 18, [
                'show_positive'    => false,
                'is_bold'          => true,
                'is_configurable'  => false,
            ]],

            ['blood_culture', 'Blood culture', 19, [
                'show_positive'    => false,
                'is_bold'          => true,
                'is_configurable'  => false,
                'include_in_grand_total' => false,
            ]],
            ['blood_staph', 'Staphylococcus', 20, ['show_total' => false, 'is_configurable' => false]],
            ['blood_strep', 'Streptococci', 21, ['show_total' => false, 'is_configurable' => false]],
            ['blood_ecoli', 'Escherichia coli', 22, ['show_total' => false, 'is_configurable' => false]],
            ['blood_shigella', 'Shigella', 23, ['show_total' => false, 'is_configurable' => false]],
            ['blood_proteus', 'Proteus', 24, ['show_total' => false, 'is_configurable' => false]],
            ['blood_salmonella', 'Salmonella', 25, ['show_total' => false, 'is_configurable' => false]],
            ['blood_other', 'Other:-specify…', 26, ['show_total' => false, 'is_configurable' => false]],

            ['urine_culture', 'Urine culture', 27, [
                'show_positive'    => false,
                'is_bold'          => true,
                'is_configurable'  => false,
                'include_in_grand_total' => false,
            ]],
            ['urine_ecoli', 'E.coli', 28, ['show_total' => false, 'is_configurable' => false]],
            ['urine_klebsiella', 'Klebsiella', 29, ['show_total' => false, 'is_configurable' => false]],
            ['urine_staph', 'Staphylococci', 30, ['show_total' => false, 'is_configurable' => false]],
            ['urine_coliforms', 'Coliforms', 31, ['show_total' => false, 'is_configurable' => false]],
            ['urine_other', 'Other:-specify…', 32, ['show_total' => false, 'is_configurable' => false]],

            ['stool_culture', 'Stool culture', 33, [
                'show_positive'    => false,
                'is_bold'          => true,
                'is_configurable'  => false,
                'include_in_grand_total' => false,
            ]],
            ['stool_rectal_swabs', 'Rectal swabs', 34, [
                'show_positive'    => false,
                'is_configurable'  => false,
                'include_in_grand_total' => false,
            ]],
            ['stool_salmonella', 'Salmonella', 35, ['show_total' => false, 'is_configurable' => false]],
            ['stool_shigella', 'Shigella', 36, ['show_total' => false, 'is_configurable' => false]],
            ['stool_vibrio', 'Vibrio cholerae', 37, ['show_total' => false, 'is_configurable' => false]],
            ['stool_other', 'Other:-specify…', 38, ['show_total' => false, 'is_configurable' => false]],

            ['genital_swab_culture', 'Genital Swabs culture', 39, [
                'show_total'       => false,
                'is_bold'          => true,
                'is_configurable'  => false,
            ]],
            ['genital_candida', 'Candida albicans', 40, ['show_total' => false, 'is_configurable' => false]],
            ['genital_gonorrhoea', 'Neisseria gonorrhoea', 41, ['show_total' => false, 'is_configurable' => false]],

            ['pus_fluids_culture', 'Pus & Body fluids culture', 42, [
                'show_positive'    => false,
                'is_bold'          => true,
                'is_configurable'  => false,
                'include_in_grand_total' => false,
            ]],
            ['pus_proteus', 'Proteus', 43, ['show_total' => false, 'is_configurable' => false]],
            ['pus_klebsiella', 'Klebsiella', 44, ['show_total' => false, 'is_configurable' => false]],
            ['pus_pseudomonas', 'Pseudomonas', 45, ['show_total' => false, 'is_configurable' => false]],
            ['pus_staph', 'Staphylococci', 46, ['show_total' => false, 'is_configurable' => false]],
            ['pus_pneumococcus', 'Pneumococcus', 47, ['show_total' => false, 'is_configurable' => false]],
            ['pus_other', 'Other:-specify…', 48, ['show_total' => false, 'is_configurable' => false]],

            ['sputum_culture', 'Sputum culture', 49, [
                'show_positive'    => false,
                'is_bold'          => true,
                'is_configurable'  => false,
                'include_in_grand_total' => false,
            ]],
            ['sputum_strept_pneumo', 'strept pneumo', 50, ['show_total' => false, 'is_configurable' => false]],
            ['sputum_kleb_pneumo', 'kleb pneumo', 51, ['show_total' => false, 'is_configurable' => false]],
            ['sputum_h_influenzae', 'H. Influenzae', 52, ['show_total' => false, 'is_configurable' => false]],

            ['immunology_hormone', 'IMMUNOLOGY - HORMONE TEST', 53, [
                'show_positive'    => false,
                'is_bold'          => true,
                'is_configurable'  => false,
            ]],
            ['hormone_fsh', 'FSH', 54, [
                'show_positive' => false, 'required_template_name' => $singleNumeric, 'include_in_grand_total' => false,
            ]],
            ['hormone_tsh', 'TSH', 55, [
                'show_positive' => false, 'required_template_name' => $singleNumeric, 'include_in_grand_total' => false,
            ]],
            ['hormone_t3', 'T3', 56, [
                'show_positive' => false, 'required_template_name' => $singleNumeric, 'include_in_grand_total' => false,
            ]],
            ['hormone_t4', 'T4', 57, [
                'show_positive' => false, 'required_template_name' => $singleNumeric, 'include_in_grand_total' => false,
            ]],
            ['hormone_lh', 'LH', 58, [
                'show_positive' => false, 'required_template_name' => $singleNumeric, 'include_in_grand_total' => false,
            ]],
            ['hormone_prolactin', 'Prolactin', 59, [
                'show_positive' => false, 'required_template_name' => $singleNumeric, 'include_in_grand_total' => false,
            ]],
            ['hormone_testosterone', 'Testosterone', 60, [
                'show_positive' => false, 'required_template_name' => $singleNumeric, 'include_in_grand_total' => false,
            ]],
            ['hormone_progesterone', 'Progesterone', 61, [
                'show_positive' => false, 'required_template_name' => $singleNumeric, 'include_in_grand_total' => false,
            ]],

            ['other_specify_final', 'Other:-specify…', 62, [
                'is_configurable' => false,
            ]],
        ];

        foreach ($rows as [$key, $label, $sort, $overrides]) {
            $row = array_merge($defaults, $overrides, [
                'row_key'    => $key,
                'row_label'  => $label,
                'sort_order' => $sort,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach (['service_ids', 'match_values', 'json_path_values'] as $jsonField) {
                if ($row[$jsonField] !== null) {
                    $row[$jsonField] = json_encode($row[$jsonField]);
                }
            }

            DB::table('microbiology_report_rows')->insert($row);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('microbiology_report_rows');
    }
};
