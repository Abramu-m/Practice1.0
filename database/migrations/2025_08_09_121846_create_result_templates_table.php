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
        Schema::create('result_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Display name for the template');
            $table->string('code')->unique()->comment('Unique code matching enum values');
            $table->string('description')->nullable()->comment('Description of the template');
            $table->unsignedBigInteger('service_category_id')->nullable()->comment('Link to service category for filtering');
            $table->string('investigation_type')->nullable()->comment('Type of investigation this template is for');
            $table->text('template_fields')->nullable()->comment('JSON structure of template fields');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('service_category_id')->references('id')->on('service_categories')->onDelete('set null');
            
            // Index for performance
            $table->index(['service_category_id', 'is_active']);
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_templates');
    }
};
