-- Adapted from ../2.results.sql for the online migration (see ../9.migration_runbook.md).
-- Source : yyfcolmy_medcom (unchanged). Target: yyfcolmy_brigita_practice.
-- No Section A removals needed here — every INSERT targets
-- investigation_template_results (Section B), joined against the
-- investigations rows created by online/1.first.sql. Run AFTER 1.first.sql.

SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));

SET SESSION group_concat_max_len = 1000000;

INSERT INTO yyfcolmy_brigita_practice.`investigation_template_results`
    (`id`, `investigation_id`, `template_name`, `template_version`, `form_data`,
     `form_status`, `metadata`, `reported_by`, `reported_at`, `verified_by`, `verified_at`,
     `created_at`, `updated_at`)

SELECT
    NULL,
    src1.pl_id,

    /* ── template_name ─────────────────────────────────────────────────────── */
    CASE
        WHEN src3.result = 2  THEN 'Qualitative Positive Negative'
        WHEN src3.result = 3  THEN 'Long Text'
        WHEN src3.result = 5  THEN 'Single Numeric Lab Values'
        WHEN src3.result = 6  THEN 'One Line'
        WHEN src3.result = 7  THEN 'Urinalysis'
        WHEN src3.result = 8  THEN 'Wet Preparation Microscopy'
        WHEN src3.result = 9  THEN 'Stool Analysis'
        WHEN src3.result = 10 THEN 'Multistix'
        WHEN src3.result = 12 THEN 'Full Blood Picture'
        WHEN src3.result = 13 THEN 'Spermiogram'
        WHEN src3.result = 14 THEN 'Full Blood Picture'
        WHEN src3.result = 16 THEN 'Anamnesis for Sterility Patients'
        ELSE 'LEGACY'
    END AS template_name,

    1 AS template_version,

    /* ── form_data ──────────────────────────────────────────────────────────── */
    JSON_OBJECT(
        'parameters', JSON_ARRAYAGG(
            JSON_OBJECT(

                /* ── parameter_name ── */
                'parameter_name',
                CASE src3.result
                    WHEN 2  THEN CONVERT(src3.svdescription USING utf8mb4)
                    WHEN 3  THEN CONVERT(src3.svdescription USING utf8mb4)
                    WHEN 5  THEN CONVERT(src3.svdescription USING utf8mb4)
                    WHEN 6  THEN CONVERT(src3.svdescription USING utf8mb4)

                    WHEN 7  THEN CASE CONVERT(src1.perimeter USING utf8mb4)
                        WHEN 'Specific Gravidity' THEN 'Specific Gravity'
                        WHEN 'pH'                 THEN 'pH'
                        WHEN 'Protein'            THEN 'Protein'
                        WHEN 'Glucose'            THEN 'Glucose'
                        WHEN 'Keton'              THEN 'Ketones'
                        WHEN 'Bilirubin'          THEN 'Bilirubin'
                        WHEN 'Urobilinogen'       THEN 'Urobilinogen'
                        WHEN 'Nitrite'            THEN 'Nitrites'
                        WHEN 'Leucocytes'         THEN 'Leukocyte Esterase'
                        WHEN 'Blood'              THEN 'Blood'
                        WHEN 'T vaginalis'        THEN 'T. vaginalis'
                        WHEN 'Leucos/HPF'         THEN 'WBCs'
                        WHEN 'Erys/HPF'           THEN 'RBCs'
                        WHEN 'Sch haem'           THEN 'RBC Casts'
                        WHEN 'epith'              THEN 'Epithelial Cells'
                        WHEN 'Granular Cast'      THEN 'Casts'
                        WHEN 'Glomerular Cast'    THEN 'Casts (Glomerular)'
                        WHEN 'Calcium Oxalate'    THEN 'Crystals'
                        WHEN 'Sperms'             THEN 'Sperms'
                        ELSE CONVERT(src1.perimeter USING utf8mb4)
                    END

                    WHEN 8  THEN CASE CONVERT(src1.perimeter USING utf8mb4)
                        WHEN 'T vaginalis'      THEN 'T. vaginalis'
                        WHEN 'Leucos/HPF'       THEN 'WBCs'
                        WHEN 'Erys/HPF'         THEN 'RBCs'
                        WHEN 'Epithelia cells'  THEN 'Epithelial Cells'
                        ELSE CONVERT(src1.perimeter USING utf8mb4)
                    END

                    WHEN 9  THEN CASE CONVERT(src1.perimeter USING utf8mb4)
                        WHEN 'Pus cells'  THEN 'Pus Cells'
                        WHEN 'RBC'        THEN 'RBC'
                        WHEN 'Hookworms'  THEN 'Hookworms'
                        WHEN 'Ascaris'    THEN 'Ascaris lumbricoides'
                        WHEN 'Amoeba'     THEN 'Amoeba cysts'
                        WHEN 'Enterobius' THEN 'Enterobius vermicularis'
                        WHEN 'hominis'    THEN 'Trichomonas hominis'
                        WHEN 'Giardia'    THEN 'Giardia lamblia'
                        WHEN 'mansoni'    THEN 'Schistosomiasis mansoni'
                        WHEN 'Taenia'     THEN 'Taenia solium'
                        WHEN 'Trichuris'  THEN 'Trichuris trichiura'
                        ELSE CONVERT(src1.perimeter USING utf8mb4)
                    END

                    WHEN 10 THEN CASE CONVERT(src1.perimeter USING utf8mb4)
                        WHEN 'Specific Gravidity' THEN 'Specific Gravity'
                        WHEN 'pH'                 THEN 'pH'
                        WHEN 'Protein'            THEN 'Protein'
                        WHEN 'Glucose'            THEN 'Glucose'
                        WHEN 'Keton'              THEN 'Ketones'
                        WHEN 'Bilirubin'          THEN 'Bilirubin'
                        WHEN 'Urobilinogen'       THEN 'Urobilinogen'
                        WHEN 'Nitrite'            THEN 'Nitrites'
                        WHEN 'Leucocytes'         THEN 'Leukocyte Esterase'
                        WHEN 'Blood'              THEN 'Blood'
                        ELSE CONVERT(src1.perimeter USING utf8mb4)
                    END

                    WHEN 12 THEN CASE CONVERT(src1.perimeter USING utf8mb4)
                        WHEN 'hb'       THEN 'Haemoglobin (Hb)'
                        WHEN 'hct'      THEN 'Haematocrit (HCT / PCV)'
                        WHEN 'rbc'      THEN 'RBC Count'
                        WHEN 'mcv'      THEN 'MCV'
                        WHEN 'mch'      THEN 'MCH'
                        WHEN 'mchc'     THEN 'MCHC'
                        WHEN 'rdwcv'    THEN 'RDW'
                        WHEN 'rdwsd'    THEN 'RDW-SD'
                        WHEN 'wbc'      THEN 'Total WBC'
                        WHEN 'lymnum'   THEN 'Lymphocytes'
                        WHEN 'lymperc'  THEN 'Lymphocytes %'
                        WHEN 'grannum'  THEN 'Neutrophils'
                        WHEN 'granperc' THEN 'Neutrophils %'
                        WHEN 'midnum'   THEN 'Monocytes'
                        WHEN 'midperc'  THEN 'Monocytes %'
                        WHEN 'plt'      THEN 'Platelet Count'
                        WHEN 'mpv'      THEN 'MPV'
                        WHEN 'pdw'      THEN 'PDW'
                        WHEN 'plcc'     THEN 'PLCC'
                        WHEN 'plcr'     THEN 'PLCR'
                        WHEN 'pct'      THEN 'PCT'
                        WHEN 'flags'    THEN 'Flags'
                        ELSE CONVERT(src1.perimeter USING utf8mb4)
                    END

                    WHEN 13 THEN CASE CONVERT(src1.perimeter USING utf8mb4)
                        WHEN 'sp_quality'  THEN 'Specimen Quality'
                        WHEN 'reason'      THEN 'Reason'
                        WHEN 'color'       THEN 'Color'
                        WHEN 'volume'      THEN 'Volume'
                        WHEN 'viscocity'   THEN 'Viscosity'
                        WHEN 'ph'          THEN 'pH'
                        WHEN 'spermcount'  THEN 'Sperm Count'
                        WHEN 'morphology'  THEN 'Morphology'
                        WHEN 'motility'    THEN 'Motility'
                        WHEN 'progressive' THEN 'Progressive'
                        WHEN 'pus'         THEN 'Pus Cells'
                        WHEN 'spermiogram' THEN 'Conclusion'
                        ELSE CONVERT(src1.perimeter USING utf8mb4)
                    END

                    WHEN 14 THEN CASE CONVERT(src1.perimeter USING utf8mb4)
                        WHEN 'wbctot'     THEN 'Total WBC'
                        WHEN 'neutrophil' THEN 'Neutrophils %'
                        WHEN 'lymphocyte' THEN 'Lymphocytes %'
                        WHEN 'monocyte'   THEN 'Monocytes %'
                        WHEN 'eosinophil' THEN 'Eosinophils %'
                        WHEN 'basophil'   THEN 'Basophils %'
                        ELSE CONVERT(src1.perimeter USING utf8mb4)
                    END

                    WHEN 16 THEN CASE CONVERT(src1.perimeter USING utf8mb4)
                        WHEN 'para'                            THEN 'Para'
                        WHEN 'delivery_year'                   THEN 'Years of Delivery'
                        WHEN 'abortion'                        THEN 'Abortions'
                        WHEN 'abortion_year'                   THEN 'Years of Abortion'
                        WHEN 'alive'                           THEN 'Alive'
                        WHEN 'd_c_eva'                         THEN 'D+C or EVA'
                        WHEN 'cd4'                             THEN 'CD4'
                        WHEN 'hvl'                             THEN 'HVL'
                        WHEN 'operation_hx'                    THEN 'Operations'
                        WHEN 'operations'                      THEN 'Which Operations'
                        WHEN 'hsg'                             THEN 'Hysterosalpingography'
                        WHEN 'hiv'                             THEN 'HIV/AIDS/ART'
                        WHEN 'husband_number'                  THEN '1st/2nd/3rd.. Husband'
                        WHEN 'orchitis'                        THEN 'History of Orchitis'
                        WHEN 'father'                          THEN 'Husband is Father of Children'
                        WHEN 'pitc'                            THEN 'PITC'
                        WHEN 'wives'                           THEN 'How Many Wives'
                        WHEN 'drug_intake'                     THEN 'Regular Drug Intake'
                        WHEN 'drug_name'                       THEN 'Drug Name'
                        WHEN 'wife_number'                     THEN 'She is the Nth Wife'
                        WHEN 'children_number'                 THEN 'Number of Children of Husband'
                        WHEN 'relationship_duration'           THEN 'Years with Partner'
                        WHEN 'lastborn_age'                    THEN 'Age of Lastborn Child'
                        WHEN 'husband_operation'               THEN 'Husband Operations'
                        WHEN 'operation_type'                  THEN 'Type of Husband Operations'
                        WHEN 'contraceptive_method_1'          THEN 'Contraceptive Method 1'
                        WHEN 'contraceptive_method_1_duration' THEN 'Contraceptive Method 1 Duration'
                        WHEN 'contraceptive_method_2'          THEN 'Contraceptive Method 2'
                        WHEN 'contraceptive_method_2_duration' THEN 'Contraceptive Method 2 Duration'
                        WHEN 'contraceptive_method_3'          THEN 'Contraceptive Method 3'
                        WHEN 'contraceptive_method_3_duration' THEN 'Contraceptive Method 3 Duration'
                        WHEN 'cycle_length'                    THEN 'Cycle Length'
                        WHEN 'num_of_days'                     THEN 'Menstrual Bleeding'
                        WHEN 'current_amenorrhea'              THEN 'Amenorrhea at the Moment'
                        WHEN 'cycle_changing'                  THEN 'Duration of Cycle Changing'
                        WHEN 'intermediate_bleeding'           THEN 'Intermediate Bleeding'
                        WHEN 'bleeding_intensity'              THEN 'Bleeding Intensity'
                        WHEN 'milk_discharge'                  THEN 'Milk Discharge'
                        WHEN 'previous_std'                    THEN 'Previous PID'
                        WHEN 'previous_std_year'               THEN 'When (PID)'
                        WHEN 'Dyspareunie'                     THEN 'Dyspareunie'
                        WHEN 'Dysmenstruation'                 THEN 'Dysmenstruation'
                        WHEN 'genital_itching'                 THEN 'Genital Itching'
                        WHEN 'wife_std'                        THEN 'Wife STD'
                        WHEN 'wife_std_disease'                THEN 'Wife STD Disease'
                        WHEN 'wife_std_year'                   THEN 'Wife STD Year'
                        WHEN 'husband_std'                     THEN 'Husband STD'
                        WHEN 'husband_std_disease'             THEN 'Husband STD Disease'
                        WHEN 'husband_std_year'                THEN 'Husband STD Year'
                        WHEN 'spermiogram'                     THEN 'Spermiogram'
                        ELSE CONVERT(src1.perimeter USING utf8mb4)
                    END

                    ELSE CONVERT(src3.svdescription USING utf8mb4)
                END,

                /* ── value ── */
                'value',
                CASE
                    WHEN src3.result = 5 THEN
                        NULLIF(
                            REGEXP_REPLACE(CONVERT(src1.preports USING utf8mb4), '[^0-9.]', ''),
                            ''
                        )
                    WHEN src3.result IN (2, 3, 6, 7, 8, 9, 10, 12, 13, 14, 16) THEN
                        CONVERT(src1.preports USING utf8mb4)
                    ELSE
                        /* Use CONVERT on perimeter for the comparison too */
                        IF(
                            CONVERT(src1.perimeter USING utf8mb4) <> '',
                            CONCAT(
                                CONVERT(src1.perimeter USING utf8mb4), ': ',
                                CONVERT(src1.preports  USING utf8mb4)
                            ),
                            CONVERT(src1.preports USING utf8mb4)
                        )
                END,

                /* ── unit ── */
                'unit',
                CASE
                    WHEN src3.result IN (2, 5) THEN CONVERT(src3.unit USING utf8mb4)
                    WHEN src3.result = 13 THEN CASE CONVERT(src1.perimeter USING utf8mb4)
                        WHEN 'volume'      THEN 'mL'
                        WHEN 'spermcount'  THEN 'million/mL'
                        WHEN 'morphology'  THEN '%'
                        WHEN 'motility'    THEN '%'
                        WHEN 'progressive' THEN '%'
                        ELSE NULL
                    END
                    ELSE NULL
                END,

                /* ── normal_range ── */
                'normal_range',
                CASE
                    WHEN src3.result = 5 THEN
                        /* CAST to CHAR to avoid any latin1 vs utf8mb4 clash from range columns */
                        CONCAT(
                            CAST(src3.range1 AS CHAR),
                            ' - ',
                            CAST(src3.range2 AS CHAR)
                        )
                    WHEN src3.result = 13 THEN CASE CONVERT(src1.perimeter USING utf8mb4)
                        WHEN 'volume'      THEN '1.5 - 4'
                        WHEN 'ph'          THEN '7.2 - 7.8'
                        WHEN 'spermcount'  THEN '20 - 60'
                        WHEN 'morphology'  THEN '> 80'
                        WHEN 'motility'    THEN '> 70'
                        WHEN 'progressive' THEN '> 60'
                        ELSE NULL
                    END
                    ELSE NULL
                END,

                /* ── status ── */
                'status',
                CASE
                    WHEN src3.result = 5 THEN
                        CASE
                            WHEN NULLIF(REGEXP_REPLACE(CONVERT(src1.preports USING utf8mb4), '[^0-9.]', ''), '') + 0 < src3.range1 THEN 'low'
                            WHEN NULLIF(REGEXP_REPLACE(CONVERT(src1.preports USING utf8mb4), '[^0-9.]', ''), '') + 0 > src3.range2 THEN 'high'
                            ELSE 'normal'
                        END

                    WHEN src3.result IN (2, 6) THEN
                        IF(LOWER(CONVERT(src1.preports USING utf8mb4)) LIKE '%pos%', 'abnormal', 'normal')

                    WHEN src3.result = 3 THEN NULL

                    WHEN src3.result = 7 THEN
                        CASE
                            WHEN CONVERT(src1.perimeter USING utf8mb4) IN (
                                'Leucos/HPF', 'Erys/HPF', 'Sch haem', 'Granular Cast',
                                'Glomerular Cast', 'Calcium Oxalate', 'epith', 'T vaginalis', 'Sperms'
                            ) THEN IF(CONVERT(src1.preports USING utf8mb4) IN ('None', '0', ''), 'normal', 'abnormal')
                            WHEN CONVERT(src1.perimeter USING utf8mb4) IN (
                                'Bilirubin', 'Blood', 'Glucose', 'Keton', 'Nitrite', 'Leucocytes'
                            ) THEN IF(CONVERT(src1.preports USING utf8mb4) LIKE 'Neg%' OR CONVERT(src1.preports USING utf8mb4) = '', 'normal', 'abnormal')
                            ELSE 'normal'
                        END

                    WHEN src3.result = 8 THEN
                        CASE CONVERT(src1.perimeter USING utf8mb4)
                            WHEN 'T vaginalis'     THEN IF(CONVERT(src1.preports USING utf8mb4) IN ('None', 'Negative', ''), 'normal', 'abnormal')
                            WHEN 'Leucos/HPF'      THEN IF(CONVERT(src1.preports USING utf8mb4) IN ('None', '0', ''), 'normal', 'abnormal')
                            WHEN 'Erys/HPF'        THEN IF(CONVERT(src1.preports USING utf8mb4) IN ('None', '0', ''), 'normal', 'abnormal')
                            WHEN 'Epithelia cells' THEN IF(CONVERT(src1.preports USING utf8mb4) IN ('None', '0', ''), 'normal', 'abnormal')
                            ELSE 'normal'
                        END

                    WHEN src3.result = 9 THEN
                        CASE CONVERT(src1.perimeter USING utf8mb4)
                            WHEN 'Pus cells' THEN IF(CONVERT(src1.preports USING utf8mb4) IN ('None', ''), 'normal', 'abnormal')
                            WHEN 'RBC'       THEN IF(CONVERT(src1.preports USING utf8mb4) IN ('None', ''), 'normal', 'abnormal')
                            ELSE                  IF(CONVERT(src1.preports USING utf8mb4) LIKE 'Neg%' OR CONVERT(src1.preports USING utf8mb4) = '', 'normal', 'abnormal')
                        END

                    WHEN src3.result = 10 THEN
                        CASE
                            WHEN CONVERT(src1.perimeter USING utf8mb4) IN (
                                'Bilirubin', 'Blood', 'Glucose', 'Keton', 'Nitrite', 'Leucocytes'
                            ) THEN IF(CONVERT(src1.preports USING utf8mb4) LIKE 'Neg%' OR CONVERT(src1.preports USING utf8mb4) = '', 'normal', 'abnormal')
                            ELSE 'normal'
                        END

                    WHEN src3.result IN (12, 14) THEN 'unknown'

                    WHEN src3.result = 13 THEN
                        CASE CONVERT(src1.perimeter USING utf8mb4)
                            WHEN 'sp_quality'  THEN IF(CONVERT(src1.preports USING utf8mb4) = 'Adequate',  'normal', 'abnormal')
                            WHEN 'viscocity'   THEN IF(CONVERT(src1.preports USING utf8mb4) = 'Liquefied', 'normal', 'abnormal')
                            WHEN 'pus'         THEN IF(CONVERT(src1.preports USING utf8mb4) = 'None',      'normal', 'abnormal')
                            WHEN 'spermiogram' THEN IF(CONVERT(src1.preports USING utf8mb4) LIKE 'Normo%', 'normal', 'abnormal')
                            WHEN 'volume'      THEN IF(NULLIF(REGEXP_REPLACE(CONVERT(src1.preports USING utf8mb4), '[^0-9.]', ''), '') + 0 BETWEEN 1.5 AND 4,   'normal', IF(NULLIF(REGEXP_REPLACE(CONVERT(src1.preports USING utf8mb4), '[^0-9.]', ''), '') + 0 < 1.5, 'low', 'high'))
                            WHEN 'ph'          THEN IF(NULLIF(REGEXP_REPLACE(CONVERT(src1.preports USING utf8mb4), '[^0-9.]', ''), '') + 0 BETWEEN 7.2 AND 7.8, 'normal', IF(NULLIF(REGEXP_REPLACE(CONVERT(src1.preports USING utf8mb4), '[^0-9.]', ''), '') + 0 < 7.2, 'low', 'high'))
                            WHEN 'spermcount'  THEN IF(NULLIF(REGEXP_REPLACE(CONVERT(src1.preports USING utf8mb4), '[^0-9.]', ''), '') + 0 BETWEEN 20 AND 60,   'normal', IF(NULLIF(REGEXP_REPLACE(CONVERT(src1.preports USING utf8mb4), '[^0-9.]', ''), '') + 0 < 20,  'low', 'high'))
                            WHEN 'morphology'  THEN IF(NULLIF(REGEXP_REPLACE(CONVERT(src1.preports USING utf8mb4), '[^0-9.]', ''), '') + 0 >= 80, 'normal', 'low')
                            WHEN 'motility'    THEN IF(NULLIF(REGEXP_REPLACE(CONVERT(src1.preports USING utf8mb4), '[^0-9.]', ''), '') + 0 >= 70, 'normal', 'low')
                            WHEN 'progressive' THEN IF(NULLIF(REGEXP_REPLACE(CONVERT(src1.preports USING utf8mb4), '[^0-9.]', ''), '') + 0 >= 60, 'normal', 'low')
                            ELSE 'normal'
                        END

                    WHEN src3.result = 16 THEN 'normal'

                    ELSE NULL
                END,

                /* ── remarks ── */
                'remarks', NULL

            )
        ),

        'analyzed_by',         src2.resulted_by,
        'analysis_date',       src2.resulted_at,
        'additional_comments', NULL
    ) AS form_data,

    'final' AS form_status,

    /* ── metadata ── */
    JSON_OBJECT(
        'template_code',
        CASE
            WHEN src3.result = 2  THEN 'qualitative_lab'
            WHEN src3.result = 3  THEN 'narrative_lab'
            WHEN src3.result = 5  THEN 'single_numeric_lab'
            WHEN src3.result = 6  THEN 'qualitative_lab'
            WHEN src3.result = 7  THEN 'urinalysis'
            WHEN src3.result = 8  THEN 'wet_prep_microscopy'
            WHEN src3.result = 9  THEN 'stool_analysis'
            WHEN src3.result = 10 THEN 'multistix'
            WHEN src3.result = 12 THEN 'full_blood_picture'
            WHEN src3.result = 13 THEN 'spermiogram'
            WHEN src3.result = 14 THEN 'full_blood_picture'
            WHEN src3.result = 16 THEN 'sterility_anamnesis'
            ELSE 'legacy'
        END
    ) AS metadata,

    COALESCE(src2.resulted_by, 23) AS reported_by,
    COALESCE(src2.resulted_at, NOW()) AS reported_at,
    COALESCE(src2.resulted_by, 23) AS verified_by,
    COALESCE(src2.resulted_at, NOW()) AS verified_at,
    COALESCE(src2.created_at, NOW()) AS created_at,
    COALESCE(src2.updated_at, NOW()) AS updated_at

