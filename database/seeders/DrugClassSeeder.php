<?php

namespace Database\Seeders;

use App\Models\DrugClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DrugClassSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            // Analgesics & Anti-inflammatory
            ['name' => 'NSAIDs',                    'category' => 'Analgesics'],
            ['name' => 'Opioids',                   'category' => 'Analgesics'],
            ['name' => 'Salicylates',               'category' => 'Analgesics'],
            // Antibiotics
            ['name' => 'Penicillins',               'category' => 'Antibiotics'],
            ['name' => 'Cephalosporins',            'category' => 'Antibiotics'],
            ['name' => 'Macrolides',                'category' => 'Antibiotics'],
            ['name' => 'Fluoroquinolones',          'category' => 'Antibiotics'],
            ['name' => 'Tetracyclines',             'category' => 'Antibiotics'],
            ['name' => 'Aminoglycosides',           'category' => 'Antibiotics'],
            ['name' => 'Sulfonamides',              'category' => 'Antibiotics'],
            ['name' => 'Nitroimidazoles',           'category' => 'Antibiotics'],
            ['name' => 'Carbapenems',               'category' => 'Antibiotics'],
            // Cardiovascular
            ['name' => 'Beta Blockers',             'category' => 'Cardiovascular'],
            ['name' => 'ACE Inhibitors',            'category' => 'Cardiovascular'],
            ['name' => 'ARBs',                      'category' => 'Cardiovascular'],
            ['name' => 'Calcium Channel Blockers',  'category' => 'Cardiovascular'],
            ['name' => 'Diuretics (Loop)',          'category' => 'Cardiovascular'],
            ['name' => 'Diuretics (Thiazide)',      'category' => 'Cardiovascular'],
            ['name' => 'Diuretics (K-sparing)',     'category' => 'Cardiovascular'],
            ['name' => 'Nitrates',                  'category' => 'Cardiovascular'],
            ['name' => 'Antiarrhythmics',           'category' => 'Cardiovascular'],
            ['name' => 'Statins',                   'category' => 'Cardiovascular'],
            ['name' => 'Anticoagulants',            'category' => 'Cardiovascular'],
            ['name' => 'Antiplatelets',             'category' => 'Cardiovascular'],
            // CNS
            ['name' => 'Benzodiazepines',           'category' => 'CNS'],
            ['name' => 'SSRIs',                     'category' => 'CNS'],
            ['name' => 'SNRIs',                     'category' => 'CNS'],
            ['name' => 'TCAs',                      'category' => 'CNS'],
            ['name' => 'MAOIs',                     'category' => 'CNS'],
            ['name' => 'Antipsychotics (Typical)',  'category' => 'CNS'],
            ['name' => 'Antipsychotics (Atypical)', 'category' => 'CNS'],
            ['name' => 'Antiepileptics',            'category' => 'CNS'],
            // Endocrine / Metabolic
            ['name' => 'Biguanides',                'category' => 'Endocrine'],
            ['name' => 'Sulfonylureas',             'category' => 'Endocrine'],
            ['name' => 'Insulin',                   'category' => 'Endocrine'],
            ['name' => 'Corticosteroids',           'category' => 'Endocrine'],
            ['name' => 'Thyroid Agents',            'category' => 'Endocrine'],
            // Respiratory
            ['name' => 'Short-acting Beta2-agonists (SABA)', 'category' => 'Respiratory'],
            ['name' => 'Long-acting Beta2-agonists (LABA)',  'category' => 'Respiratory'],
            ['name' => 'Inhaled Corticosteroids',   'category' => 'Respiratory'],
            ['name' => 'Antihistamines',            'category' => 'Respiratory'],
            // GI
            ['name' => 'Proton Pump Inhibitors',    'category' => 'Gastrointestinal'],
            ['name' => 'H2 Blockers',               'category' => 'Gastrointestinal'],
            ['name' => 'Laxatives',                 'category' => 'Gastrointestinal'],
            // Other
            ['name' => 'Antifungals',               'category' => 'Antimicrobials'],
            ['name' => 'Antivirals',                'category' => 'Antimicrobials'],
            ['name' => 'Antimalarials',             'category' => 'Antimicrobials'],
            ['name' => 'Antituberculars',           'category' => 'Antimicrobials'],
            ['name' => 'Immunosuppressants',        'category' => 'Other'],
            ['name' => 'Uricosurics',               'category' => 'Other'],
            ['name' => 'Bisphosphonates',           'category' => 'Other'],
        ];

        foreach ($classes as $item) {
            DrugClass::firstOrCreate(
                ['slug' => Str::slug($item['name'])],
                ['name' => $item['name'], 'category' => $item['category'], 'is_active' => true]
            );
        }
    }
}
