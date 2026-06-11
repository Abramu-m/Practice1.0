<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('result_templates')->where('code', 'general')->exists()) {
            return;
        }

        DB::table('result_templates')->insert([
            'name'        => 'General / Free Text Result',
            'code'        => 'general',
            'description' => 'Generic free-text result entry for investigations without a specialized template',
            'sort_order'  => 1,
            'is_active'   => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('result_templates')->where('code', 'general')->delete();
    }
};
