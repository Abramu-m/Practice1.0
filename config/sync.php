<?php

/**
 * Phase 6.2 — bidirectional sync between practice.local and janet-healthcare.com.
 * See docs/phase6.2-bidirectional-sync-design.md for the full design.
 *
 * Two-node design: this instance only ever talks to one counterpart.
 */
return [

    'enabled' => env('SYNC_ENABLED', false),

    // This instance's identifier, written to sync_outbox.origin_site.
    'site_id' => env('SYNC_SITE_ID', ''),

    // The counterpart's identifier, used as sync_state.remote_site.
    'remote_site_id' => env('SYNC_REMOTE_SITE_ID', ''),

    // Base URL of the counterpart instance (e.g. https://janet-healthcare.com).
    'remote_url' => env('SYNC_REMOTE_URL', ''),

    // Shared HMAC secret, identical on both instances.
    'secret' => env('SYNC_SECRET', ''),

    // Max sync_outbox rows pushed/pulled per sync:run invocation.
    'batch_size' => env('SYNC_BATCH_SIZE', 500),

    // Connectivity check timeout (seconds) before sync:run no-ops as "offline".
    'ping_timeout' => 3,

    /*
    |--------------------------------------------------------------------------
    | v1 syncable tables
    |--------------------------------------------------------------------------
    |
    | Listed in dependency order: a table only appears after every table its
    | foreign_keys map points to. The sync apply step (§7) processes an
    | incoming batch grouped by table in this order, so FK-uuid lookups for
    | earlier-listed tables can already be resolved.
    |
    | - foreign_keys: local column => table the column references. On the way
    |   out, <col> is replaced with <col>_uuid (resolved from the referenced
    |   table's uuid). On the way in, <col>_uuid is resolved back to a local id
    |   via `WHERE uuid = ?` on the referenced table.
    | - conflict_check: when true, an incoming update/delete for a row also
    |   modified locally since the last pull is resolved via last-write-wins +
    |   a sync_conflicts row on tie/older incoming (see §7).
    | - exclude: payload fields stripped before the row is written to the
    |   outbox (cosmetic/session-local fields that shouldn't sync or trigger
    |   conflicts).
    |
    */
    'tables' => [

        'users' => [
            'foreign_keys' => [],
            'conflict_check' => true,
            'exclude' => ['remember_token', 'email_verified_at'],
        ],

        'patients' => [
            'foreign_keys' => [
                'created_by' => 'users',
                'patient_category' => 'patient_categories',
            ],
            'conflict_check' => true,
        ],

        'patient_visits' => [
            'foreign_keys' => [
                'patient' => 'patients',
                'visit_type' => 'visit_types',
                'visit_category' => 'patient_categories',
                'doctor' => 'users',
                'created_by' => 'users',
                'informed_by' => 'users',
            ],
            'conflict_check' => true,
        ],

        'consultations' => [
            'foreign_keys' => [
                'patient_id' => 'patients',
                'doctor_id' => 'users',
                'visit_id' => 'patient_visits',
            ],
            'conflict_check' => true,
        ],

        'vital_signs' => [
            'foreign_keys' => [
                'consultation_id' => 'consultations',
                'visit_id' => 'patient_visits',
                'patient_id' => 'patients',
                'recorded_by' => 'users',
                'updated_by' => 'users',
            ],
            'conflict_check' => true,
        ],

        'investigations' => [
            'foreign_keys' => [
                'patient_id' => 'patients',
                'consultation_id' => 'consultations',
                'doctor_id' => 'users',
                'medical_service_id' => 'medical_services',
                'visit_id' => 'patient_visits',
                'ordered_by' => 'users',
                'paid_by' => 'users',
                'collected_by' => 'users',
                'resulted_by' => 'users',
                'cancelled_by' => 'users',
            ],
            'conflict_check' => true,
        ],

        'prescriptions' => [
            'foreign_keys' => [
                'patient_id' => 'patients',
                'consultation_id' => 'consultations',
                'doctor_id' => 'users',
                'medication_id' => 'medications',
                'administration_route_id' => 'administration_routes',
                'frequency_id' => 'medication_frequencies',
                'visit_id' => 'patient_visits',
                'paid_by' => 'users',
                'reviewed_by' => 'users',
                'prepared_by' => 'users',
                'dispensed_by' => 'users',
            ],
            'conflict_check' => true,
        ],

        'allergies' => [
            'foreign_keys' => [
                'patient_id' => 'patients',
                'medication_id' => 'medications',
            ],
            'conflict_check' => true,
        ],

        'past_medical_history' => [
            'foreign_keys' => [
                'patient_id' => 'patients',
            ],
            'conflict_check' => true,
        ],

        'patient_referrals' => [
            'foreign_keys' => [
                'patient_id' => 'patients',
                'consultation_id' => 'consultations',
                'visit_id' => 'patient_visits',
                'referral_hospital_id' => 'referral_hospitals',
                'referral_department_id' => 'referral_departments',
                'created_by' => 'users',
            ],
            'conflict_check' => true,
        ],

        'financial_transactions' => [
            'foreign_keys' => [
                'patient_id' => 'patients',
                'visit_id' => 'patient_visits',
                'created_by' => 'users',
                'approved_by' => 'users',
                // source_id is polymorphic (keyed by source_type) and is not
                // remapped in v1 - left as a local-only reference.
            ],
            'conflict_check' => true,
        ],

        'payment_receipts' => [
            'foreign_keys' => [
                'patient_id' => 'patients',
                'visit_id' => 'patient_visits',
                'created_by' => 'users',
                'printed_by' => 'users',
                'cancelled_by' => 'users',
            ],
            'conflict_check' => true,
        ],

        'medication_cash_sales' => [
            'foreign_keys' => [
                'patient_category_id' => 'patient_categories',
                'created_by' => 'users',
                'dispensed_by' => 'users',
                'paid_by' => 'users',
                'cancelled_by' => 'users',
            ],
            'conflict_check' => true,
        ],

        'medication_cash_sale_items' => [
            'foreign_keys' => [
                'cash_sale_id' => 'medication_cash_sales',
                'medication_id' => 'medications',
                'medication_frequency_id' => 'medication_frequencies',
                'administration_route_id' => 'administration_routes',
                'dispensed_by' => 'users',
            ],
            'conflict_check' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reference / FK-target tables
    |--------------------------------------------------------------------------
    |
    | These tables are not in `tables` above (not synced via the outbox in
    | v1), but v1 tables have foreign keys into them, so they need a `uuid`
    | column for FK lookups (§11). Until the config-table sync mechanism (§9)
    | is built (6.2b/c), their `uuid` values are NOT guaranteed to match
    | across the two instances - FK remapping for these targets will only be
    | reliable once that mechanism ships.
    |
    */
    'reference_tables' => [
        'visit_types',
        'medical_services',
        'medications',
        'patient_categories',
        'administration_routes',
        'medication_frequencies',
        'referral_hospitals',
        'referral_departments',
    ],
];
