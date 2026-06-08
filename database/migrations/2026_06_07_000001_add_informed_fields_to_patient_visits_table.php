<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_visits', function (Blueprint $table) {
            $table->timestamp('informed_at')->nullable()->after('resulted_at');
            $table->unsignedBigInteger('informed_by')->nullable()->after('informed_at');
            $table->foreign('informed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('patient_visits', function (Blueprint $table) {
            $table->dropForeign(['informed_by']);
            $table->dropColumn(['informed_at', 'informed_by']);
        });
    }
};
