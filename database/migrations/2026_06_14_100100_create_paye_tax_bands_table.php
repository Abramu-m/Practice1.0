<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paye_tax_bands', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->nullable()->unique();
            $table->unsignedInteger('band_order');
            $table->decimal('min_income', 12, 2);
            $table->decimal('max_income', 12, 2)->nullable();
            $table->decimal('rate', 5, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active', 'paye_tax_bands_is_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paye_tax_bands');
    }
};
