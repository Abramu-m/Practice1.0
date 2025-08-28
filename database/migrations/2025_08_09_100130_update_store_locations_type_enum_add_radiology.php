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
        DB::statement("ALTER TABLE store_locations MODIFY COLUMN type ENUM('store', 'dispensing', 'laboratory', 'nursing', 'radiology') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE store_locations MODIFY COLUMN type ENUM('store', 'dispensing', 'department', 'laboratory', 'nursing', 'other') NOT NULL");
    }
};
