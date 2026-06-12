<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_outbox', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 64);
            $table->uuid('record_uuid');
            $table->enum('operation', ['insert', 'update', 'delete']);
            $table->json('payload');
            $table->string('origin_site', 32);
            $table->timestamp('created_at');
            $table->timestamp('synced_at')->nullable();
            $table->index(['synced_at', 'created_at'], 'sync_outbox_synced_at_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_outbox');
    }
};
