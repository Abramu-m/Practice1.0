<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_payment_items', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->nullable()->unique();
            $table->foreignId('salary_payment_id')->constrained('salary_payments')->cascadeOnDelete();
            $table->enum('type', ['allowance', 'deduction']);
            $table->string('name', 100);
            $table->decimal('amount', 10, 2);
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_pre_tax')->default(false);
            $table->boolean('is_statutory')->default(false);
            $table->foreignId('source_component_id')->nullable()->constrained('employee_salary_components')->nullOnDelete();
            $table->timestamps();

            $table->index('salary_payment_id', 'salary_payment_items_salary_payment_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payment_items');
    }
};
