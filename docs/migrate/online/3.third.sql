-- Adapted from ../3.third.sql for the online migration (see ../9.migration_runbook.md).
--
-- Source   : yyfcolmy_medcom        (legacy Medcom, hosted online — unchanged)
-- Target   : yyfcolmy_brigita_practice (new online Practice1.0 database)
--
-- All Section A (setup/config) INSERTs from the original script have been
-- removed — those tables are already populated by 8.setup_export.sh
-- (run before this script, per the runbook). Removed blocks:
--   medication_frequencies, mtuha_diagnoses, medication_formulations,
--   result_templates, nhif_tariffs
--
-- Run this AFTER online/1.first.sql (prescriptions/vital_signs/etc. join to
-- patients/consultations/doctors/medications created there or carried via
-- the local setup export).

INSERT INTO yyfcolmy_brigita_practice.`prescriptions` (`id`, `patient_id`, `consultation_id`, `doctor_id`, `medication_id`, `dosage`, `administration_route_id`, `frequency_id`, `duration_days`, `quantity`, `quantity_dispensed`, `batches_used`, `dispensing_type`, `cash_amount`, `insurance_covered_amount`, `instructions`, `notes`, `pharmacist_notes`, `status`, `is_paid`, `is_discount`, `discount_percent`, `payment_method`, `amount_paid`, `paid_at`, `paid_by`, `visit_id`, `folio_item_id`, `prescribed_at`, `prepared_at`, `dispensed_at`, `reviewed_at`, `reviewed_by`, `prepared_by`, `dispensed_by`, `created_at`, `updated_at`)

SELECT NULL, src2.id, src3.id, src4.doctor_id, src5.id, src1.dosage,
CASE
    WHEN EXISTS (
        SELECT 1
        FROM yyfcolmy_brigita_practice.administration_routes ar
        WHERE ar.id = src1.route
    )
    THEN src1.route
    ELSE 1
END,
CASE
    WHEN EXISTS (
        SELECT 1
        FROM yyfcolmy_brigita_practice.medication_frequencies mf
        WHERE mf.id = src1.frequency
    )
    THEN src1.frequency
    ELSE 2
END, src1.days, src1.dqty, src1.dqty, NULL, 'individual', src1.dprice, src1.dcovered, NULL, NULL, NULL,
CASE
	WHEN pdstatus > 4 THEN 'dispensed'
    ELSE 'prescribed'
END,
CASE
	WHEN pdstatus > 3 THEN 1
    ELSE 0
END, 0, 0, NULL, src1.dprice, TIMESTAMP(src1.cashon, src1.cashat), src1.cashier, src3.visit_id, src1.FolioItemID, TIMESTAMP(src1.issuedon, src1.issuedat), TIMESTAMP(src1.issuedon, src1.issuedat), TIMESTAMP(src1.issuedon, src1.issuedat), TIMESTAMP(src1.issuedon, src1.issuedat), 23, 23, 23, TIMESTAMP(src1.createdon, src1.createdat),  TIMESTAMP(src1.createdon, src1.createdat)
FROM yyfcolmy_medcom.`pat_pharm` AS src1
INNER JOIN yyfcolmy_brigita_practice.patients AS src2 ON src1.pat_id = src2.legacy_mrn
INNER JOIN yyfcolmy_brigita_practice.consultations AS src3 ON src1.visit_id = src3.visit_id
INNER JOIN yyfcolmy_brigita_practice.doctors AS src4 ON src1.createdby = src4.doctor_id
INNER JOIN yyfcolmy_brigita_practice.medications AS src5 ON src1.dcode = src5.id;

INSERT IGNORE INTO yyfcolmy_brigita_practice.`medication_ledger` (`id`, `medication_id`, `grn_id`, `grn_item_id`, `batch_number`, `manufacture_date`, `expiry_date`, `unit_cost`, `quantity_received`, `status`, `location_id`, `notes`, `created_at`, `updated_at`)

SELECT NULL, l_did,  NULL, NULL, bnumber, NULL, l_xdate, ucost, l_newqty,
CASE
	WHEN l_newqty > 0 THEN 'active'
	ELSE 'exhausted'
END, NULL, NULL, NOW(), NOW()
FROM yyfcolmy_medcom.`ledger` AS src1
INNER JOIN yyfcolmy_brigita_practice.medications AS src2 ON src1.l_did = src2.id;

INSERT INTO yyfcolmy_brigita_practice.vital_signs
( id, consultation_id, visit_id, patient_id, recorded_by, recorded_at, updated_by, pulse_rate, temperature, respiratory_rate, weight, height, bmi, oxygen_saturation, systolic_bp, diastolic_bp, muac, ofc, status, notes, created_at, updated_at)

