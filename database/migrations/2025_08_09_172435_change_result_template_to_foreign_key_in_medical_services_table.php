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
        // First, let's map existing enum values to result_templates table IDs
        $enumToIdMapping = [
            'simple_procedure' => 1,
            'vital_observations' => 2,
            'complex_form' => 3,
            'imaging' => 4,
            'general_procedure' => 5,
            'simple_lab' => 6,
            'cd4' => 7,
            'tb' => 8,
            'general_lab' => 9
        ];

        // Add new column for result_template_id
        Schema::table('medical_services', function (Blueprint $table) {
            $table->unsignedBigInteger('result_template_id')->nullable()->after('result_template');
            $table->foreign('result_template_id')->references('id')->on('result_templates')->onDelete('set null');
        });

        // Update existing records to use the new foreign key
        foreach ($enumToIdMapping as $enumValue => $templateId) {
            DB::table('medical_services')
                ->where('result_template', $enumValue)
                ->update(['result_template_id' => $templateId]);
        }

        // Drop the old enum column
        Schema::table('medical_services', function (Blueprint $table) {
            $table->dropColumn('result_template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse mapping
        $idToEnumMapping = [
            1 => 'simple_procedure',
            2 => 'vital_observations', 
            3 => 'complex_form',
            4 => 'imaging',
            5 => 'general_procedure',
            6 => 'simple_lab',
            7 => 'cd4',
            8 => 'tb',
            9 => 'general_lab'
        ];

        // Add back the enum column
        Schema::table('medical_services', function (Blueprint $table) {
            $table->enum('result_template', [
                'simple_procedure', 'vital_observations', 'complex_form', 
                'imaging', 'general_procedure', 'simple_lab', 'cd4', 'tb', 'general_lab'
            ])->default('general_procedure')->after('result_template_id');
        });

        // Update records back to enum values
        foreach ($idToEnumMapping as $templateId => $enumValue) {
            DB::table('medical_services')
                ->where('result_template_id', $templateId)
                ->update(['result_template' => $enumValue]);
        }

        // Drop the foreign key constraint and column
        Schema::table('medical_services', function (Blueprint $table) {
            $table->dropForeign(['result_template_id']);
            $table->dropColumn('result_template_id');
        });
    }
};
