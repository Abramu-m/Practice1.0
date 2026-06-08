-- ============================================================
-- MTUHA diagnosis-category mapping migration
--
-- Source (legacy): yyfcolmy_medcom.new_icd_grouping
--   (icd -> category, where category == mtuha_diagnoses.id)
--
-- Populates icd_10.mtuha_diagnosis so the MTUHA monthly report
-- (MtuhaReportService::diagnoses()) can group recorded diagnoses
-- into the 120 MTUHA categories. Without this mapping every
-- diagnosis row in the report renders as zero, because the
-- report joins icd_10.mtuha_diagnosis to mtuha_diagnoses.id and
-- currently 0 / 14,260 codes carry that mapping.
--
-- Matches by ICD-10 code (new_icd_grouping.icd = icd_10.code).
-- 14,258 / 14,260 legacy rows match a current icd_10 code; the
-- 2 that don't ('ICD 10 CODE' — a header row left over from a
-- CSV import — and 'U84.9', which has no row in icd_10) are
-- silently skipped by the INNER JOIN.
--
-- All matched rows are applied regardless of the legacy `status`
-- flag. The 17 status = 2 rows are active malaria codes
-- (B50–B54, category 20) used 7,440+ times in icd_diagnoses and
-- have no status = 1 alternative — excluding them would leave a
-- heavily-used diagnosis group permanently unmapped.
--
-- Safe to re-run: overwrites mtuha_diagnosis with the mapped
-- value each time (idempotent).
-- ============================================================

UPDATE yyfcolmy_practice_brigita.icd_10 i
INNER JOIN yyfcolmy_medcom.new_icd_grouping g ON g.icd = i.code
SET i.mtuha_diagnosis = g.category,
    i.updated_at = NOW();
