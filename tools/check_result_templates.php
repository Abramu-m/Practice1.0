<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Fix any InvestigationTemplateResult records where template_name is 'Long Text'
// but metadata['template_code'] is null — set it to 'narrative_lab'
$results = \App\Models\InvestigationTemplateResult::where('template_name', 'Long Text')->get();
$fixed = 0;
foreach ($results as $r) {
    if (empty($r->metadata['template_code'])) {
        $meta = $r->metadata ?? [];
        $meta['template_code'] = 'narrative_lab';
        $r->metadata = $meta;
        $r->save();
        echo "Fixed ID:{$r->id} — set template_code=narrative_lab\n";
        $fixed++;
    }
}
echo "Done. Fixed {$fixed} record(s).\n";

