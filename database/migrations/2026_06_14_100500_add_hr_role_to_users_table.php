<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user','admin','doctor','nurse','receptionist','cashier','pharmacist','lab_technician','radiologist','super_admin','hr') NOT NULL DEFAULT 'user'");
    }

    public function down(): void
    {
        DB::statement("UPDATE users SET role = 'user' WHERE role = 'hr'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user','admin','doctor','nurse','receptionist','cashier','pharmacist','lab_technician','radiologist','super_admin') NOT NULL DEFAULT 'user'");
    }
};
