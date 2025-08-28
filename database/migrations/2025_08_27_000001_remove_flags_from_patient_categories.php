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
        Schema::table('patient_categories', function (Blueprint $table) {
            if (Schema::hasColumn('patient_categories', 'is_insurance')) {
                $table->dropColumn('is_insurance');
            }
            if (Schema::hasColumn('patient_categories', 'is_nhif')) {
                $table->dropColumn('is_nhif');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('patient_categories', 'is_insurance')) {
                $table->boolean('is_insurance')->default(false)->after('type');
            }
            if (!Schema::hasColumn('patient_categories', 'is_nhif')) {
                $table->boolean('is_nhif')->default(false)->after('is_insurance');
            }
        });
    }
};
