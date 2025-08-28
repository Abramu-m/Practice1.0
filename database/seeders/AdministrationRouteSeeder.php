<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdministrationRoute;

class AdministrationRouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $routes = [
            [
                'route_name' => 'Oral',
                'route_code' => 'PO',
                'route_abbreviation' => 'PO',
                'description' => 'By mouth - swallowed',
                'instructions' => 'Take with water. May be taken with or without food unless specified otherwise.',
                'requires_prescription' => false,
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'route_name' => 'Sublingual',
                'route_code' => 'SL',
                'route_abbreviation' => 'SL',
                'description' => 'Under the tongue',
                'instructions' => 'Place under tongue and allow to dissolve completely. Do not swallow.',
                'requires_prescription' => true,
                'display_order' => 2,
                'is_active' => true,
            ],
            [
                'route_name' => 'Intravenous',
                'route_code' => 'IV',
                'route_abbreviation' => 'IV',
                'description' => 'Into a vein',
                'instructions' => 'Must be administered by qualified healthcare professional. Requires sterile technique.',
                'requires_prescription' => true,
                'display_order' => 3,
                'is_active' => true,
            ],
            [
                'route_name' => 'Intramuscular',
                'route_code' => 'IM',
                'route_abbreviation' => 'IM',
                'description' => 'Into muscle tissue',
                'instructions' => 'Inject into muscle tissue. Use appropriate needle size and technique.',
                'requires_prescription' => true,
                'display_order' => 4,
                'is_active' => true,
            ],
            [
                'route_name' => 'Subcutaneous',
                'route_code' => 'SC',
                'route_abbreviation' => 'SC',
                'description' => 'Under the skin',
                'instructions' => 'Inject into subcutaneous tissue. Rotate injection sites.',
                'requires_prescription' => true,
                'display_order' => 5,
                'is_active' => true,
            ],
            [
                'route_name' => 'Topical',
                'route_code' => 'TOP',
                'route_abbreviation' => 'TOP',
                'description' => 'Applied to skin surface',
                'instructions' => 'Apply to affected area as directed. Wash hands before and after application.',
                'requires_prescription' => false,
                'display_order' => 6,
                'is_active' => true,
            ],
            [
                'route_name' => 'Inhaled',
                'route_code' => 'INH',
                'route_abbreviation' => 'INH',
                'description' => 'Breathed into lungs',
                'instructions' => 'Use inhaler device as instructed. Rinse mouth after use if corticosteroid.',
                'requires_prescription' => true,
                'display_order' => 7,
                'is_active' => true,
            ],
            [
                'route_name' => 'Nasal',
                'route_code' => 'NAS',
                'route_abbreviation' => 'NAS',
                'description' => 'Through the nose',
                'instructions' => 'Administer into nasal passages. Clear nasal passages before use.',
                'requires_prescription' => false,
                'display_order' => 8,
                'is_active' => true,
            ],
            [
                'route_name' => 'Ophthalmic',
                'route_code' => 'OPH',
                'route_abbreviation' => 'OPH',
                'description' => 'Into the eye',
                'instructions' => 'Apply to affected eye(s). Wash hands before use. Avoid touching tip to eye.',
                'requires_prescription' => true,
                'display_order' => 9,
                'is_active' => true,
            ],
            [
                'route_name' => 'Otic',
                'route_code' => 'OT',
                'route_abbreviation' => 'OT',
                'description' => 'Into the ear',
                'instructions' => 'Apply to affected ear(s). Warm to body temperature before use.',
                'requires_prescription' => true,
                'display_order' => 10,
                'is_active' => true,
            ],
            [
                'route_name' => 'Rectal',
                'route_code' => 'PR',
                'route_abbreviation' => 'PR',
                'description' => 'Into the rectum',
                'instructions' => 'Insert into rectum. Retain for recommended time.',
                'requires_prescription' => true,
                'display_order' => 11,
                'is_active' => true,
            ],
            [
                'route_name' => 'Vaginal',
                'route_code' => 'PV',
                'route_abbreviation' => 'PV',
                'description' => 'Into the vagina',
                'instructions' => 'Insert into vagina as directed. Use at bedtime unless specified otherwise.',
                'requires_prescription' => true,
                'display_order' => 12,
                'is_active' => true,
            ],
            [
                'route_name' => 'Transdermal',
                'route_code' => 'TD',
                'route_abbreviation' => 'TD',
                'description' => 'Through the skin via patch',
                'instructions' => 'Apply patch to clean, dry skin. Rotate application sites. Remove old patch before applying new one.',
                'requires_prescription' => true,
                'display_order' => 13,
                'is_active' => true,
            ],
            [
                'route_name' => 'Buccal',
                'route_code' => 'BUCC',
                'route_abbreviation' => 'BUCC',
                'description' => 'Between cheek and gum',
                'instructions' => 'Place between cheek and gum. Allow to dissolve completely.',
                'requires_prescription' => true,
                'display_order' => 14,
                'is_active' => true,
            ],
            [
                'route_name' => 'Intradermal',
                'route_code' => 'ID',
                'route_abbreviation' => 'ID',
                'description' => 'Into the skin layers',
                'instructions' => 'Inject into dermal layer. Used primarily for testing and vaccines.',
                'requires_prescription' => true,
                'display_order' => 15,
                'is_active' => true,
            ],
            [
                'route_name' => 'Epidural',
                'route_code' => 'EPI',
                'route_abbreviation' => 'EPI',
                'description' => 'Into epidural space',
                'instructions' => 'Specialist administration required. Used for anesthesia and pain management.',
                'requires_prescription' => true,
                'display_order' => 16,
                'is_active' => true,
            ],
            [
                'route_name' => 'Intrathecal',
                'route_code' => 'IT',
                'route_abbreviation' => 'IT',
                'description' => 'Into spinal fluid',
                'instructions' => 'Specialist administration required. Direct injection into cerebrospinal fluid.',
                'requires_prescription' => true,
                'display_order' => 17,
                'is_active' => true,
            ],
            [
                'route_name' => 'Intraarticular',
                'route_code' => 'IA',
                'route_abbreviation' => 'IA',
                'description' => 'Into joint space',
                'instructions' => 'Specialist administration required. Injection into joint cavity.',
                'requires_prescription' => true,
                'display_order' => 18,
                'is_active' => true,
            ],
        ];

        foreach ($routes as $route) {
            AdministrationRoute::create($route);
        }
    }
}
