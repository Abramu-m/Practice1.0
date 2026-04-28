<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find the narrative_lab template
$narrativeTpl = \App\Models\ResultTemplate::where('code', 'narrative_lab')->first();
if (!$narrativeTpl) {
    echo "ERROR: narrative_lab result template not found in DB.\n";
    exit(1);
}
echo "Using template: ID:{$narrativeTpl->id} | name:{$narrativeTpl->name} | code:{$narrativeTpl->code}\n\n";

// Find all 'Procedure' categories (case-insensitive match)
$categories = \App\Models\ServiceCategory::whereRaw("LOWER(name) LIKE '%procedure%'")->get();
if ($categories->isEmpty()) {
    echo "No service categories found with name matching 'procedure'.\n";
    echo "All categories:\n";
    \App\Models\ServiceCategory::all()->each(fn($c) => print("  ID:{$c->id} | {$c->name}\n"));
    exit(1);
}

echo "Matching categories:\n";
$categoryIds = [];
foreach ($categories as $cat) {
    echo "  ID:{$cat->id} | {$cat->name}\n";
    $categoryIds[] = $cat->id;
}
echo "\n";

// Find affected services
$services = \App\Models\MedicalService::whereIn('service_category_id', $categoryIds)->get();
if ($services->isEmpty()) {
    echo "No medical services found in procedure categories.\n";
    exit(0);
}

echo "Services to update ({$services->count()}):\n";
foreach ($services as $svc) {
    $oldTpl = $svc->result_template_id
        ? \App\Models\ResultTemplate::find($svc->result_template_id)?->code ?? $svc->result_template_id
        : 'none';
    echo "  ID:{$svc->id} | {$svc->name} | current_template:{$oldTpl}\n";
}
echo "\n";

// Perform the update
$updated = \App\Models\MedicalService::whereIn('service_category_id', $categoryIds)
    ->update(['result_template_id' => $narrativeTpl->id]);

echo "Done. Updated {$updated} service(s) → result_template_id:{$narrativeTpl->id} (narrative_lab).\n";
