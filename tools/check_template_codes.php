<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$results = \App\Models\InvestigationTemplateResult::latest()->take(10)->get(['id','template_name','metadata','form_status']);
foreach ($results as $r) {
    $code = $r->metadata['template_code'] ?? 'NULL';
    echo "ID:{$r->id} | name:{$r->template_name} | code:{$code} | status:{$r->form_status}\n";
}
