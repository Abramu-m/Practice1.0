<?php
// Smoke test: overall counts for a month (no gender/age/category/diagnosis filters)
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$year = 2025;
$month = 8;

echo "Running unfiltered MTUHA totals for year={$year} month={ $month } (whole month)\n\n";

$sql = <<<'SQL'
SELECT
  COUNT(DISTINCT icd_diagnoses.id) AS total_diagnoses,
  COUNT(DISTINCT c.id) AS total_consultations,
  COUNT(DISTINCT v.id) AS total_visits
FROM icd_diagnoses
JOIN consultations c ON c.id = icd_diagnoses.consultation_id
JOIN patient_visits v ON v.id = c.visit_id
WHERE YEAR(v.visit_date) = ?
  AND MONTH(v.visit_date) = ?;
SQL;

try {
    $rows = DB::select($sql, [$year, $month]);
} catch (\Exception $e) {
    echo "Query failed: " . $e->getMessage() . "\n";
    exit(1);
}

if (empty($rows)) {
    echo "No rows returned.\n";
    exit(0);
}

$r = $rows[0];

echo "Total diagnoses: " . ($r->total_diagnoses ?? 0) . "\n";
echo "Total consultations with diagnoses: " . ($r->total_consultations ?? 0) . "\n";
echo "Total patient visits with diagnoses: " . ($r->total_visits ?? 0) . "\n";

return 0;
