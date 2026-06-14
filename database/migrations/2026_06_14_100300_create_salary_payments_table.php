<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->nullable()->unique();
            $table->string('payment_number', 50)->unique();
            $table->foreignId('employee_id')->constrained('employees');
            $table->unsignedSmallInteger('pay_period_year');
            $table->unsignedTinyInteger('pay_period_month');
            $table->decimal('basic_salary', 10, 2);
            $table->decimal('total_allowances', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2);
            $table->date('payment_date')->nullable();
            $table->string('payment_method', 50)->default('cash');
            $table->string('payment_reference', 100)->nullable();
            $table->enum('status', ['draft', 'approved', 'paid', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('financial_transaction_id')->nullable()->constrained('financial_transactions')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'pay_period_year', 'pay_period_month'], 'salary_payments_employee_period_unique');
            $table->index(['pay_period_year', 'pay_period_month'], 'salary_payments_period_index');
            $table->index('status', 'salary_payments_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
