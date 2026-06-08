<?php

return [
    'enabled' => env('CDS_ENABLED', true),
    'log_channel' => env('CDS_LOG_CHANNEL', 'cds'),

    'features' => [
        'medication' => [
            'allergy'     => true,
            'duplicate'   => true,
            'dose_range'  => true,
            'renal_dose'  => true,
            'formulary'   => false,
            'interactions' => true,
        ],
        'guidelines' => [
            'order_sets' => false,
            'prompts' => false,
            'preventive' => false,
        ],
        'diagnostics' => [
            'calculators' => false,
            'lab_highlight' => true,
            'early_warning' => false,
        ],
    ],

    // Minimal example dose policies used by DoseRangeRule.
    // Keys should roughly match contained text in medication_name (generic preferred).
    'dose_policies' => [
        // Paracetamol (Acetaminophen)
            'paracetamol' => [
                'max_single_mg' => 1000,
                'max_daily_mg' => 4000, // adults
                'peds_mg_per_kg_dose' => [
                    'min_age_years' => 0,
                    'max_age_years' => 12,
                    'mg_per_kg' => 15,
                    'max_single_mg' => 1000,
                ],
                // illustrative renal adjustment caps (tune clinically!)
                'renal' => [
                    ['egfr_max' => 30, 'max_daily_mg' => 2000],
                    ['egfr_max' => 15, 'max_daily_mg' => 1500],
                ],
            ],
        // Ibuprofen
            'ibuprofen' => [
                'max_single_mg' => 800,
                'max_daily_mg' => 2400,
                'peds_mg_per_kg_dose' => [
                    'min_age_years' => 0,
                    'max_age_years' => 12,
                    'mg_per_kg' => 10,
                    'max_single_mg' => 400,
                ],
                'renal' => [
                    ['egfr_max' => 60, 'max_daily_mg' => 1200],
                    ['egfr_max' => 30, 'max_daily_mg' => 800],
                ],
            ],
    ],
];
