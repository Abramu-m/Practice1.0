<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cols = \Illuminate\Support\Facades\Schema::getColumnListing('medications');
echo "medications columns: " . implode(', ', $cols) . "\n";

$first = \Illuminate\Support\Facades\DB::table('medications')->first();
echo "First row: " . json_encode($first) . "\n";
