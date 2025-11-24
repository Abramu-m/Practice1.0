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
        Schema::create('cds_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_type_id')->constrained('cds_rule_types')->onDelete('cascade');
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->tinyInteger('priority')->unsigned()->default(5)->comment('1=Low, 5=Medium, 10=Critical');
            $table->enum('severity', ['info', 'warning', 'critical'])->default('warning');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['is_active', 'priority']);
            $table->index(['rule_type_id']);
            $table->index(['deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cds_rules');
    }
};
