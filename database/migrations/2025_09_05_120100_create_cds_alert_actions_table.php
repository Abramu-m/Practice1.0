<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cds_alert_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cds_alert_id')->index();
            $table->string('action'); // accept | override | dismiss
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cds_alert_actions');
    }
};