SELECT NULL, src1.cscsid, src2.visit_id, src2.patient_id, src2.doctor_id, src2.consultation_date, NULL,
    CASE
        WHEN TRIM(src1.vtpurate) REGEXP '^[0-9]+(\\.[0-9]+)?$'
        THEN src1.vtpurate
        ELSE NULL
    END AS pulse_rate,

    CASE
        WHEN TRIM(src1.vttemp) REGEXP '^[0-9]+(\\.[0-9]+)?$'
        THEN src1.vttemp
        ELSE NULL
    END AS temperature,

    CASE
        WHEN TRIM(src1.vtresprate) REGEXP '^[0-9]+(\\.[0-9]+)?$'
        THEN src1.vtresprate
        ELSE NULL
    END AS respiratory_rate,

    CASE
        WHEN TRIM(src1.vtweight) REGEXP '^[0-9]+(\\.[0-9]+)?$'
        THEN src1.vtweight
        ELSE NULL
    END AS weight,

    CASE
        WHEN TRIM(src1.vtheight) REGEXP '^[0-9]+(\\.[0-9]+)?$'
        THEN src1.vtheight
        ELSE NULL
    END AS height,

CASE
    WHEN TRIM(src1.vtbmi) REGEXP '^[0-9]+(\\.[0-9]+)?$'
         AND CAST(src1.vtbmi AS DECIMAL(10,2)) BETWEEN 10 AND 60
    THEN src1.vtbmi
    ELSE NULL
END,

    CASE
        WHEN TRIM(src1.vtsatox) REGEXP '^[0-9]+(\\.[0-9]+)?$'
        THEN src1.vtsatox
        ELSE NULL
    END AS oxygen_saturation,

    CASE
        WHEN TRIM(src1.systolic) REGEXP '^[0-9]+(\\.[0-9]+)?$'
        THEN src1.systolic
        ELSE NULL
    END AS systolic_bp,

    CASE
        WHEN TRIM(src1.diastolic) REGEXP '^[0-9]+(\\.[0-9]+)?$'
        THEN src1.diastolic
        ELSE NULL
    END AS diastolic_bp, NULL, NULL, 1, NULL, src2.consultation_date, NOW()
FROM yyfcolmy_medcom.vital AS src1
INNER JOIN yyfcolmy_brigita_practice.consultations AS src2
    ON src1.cscsid = src2.id;

INSERT INTO yyfcolmy_brigita_practice.`systemic_examinations` (`id`, `consultation_id`, `visit_id`, `patient_id`, `examination_type`, `general_findings`, `cardiovascular_system`, `respiratory_system`, `gastrointestinal_system`, `nervous_system`, `musculoskeletal_system`, `genitourinary_system`, `endocrine_system`, `skin_examination`, `psychiatric_assessment`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`)

SELECT NULL, src1.cons_id, src2.visit_id, src2.patient_id, 'General', src1.general, Cardiovascular, Respiratory, Hepatobiliary, ENT, muscolosketal, NULL, NULL, NULL, NULL, 1, src2.doctor_id, NULL, src2.consultation_date, NULL FROM yyfcolmy_medcom.`examinations` as src1
INNER JOIN yyfcolmy_brigita_practice.consultations AS src2 ON src1.cons_id = src2.id;

INSERT INTO yyfcolmy_brigita_practice.store_locations_stock
(
    id,
    location_id,
    medication_id,
    requisition_id,
    requisition_item_id,
    batch_number,
    manufacture_date,
    expiry_date,
    unit_cost,
    quantity,
    status,
    created_at,
    updated_at
)
SELECT
    NULL,
    src1.section,
    src1.material,
    NULL,
    NULL,

    COALESCE(NULLIF(TRIM(src1.batch), ''), 'NO-BATCH'),

    NULL,

    COALESCE(
        STR_TO_DATE(
            NULLIF(NULLIF(TRIM(src1.expiry), ''), '0000-00-00'),
            '%Y-%m-%d'
        ),
        '1900-01-01'
    ) AS expiry_date,

    COALESCE(
        NULLIF(TRIM(src1.unit_cost), ''),
        0
    ) AS unit_cost,

    src1.quantity,
    1,
    NOW(),
    NOW()

FROM yyfcolmy_medcom.store_mst AS src1
INNER JOIN yyfcolmy_brigita_practice.store_locations AS src2
    ON src2.id = src1.section
INNER JOIN yyfcolmy_brigita_practice.medications AS src3
    ON src3.id = src1.material
WHERE NOT (
    src1.quantity = 0
    AND TRIM(IFNULL(src1.batch, '')) = ''
    AND TRIM(IFNULL(src1.expiry, '')) IN ('', '0000-00-00')
    AND TRIM(IFNULL(src1.unit_cost, '')) IN ('', '0', '0.00')
);
