<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->index(['medication_id', 'dispensed_at'], 'prescriptions_medication_id_dispensed_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropIndex('prescriptions_medication_id_dispensed_at_index');
        });
    }
};
