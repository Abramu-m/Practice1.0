<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class MedicalServiceFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, run the migration if it hasn't been run
        if (!Schema::hasColumn('medical_services', 'requires_form')) {
            $this->command->info('Running migration to add form fields...');
            Artisan::call('migrate');
        }

        // Update specific medical services to require forms
        
        // TB/Leprosy Form
        $tbService = MedicalService::where('name', 'LIKE', '%TB%')
            ->orWhere('name', 'LIKE', '%Tuberculosis%')
            ->orWhere('name', 'LIKE', '%Leprosy%')
            ->first();
        
        if ($tbService) {
            $tbService->update([
                'requires_form' => true,
                'form_fields' => json_encode([
                    'form_type' => 'tb_leprosy',
                    'form_title' => 'TB/Leprosy Investigation Form',
                    'fields' => [
                        [
                            'name' => 'patient_type',
                            'label' => 'Patient Type',
                            'type' => 'select',
                            'options' => ['New Patient', 'Follow-up', 'Relapse', 'Treatment Default'],
                            'required' => true
                        ],
                        [
                            'name' => 'symptoms',
                            'label' => 'Symptoms',
                            'type' => 'checkbox_group',
                            'options' => ['Cough > 2 weeks', 'Weight Loss', 'Night Sweats', 'Fever', 'Chest Pain', 'Hemoptysis'],
                            'required' => false
                        ],
                        [
                            'name' => 'duration_symptoms',
                            'label' => 'Duration of Symptoms (weeks)',
                            'type' => 'number',
                            'required' => true
                        ],
                        [
                            'name' => 'previous_tb_treatment',
                            'label' => 'Previous TB Treatment',
                            'type' => 'select',
                            'options' => ['Yes', 'No', 'Unknown'],
                            'required' => true
                        ],
                        [
                            'name' => 'contact_history',
                            'label' => 'Contact History',
                            'type' => 'textarea',
                            'required' => false
                        ],
                        [
                            'name' => 'hiv_status',
                            'label' => 'HIV Status',
                            'type' => 'select',
                            'options' => ['Positive', 'Negative', 'Unknown', 'Not Tested'],
                            'required' => true
                        ],
                        [
                            'name' => 'specimen_type',
                            'label' => 'Specimen Type',
                            'type' => 'select',
                            'options' => ['Sputum', 'Gastric Aspirate', 'Lymph Node', 'Pleural Fluid', 'Other'],
                            'required' => true
                        ],
                        [
                            'name' => 'clinical_notes',
                            'label' => 'Clinical Notes',
                            'type' => 'textarea',
                            'required' => false
                        ]
                    ]
                ])
            ]);
        }

        // CD4 Form
        $cd4Service = MedicalService::where('name', 'LIKE', '%CD4%')
            ->orWhere('name', 'LIKE', '%CD4 Count%')
            ->first();
        
        if ($cd4Service) {
            $cd4Service->update([
                'requires_form' => true,
                'form_fields' => json_encode([
                    'form_type' => 'cd4',
                    'form_title' => 'CD4 Count Investigation Form',
                    'fields' => [
                        [
                            'name' => 'hiv_status',
                            'label' => 'HIV Status',
                            'type' => 'select',
                            'options' => ['Positive', 'Negative', 'Unknown'],
                            'required' => true
                        ],
                        [
                            'name' => 'art_status',
                            'label' => 'ART Status',
                            'type' => 'select',
                            'options' => ['On ART', 'Not on ART', 'ART Naive', 'ART Experienced'],
                            'required' => true
                        ],
                        [
                            'name' => 'art_duration',
                            'label' => 'Duration on ART (months)',
                            'type' => 'number',
                            'required' => false
                        ],
                        [
                            'name' => 'last_cd4_count',
                            'label' => 'Last CD4 Count',
                            'type' => 'number',
                            'required' => false
                        ],
                        [
                            'name' => 'last_cd4_date',
                            'label' => 'Date of Last CD4 Count',
                            'type' => 'date',
                            'required' => false
                        ],
                        [
                            'name' => 'viral_load_status',
                            'label' => 'Viral Load Status',
                            'type' => 'select',
                            'options' => ['Suppressed', 'Unsuppressed', 'Unknown', 'Not Done'],
                            'required' => false
                        ],
                        [
                            'name' => 'clinical_indication',
                            'label' => 'Clinical Indication',
                            'type' => 'select',
                            'options' => ['Baseline', 'Monitoring', 'Treatment Failure', 'Routine Follow-up', 'Other'],
                            'required' => true
                        ],
                        [
                            'name' => 'opportunistic_infections',
                            'label' => 'Opportunistic Infections',
                            'type' => 'checkbox_group',
                            'options' => ['TB', 'Pneumonia', 'Candidiasis', 'Diarrhea', 'Skin Conditions', 'None'],
                            'required' => false
                        ],
                        [
                            'name' => 'clinical_notes',
                            'label' => 'Clinical Notes',
                            'type' => 'textarea',
                            'required' => false
                        ]
                    ]
                ])
            ]);
        }

        // X-ray Form
        $xrayService = MedicalService::where('name', 'LIKE', '%X-ray%')
            ->orWhere('name', 'LIKE', '%X-Ray%')
            ->orWhere('name', 'LIKE', '%Chest X-ray%')
            ->first();
        
        if ($xrayService) {
            $xrayService->update([
                'requires_form' => true,
                'form_fields' => json_encode([
                    'form_type' => 'xray',
                    'form_title' => 'X-Ray Investigation Form',
                    'fields' => [
                        [
                            'name' => 'xray_type',
                            'label' => 'X-Ray Type',
                            'type' => 'select',
                            'options' => ['Chest X-Ray', 'Abdominal X-Ray', 'Extremity X-Ray', 'Spine X-Ray', 'Other'],
                            'required' => true
                        ],
                        [
                            'name' => 'clinical_indication',
                            'label' => 'Clinical Indication',
                            'type' => 'textarea',
                            'required' => true
                        ],
                        [
                            'name' => 'symptoms',
                            'label' => 'Symptoms',
                            'type' => 'checkbox_group',
                            'options' => ['Chest Pain', 'Shortness of Breath', 'Cough', 'Fever', 'Trauma', 'Other'],
                            'required' => false
                        ],
                        [
                            'name' => 'previous_xray',
                            'label' => 'Previous X-Ray',
                            'type' => 'select',
                            'options' => ['Yes', 'No', 'Unknown'],
                            'required' => false
                        ],
                        [
                            'name' => 'previous_xray_date',
                            'label' => 'Previous X-Ray Date',
                            'type' => 'date',
                            'required' => false
                        ],
                        [
                            'name' => 'pregnancy_status',
                            'label' => 'Pregnancy Status (if applicable)',
                            'type' => 'select',
                            'options' => ['Not Applicable', 'Pregnant', 'Not Pregnant', 'Unknown'],
                            'required' => false
                        ],
                        [
                            'name' => 'contrast_required',
                            'label' => 'Contrast Required',
                            'type' => 'select',
                            'options' => ['Yes', 'No', 'To be determined'],
                            'required' => false
                        ],
                        [
                            'name' => 'urgency',
                            'label' => 'Urgency',
                            'type' => 'select',
                            'options' => ['Routine', 'Urgent', 'Emergency'],
                            'required' => true
                        ],
                        [
                            'name' => 'clinical_notes',
                            'label' => 'Additional Clinical Notes',
                            'type' => 'textarea',
                            'required' => false
                        ]
                    ]
                ])
            ]);
        }

        // If services don't exist, create them with forms
        if (!$tbService) {
            MedicalService::create([
                'name' => 'TB Investigation',
                'code' => 'TB001',
                'description' => 'Tuberculosis investigation with clinical form',
                'service_category_id' => 1, // Assuming Laboratory category
                'price' => 25000.00,
                'requires_form' => true,
                'form_fields' => json_encode([
                    'form_type' => 'tb_leprosy',
                    'form_title' => 'TB/Leprosy Investigation Form',
                    'fields' => [
                        [
                            'name' => 'patient_type',
                            'label' => 'Patient Type',
                            'type' => 'select',
                            'options' => ['New Patient', 'Follow-up', 'Relapse', 'Treatment Default'],
                            'required' => true
                        ],
                        [
                            'name' => 'symptoms',
                            'label' => 'Symptoms',
                            'type' => 'checkbox_group',
                            'options' => ['Cough > 2 weeks', 'Weight Loss', 'Night Sweats', 'Fever', 'Chest Pain', 'Hemoptysis'],
                            'required' => false
                        ]
                    ]
                ])
            ]);
        }

        if (!$cd4Service) {
            MedicalService::create([
                'name' => 'CD4 Count',
                'code' => 'CD4001',
                'description' => 'CD4 count test with clinical form',
                'service_category_id' => 1, // Assuming Laboratory category
                'price' => 15000.00,
                'requires_form' => true,
                'form_fields' => json_encode([
                    'form_type' => 'cd4',
                    'form_title' => 'CD4 Count Investigation Form',
                    'fields' => [
                        [
                            'name' => 'hiv_status',
                            'label' => 'HIV Status',
                            'type' => 'select',
                            'options' => ['Positive', 'Negative', 'Unknown'],
                            'required' => true
                        ]
                    ]
                ])
            ]);
        }

        if (!$xrayService) {
            MedicalService::create([
                'name' => 'Chest X-Ray',
                'code' => 'XRAY001',
                'description' => 'Chest X-Ray with clinical form',
                'service_category_id' => 2, // Assuming Radiology category
                'price' => 30000.00,
                'requires_form' => true,
                'form_fields' => json_encode([
                    'form_type' => 'xray',
                    'form_title' => 'X-Ray Investigation Form',
                    'fields' => [
                        [
                            'name' => 'xray_type',
                            'label' => 'X-Ray Type',
                            'type' => 'select',
                            'options' => ['Chest X-Ray', 'Abdominal X-Ray', 'Extremity X-Ray'],
                            'required' => true
                        ]
                    ]
                ])
            ]);
        }

        $this->command->info('Medical service forms configured successfully!');
    }
}
