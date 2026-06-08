<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check patient columns
$pcols = \Illuminate\Support\Facades\Schema::getColumnListing('patients');
echo "patients columns: " . implode(', ', $pcols) . "\n";

$patient = \Illuminate\Support\Facades\DB::table('patients')->where('card_number', 'CDS-TEST-003')->first();
echo "CDS-TEST-003: " . json_encode($patient) . "\n";
