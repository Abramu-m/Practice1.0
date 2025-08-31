<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('mtuha_diagnoses', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('description', 100)->unique();
            // keep schema small and compatible with the provided dump
        });
    }

    public function down()
    {
        Schema::dropIfExists('mtuha_diagnoses');
    }
};
