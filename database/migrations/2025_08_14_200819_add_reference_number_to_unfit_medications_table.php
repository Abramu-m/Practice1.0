<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if column already exists
        if (!Schema::hasColumn('unfit_medications', 'reference_number')) {
            Schema::table('unfit_medications', function (Blueprint $table) {
                $table->string('reference_number')->nullable()->after('id');
            });
        }
        
        // Update existing records with reference numbers
        DB::statement("
            UPDATE unfit_medications 
            SET reference_number = CONCAT('DISP-', YEAR(created_at), '-', LPAD(id, 6, '0'))
            WHERE reference_number IS NULL OR reference_number = ''
        ");
        
        // Add unique constraint if not already exists
        $indexes = DB::select("SHOW INDEXES FROM unfit_medications WHERE Key_name = 'unfit_medications_reference_number_unique'");
        if (empty($indexes)) {
            Schema::table('unfit_medications', function (Blueprint $table) {
                $table->string('reference_number')->unique()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unfit_medications', function (Blueprint $table) {
            $table->dropColumn('reference_number');
        });
    }
};
