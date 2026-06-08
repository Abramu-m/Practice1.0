-- ============================================================
-- NHIF item-code mapping migration
--
-- Source (legacy): yyfcolmy_medcom
--   nhif_service_mapping  (local_svsvid -> nhif_itemcode)
--   nhif_pharmacy_mapping (local_did    -> nhif_itemcode)
--
-- The original data migration preserved primary keys 1:1, so:
--   medical_services.id == legacy services.svsvid == local_svsvid
--   medications.id      == legacy drugs.did       == local_did
-- (verified against docs/migrate/first.sql and live data)
--
-- This copies the genuine NHIF item codes into the NHIF rows of
-- the insurance map tables, replacing placeholder/incorrect codes
-- with the real tariff codes used when submitting NHIF claims.
--
-- The NHIF patient category is looked up inline via an INNER JOIN
-- on patient_categories.tariffs_table = 'nhif_tariffs'. This keeps
-- each INSERT a single self-contained statement (no session user
-- variable to set first) and, if no such category exists, the JOIN
-- simply yields zero rows instead of inserting a NULL FK.
--
-- nhif_itemcode = 0 means "not mapped" in the legacy system and
-- is skipped; legacy rows whose local id has no counterpart in
-- the new system are skipped by the INNER JOIN.
--
-- Safe to re-run: ON DUPLICATE KEY UPDATE keeps codes in sync.
-- Prerequisite: 4.pricing.sql must have run first — it sets the NHIF
-- category's tariffs_table = 'nhif_tariffs', which the INNER JOIN below
-- relies on to resolve the patient_category_id.
-- ============================================================

-- Medical services -> medical_service_insurance_map
INSERT INTO yyfcolmy_practice_brigita.medical_service_insurance_map
    (medical_service_id, patient_category_id, insurance_item_code, created_at, updated_at)
SELECT ms.id, pc.id, sm.nhif_itemcode, NOW(), NOW()
FROM yyfcolmy_medcom.nhif_service_mapping sm
INNER JOIN yyfcolmy_practice_brigita.medical_services ms ON ms.id = sm.local_svsvid
INNER JOIN yyfcolmy_practice_brigita.patient_categories pc ON pc.tariffs_table = 'nhif_tariffs'
WHERE sm.nhif_itemcode <> 0
ON DUPLICATE KEY UPDATE
    insurance_item_code = VALUES(insurance_item_code),
    updated_at = NOW();

-- Medications -> medication_insurance_map
INSERT INTO yyfcolmy_practice_brigita.medication_insurance_map
    (medication_id, patient_category_id, insurance_item_code, created_at, updated_at)
SELECT m.id, pc.id, pm.nhif_itemcode, NOW(), NOW()
FROM yyfcolmy_medcom.nhif_pharmacy_mapping pm
INNER JOIN yyfcolmy_practice_brigita.medications m ON m.id = pm.local_did
INNER JOIN yyfcolmy_practice_brigita.patient_categories pc ON pc.tariffs_table = 'nhif_tariffs'
WHERE pm.nhif_itemcode <> 0
ON DUPLICATE KEY UPDATE
    insurance_item_code = VALUES(insurance_item_code),
    updated_at = NOW();
