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
        // Update the role enum to include 'radiologist'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user','admin','doctor','nurse','receptionist','cashier','pharmacist','lab_technician','radiologist','super_admin') NOT NULL DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'radiologist' from the role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user','admin','doctor','nurse','receptionist','cashier','pharmacist','lab_technician','super_admin') NOT NULL DEFAULT 'user'");
    }
};
