<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->nullable()->unique();
            $table->string('employee_number', 50)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('job_title')->nullable();
            $table->string('department')->nullable();
            $table->enum('employment_type', ['permanent', 'contract', 'casual', 'volunteer'])->default('permanent');
            $table->date('date_joined')->nullable();
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('tin_number')->nullable();
            $table->string('nssf_number')->nullable();
            $table->string('payment_method', 50)->default('cash');
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('status', 'employees_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
