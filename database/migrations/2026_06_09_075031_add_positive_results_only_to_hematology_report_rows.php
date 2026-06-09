<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hematology_report_rows', function (Blueprint $table) {
            $table->boolean('positive_results_only')->default(false)->after('required_template_name');
        });

        DB::table('hematology_report_rows')
            ->where('row_key', 'sickling_test_pos')
            ->update(['positive_results_only' => true]);
    }

    public function down(): void
    {
        Schema::table('hematology_report_rows', function (Blueprint $table) {
            $table->dropColumn('positive_results_only');
        });
    }
};
