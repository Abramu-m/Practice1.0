<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_stock_movements', function (Blueprint $table) {
            $table->dropForeign('store_stock_movements_batch_id_foreign');
            $table->dropColumn('batch_id');
        });
    }

    public function down(): void
    {
        Schema::table('store_stock_movements', function (Blueprint $table) {
            $table->foreignId('batch_id')
                ->nullable()
                ->after('to_location_id')
                ->constrained('store_stock_batches')
                ->nullOnDelete();
        });
    }
};
