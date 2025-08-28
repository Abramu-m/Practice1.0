<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Icd10;

class Icd10Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $icd10Codes = [
            // Infectious Diseases
            ['code' => 'A00.0', 'description' => 'Cholera due to Vibrio cholerae 01, biovar cholerae', 'category' => 'Infectious Diseases', 'subcategory' => 'Cholera'],
            ['code' => 'A09', 'description' => 'Infectious gastroenteritis and colitis, unspecified', 'category' => 'Infectious Diseases', 'subcategory' => 'Gastroenteritis'],
            ['code' => 'A15.0', 'description' => 'Tuberculosis of lung', 'category' => 'Infectious Diseases', 'subcategory' => 'Tuberculosis'],
            ['code' => 'A16.9', 'description' => 'Respiratory tuberculosis, unspecified', 'category' => 'Infectious Diseases', 'subcategory' => 'Tuberculosis'],
            
            // Neoplasms
            ['code' => 'C50.9', 'description' => 'Malignant neoplasm of breast, unspecified', 'category' => 'Neoplasms', 'subcategory' => 'Breast Cancer'],
            ['code' => 'C78.0', 'description' => 'Secondary malignant neoplasm of lung', 'category' => 'Neoplasms', 'subcategory' => 'Secondary Neoplasms'],
            ['code' => 'C80.1', 'description' => 'Malignant neoplasm, unspecified', 'category' => 'Neoplasms', 'subcategory' => 'Unspecified'],
            
            // Endocrine, Nutritional and Metabolic Diseases
            ['code' => 'E10.9', 'description' => 'Type 1 diabetes mellitus without complications', 'category' => 'Endocrine Diseases', 'subcategory' => 'Diabetes'],
            ['code' => 'E11.9', 'description' => 'Type 2 diabetes mellitus without complications', 'category' => 'Endocrine Diseases', 'subcategory' => 'Diabetes'],
            ['code' => 'E11.65', 'description' => 'Type 2 diabetes mellitus with hyperglycemia', 'category' => 'Endocrine Diseases', 'subcategory' => 'Diabetes'],
            ['code' => 'E78.5', 'description' => 'Hyperlipidemia, unspecified', 'category' => 'Endocrine Diseases', 'subcategory' => 'Lipid Disorders'],
            
            // Mental and Behavioral Disorders
            ['code' => 'F32.9', 'description' => 'Major depressive disorder, single episode, unspecified', 'category' => 'Mental Disorders', 'subcategory' => 'Depression'],
            ['code' => 'F41.1', 'description' => 'Generalized anxiety disorder', 'category' => 'Mental Disorders', 'subcategory' => 'Anxiety'],
            ['code' => 'F43.10', 'description' => 'Post-traumatic stress disorder, unspecified', 'category' => 'Mental Disorders', 'subcategory' => 'PTSD'],
            
            // Circulatory System
            ['code' => 'I10', 'description' => 'Essential (primary) hypertension', 'category' => 'Circulatory System', 'subcategory' => 'Hypertension'],
            ['code' => 'I25.10', 'description' => 'Atherosclerotic heart disease of native coronary artery without angina pectoris', 'category' => 'Circulatory System', 'subcategory' => 'Coronary Disease'],
            ['code' => 'I50.9', 'description' => 'Heart failure, unspecified', 'category' => 'Circulatory System', 'subcategory' => 'Heart Failure'],
            
            // Respiratory System
            ['code' => 'J00', 'description' => 'Acute nasopharyngitis [common cold]', 'category' => 'Respiratory System', 'subcategory' => 'Upper Respiratory'],
            ['code' => 'J06.9', 'description' => 'Acute upper respiratory infection, unspecified', 'category' => 'Respiratory System', 'subcategory' => 'Upper Respiratory'],
            ['code' => 'J44.0', 'description' => 'Chronic obstructive pulmonary disease with acute lower respiratory infection', 'category' => 'Respiratory System', 'subcategory' => 'COPD'],
            ['code' => 'J45.9', 'description' => 'Asthma, unspecified', 'category' => 'Respiratory System', 'subcategory' => 'Asthma'],
            
            // Digestive System
            ['code' => 'K21.9', 'description' => 'Gastro-esophageal reflux disease without esophagitis', 'category' => 'Digestive System', 'subcategory' => 'GERD'],
            ['code' => 'K29.70', 'description' => 'Gastritis, unspecified, without bleeding', 'category' => 'Digestive System', 'subcategory' => 'Gastritis'],
            ['code' => 'K59.00', 'description' => 'Constipation, unspecified', 'category' => 'Digestive System', 'subcategory' => 'Constipation'],
            
            // Musculoskeletal System
            ['code' => 'M79.3', 'description' => 'Panniculitis, unspecified', 'category' => 'Musculoskeletal System', 'subcategory' => 'Soft Tissue'],
            ['code' => 'M25.50', 'description' => 'Pain in unspecified joint', 'category' => 'Musculoskeletal System', 'subcategory' => 'Joint Pain'],
            ['code' => 'M54.5', 'description' => 'Low back pain', 'category' => 'Musculoskeletal System', 'subcategory' => 'Back Pain'],
            
            // Genitourinary System
            ['code' => 'N39.0', 'description' => 'Urinary tract infection, site not specified', 'category' => 'Genitourinary System', 'subcategory' => 'UTI'],
            ['code' => 'N18.6', 'description' => 'End stage renal disease', 'category' => 'Genitourinary System', 'subcategory' => 'Kidney Disease'],
            
            // Symptoms and Signs
            ['code' => 'R06.02', 'description' => 'Shortness of breath', 'category' => 'Symptoms and Signs', 'subcategory' => 'Respiratory Symptoms'],
            ['code' => 'R50.9', 'description' => 'Fever, unspecified', 'category' => 'Symptoms and Signs', 'subcategory' => 'Constitutional'],
            ['code' => 'R51', 'description' => 'Headache', 'category' => 'Symptoms and Signs', 'subcategory' => 'Neurological Symptoms'],
            ['code' => 'R06.00', 'description' => 'Dyspnea, unspecified', 'category' => 'Symptoms and Signs', 'subcategory' => 'Respiratory Symptoms'],
            
            // Injury and Poisoning
            ['code' => 'S72.001A', 'description' => 'Fracture of unspecified part of neck of right femur, initial encounter', 'category' => 'Injury and Poisoning', 'subcategory' => 'Fractures'],
            ['code' => 'T14.90XA', 'description' => 'Injury, unspecified, initial encounter', 'category' => 'Injury and Poisoning', 'subcategory' => 'Unspecified Injury'],
        ];

        foreach ($icd10Codes as $code) {
            Icd10::create($code);
        }
    }
}
