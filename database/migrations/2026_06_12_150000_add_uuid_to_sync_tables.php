<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6.2a — adds a global `uuid` identity column to every table that is either
 * directly synced (v1 syncable tables) or is an FK target referenced from a synced
 * table (see docs/phase6.2-bidirectional-sync-design.md §3 and §11).
 */
return new class extends Migration
{
    private array $tables = [
        // v1 syncable tables (registration -> clinical -> billing chain)
        'patients',
        'patient_visits',
        'consultations',
        'vital_signs',
        'investigations',
        'prescriptions',
        'allergies',
        'past_medical_history',
        'patient_referrals',
        'financial_transactions',
        'payment_receipts',
        'medication_cash_sales',
        'medication_cash_sale_items',
        'users',
        // FK-target reference tables
        'visit_types',
        'medical_services',
        'medications',
        'patient_categories',
        'administration_routes',
        'medication_frequencies',
        'referral_hospitals',
        'referral_departments',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table) || Schema::hasColumn($table, 'uuid')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->uuid('uuid')->nullable()->unique()->after('id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'uuid')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropUnique(['uuid']);
                $blueprint->dropColumn('uuid');
            });
        }
    }
};
