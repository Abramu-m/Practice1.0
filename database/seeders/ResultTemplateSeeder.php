<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ResultTemplate;
use App\Models\ServiceCategory;
use App\Models\MedicalService;

class ResultTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get service categories to link templates
        $laboratoryCategory = ServiceCategory::where('name', 'LIKE', '%Laboratory%')->first();
        $radiologyCategory = ServiceCategory::where('name', 'LIKE', '%Radiology%')->first();
        $procedureCategory = ServiceCategory::where('name', 'Procedures')->first();

        $templates = [
            [
                'name' => 'Simple Procedure Report',
                'code' => MedicalService::TEMPLATE_SIMPLE_PROCEDURE,
                'description' => 'Basic procedure report entry form for simple medical procedures',
                'service_category_id' => $procedureCategory?->id,
                'investigation_type' => 'Procedure',
                'template_fields' => [
                    'fields' => [
                        ['name' => 'procedure_outcome', 'type' => 'select', 'options' => ['successful', 'partially_successful', 'unsuccessful'], 'required' => true],
                        ['name' => 'findings', 'type' => 'textarea', 'required' => true],
                        ['name' => 'complications', 'type' => 'textarea', 'required' => false],
                        ['name' => 'recommendations', 'type' => 'textarea', 'required' => false]
                    ]
                ],
                'sort_order' => 1
            ],
            [
                'name' => 'Vital Signs & Observations',
                'code' => MedicalService::TEMPLATE_VITAL_OBSERVATIONS,
                'description' => 'Bedrest monitoring and vital signs tracking template',
                'service_category_id' => null, // Can be used by any category
                'investigation_type' => 'Monitoring',
                'template_fields' => [
                    'fields' => [
                        ['name' => 'blood_pressure', 'type' => 'text', 'unit' => 'mmHg', 'required' => true],
                        ['name' => 'heart_rate', 'type' => 'number', 'unit' => 'bpm', 'required' => true],
                        ['name' => 'temperature', 'type' => 'number', 'unit' => '°C', 'required' => true],
                        ['name' => 'respiratory_rate', 'type' => 'number', 'unit' => 'breaths/min', 'required' => true],
                        ['name' => 'oxygen_saturation', 'type' => 'number', 'unit' => '%', 'required' => false],
                        ['name' => 'observations', 'type' => 'textarea', 'required' => false]
                    ]
                ],
                'sort_order' => 2
            ],
            [
                'name' => 'Complex Assessment Form',
                'code' => MedicalService::TEMPLATE_COMPLEX_FORM,
                'description' => 'Comprehensive assessment questionnaire for detailed evaluations',
                'service_category_id' => null,
                'investigation_type' => 'Assessment',
                'template_fields' => [
                    'sections' => [
                        [
                            'title' => 'Clinical Assessment',
                            'fields' => [
                                ['name' => 'chief_complaint', 'type' => 'textarea', 'required' => true],
                                ['name' => 'clinical_findings', 'type' => 'textarea', 'required' => true],
                                ['name' => 'assessment', 'type' => 'textarea', 'required' => true]
                            ]
                        ],
                        [
                            'title' => 'Recommendations',
                            'fields' => [
                                ['name' => 'treatment_plan', 'type' => 'textarea', 'required' => true],
                                ['name' => 'follow_up', 'type' => 'textarea', 'required' => false]
                            ]
                        ]
                    ]
                ],
                'sort_order' => 3
            ],
            [
                'name' => 'Imaging Results',
                'code' => MedicalService::TEMPLATE_IMAGING,
                'description' => 'Radiology and imaging results reporting template',
                'service_category_id' => $radiologyCategory?->id,
                'investigation_type' => 'Radiology',
                'template_fields' => [
                    'fields' => [
                        ['name' => 'imaging_technique', 'type' => 'text', 'required' => true],
                        ['name' => 'findings', 'type' => 'textarea', 'required' => true],
                        ['name' => 'impression', 'type' => 'textarea', 'required' => true],
                        ['name' => 'recommendations', 'type' => 'textarea', 'required' => false],
                        ['name' => 'comparison', 'type' => 'textarea', 'required' => false]
                    ]
                ],
                'sort_order' => 4
            ],
            [
                'name' => 'General Procedure',
                'code' => MedicalService::TEMPLATE_GENERAL_PROCEDURE,
                'description' => 'Standard procedure results entry form',
                'service_category_id' => $procedureCategory?->id,
                'investigation_type' => 'Procedure',
                'template_fields' => [
                    'fields' => [
                        ['name' => 'procedure_performed', 'type' => 'text', 'required' => true],
                        ['name' => 'findings', 'type' => 'textarea', 'required' => true],
                        ['name' => 'outcome', 'type' => 'select', 'options' => ['successful', 'complications', 'incomplete'], 'required' => true],
                        ['name' => 'notes', 'type' => 'textarea', 'required' => false]
                    ]
                ],
                'sort_order' => 5
            ],
            [
                'name' => 'Simple Lab Values',
                'code' => MedicalService::TEMPLATE_SIMPLE_LAB,
                'description' => 'Basic laboratory parameter values and results',
                'service_category_id' => $laboratoryCategory?->id,
                'investigation_type' => 'Laboratory',
                'template_fields' => [
                    'fields' => [
                        ['name' => 'parameters', 'type' => 'dynamic_table', 'columns' => ['parameter', 'value', 'unit', 'reference_range', 'status'], 'required' => true],
                        ['name' => 'comments', 'type' => 'textarea', 'required' => false]
                    ]
                ],
                'sort_order' => 6
            ],
            [
                'name' => 'CD4 Count Results',
                'code' => MedicalService::TEMPLATE_CD4,
                'description' => 'CD4 count laboratory findings and interpretation',
                'service_category_id' => $laboratoryCategory?->id,
                'investigation_type' => 'Laboratory',
                'template_fields' => [
                    'fields' => [
                        ['name' => 'cd4_count', 'type' => 'number', 'unit' => 'cells/μL', 'required' => true],
                        ['name' => 'cd4_percentage', 'type' => 'number', 'unit' => '%', 'required' => false],
                        ['name' => 'total_lymphocytes', 'type' => 'number', 'unit' => 'cells/μL', 'required' => false],
                        ['name' => 'interpretation', 'type' => 'textarea', 'required' => true],
                        ['name' => 'recommendations', 'type' => 'textarea', 'required' => false]
                    ]
                ],
                'sort_order' => 7
            ],
            [
                'name' => 'TB Investigation',
                'code' => MedicalService::TEMPLATE_TB,
                'description' => 'Tuberculosis investigation results and findings',
                'service_category_id' => $laboratoryCategory?->id,
                'investigation_type' => 'Laboratory',
                'template_fields' => [
                    'fields' => [
                        ['name' => 'specimen_type', 'type' => 'select', 'options' => ['sputum', 'pleural_fluid', 'csf', 'urine', 'other'], 'required' => true],
                        ['name' => 'microscopy_result', 'type' => 'select', 'options' => ['negative', '1+', '2+', '3+'], 'required' => true],
                        ['name' => 'culture_result', 'type' => 'select', 'options' => ['pending', 'negative', 'positive', 'contaminated'], 'required' => false],
                        ['name' => 'genexpert_result', 'type' => 'select', 'options' => ['not_done', 'mtb_detected', 'mtb_not_detected', 'error'], 'required' => false],
                        ['name' => 'rifampicin_resistance', 'type' => 'select', 'options' => ['not_detected', 'detected', 'indeterminate'], 'required' => false],
                        ['name' => 'clinical_correlation', 'type' => 'textarea', 'required' => false]
                    ]
                ],
                'sort_order' => 8
            ],
            [
                'name' => 'General Lab Results',
                'code' => MedicalService::TEMPLATE_GENERAL_LAB,
                'description' => 'Comprehensive laboratory results with multiple parameters',
                'service_category_id' => $laboratoryCategory?->id,
                'investigation_type' => 'Laboratory',
                'template_fields' => [
                    'fields' => [
                        ['name' => 'lab_serial_no', 'type' => 'text', 'required' => true],
                        ['name' => 'specimen_type', 'type' => 'text', 'required' => true],
                        ['name' => 'collection_date', 'type' => 'datetime', 'required' => true],
                        ['name' => 'parameters', 'type' => 'dynamic_table', 'columns' => ['parameter', 'value', 'unit', 'reference_range', 'status'], 'required' => true],
                        ['name' => 'interpretation', 'type' => 'textarea', 'required' => false],
                        ['name' => 'recommendations', 'type' => 'textarea', 'required' => false],
                        ['name' => 'technician', 'type' => 'text', 'required' => true],
                        ['name' => 'pathologist', 'type' => 'text', 'required' => false]
                    ]
                ],
                'sort_order' => 9
            ]
        ];

        foreach ($templates as $templateData) {
            ResultTemplate::updateOrCreate(
                ['code' => $templateData['code']],
                $templateData
            );
        }

        $this->command->info('Result templates seeded successfully!');
    }
}
