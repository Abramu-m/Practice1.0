<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $template = DB::table('result_templates')->where('code', 'cd4')->value('id');
        if (!$template) return;

        DB::table('medical_services')
            ->whereRaw('LOWER(name) LIKE ?', ['%cd4%'])
            ->whereNull('result_template_id')
            ->update(['result_template_id' => $template]);
    }

    public function down(): void
    {
        $template = DB::table('result_templates')->where('code', 'cd4')->value('id');
        if (!$template) return;

        DB::table('medical_services')
            ->where('result_template_id', $template)
            ->whereRaw('LOWER(name) LIKE ?', ['%cd4%'])
            ->update(['result_template_id' => null]);
    }
};
