<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$keywords = ['penicillin','amoxicillin','sulfa','aspirin','ibuprofen','naproxen',
             'diclofenac','metformin','codeine','tramadol','nsaid','morphine',
             'cotrimoxazole','trimethoprim','sulfamethoxazole'];

echo "=== Medications in DB matching allergy-relevant drugs ===\n";
$meds = DB::table('medications')->select('id','generic_name','brand_name','is_active')->get();
foreach ($meds as $m) {
    $lc = strtolower($m->generic_name . ' ' . $m->brand_name);
    foreach ($keywords as $kw) {
        if (str_contains($lc, $kw)) {
            echo "  [{$m->id}] {$m->generic_name} / {$m->brand_name} (active: {$m->is_active})\n";
            break;
        }
    }
}

echo "\n=== All medications (first 60) ===\n";
$all = DB::table('medications')->select('id','generic_name','brand_name','is_active')->limit(60)->get();
foreach ($all as $m) {
    echo "  [{$m->id}] {$m->generic_name} / {$m->brand_name}\n";
}