FROM yyfcolmy_medcom.procedures AS src1
INNER JOIN yyfcolmy_brigita_practice.investigations AS src2
    ON src1.pl_id = src2.id
INNER JOIN yyfcolmy_medcom.services AS src3
    ON src3.svsvid = src2.medical_service_id

/* Malaria Blood Smear (svsvid=28) is migrated separately below as `pbs_malaria` —
   its legacy `result` code (6) is shared with mRDT (svsvid=29), so it can't be
   told apart by the generic result-code branching above. */
WHERE src3.svsvid <> 28

GROUP BY src1.pl_id;


/* ════════════════════════════════════════════════════════════════════════════
   Malaria Blood Smear {BS} (svsvid = 28) → `pbs_malaria` template
   ────────────────────────────────────────────────────────────────────────────
   Legacy results are free text: "Negative" / "neg." (356 rows) or a raw parasite
   count such as "10 mps" / "200mps/200wbcs" (36 rows) — never a graded parasitemia
   (+/++/+++/++++), species, gametocyte, or stage. The only fact the legacy data
   reliably carries is Seen vs. Not Seen, so that's the only finding we derive;
   everything else defaults to "not recorded" and the original raw text is kept
   verbatim in the Malaria Parasites parameter's remarks for audit/re-grading.
   ════════════════════════════════════════════════════════════════════════════ */
