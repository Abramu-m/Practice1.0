<?php
// Inspect joined rows for icd_diagnoses in August 2025
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$year = 2025;
$month = 8;

$sql = "SELECT id.id AS icd_diag_id, id.icd_code, id.description AS diag_description, c.id AS consultation_id, v.id AS visit_id, v.visit_date, p.id AS patient_id, p.date_of_birth, p.gender, icd.mtuha_diagnosis
FROM icd_diagnoses id
JOIN consultations c ON c.id = id.consultation_id
JOIN patient_visits v ON v.id = c.visit_id
JOIN patients p ON p.id = v.patient
LEFT JOIN icd_10 icd ON icd.code = id.icd_code
WHERE YEAR(v.visit_date) = ? AND MONTH(v.visit_date) = ?
ORDER BY id.id ASC";

$rows = DB::select($sql, [$year, $month]);

if (empty($rows)) {
    echo "No joined rows found for {$month}/{$year}\n";
    exit(0);
}

foreach ($rows as $r) {
    echo sprintf("icd_diag_id=%s icd_code=%s mtuha=%s consultation=%s visit=%s visit_date=%s patient=%s dob=%s gender=%s desc=%s\n",
        $r->icd_diag_id, $r->icd_code, $r->mtuha_diagnosis ?? 'NULL', $r->consultation_id, $r->visit_id, $r->visit_date, $r->patient_id, $r->date_of_birth, $r->gender, ($r->diag_description ?? ''));
}


