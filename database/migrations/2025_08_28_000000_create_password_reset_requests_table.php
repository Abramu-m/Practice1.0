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
        Schema::create('password_reset_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_email');
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('admin_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_requests');
    }
};
