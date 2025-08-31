<?php
// Check counts from MtuhaReportService::diagnoses() for specific mtuha categories and buckets
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new App\Services\MtuhaReportService();
$year = 2025; $month = 8;
$buckets = [
    ['min' => 0, 'max' => 30],
    ['min' => 30, 'max' => 365],
    ['min' => 365, 'max' => 1825],
    ['min' => 1825, 'max' => 21900],
    ['min' => 21900, 'max' => 36500],
];
// categories observed in the joined rows
$categories = [81,89,83,28,5];

foreach ($categories as $cat) {
    echo "Category {$cat}\n";
    foreach ($buckets as $i => $b) {
    $m = $service->diagnoses('male', $year, $month, $cat, $b['min'], $b['max']);
    $f = $service->diagnoses('female', $year, $month, $cat, $b['min'], $b['max']);
        echo sprintf("  Bucket %d (%d-%d days): Male=%d Female=%d Both=%d\n", $i, $b['min'], $b['max'], $m, $f, $m+$f);
    }
}

