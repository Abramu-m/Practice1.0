<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investigations', function (Blueprint $table) {
            $table->index(['medical_service_id', 'ordered_at'], 'investigations_medical_service_id_ordered_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('investigations', function (Blueprint $table) {
            $table->dropIndex('investigations_medical_service_id_ordered_at_index');
        });
    }
};
