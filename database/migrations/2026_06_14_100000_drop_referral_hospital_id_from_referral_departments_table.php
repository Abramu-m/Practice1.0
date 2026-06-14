<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referral_departments', function (Blueprint $table) {
            $table->dropForeign('referral_departments_referral_hospital_id_foreign');
            $table->dropColumn('referral_hospital_id');
        });
    }

    public function down(): void
    {
        Schema::table('referral_departments', function (Blueprint $table) {
            $table->foreignId('referral_hospital_id')->after('uuid')->nullable()->constrained('referral_hospitals')->cascadeOnDelete();
        });
    }
};
