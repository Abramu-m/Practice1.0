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
        Schema::create('lab_codes', function (Blueprint $table) {
            $table->id();
            $table->enum('coding_system', ['loinc', 'snomed']);
            $table->string('code');
            $table->string('display_name');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['coding_system', 'code'], 'lab_codes_coding_system_code_unique');
            $table->index('is_active', 'lab_codes_is_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_codes');
    }
};
