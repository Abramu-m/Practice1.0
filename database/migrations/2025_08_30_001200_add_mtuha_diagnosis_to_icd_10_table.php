<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('icd_10') && ! Schema::hasColumn('icd_10', 'mtuha_diagnosis')) {
            // Use raw SQL to add the column and position it after `subcategory`.
            DB::statement("ALTER TABLE `icd_10` ADD COLUMN `mtuha_diagnosis` INT NULL AFTER `subcategory`");

            // Add foreign key constraint pointing to mtuha_diagnoses(id) if the table exists.
            if (Schema::hasTable('mtuha_diagnoses')) {
                try {
                    DB::statement("ALTER TABLE `icd_10` ADD CONSTRAINT `icd10_mtuha_diagnosis_fk` FOREIGN KEY (`mtuha_diagnosis`) REFERENCES `mtuha_diagnoses`(`id`) ON DELETE SET NULL ON UPDATE CASCADE");
                } catch (\Exception $e) {
                    // ignore if FK already exists or cannot be added
                }
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('icd_10') && Schema::hasColumn('icd_10', 'mtuha_diagnosis')) {
            // Drop FK if exists, then drop the column
            try {
                DB::statement("ALTER TABLE `icd_10` DROP FOREIGN KEY `icd10_mtuha_diagnosis_fk`");
            } catch (\Exception $e) {
                // ignore if FK doesn't exist
            }

            DB::statement("ALTER TABLE `icd_10` DROP COLUMN `mtuha_diagnosis`");
        }
    }
};
