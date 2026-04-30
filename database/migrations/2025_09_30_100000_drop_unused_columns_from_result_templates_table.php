<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('result_templates', function (Blueprint $table) {
            if (Schema::hasColumn('result_templates', 'service_category_id')) {
                $table->dropForeign(['service_category_id']);
                $table->dropIndex('result_templates_service_category_id_is_active_index');
                $table->dropColumn('service_category_id');
            }

            if (Schema::hasColumn('result_templates', 'investigation_type')) {
                $table->dropColumn('investigation_type');
            }

            if (Schema::hasColumn('result_templates', 'template_fields')) {
                $table->dropColumn('template_fields');
            }
        });
    }

    public function down(): void
    {
        Schema::table('result_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('service_category_id')->nullable()->comment('Link to service category for filtering');
            $table->string('investigation_type')->nullable()->comment('Type of investigation this template is for');
            $table->text('template_fields')->nullable()->comment('JSON structure of template fields');

            $table->index(['service_category_id', 'is_active'], 'result_templates_service_category_id_is_active_index');
            $table->foreign('service_category_id')
                ->references('id')->on('service_categories')
                ->onDelete('set null');
        });
    }
};
