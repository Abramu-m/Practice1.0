<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChapterToIcd10Table extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a nullable `chapter` column to the `icd_10` table.
     */
    public function up()
    {
        if (!Schema::hasTable('icd_10')) {
            return;
        }

        Schema::table('icd_10', function (Blueprint $table) {
            if (!Schema::hasColumn('icd_10', 'chapter')) {
                $table->string('chapter')->nullable()->after('category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (!Schema::hasTable('icd_10')) {
            return;
        }

        Schema::table('icd_10', function (Blueprint $table) {
            if (Schema::hasColumn('icd_10', 'chapter')) {
                $table->dropColumn('chapter');
            }
        });
    }
}
