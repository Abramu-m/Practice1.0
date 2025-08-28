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
        // Add indexes only if they don't already exist
        $existing1 = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM nhif_tariffs WHERE Key_name = ?", ['nhif_tariffs_item_code_index']);
        if (empty($existing1)) {
            Schema::table('nhif_tariffs', function (Blueprint $table) {
                $table->index('item_code');
            });
        }

        $existing2 = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM nhif_tariffs WHERE Key_name = ?", ['nhif_tariffs_item_name_index']);
        if (empty($existing2)) {
            Schema::table('nhif_tariffs', function (Blueprint $table) {
                $table->index('item_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes only if they exist
        $existing1 = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM nhif_tariffs WHERE Key_name = ?", ['nhif_tariffs_item_code_index']);
        if (!empty($existing1)) {
            Schema::table('nhif_tariffs', function (Blueprint $table) {
                $table->dropIndex(['item_code']);
            });
        }

        $existing2 = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM nhif_tariffs WHERE Key_name = ?", ['nhif_tariffs_item_name_index']);
        if (!empty($existing2)) {
            Schema::table('nhif_tariffs', function (Blueprint $table) {
                $table->dropIndex(['item_name']);
            });
        }
    }
};