INSERT INTO yyfcolmy_brigita_practice.`investigation_template_results`
    (`id`, `investigation_id`, `template_name`, `template_version`, `form_data`,
     `form_status`, `metadata`, `reported_by`, `reported_at`, `verified_by`, `verified_at`,
     `created_at`, `updated_at`)

SELECT
    NULL,
    src1.pl_id,
    'PBS – Malaria Parasites' AS template_name,
    1 AS template_version,

    JSON_OBJECT(
        'parameters', JSON_ARRAY(
            JSON_OBJECT(
                'parameter_name', 'Malaria Parasites',
                'value',
                    IF(LOWER(TRIM(CONVERT(src1.preports USING utf8mb4))) LIKE 'neg%', 'Not Seen', 'Seen'),
                'unit', NULL,
                'normal_range', NULL,
                'status',
                    IF(LOWER(TRIM(CONVERT(src1.preports USING utf8mb4))) LIKE 'neg%', 'normal', 'abnormal'),
                'remarks', CONVERT(src1.preports USING utf8mb4)
            ),
            JSON_OBJECT(
                'parameter_name', 'Species',
                'value', 'Not Applicable',
                'unit', NULL,
                'normal_range', NULL,
                'status', 'normal',
                'remarks', NULL
            ),
            JSON_OBJECT(
                'parameter_name', 'Parasitemia',
                'value',
                    IF(LOWER(TRIM(CONVERT(src1.preports USING utf8mb4))) LIKE 'neg%', 'Negative', NULL),
                'unit', NULL,
                'normal_range', NULL,
                'status',
                    IF(LOWER(TRIM(CONVERT(src1.preports USING utf8mb4))) LIKE 'neg%', 'normal', 'unknown'),
                'remarks', NULL
            ),
            JSON_OBJECT(
                'parameter_name', 'Gametocytes',
                'value', 'Absent',
                'unit', NULL,
                'normal_range', NULL,
                'status', 'normal',
                'remarks', NULL
            ),
            JSON_OBJECT(
                'parameter_name', 'Stage',
                'value', 'Not Applicable',
                'unit', NULL,
                'normal_range', NULL,
                'status', 'normal',
                'remarks', NULL
            )
        ),

        'analyzed_by',         src2.resulted_by,
        'analysis_date',       src2.resulted_at,
        'additional_comments', NULL
    ) AS form_data,

    'final' AS form_status,

    JSON_OBJECT('template_code', 'pbs_malaria') AS metadata,

    COALESCE(src2.resulted_by, 23) AS reported_by,
    COALESCE(src2.resulted_at, NOW()) AS reported_at,
    COALESCE(src2.resulted_by, 23) AS verified_by,
    COALESCE(src2.resulted_at, NOW()) AS verified_at,
    COALESCE(src2.created_at, NOW()) AS created_at,
    COALESCE(src2.updated_at, NOW()) AS updated_at

FROM yyfcolmy_medcom.procedures AS src1
INNER JOIN yyfcolmy_brigita_practice.investigations AS src2
    ON src1.pl_id = src2.id
INNER JOIN yyfcolmy_medcom.services AS src3
    ON src3.svsvid = src2.medical_service_id

WHERE src3.svsvid = 28;
