<?php

return [
    // Legacy MTUHA section definitions. Editable without code changes.
    // Each section has an index (Roman) or null, a title and an array of id ranges.
    'sections' => [
        ['index' => null, 'title' => 'Diagnosis za OPD', 'ranges' => []],
        ['index' => 'I', 'title' => 'Infections and Parasitic diseases', 'ranges' => [['min' => 1, 'max' => 13], ['min' => 15, 'max' => 20]]],
        ['index' => 'II', 'title' => 'Neoplasms', 'ranges' => [['min' => 21, 'max' => 21]]],
        ['index' => 'III', 'title' => 'Diseases of Blood and blood forming Organs', 'ranges' => [['min' => 22, 'max' => 26]]],
        ['index' => 'IV', 'title' => 'Endocrine, Nutritional and Metabolic Diseases', 'ranges' => [['min' => 27, 'max' => 35]]],
        ['index' => 'V', 'title' => 'Mental and Behavioral Disorders', 'ranges' => [['min' => 36, 'max' => 40]]],
        ['index' => 'VI', 'title' => 'Diseases of the Nervous System', 'ranges' => [['min' => 41, 'max' => 42]]],
        ['index' => 'VII', 'title' => 'Diseases of the Eye', 'ranges' => [['min' => 43, 'max' => 46]]],
        ['index' => 'VIII', 'title' => 'Diseases of the Ear and Mastoid Process', 'ranges' => [['min' => 47, 'max' => 49]]],
        ['index' => 'IX', 'title' => 'Diseases of the Circulatory System', 'ranges' => [['min' => 50, 'max' => 52]]],
        ['index' => 'X', 'title' => 'Diseases of the Respiratory System', 'ranges' => [['min' => 53, 'max' => 57]]],
        ['index' => 'XI', 'title' => 'Diseases of the Digestive System', 'ranges' => [['min' => 58, 'max' => 67]]],
        ['index' => 'XII', 'title' => 'Diseases of the Skin and Subcutaneous Tissue', 'ranges' => [['min' => 68, 'max' => 72]]],
        ['index' => 'XIII', 'title' => 'Diseases of the Musculoskeletal System and Connective Tissue', 'ranges' => [['min' => 73, 'max' => 78]]],
        ['index' => 'XIV', 'title' => 'Diseases of the Genitourinary System and Pelvic Infalammatory diseases', 'ranges' => [['min' => 79, 'max' => 87]]],
        ['index' => 'XV', 'title' => 'Pregnancy, Childbirth and the Puerperium', 'ranges' => [['min' => 88, 'max' => 95]]],
        ['index' => 'XVI', 'title' => 'Certain Conditions Originating in the Perinatal Period', 'ranges' => [['min' => 96, 'max' => 99]]],
        ['index' => 'XVII', 'title' => 'Congenital Malformations, Deformations and Chromosomal Abnormalities', 'ranges' => [['min' => 100, 'max' => 101]]],
        ['index' => 'XVIII', 'title' => 'Symptoms, Signs and Abnormal Clinical and Laboratory Findings, Not Elsewhere Classified', 'ranges' => [['min' => 0, 'max' => 0]]],
        ['index' => 'XIX', 'title' => 'Injury, Poisoning and Certain Other Consequences of External Causes', 'ranges' => [['min' => 102, 'max' => 111]]],
        ['index' => 'XX', 'title' => 'External Causes of Morbidity and Mortality', 'ranges' => [['min' => 112, 'max' => 115]]],
        ['index' => null, 'title' => 'Matokeo', 'ranges' => [['min' => 116, 'max' => 117]]],
        ['index' => null, 'title' => 'Ugharamiaji wa Matibabu', 'ranges' => [['min' => 118, 'max' => 122]]],
    ],
];
