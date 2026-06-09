<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_transfusion_report_rows', function (Blueprint $table) {
            $table->id();
            $table->string('row_key', 100)->unique();
            $table->string('row_label');
            $table->tinyInteger('sort_order');
            $table->json('service_ids')->nullable();
            $table->timestamps();
        });

        DB::table('blood_transfusion_report_rows')->insert([
            ['row_key' => 'blood_grouping_rh_crossmatch',    'row_label' => 'Blood grouping, RH typing & Crossmatching',                              'sort_order' => 1,  'service_ids' => null, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'coombs_test',                     'row_label' => "COOMB'S Test",                                                            'sort_order' => 2,  'service_ids' => null, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'cross_matches',                   'row_label' => 'Cross Matches',                                                           'sort_order' => 3,  'service_ids' => null, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'blood_units_collected',           'row_label' => 'Blood units Collected',                                                   'sort_order' => 4,  'service_ids' => null, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'blood_units_transfused',          'row_label' => 'Blood units Transfused',                                                  'sort_order' => 5,  'service_ids' => null, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'transfused_female_complications', 'row_label' => 'Blood units transfused to Female with complications',                     'sort_order' => 6,  'service_ids' => null, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'transfused_hiv_complications',    'row_label' => 'Blood units transfused to HIV related complications',                     'sort_order' => 7,  'service_ids' => null, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'transfused_under_five',           'row_label' => 'Blood transfused to under five patients',                                 'sort_order' => 8,  'service_ids' => null, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'transfused_surgical_accidents',   'row_label' => 'Blood units transfused to Surgical, Accidents Patients and other reason', 'sort_order' => 9,  'service_ids' => null, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'pilot_samples_zonal_bts',         'row_label' => 'Pilot Samples send to Zonal BTS for screening of TTIs',                  'sort_order' => 10, 'service_ids' => null, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'positive_hep_b',                  'row_label' => 'Blood units tested positive for Hepatitis B Viruses',                    'sort_order' => 11, 'service_ids' => null, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'positive_hep_c',                  'row_label' => 'Blood units tested positive for Hepatitis C Viruses',                    'sort_order' => 12, 'service_ids' => null, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'positive_hiv',                    'row_label' => 'Blood units tested positive for HIV Viruses',                            'sort_order' => 13, 'service_ids' => null, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'positive_syphilis',               'row_label' => 'Blood units tested positive for Syphilis',                               'sort_order' => 14, 'service_ids' => null, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'screened_safe_units',             'row_label' => 'Screened / Safe blood units available',                                  'sort_order' => 15, 'service_ids' => null, 'created_at' => now(), 'updated_at' => now()],
            ['row_key' => 'unscreened_unsafe_units',         'row_label' => 'Unscreened / unsafe blood units available',                              'sort_order' => 16, 'service_ids' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_transfusion_report_rows');
    }
};
