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
        Schema::table('medications', function (Blueprint $table) {
            $table->foreignId('msd_code_id')
                ->nullable()
                ->after('category_id')
                ->constrained('msd_codes')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('msd_code_id');
        });
    }
};
