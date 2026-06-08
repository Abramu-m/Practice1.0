-- ============================================================
-- pricing.sql — migrate prices from old medcom system
--
-- Part 1: Set selling_price on medications / medical_services
--         from the cash patient category (dc_category = 1).
--
-- Part 2: For every non-NHIF insurance category (type='insurance',
--         tariffs_table IS NULL), create a dedicated tariff table,
--         populate it, wire patient_categories.tariffs_table, and
--         create insurance map entries so PricingService can resolve
--         prices for those categories.
--
-- Run from phpMyAdmin on the production server.
-- Schema names: yyfcolmy_medcom (old) · yyfcolmy_practice_brigita (new)
-- ============================================================

SET sql_mode = (SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));

-- ============================================================
-- PART 1 — selling_price on master records (cash category)
-- ============================================================

-- Medications
UPDATE yyfcolmy_practice_brigita.medications m
INNER JOIN yyfcolmy_medcom.drugs_charges dc ON dc.dc_d_id = m.id
SET m.selling_price = COALESCE(
        NULLIF(TRIM(CONVERT(dc.dc_amount USING utf8mb4)), ''),
        0
    )
WHERE dc.dc_category = 1;

-- Medical services
UPDATE yyfcolmy_practice_brigita.medical_services ms
INNER JOIN yyfcolmy_medcom.services_charges sc ON sc.sc_d_id = ms.id
SET ms.selling_price = COALESCE(
        NULLIF(TRIM(CONVERT(sc.sc_amount USING utf8mb4)), ''),
        0
    )
WHERE sc.sc_category = 1;

-- ============================================================
-- PART 1.5 — flag the NHIF category before the Part 2 loop
--
-- NHIF uses the shared nhif_tariffs table (created/populated in
-- 3.third.sql), NOT a generated per-category table. The Part 2 loop
-- below picks up every insurance category WHERE tariffs_table IS NULL,
-- so NHIF must be pointed at nhif_tariffs first — otherwise the loop
-- generates a stray 'nhif_tariff' table and wires NHIF to it.
-- The app UI never sets tariffs_table, so it must be done here.
-- Idempotent.
-- ============================================================
UPDATE yyfcolmy_practice_brigita.patient_categories
SET tariffs_table = 'nhif_tariffs', updated_at = NOW()
WHERE description = 'NHIF';

-- ============================================================
-- PART 2 — per-category tariff tables (non-cash, non-NHIF)
-- ============================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS yyfcolmy_practice_brigita.`_migrate_tariffs` $$

