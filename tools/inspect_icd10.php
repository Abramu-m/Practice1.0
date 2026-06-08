<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

echo "Columns in icd_10:\n";
$cols = DB::select('SHOW COLUMNS FROM icd_10');
foreach ($cols as $c) {
    echo " - {$c->Field} ({$c->Type})\n";
}

$count = DB::table('icd_10')->count();
echo "Total rows: {$count}\n";
$withChapter = DB::table('icd_10')->whereNotNull('chapter')->count();
echo "Rows with chapter not null: {$withChapter}\n";

echo "\nFirst 10 rows (code,chapter):\n";
$rows = DB::table('icd_10')->select('code','chapter')->limit(10)->get();
foreach ($rows as $r) {
    $ch = $r->chapter === null ? '(null)' : $r->chapter;
    echo "{$r->code} => {$ch}\n";
}
