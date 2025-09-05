<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('drug_atc_map', function (Blueprint $table) {
            $table->id();
            $table->string('medication_name');
            $table->unsignedBigInteger('atc_code_id');
            $table->timestamps();

            $table->foreign('atc_code_id')->references('id')->on('atc_codes')->cascadeOnDelete();
            $table->index(['medication_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drug_atc_map');
    }
};
