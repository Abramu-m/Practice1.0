<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('medication_pricing');
        Schema::dropIfExists('medical_services_pricing');
    }

    public function down(): void
    {
        // intentionally not restored — pricing logic changed
    }
};
