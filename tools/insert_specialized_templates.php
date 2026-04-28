<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$templates = [
    ['name' => 'GeneXpert MTB/RIF',           'code' => 'genxpert_tb',          'description' => 'Molecular detection of M. tuberculosis and rifampicin resistance'],
    ['name' => 'ZN Stain Microscopy (AFB)',    'code' => 'zn_stain_tb',          'description' => 'Ziehl-Neelsen stain for acid-fast bacilli (TB)'],
    ['name' => 'Blood Group & Rh Typing',      'code' => 'blood_grouping',       'description' => 'ABO blood grouping and Rhesus factor determination'],
    ['name' => 'PBS – Microfilaria',           'code' => 'pbs_microfilaria',     'description' => 'Peripheral blood smear for microfilariae'],
    ['name' => 'PBS – Malaria Parasites',      'code' => 'pbs_malaria',          'description' => 'Peripheral blood smear for malaria parasites'],
    ['name' => 'PBS – RBC Morphology',         'code' => 'pbs_rbc_morphology',   'description' => 'Peripheral blood smear red cell morphology assessment'],
    ['name' => 'PSA Semi-quantitative',        'code' => 'psa_semiquantitative', 'description' => 'Prostate-Specific Antigen semi-quantitative screening'],
    ['name' => 'Gram Stain Microscopy',        'code' => 'gram_stain',           'description' => 'Gram stain examination for bacterial morphology'],
];

foreach ($templates as $tpl) {
    $exists = \App\Models\ResultTemplate::where('code', $tpl['code'])->first();
    if ($exists) {
        echo "Already exists: ID:{$exists->id} | name:{$exists->name} | code:{$exists->code}\n";
    } else {
        $t = \App\Models\ResultTemplate::create([
            'name'        => $tpl['name'],
            'code'        => $tpl['code'],
            'description' => $tpl['description'],
            'is_active'   => true,
        ]);
        echo "Created: ID:{$t->id} | name:{$t->name} | code:{$t->code}\n";
    }
}
