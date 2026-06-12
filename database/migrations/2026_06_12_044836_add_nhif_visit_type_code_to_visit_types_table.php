<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('visit_types', function (Blueprint $table) {
            $table->unsignedTinyInteger('nhif_visit_type_code')->nullable()->after('description');
        });

        // Backfill the NHIF AuthorizeCard VisitTypeID codes for the existing
        // visit types (1=Normal, 2=Emergency, 3=Referral, 4=Follow up).
        DB::table('visit_types')->where('description', 'Normal Visit')->update(['nhif_visit_type_code' => 1]);
        DB::table('visit_types')->where('description', 'Emergency')->update(['nhif_visit_type_code' => 2]);
        DB::table('visit_types')->where('description', 'Referral')->update(['nhif_visit_type_code' => 3]);
        DB::table('visit_types')->where('description', 'Follow Up')->update(['nhif_visit_type_code' => 4]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visit_types', function (Blueprint $table) {
            $table->dropColumn('nhif_visit_type_code');
        });
    }
};
