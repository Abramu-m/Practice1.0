<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categoryId = DB::table('cds_rule_categories')->where('name', 'lab_diagnostics')->value('id');

        if (!$categoryId) {
            $categoryId = DB::table('cds_rule_categories')->insertGetId([
                'name' => 'lab_diagnostics',
                'display_name' => 'Diagnostics & Lab Safety',
                'description' => 'Rules that check lab/investigation results against critical thresholds.',
                'is_active' => 1,
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $exists = DB::table('cds_rule_types')
            ->where('category_id', $categoryId)
            ->where('name', 'lab_critical')
            ->exists();

        if (!$exists) {
            DB::table('cds_rule_types')->insert([
                'category_id' => $categoryId,
                'name' => 'lab_critical',
                'display_name' => 'Lab Critical Value',
                'description' => 'Alerts when a specific lab result parameter crosses a configured critical threshold.',
                'handler_class' => \App\Services\CDS\Rules\LabCriticalValueRule::class,
                'is_active' => 1,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $categoryId = DB::table('cds_rule_categories')->where('name', 'lab_diagnostics')->value('id');

        if ($categoryId) {
            DB::table('cds_rule_types')->where('category_id', $categoryId)->where('name', 'lab_critical')->delete();

            if (!DB::table('cds_rule_types')->where('category_id', $categoryId)->exists()) {
                DB::table('cds_rule_categories')->where('id', $categoryId)->delete();
            }
        }
    }
};
