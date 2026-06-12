<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_conflicts', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 64);
            $table->uuid('record_uuid');
            $table->json('local_payload');
            $table->json('incoming_payload');
            $table->timestamp('detected_at');
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->enum('resolution', ['kept_local', 'kept_incoming', 'merged'])->nullable();
            $table->index(['resolved_at'], 'sync_conflicts_resolved_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_conflicts');
    }
};
