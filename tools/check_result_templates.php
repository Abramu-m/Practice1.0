<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$exists = \App\Models\ResultTemplate::where('code', 'full_blood_picture')->first();
if ($exists) {
    echo "Already exists: ID:{$exists->id} | name:{$exists->name} | code:{$exists->code}\n";
} else {
    $t = \App\Models\ResultTemplate::create([
        'name'        => 'Full Blood Picture',
        'code'        => 'full_blood_picture',
        'description' => 'Complete blood count with RBC indices, WBC differential and platelet indices',
        'is_active'   => true,
    ]);
    echo "Created: ID:{$t->id} | name:{$t->name} | code:{$t->code}\n";
}