CREATE PROCEDURE yyfcolmy_practice_brigita.`_migrate_tariffs`()
BEGIN
    DECLARE v_done     INT          DEFAULT FALSE;
    DECLARE v_cat_id   BIGINT;
    DECLARE v_cat_desc VARCHAR(100);
    DECLARE v_tbl      VARCHAR(120);

    -- Only insurance categories that don't already have a tariffs_table set
    DECLARE cur CURSOR FOR
        SELECT id, description
        FROM yyfcolmy_practice_brigita.patient_categories
        WHERE type = 'insurance'
          AND (tariffs_table IS NULL OR TRIM(tariffs_table) = '')
        ORDER BY id;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = TRUE;

    OPEN cur;

    cat_loop: LOOP
        FETCH cur INTO v_cat_id, v_cat_desc;
        IF v_done THEN LEAVE cat_loop; END IF;

        -- Derive a safe table name from the category description
        -- e.g. "Jubilee Insurance"  →  jubilee_insurance_tariff
        SET v_tbl = CONCAT(
            LOWER(TRIM(REGEXP_REPLACE(v_cat_desc, '[^a-zA-Z0-9]+', '_'))),
            '_tariff'
        );

        -- ── a) Create tariff table ───────────────────────────────────
        SET @s = CONCAT(
            'CREATE TABLE IF NOT EXISTS yyfcolmy_practice_brigita.`', v_tbl, '` (',
            ' `id`         bigint unsigned NOT NULL AUTO_INCREMENT,',
            ' `item_code`  varchar(50)     NOT NULL,',
            ' `item_name`  varchar(255)    DEFAULT NULL,',
            ' `unit_price` decimal(10,2)   NOT NULL DEFAULT 0.00,',
            ' PRIMARY KEY (`id`),',
            ' KEY `item_code_index` (`item_code`)',
            ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

        -- ── b) Medication rows  (item_code = "m_{id}") ──────────────
        SET @s = CONCAT(
            'INSERT INTO yyfcolmy_practice_brigita.`', v_tbl, '`',
            '    (item_code, item_name, unit_price)',
            ' SELECT CONCAT(''m_'', m.id),',
            '        COALESCE(CONVERT(m.generic_name USING utf8mb4), ''''),',
            '        COALESCE(NULLIF(TRIM(CONVERT(dc.dc_amount USING utf8mb4)), ''''), 0)',
            ' FROM yyfcolmy_medcom.drugs_charges dc',
            ' INNER JOIN yyfcolmy_practice_brigita.medications m ON dc.dc_d_id = m.id',
            ' WHERE dc.dc_category = ', v_cat_id
        );
        PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

        -- ── c) Medical service rows  (item_code = "s_{id}") ─────────
        SET @s = CONCAT(
            'INSERT INTO yyfcolmy_practice_brigita.`', v_tbl, '`',
            '    (item_code, item_name, unit_price)',
            ' SELECT CONCAT(''s_'', ms.id),',
            '        CONVERT(ms.name USING utf8mb4),',
            '        COALESCE(NULLIF(TRIM(CONVERT(sc.sc_amount USING utf8mb4)), ''''), 0)',
            ' FROM yyfcolmy_medcom.services_charges sc',
            ' INNER JOIN yyfcolmy_practice_brigita.medical_services ms ON sc.sc_d_id = ms.id',
            ' WHERE sc.sc_category = ', v_cat_id
        );
        PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

        -- ── d) Point patient_categories to this table ────────────────
        UPDATE yyfcolmy_practice_brigita.patient_categories
        SET tariffs_table = v_tbl
        WHERE id = v_cat_id;

        -- ── e) Medication insurance map (insurance_item_code = "m_{id}") ──
        INSERT IGNORE INTO yyfcolmy_practice_brigita.medication_insurance_map
            (medication_id, patient_category_id, insurance_item_code, created_at, updated_at)
        SELECT m.id, v_cat_id, CONCAT('m_', m.id), NOW(), NOW()
        FROM yyfcolmy_practice_brigita.medications m
        WHERE EXISTS (
            SELECT 1 FROM yyfcolmy_medcom.drugs_charges dc
            WHERE dc.dc_d_id = m.id AND dc.dc_category = v_cat_id
        );

        -- ── f) Medical service insurance map (insurance_item_code = "s_{id}") ──
        INSERT IGNORE INTO yyfcolmy_practice_brigita.medical_service_insurance_map
            (medical_service_id, patient_category_id, insurance_item_code, created_at, updated_at)
        SELECT ms.id, v_cat_id, CONCAT('s_', ms.id), NOW(), NOW()
        FROM yyfcolmy_practice_brigita.medical_services ms
        WHERE EXISTS (
            SELECT 1 FROM yyfcolmy_medcom.services_charges sc
            WHERE sc.sc_d_id = ms.id AND sc.sc_category = v_cat_id
        );

    END LOOP;

    CLOSE cur;
END$$

DELIMITER ;

CALL yyfcolmy_practice_brigita.`_migrate_tariffs`();
DROP PROCEDURE IF EXISTS yyfcolmy_practice_brigita.`_migrate_tariffs`;

-- ============================================================
-- PART 3 — Fix insurance_item_code to match tariff table format
--
-- The stored procedure created tariff tables with item_code in
-- the format "m_{id}" (medications) and "s_{id}" (services).
-- Pre-existing entries in the insurance map tables may have had
-- old numeric codes (e.g. "1500.00") from before the migration.
-- This section corrects them to match the tariff table format.
-- Safe to re-run — uses the same CONCAT logic as Part 2.
-- ============================================================

UPDATE yyfcolmy_practice_brigita.medication_insurance_map
SET insurance_item_code = CONCAT('m_', medication_id)
WHERE patient_category_id IN (
    SELECT id FROM yyfcolmy_practice_brigita.patient_categories
    WHERE type = 'insurance'
      AND tariffs_table IS NOT NULL
      AND TRIM(tariffs_table) != ''
      AND tariffs_table != 'nhif_tariffs'
);

UPDATE yyfcolmy_practice_brigita.medical_service_insurance_map
SET insurance_item_code = CONCAT('s_', medical_service_id)
WHERE patient_category_id IN (
    SELECT id FROM yyfcolmy_practice_brigita.patient_categories
    WHERE type = 'insurance'
      AND tariffs_table IS NOT NULL
      AND TRIM(tariffs_table) != ''
      AND tariffs_table != 'nhif_tariffs'
);
