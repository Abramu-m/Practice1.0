<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('investigation_forms')->insert([
            'name' => 'Indication Form',
            'description' => 'Simple clinical indication form used by Procedures and Specialized Investigations to capture the reason for the request.',
            'blade_view' => 'indication',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('medical_services')
            ->whereIn('service_category_id', [2, 3])
            ->update([
                'requires_form' => 1,
                'form_type' => 'indication',
            ]);
    }

    public function down(): void
    {
        DB::table('medical_services')
            ->whereIn('service_category_id', [2, 3])
            ->update([
                'requires_form' => 0,
                'form_type' => null,
            ]);

        // X-Ray previously used the "general" (Outsourcing) form.
        DB::table('medical_services')
            ->where('id', 82)
            ->update([
                'requires_form' => 1,
                'form_type' => 'general',
            ]);

        DB::table('investigation_forms')->where('blade_view', 'indication')->delete();
    }
};
