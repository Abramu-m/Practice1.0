<?php

return [
    'credentials' => [
        'username' => env('NHIF_USERNAME', ''),
        'password' => env('NHIF_PASSWORD', ''),
    ],
    'mode' => env('NHIF_MODE', 'test'), // test or production
    // Facility identifier (can be set in .env)
    'facility_code' => env('NHIF_FACILITY_CODE', ''),
    'url' => [
        'test' => 'http://196.13.105.15/nhifservice/breeze/',
        'production' => 'https://verification.nhif.or.tz/nhifservice/breeze/',
        'token' => [
            'test' => 'http://196.13.105.15/nhifservice/Token',
            'production' => 'https://verification.nhif.or.tz/nhifservice/Token',
            'production_claimsserver' => 'https://verification.nhif.or.tz/claimsserver/Token',
        ],
        'verification' => [
            'test' => 'http://196.13.105.15/nhifservice/breeze/verification/GetCardDetails',
            'production' => 'https://verification.nhif.or.tz/nhifservice/breeze/verification/GetCardDetails',
        ],
        'member_verification' => [
            'test' => 'http://196.13.105.15/nhifservice/breeze/verification/GetCardDetails',
            'production' => 'https://verification.nhif.or.tz/nhifservice/breeze/verification/GetCardDetails',
        ],
        'tariffs' => 'https://verification.nhif.or.tz/claimsserver/api/v1/Packages/',
        'claim' => 'https://verification.nhif.or.tz/claimsserver/api/v1/claims/SubmitFolios',
        'claim_submitted' => 'https://verification.nhif.or.tz/claimsServer/api/v1/claims/getSubmittedClaims',
        'referral' => 'https://verification.nhif.or.tz/nhifservice/breeze/verification/AddReferral',
        'pre_approved' => 'https://verification.nhif.or.tz/nhifservice/breeze/verification/GetReferenceNoStatus',
        'authorize' => [
            'test' => 'http://196.13.105.15/nhifservice/breeze/verification/AuthorizeCard',
            'production' => 'https://verification.nhif.or.tz/nhifservice/breeze/verification/AuthorizeCard',
        ],
    ],
    'timeout' => 30, // seconds
    'retry_attempts' => 3,
];
