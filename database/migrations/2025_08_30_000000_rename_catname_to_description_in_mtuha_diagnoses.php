<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('mtuha_diagnoses') && Schema::hasColumn('mtuha_diagnoses', 'catname')) {
            // Use raw SQL to avoid requiring doctrine/dbal for renameColumn
            DB::statement("ALTER TABLE `mtuha_diagnoses` CHANGE `catname` `description` varchar(100) NOT NULL");
        }
    }

    public function down()
    {
        if (Schema::hasTable('mtuha_diagnoses') && Schema::hasColumn('mtuha_diagnoses', 'description')) {
            DB::statement("ALTER TABLE `mtuha_diagnoses` CHANGE `description` `catname` varchar(100) NOT NULL");
        }
    }
};
