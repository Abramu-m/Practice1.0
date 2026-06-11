<?php

/**
 * Constrained list of selectable parameters per lab result template (result_templates.code).
 *
 * Used by App\Services\CDS\ResultParameterCatalog to:
 *  - populate the "Parameter" picker for Lab Critical Value CDS rules
 *  - normalize submitted lab results into result.parameters.<key> for CDS evaluation
 *
 * 'label' must match the parameter label used in the corresponding result template Blade file
 * (resources/views/lab/result_templates/*.blade.php) so values can be matched back to a key.
 * 'unit' of null means the unit is taken from the medical service's configured unit at runtime.
 */

return [

    'full_blood_picture' => [
        ['key' => 'hemoglobin', 'label' => 'Haemoglobin (Hb)', 'unit' => 'g/dL'],
        ['key' => 'hematocrit', 'label' => 'Haematocrit (HCT / PCV)', 'unit' => '%'],
        ['key' => 'rbc_count', 'label' => 'RBC Count', 'unit' => '×10⁶/µL'],
        ['key' => 'mcv', 'label' => 'MCV', 'unit' => 'fL'],
        ['key' => 'mch', 'label' => 'MCH', 'unit' => 'pg'],
        ['key' => 'mchc', 'label' => 'MCHC', 'unit' => 'g/dL'],
        ['key' => 'rdw', 'label' => 'RDW', 'unit' => '%'],
        ['key' => 'reticulocytes', 'label' => 'Reticulocytes', 'unit' => '%'],
        ['key' => 'total_wbc', 'label' => 'Total WBC', 'unit' => '×10³/µL'],
        ['key' => 'neutrophils', 'label' => 'Neutrophils', 'unit' => '×10³/µL'],
        ['key' => 'neutrophils_pct', 'label' => 'Neutrophils %', 'unit' => '%'],
        ['key' => 'lymphocytes', 'label' => 'Lymphocytes', 'unit' => '×10³/µL'],
        ['key' => 'lymphocytes_pct', 'label' => 'Lymphocytes %', 'unit' => '%'],
        ['key' => 'monocytes', 'label' => 'Monocytes', 'unit' => '×10³/µL'],
        ['key' => 'monocytes_pct', 'label' => 'Monocytes %', 'unit' => '%'],
        ['key' => 'eosinophils', 'label' => 'Eosinophils', 'unit' => '×10³/µL'],
        ['key' => 'eosinophils_pct', 'label' => 'Eosinophils %', 'unit' => '%'],
        ['key' => 'basophils', 'label' => 'Basophils', 'unit' => '×10³/µL'],
        ['key' => 'basophils_pct', 'label' => 'Basophils %', 'unit' => '%'],
        ['key' => 'band_neutrophils_pct', 'label' => 'Band Neutrophils %', 'unit' => '%'],
        ['key' => 'platelet_count', 'label' => 'Platelet Count', 'unit' => '×10³/µL'],
        ['key' => 'mpv', 'label' => 'MPV', 'unit' => 'fL'],
        ['key' => 'pdw', 'label' => 'PDW', 'unit' => 'fL'],
        ['key' => 'red_cell_morphology', 'label' => 'Red Cell Morphology', 'unit' => null],
        ['key' => 'platelet_morphology', 'label' => 'Platelet Morphology', 'unit' => null],
        ['key' => 'wbc_morphology_comment', 'label' => 'WBC Morphology / Comment', 'unit' => null],
    ],

    'urinalysis' => [
        ['key' => 'color', 'label' => 'Color', 'unit' => null],
        ['key' => 'clarity_turbidity', 'label' => 'Clarity / Turbidity', 'unit' => null],
        ['key' => 'odor', 'label' => 'Odor', 'unit' => null],
        ['key' => 'specific_gravity', 'label' => 'Specific Gravity', 'unit' => null],
        ['key' => 'ph', 'label' => 'pH', 'unit' => null],
        ['key' => 'protein', 'label' => 'Protein', 'unit' => 'mg/dL'],
        ['key' => 'glucose', 'label' => 'Glucose', 'unit' => null],
        ['key' => 'ketones', 'label' => 'Ketones', 'unit' => null],
        ['key' => 'bilirubin', 'label' => 'Bilirubin', 'unit' => null],
        ['key' => 'urobilinogen', 'label' => 'Urobilinogen', 'unit' => 'mg/dL'],
        ['key' => 'nitrites', 'label' => 'Nitrites', 'unit' => null],
        ['key' => 'leukocyte_esterase', 'label' => 'Leukocyte Esterase', 'unit' => null],
        ['key' => 'rbcs', 'label' => 'RBCs', 'unit' => '/hpf'],
        ['key' => 'rbc_casts', 'label' => 'RBC Casts', 'unit' => null],
        ['key' => 'wbcs', 'label' => 'WBCs', 'unit' => '/hpf'],
        ['key' => 'wbc_casts', 'label' => 'WBC Casts', 'unit' => null],
        ['key' => 'epithelial_cells', 'label' => 'Epithelial Cells', 'unit' => '/hpf'],
        ['key' => 'casts', 'label' => 'Casts', 'unit' => null],
        ['key' => 'crystals', 'label' => 'Crystals', 'unit' => null],
        ['key' => 'bacteria', 'label' => 'Bacteria', 'unit' => null],
        ['key' => 'yeast', 'label' => 'Yeast', 'unit' => null],
    ],

    // Generic single-value lab tests (e.g. ESR, Random Blood Glucose). Unit is taken from
    // the medical service's configured unit since it varies per test.
    'single_numeric_lab' => [
        ['key' => 'value', 'label' => 'Result Value', 'unit' => null],
    ],

    'vital_observations' => [
        ['key' => 'systolic_bp', 'label' => 'Systolic BP', 'unit' => 'mmHg'],
        ['key' => 'diastolic_bp', 'label' => 'Diastolic BP', 'unit' => 'mmHg'],
        ['key' => 'heart_rate', 'label' => 'Heart Rate', 'unit' => 'bpm'],
        ['key' => 'respiratory_rate', 'label' => 'Respiratory Rate', 'unit' => 'breaths/min'],
        ['key' => 'temperature', 'label' => 'Temperature', 'unit' => '°C'],
        ['key' => 'oxygen_saturation', 'label' => 'Oxygen Saturation', 'unit' => '%'],
        ['key' => 'weight', 'label' => 'Weight', 'unit' => 'kg'],
        ['key' => 'height', 'label' => 'Height', 'unit' => 'cm'],
        ['key' => 'bmi', 'label' => 'BMI', 'unit' => 'kg/m²'],
        ['key' => 'pain_scale', 'label' => 'Pain Scale', 'unit' => null],
    ],

];
