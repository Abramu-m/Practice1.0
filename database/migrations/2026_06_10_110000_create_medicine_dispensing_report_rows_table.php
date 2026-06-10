<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_dispensing_report_rows', function (Blueprint $table) {
            $table->id();
            $table->string('row_key', 100)->unique();
            $table->string('group_key', 100);
            $table->string('row_no', 10)->nullable();
            $table->unsignedTinyInteger('row_no_rowspan')->default(1);
            $table->string('drug_label')->nullable();
            $table->unsignedTinyInteger('drug_rowspan')->default(1);
            $table->string('unit_label', 50);
            $table->foreignId('medication_id')->nullable()->constrained('medications')->nullOnDelete();
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();

        // Best-guess medication matches against the existing catalogue.
        // Rows left with medication_id = null have no clear catalogue match
        // and can be configured later via the report's "Configure Mapping" page.
        $rows = [
            ['alu_1x6',           'alu',         '1',     4, 'ALu ya 1x6', 1, 'Vidonge',      536, 10],
            ['alu_2x6',           'alu',         null,    0, 'ALu ya 2x6', 1, 'Vidonge',      535, 20],
            ['alu_3x6',           'alu',         null,    0, 'ALu ya 3x6', 1, 'Vidonge',      534, 30],
            ['alu_4x6',           'alu',         null,    0, 'ALu ya 4x6', 1, 'Vidonge',      533, 40],
            ['cotrimoxazole',     'cotri',       '2',     1, 'Cotrimoxazole ya maji', 1, 'Chupa', 201, 50],
            ['amoxicillin_dt_10', 'amox10',      '3(a)',  1, 'Amoxicillin DT (250mg) x 10', 1, 'Strip', 757, 60],
            ['amoxicillin_dt_5',  'amox5',       '3(b)',  1, 'Amoxicillin DT (250mg) x 5', 1, 'Strip', null, 70],
            ['ors',               'ors',         '4',     1, 'ORS', 1, 'Sachet',          285, 80],
            ['zinc_sulphate',     'zinc',        '5',     1, 'Zinc sulphate', 1, 'Vidonge', 417, 90],
            ['mebendazole_100',   'mebendazole', '6',     2, 'Mebendazole', 2, 'Vidonge 100mg', 653, 100],
            ['mebendazole_500',   'mebendazole', null,    0, null, 0, 'Vidonge 500mg', null, 110],
            ['albendazole_200',   'albendazole', '7',     2, 'Albendazole', 2, 'Vidonge 200mg', 780, 120],
            ['albendazole_400',   'albendazole', null,    0, null, 0, 'Vidonge 400mg', 3, 130],
            ['fefo',              'fefo',        '8',     1, 'FEFO', 1, 'Vidonge',         62, 140],
            ['folic_acid',        'folic',       '9',     1, 'Folic Acid', 1, 'Vidonge',    64, 150],
            ['tle',               'tle',         '10',    1, 'TLE', 1, 'Vidonge',          618, 160],
            ['oxytocin',          'oxytocin',    '11',    1, 'Oxytocin', 1, 'Sindano',      399, 170],
            ['depoprovera',       'depoprovera', '12',    1, 'Depoprovera', 1, 'Sindano',   null, 180],
            ['sp',                'sp',          '13',    1, 'SP', 1, 'Vidonge',           140, 190],
            ['magnesium_sulphate','magnesium',   '14',    1, 'Magnesium Sulphate', 1, 'Sindano', null, 200],
            ['rhz',               'rhz',         '15',    1, 'RHZ Rifampicin 150mg/isoniazide 75mg/pyrazinamide 150mg/isoniazide', 1, 'Vidonge', 676, 210],
            ['dha_pip',           'dha_pip',     '16',    1, 'Dihydroartemisinin-Piperaquine', 1, 'Vidonge', 803, 220],
        ];

        foreach ($rows as [$rowKey, $groupKey, $rowNo, $rowNoSpan, $drugLabel, $drugSpan, $unitLabel, $medicationId, $sort]) {
            DB::table('medicine_dispensing_report_rows')->insert([
                'row_key'        => $rowKey,
                'group_key'      => $groupKey,
                'row_no'         => $rowNo,
                'row_no_rowspan' => $rowNoSpan,
                'drug_label'     => $drugLabel,
                'drug_rowspan'   => $drugSpan,
                'unit_label'     => $unitLabel,
                'medication_id'  => $medicationId,
                'sort_order'     => $sort,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_dispensing_report_rows');
    }
};
