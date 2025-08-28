<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LegacyServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        $services = [
            // Laboratory Services (stype = 1)
            [
                'name' => 'Sputum Microscopy/ZN Stain (for AFB)',
                'code' => 'LAB_ZN_001',
                'description' => 'Sputum examination for acid-fast bacilli using Ziehl-Neelsen staining',
                'service_category_id' => 1,
                'price' => 25000.00,
                'insurance_covered_amount' => 20000.00,
                'requires_sample' => 1,
                'requires_form' => 1,
                'form_fields' => json_encode([
                    'form_type' => 'tb_microscopy',
                    'form_title' => 'TB Microscopy Form',
                    'fields' => [
                        [
                            'name' => 'specimen_quality',
                            'label' => 'Specimen Quality',
                            'type' => 'select',
                            'options' => ['Good', 'Fair', 'Poor'],
                            'required' => true
                        ],
                        [
                            'name' => 'smear_grade',
                            'label' => 'Smear Grade',
                            'type' => 'select',
                            'options' => ['Negative', 'Scanty', '1+', '2+', '3+'],
                            'required' => true
                        ]
                    ]
                ]),
                'sample_type' => 'Sputum',
                'turnaround_time_hours' => 4,
                'preparation_instructions' => 'Collect early morning sputum sample',
                'is_active' => 1
            ],
            [
                'name' => 'Ascitic Fluid Multistix',
                'code' => 'LAB_ASC_001',
                'description' => 'Ascitic fluid analysis using multistix testing',
                'service_category_id' => 1,
                'price' => 15000.00,
                'insurance_covered_amount' => 12000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Ascitic Fluid',
                'turnaround_time_hours' => 2,
                'preparation_instructions' => 'Sterile collection required',
                'is_active' => 1
            ],
            [
                'name' => 'Bilirubin, Conjugated',
                'code' => 'LAB_BIL_001',
                'description' => 'Serum conjugated bilirubin measurement',
                'service_category_id' => 1,
                'price' => 12000.00,
                'insurance_covered_amount' => 10000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (Serum)',
                'turnaround_time_hours' => 4,
                'preparation_instructions' => 'No special preparation required',
                'is_active' => 1
            ],
            [
                'name' => 'Bilirubin, Total',
                'code' => 'LAB_BIL_002',
                'description' => 'Total serum bilirubin measurement',
                'service_category_id' => 1,
                'price' => 12000.00,
                'insurance_covered_amount' => 10000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (Serum)',
                'turnaround_time_hours' => 4,
                'preparation_instructions' => 'No special preparation required',
                'is_active' => 1
            ],
            [
                'name' => 'Bilirubin, Unconjugated',
                'code' => 'LAB_BIL_003',
                'description' => 'Serum unconjugated bilirubin measurement',
                'service_category_id' => 1,
                'price' => 12000.00,
                'insurance_covered_amount' => 10000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (Serum)',
                'turnaround_time_hours' => 4,
                'preparation_instructions' => 'No special preparation required',
                'is_active' => 1
            ],
            [
                'name' => 'Bleeding Time',
                'code' => 'LAB_BT_001',
                'description' => 'Bleeding time assessment',
                'service_category_id' => 1,
                'price' => 8000.00,
                'insurance_covered_amount' => 6000.00,
                'requires_sample' => 0,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => null,
                'turnaround_time_hours' => 1,
                'preparation_instructions' => 'No anticoagulant medications 48hrs prior',
                'is_active' => 1
            ],
            [
                'name' => 'Blood Grouping, RH Typing & Crossmatching',
                'code' => 'LAB_BG_001',
                'description' => 'Complete blood grouping and compatibility testing',
                'service_category_id' => 1,
                'price' => 20000.00,
                'insurance_covered_amount' => 16000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (EDTA)',
                'turnaround_time_hours' => 2,
                'preparation_instructions' => 'No special preparation required',
                'is_active' => 1
            ],
            [
                'name' => 'Blood Smear for Microfilaria',
                'code' => 'LAB_MF_001',
                'description' => 'Blood examination for microfilaria parasites',
                'service_category_id' => 1,
                'price' => 15000.00,
                'insurance_covered_amount' => 12000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (EDTA)',
                'turnaround_time_hours' => 3,
                'preparation_instructions' => 'Night blood collection preferred',
                'is_active' => 1
            ],
            [
                'name' => 'Clotting Time',
                'code' => 'LAB_CT_001',
                'description' => 'Blood clotting time measurement',
                'service_category_id' => 1,
                'price' => 8000.00,
                'insurance_covered_amount' => 6000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (No anticoagulant)',
                'turnaround_time_hours' => 1,
                'preparation_instructions' => 'No anticoagulant medications',
                'is_active' => 1
            ],
            [
                'name' => 'Erythrocyte Sedimentation Rate (ESR)',
                'code' => 'LAB_ESR_001',
                'description' => 'ESR measurement for inflammation assessment',
                'service_category_id' => 1,
                'price' => 10000.00,
                'insurance_covered_amount' => 8000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (EDTA)',
                'turnaround_time_hours' => 2,
                'preparation_instructions' => 'No special preparation required',
                'is_active' => 1
            ],
            [
                'name' => 'Full Blood Picture (FBP/CBC)',
                'code' => 'LAB_FBP_001',
                'description' => 'Complete blood count with differential',
                'service_category_id' => 1,
                'price' => 25000.00,
                'insurance_covered_amount' => 20000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (EDTA)',
                'turnaround_time_hours' => 4,
                'preparation_instructions' => 'No special preparation required',
                'is_active' => 1
            ],
            [
                'name' => 'Gamma GT (GGT)',
                'code' => 'LAB_GGT_001',
                'description' => 'Gamma-glutamyl transferase enzyme measurement',
                'service_category_id' => 1,
                'price' => 18000.00,
                'insurance_covered_amount' => 14000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (Serum)',
                'turnaround_time_hours' => 6,
                'preparation_instructions' => 'Fasting for 8-12 hours recommended',
                'is_active' => 1
            ],
            [
                'name' => 'Sputum GeneXpert',
                'code' => 'LAB_GX_001',
                'description' => 'GeneXpert MTB/RIF testing for tuberculosis',
                'service_category_id' => 1,
                'price' => 35000.00,
                'insurance_covered_amount' => 28000.00,
                'requires_sample' => 1,
                'requires_form' => 1,
                'form_fields' => json_encode([
                    'form_type' => 'genexpert',
                    'form_title' => 'GeneXpert TB Form',
                    'fields' => [
                        [
                            'name' => 'clinical_indication',
                            'label' => 'Clinical Indication',
                            'type' => 'textarea',
                            'required' => true
                        ],
                        [
                            'name' => 'previous_tb_treatment',
                            'label' => 'Previous TB Treatment',
                            'type' => 'select',
                            'options' => ['None', 'Completed', 'Defaulted', 'Failed'],
                            'required' => true
                        ]
                    ]
                ]),
                'sample_type' => 'Sputum',
                'turnaround_time_hours' => 2,
                'preparation_instructions' => 'Fresh early morning sputum sample',
                'is_active' => 1
            ],
            [
                'name' => 'Blood Sugar/Glucose, Fasting',
                'code' => 'LAB_FBS_001',
                'description' => 'Fasting blood glucose measurement',
                'service_category_id' => 1,
                'price' => 10000.00,
                'insurance_covered_amount' => 8000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (Fluoride)',
                'turnaround_time_hours' => 2,
                'preparation_instructions' => 'Fasting for 8-12 hours required',
                'is_active' => 1
            ],
            [
                'name' => 'Blood Sugar/Glucose, Postprandial',
                'code' => 'LAB_PPBS_001',
                'description' => 'Post-meal blood glucose measurement',
                'service_category_id' => 1,
                'price' => 10000.00,
                'insurance_covered_amount' => 8000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (Fluoride)',
                'turnaround_time_hours' => 2,
                'preparation_instructions' => 'Eat normal meal 2 hours before test',
                'is_active' => 1
            ],
            [
                'name' => 'Blood Sugar/Glucose, Random',
                'code' => 'LAB_RBS_001',
                'description' => 'Random blood glucose measurement',
                'service_category_id' => 1,
                'price' => 8000.00,
                'insurance_covered_amount' => 6000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (Fluoride)',
                'turnaround_time_hours' => 2,
                'preparation_instructions' => 'No fasting required',
                'is_active' => 1
            ],
            [
                'name' => 'Glycosylated Hemoglobin (HbA1c)',
                'code' => 'LAB_HBA1C_001',
                'description' => 'Long-term diabetes monitoring test',
                'service_category_id' => 1,
                'price' => 35000.00,
                'insurance_covered_amount' => 28000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (EDTA)',
                'turnaround_time_hours' => 6,
                'preparation_instructions' => 'No special preparation required',
                'is_active' => 1
            ],
            [
                'name' => 'GPT (ALT)',
                'code' => 'LAB_ALT_001',
                'description' => 'Alanine aminotransferase enzyme measurement',
                'service_category_id' => 1,
                'price' => 15000.00,
                'insurance_covered_amount' => 12000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (Serum)',
                'turnaround_time_hours' => 6,
                'preparation_instructions' => 'Fasting for 8-12 hours recommended',
                'is_active' => 1
            ],
            [
                'name' => 'GOT (AST)',
                'code' => 'LAB_AST_001',
                'description' => 'Aspartate aminotransferase enzyme measurement',
                'service_category_id' => 1,
                'price' => 15000.00,
                'insurance_covered_amount' => 12000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (Serum)',
                'turnaround_time_hours' => 6,
                'preparation_instructions' => 'Fasting for 8-12 hours recommended',
                'is_active' => 1
            ],
            // Continue with more services...
            [
                'name' => 'Malaria Blood Smear (BS)',
                'code' => 'LAB_MAL_001',
                'description' => 'Malaria parasite detection by microscopy',
                'service_category_id' => 1,
                'price' => 15000.00,
                'insurance_covered_amount' => 12000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (EDTA)',
                'turnaround_time_hours' => 2,
                'preparation_instructions' => 'No special preparation required',
                'is_active' => 1
            ],
            [
                'name' => 'Malaria Rapid Diagnostic Test (MRDT)',
                'code' => 'LAB_MAL_002',
                'description' => 'Rapid malaria antigen detection test',
                'service_category_id' => 1,
                'price' => 12000.00,
                'insurance_covered_amount' => 10000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (Capillary)',
                'turnaround_time_hours' => 1,
                'preparation_instructions' => 'No special preparation required',
                'is_active' => 1
            ],
            [
                'name' => 'Urinalysis Wet Preparation',
                'code' => 'LAB_URINE_001',
                'description' => 'Complete urine analysis with microscopy',
                'service_category_id' => 1,
                'price' => 15000.00,
                'insurance_covered_amount' => 12000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Urine (Mid-stream)',
                'turnaround_time_hours' => 2,
                'preparation_instructions' => 'Clean catch mid-stream sample',
                'is_active' => 1
            ],
            [
                'name' => 'Serum Creatinine',
                'code' => 'LAB_CREA_001',
                'description' => 'Serum creatinine level measurement',
                'service_category_id' => 1,
                'price' => 12000.00,
                'insurance_covered_amount' => 10000.00,
                'requires_sample' => 1,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => 'Blood (Serum)',
                'turnaround_time_hours' => 4,
                'preparation_instructions' => 'No special preparation required',
                'is_active' => 1
            ],
            
            // Procedure Services (stype = 2)
            [
                'name' => 'Visual Acuity Testing (Snellen & E Chart)',
                'code' => 'PROC_VA_001',
                'description' => 'Comprehensive visual acuity assessment',
                'service_category_id' => 4,
                'price' => 15000.00,
                'insurance_covered_amount' => 12000.00,
                'requires_sample' => 0,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => null,
                'turnaround_time_hours' => 1,
                'preparation_instructions' => 'Bring current glasses if worn',
                'is_active' => 1
            ],
            [
                'name' => 'Ascitic Fluid Tapping',
                'code' => 'PROC_PARA_001',
                'description' => 'Paracentesis for ascitic fluid drainage',
                'service_category_id' => 4,
                'price' => 75000.00,
                'insurance_covered_amount' => 60000.00,
                'requires_sample' => 0,
                'requires_form' => 1,
                'form_fields' => json_encode([
                    'form_type' => 'paracentesis',
                    'form_title' => 'Paracentesis Procedure Form',
                    'fields' => [
                        [
                            'name' => 'indication',
                            'label' => 'Clinical Indication',
                            'type' => 'textarea',
                            'required' => true
                        ],
                        [
                            'name' => 'coagulation_status',
                            'label' => 'Coagulation Status',
                            'type' => 'select',
                            'options' => ['Normal', 'Abnormal', 'Unknown'],
                            'required' => true
                        ]
                    ]
                ]),
                'sample_type' => null,
                'turnaround_time_hours' => 2,
                'preparation_instructions' => 'Check coagulation profile, empty bladder',
                'is_active' => 1
            ],
            [
                'name' => 'Minor Wound Dressing',
                'code' => 'PROC_DRESS_001',
                'description' => 'Simple wound cleaning and dressing',
                'service_category_id' => 4,
                'price' => 20000.00,
                'insurance_covered_amount' => 16000.00,
                'requires_sample' => 0,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => null,
                'turnaround_time_hours' => 1,
                'preparation_instructions' => 'Keep wound clean and dry',
                'is_active' => 1
            ],
            
            // Radiology/Cardiology Services (stype = 3)
            [
                'name' => 'Echocardiography (ECHO)',
                'code' => 'RAD_ECHO_001',
                'description' => 'Cardiac ultrasound examination',
                'service_category_id' => 2,
                'price' => 85000.00,
                'insurance_covered_amount' => 68000.00,
                'requires_sample' => 0,
                'requires_form' => 1,
                'form_fields' => json_encode([
                    'form_type' => 'echo',
                    'form_title' => 'Echocardiography Form',
                    'fields' => [
                        [
                            'name' => 'indication',
                            'label' => 'Clinical Indication',
                            'type' => 'select',
                            'options' => ['Chest Pain', 'Shortness of Breath', 'Palpitations', 'Syncope', 'Heart Murmur', 'Hypertension', 'Diabetes Follow-up', 'Follow-up Study'],
                            'required' => true
                        ],
                        [
                            'name' => 'previous_echo_date',
                            'label' => 'Previous Echo Date',
                            'type' => 'date',
                            'required' => false
                        ],
                        [
                            'name' => 'current_medications',
                            'label' => 'Current Cardiac Medications',
                            'type' => 'textarea',
                            'required' => false
                        ]
                    ]
                ]),
                'sample_type' => null,
                'turnaround_time_hours' => 2,
                'preparation_instructions' => 'No special preparation required',
                'is_active' => 1
            ],
            [
                'name' => 'Ultrasound Scan',
                'code' => 'RAD_US_001',
                'description' => 'General ultrasound examination',
                'service_category_id' => 2,
                'price' => 50000.00,
                'insurance_covered_amount' => 40000.00,
                'requires_sample' => 0,
                'requires_form' => 1,
                'form_fields' => json_encode([
                    'form_type' => 'ultrasound',
                    'form_title' => 'Ultrasound Examination Form',
                    'fields' => [
                        [
                            'name' => 'examination_area',
                            'label' => 'Examination Area',
                            'type' => 'select',
                            'options' => ['Abdomen', 'Pelvis', 'Obstetric', 'Renal', 'Small Parts'],
                            'required' => true
                        ],
                        [
                            'name' => 'clinical_indication',
                            'label' => 'Clinical Indication',
                            'type' => 'textarea',
                            'required' => true
                        ]
                    ]
                ]),
                'sample_type' => null,
                'turnaround_time_hours' => 1,
                'preparation_instructions' => 'Preparation depends on examination area',
                'is_active' => 1
            ],
            [
                'name' => 'Electrocardiography (ECG)',
                'code' => 'RAD_ECG_001',
                'description' => '12-lead electrocardiogram',
                'service_category_id' => 3,
                'price' => 20000.00,
                'insurance_covered_amount' => 15000.00,
                'requires_sample' => 0,
                'requires_form' => 0,
                'form_fields' => null,
                'sample_type' => null,
                'turnaround_time_hours' => 1,
                'preparation_instructions' => 'Remove jewelry and metal objects',
                'is_active' => 1
            ],
            [
                'name' => 'X-Ray Examination',
                'code' => 'RAD_XRAY_001',
                'description' => 'General X-ray imaging',
                'service_category_id' => 2,
                'price' => 35000.00,
                'insurance_covered_amount' => 30000.00,
                'requires_sample' => 0,
                'requires_form' => 1,
                'form_fields' => json_encode([
                    'form_type' => 'xray',
                    'form_title' => 'X-Ray Examination Form',
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
                            'name' => 'pregnancy_status',
                            'label' => 'Pregnancy Status (if applicable)',
                            'type' => 'select',
                            'options' => ['Not Applicable', 'Pregnant', 'Not Pregnant', 'Unknown'],
                            'required' => false
                        ]
                    ]
                ]),
                'sample_type' => null,
                'turnaround_time_hours' => 1,
                'preparation_instructions' => 'Remove metal objects from examination area',
                'is_active' => 1
            ]
        ];

        foreach ($services as $service) {
            $service['created_at'] = $now;
            $service['updated_at'] = $now;
            
            DB::table('medical_services')->insert($service);
        }
    }
}
