<?php
// Dump only selected diagnosisGroups from MtuhaReportService::buildReport for debugging
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new App\Services\MtuhaReportService();
$year = 2025; $month = 8;
$payload = $service->buildReport($year, $month, false);
$ids = [81,89,83,28,5];
$groups = $payload['diagnosisGroups'] ?? [];

foreach ($groups as $g) {
    if (in_array($g['id'], $ids)) {
        echo "ID: {$g['id']} Desc: {$g['description']}\n";
        foreach ($g['buckets'] as $i => $b) {
            echo sprintf("  Bucket %d: Male=%d Female=%d Both=%d\n", $i, $b['Male'], $b['Female'], $b['Both']);
        }
        echo sprintf("  Totals: Male=%d Female=%d Both=%d\n\n", $g['totals']['Male'], $g['totals']['Female'], $g['totals']['Both']);
    }
}
