<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = app(\App\Services\NhifService::class);
$facility = $argv[1] ?? '03747';
$resp = $svc->downloadTariffsWithoutExcludedService($facility);
if (! $resp['success']) {
    echo "Download failed: " . ($resp['error'] ?? 'unknown') . PHP_EOL;
    exit(1);
}
$items = $resp['data'];
$codes = [];
foreach ($items as $it) {
    $c = $it['ItemCode'] ?? $it['itemCode'] ?? null;
    if ($c) $codes[] = $c;
}

$missing = [];
foreach ($codes as $c) {
    $exists = \App\Models\NhifTariff::where('facility_code', $facility)->where('item_code', $c)->exists();
    if (! $exists) $missing[] = $c;
}
$dbCount = \App\Models\NhifTariff::where('facility_code', $facility)->count();

echo "Downloaded items: " . count($codes) . PHP_EOL;
echo "DB count for facility {$facility}: {$dbCount}" . PHP_EOL;
echo "Missing in DB (downloaded but not present): " . count($missing) . PHP_EOL;
if (! empty($missing)) {
    echo "First missing codes: " . implode(', ', array_slice($missing, 0, 50)) . PHP_EOL;
}

return 0;
