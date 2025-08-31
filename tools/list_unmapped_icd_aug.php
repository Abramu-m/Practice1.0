<?php
// List distinct ICD codes used in Aug 2025 that lack mtuha mapping
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$year = 2025;
$month = 8;

echo "ICD codes in {$year}-{$month} with NULL/0 mtuha_diagnosis\n\n";

$sql = <<<'SQL'
SELECT icd.code AS code, icd.description AS icd_description, COUNT(DISTINCT icd_diagnoses.id) AS cnt
FROM icd_diagnoses
JOIN consultations c ON c.id = icd_diagnoses.consultation_id
JOIN patient_visits v ON v.id = c.visit_id
JOIN icd_10 icd ON icd.code = icd_diagnoses.icd_code
WHERE YEAR(v.visit_date) = ?
  AND MONTH(v.visit_date) = ?
  AND (icd.mtuha_diagnosis IS NULL OR icd.mtuha_diagnosis = 0)
GROUP BY icd.code, icd.description
ORDER BY cnt DESC, icd.code
SQL;

try {
    $rows = DB::select($sql, [$year, $month]);
} catch (\Exception $e) {
    echo "Query failed: " . $e->getMessage() . "\n";
    exit(1);
}

if (empty($rows)) {
    echo "No unmapped ICD codes for that month.\n";
    exit(0);
}

foreach ($rows as $r) {
    echo str_pad($r->code, 10) . ' ' . str_pad($r->cnt, 6) . ' ' . ($r->icd_description ?? '') . "\n";
}

return 0;
