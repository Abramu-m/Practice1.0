<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_salary_components', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->nullable()->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('type', ['allowance', 'deduction']);
            $table->string('name', 100);
            $table->enum('calculation_type', ['fixed', 'percentage_of_basic'])->default('fixed');
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_pre_tax')->default(false);
            $table->boolean('is_statutory')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('employee_id', 'employee_salary_components_employee_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_salary_components');
    }
};
