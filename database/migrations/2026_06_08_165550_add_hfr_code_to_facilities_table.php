<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->string('hfr_code', 50)->nullable()->after('nhif_facility_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn('hfr_code');
        });
    }
};
