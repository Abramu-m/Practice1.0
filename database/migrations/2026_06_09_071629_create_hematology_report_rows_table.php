<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hematology_report_rows', function (Blueprint $table) {
            $table->id();
            $table->string('row_key', 100)->unique();
            $table->string('row_label');
            $table->tinyInteger('sort_order')->default(0);
            $table->json('service_ids')->nullable();
            $table->string('fbp_param_name', 100)->nullable();
            $table->boolean('track_low_high')->default(false);
            $table->boolean('is_section_header')->default(false);
            $table->timestamps();
        });

        DB::table('hematology_report_rows')->insert([
            ['row_key' => 'haematology_header',   'row_label' => 'Haematology',           'sort_order' => 1,  'service_ids' => null, 'fbp_param_name' => null,                       'track_low_high' => 0, 'is_section_header' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'haemoglobin',           'row_label' => 'Haemoglobin',           'sort_order' => 2,  'service_ids' => null, 'fbp_param_name' => null,                       'track_low_high' => 1, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'fbp',                   'row_label' => 'FBP',                   'sort_order' => 3,  'service_ids' => null, 'fbp_param_name' => null,                       'track_low_high' => 0, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'wbc_count',             'row_label' => 'WBC COUNT',             'sort_order' => 4,  'service_ids' => null, 'fbp_param_name' => 'Total WBC',                'track_low_high' => 1, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'wbc_diff',              'row_label' => 'WBC DIFF',              'sort_order' => 5,  'service_ids' => null, 'fbp_param_name' => null,                       'track_low_high' => 0, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'platelets',             'row_label' => 'Platelets',             'sort_order' => 6,  'service_ids' => null, 'fbp_param_name' => 'Platelet Count',           'track_low_high' => 1, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'reticulocytes',         'row_label' => 'Reticulocytes count',   'sort_order' => 7,  'service_ids' => null, 'fbp_param_name' => 'Reticulocytes',            'track_low_high' => 1, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'peripheral_blood_film', 'row_label' => 'Peripheral Blood film', 'sort_order' => 8,  'service_ids' => null, 'fbp_param_name' => null,                       'track_low_high' => 0, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'pcv',                   'row_label' => 'PCV',                   'sort_order' => 9,  'service_ids' => null, 'fbp_param_name' => 'Haematocrit (HCT / PCV)',  'track_low_high' => 1, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'rbc_count',             'row_label' => 'RBC Count',             'sort_order' => 10, 'service_ids' => null, 'fbp_param_name' => 'RBC Count',                'track_low_high' => 1, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'sickling_test',         'row_label' => 'Sickling test',         'sort_order' => 11, 'service_ids' => null, 'fbp_param_name' => null,                       'track_low_high' => 0, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'sickling_test_pos',     'row_label' => 'Sickling test pos',     'sort_order' => 12, 'service_ids' => null, 'fbp_param_name' => null,                       'track_low_high' => 0, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'hb_electrophoresis',    'row_label' => 'HB electrophoresis',    'sort_order' => 13, 'service_ids' => null, 'fbp_param_name' => null,                       'track_low_high' => 0, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'clotting_time',         'row_label' => 'Clotting Time',         'sort_order' => 14, 'service_ids' => null, 'fbp_param_name' => null,                       'track_low_high' => 0, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'bleeding_time',         'row_label' => 'Bleeding Time',         'sort_order' => 15, 'service_ids' => null, 'fbp_param_name' => null,                       'track_low_high' => 0, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'prothrombin_time',      'row_label' => 'Prothrombin time -INR', 'sort_order' => 16, 'service_ids' => null, 'fbp_param_name' => null,                       'track_low_high' => 0, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'gcpd',                  'row_label' => 'GCPD',                  'sort_order' => 17, 'service_ids' => null, 'fbp_param_name' => null,                       'track_low_high' => 0, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'le_cell',               'row_label' => 'LE cell',               'sort_order' => 18, 'service_ids' => null, 'fbp_param_name' => null,                       'track_low_high' => 0, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'ptt',                   'row_label' => 'PTT',                   'sort_order' => 19, 'service_ids' => null, 'fbp_param_name' => null,                       'track_low_high' => 0, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'esr',                   'row_label' => 'ESR',                   'sort_order' => 20, 'service_ids' => null, 'fbp_param_name' => null,                       'track_low_high' => 1, 'is_section_header' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hematology_report_rows');
    }
};
