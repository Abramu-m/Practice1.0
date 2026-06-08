INSERT INTO yyfcolmy_practice_brigita.`administration_routes` (`id`, `route_name`, `description`, `is_active`, `created_at`, `updated_at`)
SELECT r_id, r_description, NULL, r_status, NOW(), NOW() FROM yyfcolmy_medcom.`route`;

INSERT IGNORE INTO yyfcolmy_practice_brigita.`users` (`id`, `first_name`, `middle_name`, `last_name`, `date_of_birth`, `gender`, `username`, `phone`, `address`, `profile_picture`, `role`, `is_admin`, `is_super`, `is_active`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `is_verified`, `verified_at`)
SELECT user_id, firstname, NULL, lastname, NULL, NULL, username, NULL, NULL, NULL, 'user', 0, 0, 0, CONCAT(TRIM(username), '@brigita.com'), NULL, 'Medcom1', NULL, NOW(), NOW(), 0, NULL FROM yyfcolmy_medcom.`user`;

INSERT INTO yyfcolmy_practice_brigita.`designations` (`id`, `designation_code`, `description`, `status`, `created_at`, `updated_at`)
SELECT NULL, d_code, d_description, d_status, NOW(), NOW() FROM yyfcolmy_medcom.`designation`;

INSERT INTO yyfcolmy_practice_brigita.`doctors` (`doctor_id`, `designation`, `mct_number`, `specialization`, `drsignature`, `created_by`, `status`, `created_at`, `updated_at`)
SELECT d_id, Designation, mat, Specialization, NULL, 23, status, NOW(), NOW()  FROM yyfcolmy_medcom.doctor;

INSERT INTO yyfcolmy_practice_brigita.`visit_types` (`id`, `description`, `created_at`, `updated_at`)
SELECT id, description, NOW(), NOW() FROM yyfcolmy_practice1_0.`visit_types`;

INSERT INTO yyfcolmy_practice_brigita.`patient_categories` (`id`, `description`, `code`, `type`, `is_active`, `created_at`, `updated_at`, `created_by`)
SELECT pat_cat_id, cat_description, NULL, 
	CASE 
        WHEN pat_cat_id = 1 OR pat_cat_id >= 8 THEN 'cash'
        ELSE 'insurance'
    END AS price_category,
cat_status, NOW(), NOW(), 23 FROM yyfcolmy_medcom.`pat_category`;

INSERT INTO yyfcolmy_practice_brigita.`consultation_fees` ( `doctor_id`, `patient_category_id`, `visit_type_id`, `cash_amount`, `covered_amount`, `description`, `status`)
SELECT d.doctor_id, COALESCE(pc.id, 1), 1,
CASE
    WHEN pc.type = 'cash' THEN c.cc_amount
    ELSE 0
END AS cash,
CASE
    WHEN pc.type = 'insurance' THEN c.cc_amount
    ELSE 0
END AS covered, NULL, c.cc_status
FROM yyfcolmy_medcom.`cons_charges` c
INNER JOIN yyfcolmy_practice_brigita.`doctors` d
    ON d.doctor_id = c.d_id
LEFT JOIN yyfcolmy_practice_brigita.`patient_categories` pc
    ON pc.id = c.bill_category;


INSERT INTO yyfcolmy_practice_brigita.`cds_rule_categories` (`id`, `name`, `display_name`, `description`, `is_active`, `sort_order`, `created_at`, `updated_at`)
SELECT id, name, display_name, description, is_active, sort_order, NOW(), NOW()  FROM yyfcolmy_practice1_0.`cds_rule_categories`;

INSERT INTO yyfcolmy_practice_brigita.`cds_rule_types` (`id`, `category_id`, `name`, `display_name`, `description`, `handler_class`, `is_active`, `sort_order`, `created_at`, `updated_at`)
SELECT id, category_id, name, display_name, description, handler_class, is_active, sort_order, NOW(), NOW() FROM yyfcolmy_practice1_0.`cds_rule_types`;

INSERT INTO yyfcolmy_practice_brigita.`patients` (`id`, `first_name`, `middle_name`, `last_name`, `date_of_birth`, `gender`, `contact`, `email`, `residence`, `occupation`, `nida`, `patient_category`, `card_number`, `membership_number`, `vote`, `SchemeID`, `ProductCode`, `PackageID`, `HasSupplementary`, `SchemeName`, `mtuha_new`, `created_by`, `status`, `created_at`, `updated_at`, `legacy_mrn`)
SELECT NULL,
-- first_name: split from pat_names when fname+lname both empty; split from fname when only lname empty; else use fname directly
CASE
    WHEN (fname IS NULL OR TRIM(fname) = '') AND (lname IS NULL OR TRIM(lname) = '')
        THEN TRIM(SUBSTRING_INDEX(TRIM(pat_names), ' ', 1))
    WHEN (lname IS NULL OR TRIM(lname) = '') AND fname IS NOT NULL AND TRIM(fname) != ''
         AND LOCATE(' ', TRIM(fname)) > 0
        THEN TRIM(SUBSTRING_INDEX(TRIM(fname), ' ', 1))
    ELSE TRIM(fname)
END AS first_name,
-- middle_name: extract from pat_names or fname when lname absent; else use mname
CASE
    WHEN (fname IS NULL OR TRIM(fname) = '') AND (lname IS NULL OR TRIM(lname) = '')
         AND LENGTH(TRIM(pat_names)) - LENGTH(REPLACE(TRIM(pat_names), ' ', '')) >= 2
        THEN TRIM(SUBSTRING(
                 TRIM(pat_names),
                 LOCATE(' ', TRIM(pat_names)) + 1,
                 LENGTH(TRIM(pat_names))
                   - LOCATE(' ', TRIM(pat_names))
                   - LENGTH(SUBSTRING_INDEX(TRIM(pat_names), ' ', -1))
                   - 1
             ))
    WHEN (lname IS NULL OR TRIM(lname) = '') AND fname IS NOT NULL AND TRIM(fname) != ''
         AND LENGTH(TRIM(fname)) - LENGTH(REPLACE(TRIM(fname), ' ', '')) >= 2
        THEN TRIM(SUBSTRING(
                 TRIM(fname),
                 LOCATE(' ', TRIM(fname)) + 1,
                 LENGTH(TRIM(fname))
                   - LOCATE(' ', TRIM(fname))
                   - LENGTH(SUBSTRING_INDEX(TRIM(fname), ' ', -1))
                   - 1
             ))
    ELSE TRIM(mname)
END AS middle_name,
-- last_name: last word from pat_names or fname when lname absent; else use lname directly
CASE
    WHEN (fname IS NULL OR TRIM(fname) = '') AND (lname IS NULL OR TRIM(lname) = '')
         AND LOCATE(' ', TRIM(pat_names)) > 0
        THEN TRIM(SUBSTRING_INDEX(TRIM(pat_names), ' ', -1))
    WHEN (lname IS NULL OR TRIM(lname) = '') AND fname IS NOT NULL AND TRIM(fname) != ''
         AND LOCATE(' ', TRIM(fname)) > 0
        THEN TRIM(SUBSTRING_INDEX(TRIM(fname), ' ', -1))
    ELSE TRIM(lname)
END AS last_name,

   CASE
        WHEN CAST(age AS CHAR) = '0000-00-00'
             OR CAST(age AS CHAR) = ''
        THEN '1800-01-01'
        ELSE age
    END AS date_of_birth, 
CASE
	WHEN gender = 1 THEN 'male'
	WHEN gender = 2 THEN 'female'
	ELSE 'other'
END AS gender, contact, NULL, place, Occupation, nida, pat_cat_id, membership AS cardno, membership AS membershipno, vote, SchemeID, ProductCode, PackageID, 
CASE
    WHEN HasSupplementary IS NULL OR TRIM(HasSupplementary) = '' THEN 'No'
    WHEN LOWER(TRIM(HasSupplementary)) IN ('1','yes','y','true') THEN 'Yes'
    WHEN LOWER(TRIM(HasSupplementary)) IN ('0','no','n','false') THEN 'No'
    ELSE 'No'
END, SchemeName, 
CASE
    WHEN mtuha_new_tz IS NULL OR TRIM(mtuha_new_tz) = '' THEN 'No'
    WHEN LOWER(TRIM(mtuha_new_tz)) IN ('1','yes','y','true') THEN 'Yes'
    WHEN LOWER(TRIM(mtuha_new_tz)) IN ('0','no','n','false') THEN 'No'
    ELSE 'No'
END, 23, pstatus, createdon, NOW(), pat_id FROM yyfcolmy_medcom.`patients`;

INSERT INTO yyfcolmy_practice_brigita.`patient_visits`
( id, patient, visit_type, visit_date, visit_category, doctor, amount_cash, amount_covered, sic_no, authorization_no, nhif_reference_no, item_code, folio_item_id, created_by, created_on, visit_status, post_status, vital_status, pitc_at, vitals_at, consulted_at, resulted_at, signature, created_at, updated_at)
SELECT v.v_id, p.id, 1, v.createdon, v.pv_cat_id, v.d_id,
    CASE 
        WHEN v.pv_cat_id = 1 OR v.pv_cat_id >= 8 THEN v.c_amount
        ELSE 0
    END AS amount_cash,
    CASE 
        WHEN v.pv_cat_id = 1 OR v.pv_cat_id >= 8 THEN 0
        ELSE v.c_amount
    END AS amount_covered, v.sic_no, v.Authorization, v.nhif_reffarenceno, v.ItemCode, v.FolioItemID, v.cretedby, v.createdon, v.pvstatus, v.post_status, v.vital_status, v.pitc_at, v.vitals_at, v.consulted_at, v.resulted_at, NULL, v.createdon, NOW()
FROM yyfcolmy_medcom.`pat_visit` v
INNER JOIN yyfcolmy_practice_brigita.`patients` p
    ON p.legacy_mrn = v.pat_id;

INSERT INTO yyfcolmy_practice_brigita.consultations (id, patient_id, doctor_id, visit_id, history_of_present_illness, provisional_diagnosis, final_diagnosis, remarks, followup_date, followup_instructions, status, consultation_date, created_at, updated_at)
SELECT c.c_id, pv.patient, COALESCE(d.doctor_id, 68), c.v_id, c.chief, c.pdiagonises, c.fdiagonises, c.t_plan, NULL, NULL, c.status, 
COALESCE(c.editedon, pv.created_on, c.createdon), COALESCE(c.editedon, pv.created_on, c.createdon), NULL FROM yyfcolmy_medcom.consultation c
INNER JOIN yyfcolmy_practice_brigita.patient_visits pv ON pv.id = c.v_id
LEFT JOIN yyfcolmy_practice_brigita.doctors d ON d.doctor_id = c.editedby;


INSERT INTO yyfcolmy_practice_brigita.`drug_classes` (`id`, `name`, `slug`, `category`, `description`, `is_active`, `created_at`, `updated_at`)
SELECT id, name, slug, category, description, is_active, NOW(), NOW() FROM yyfcolmy_practice1_0.`drug_classes`;

INSERT INTO yyfcolmy_practice_brigita.`financial_transactions` (`id`, `transaction_number`, `transaction_date`, `transaction_type`, `category`, `subcategory`, `amount`, `description`, `source_type`, `source_id`, `patient_id`, `visit_id`, `payment_method`, `payment_reference`, `insurance_covered_amount`, `patient_paid_amount`, `status`, `created_by`, `approved_by`, `approved_at`, `notes`, `created_at`, `updated_at`)

SELECT NULL, CONCAT('TXN', acctran_createdon, acctran_id) AS txn, acctran_createdon,  
CASE
	WHEN src2.types = 2 THEN 'expense'
	ELSE  'income'
END AS trans_type, 
CASE
	WHEN src2.types = 2 THEN 'general_expense'
	ELSE  'general_income'
END AS cat, NULL, acctran_amount, acctran_coments, 
CASE
	WHEN src2.types = 2 THEN 'general_expense'
	ELSE  'general_income'
END AS source_type, NULL, NULL, NULL, 'cash', NULL, 0, 0, 'completed', COALESCE(acctran_createdby, 68) AS cby, NULL, acctran_createdon, NULL, acctran_createdon, NOW()
FROM yyfcolmy_medcom.`acc_transactions` AS src1
INNER JOIN yyfcolmy_medcom.`acc_transaction_codes` AS src2
ON src2.acctrcode_id = src1.actranc_code;


INSERT INTO yyfcolmy_practice_brigita.`store_suppliers` (`id`, `name`, `email`, `phone`, `address`, `city`, `country`, `postal_code`, `tax_number`, `license_number`, `credit_limit`, `credit_days`, `payment_terms`, `is_active`, `created_at`, `updated_at`)

SELECT sp_id, sp_name, NULL, sp_contact, sp_location, NULL, 'Tanzania', NULL, NULL, NULL, 0, 0, NULL, sp_status, NOW(), NOW() 
FROM yyfcolmy_medcom.`supplier`;

INSERT INTO yyfcolmy_practice_brigita.`goods_received_notes` (`id`, `grn_number`, `grn_date`, `supplier_id`, `invoice_number`, `invoice_date`, `delivery_note_number`, `delivery_date`, `total_amount`, `discount_amount`, `tax_amount`, `net_amount`, `status`, `notes`, `received_by`, `received_at`)

SELECT NULL, grn_no, createdon, Supplier, invoice_no, createdon, NULL, NULL, amount AS amt, 0, 0, amount AS net, 'posted', NULL, createdby, createdon
FROM yyfcolmy_medcom.`grn_hd`;

INSERT INTO yyfcolmy_practice_brigita.`medication_units` (`id`, `unit_name`, `unit_code`, `unit_symbol`, `unit_type`, `is_active`, `display_order`, `created_at`, `updated_at`, `description`, `base_conversion_factor`)

SELECT du_id, du_name, du_id, NULL, 'other', du_status, 0, NOW(), NOW(), NULL, 1  FROM yyfcolmy_medcom.`drug_unit`;

INSERT INTO yyfcolmy_practice_brigita.`store_categories` (`id`, `description`, `created_at`, `updated_at`)
SELECT dt_id, t_name, NOW(), NOW() FROM yyfcolmy_medcom.`drugs_type`;

INSERT INTO yyfcolmy_practice_brigita.`medications` (`id`, `generic_name`, `brand_name`, `strength`, `formulation_id`, `dispensing_unit_id`, `description`, `category_id`, `stock_quantity`, `reorder_level`, `maximum_stock_level`, `requires_prescription`, `is_controlled`, `storage_conditions`, `is_active`, `created_at`, `updated_at`)

SELECT did, ddescription, NULL, dosage, NULL, dunit, NULL, dtype, cqty, reorderlevel, 1000000, 0, 0, NULL, 
CASE
	WHEN dstatus = 1 THEN 1
	ELSE 2
END AS status_is_active,
NOW(), NOW() FROM yyfcolmy_medcom.`drugs`;

INSERT INTO yyfcolmy_practice_brigita.`store_units` (`id`, `name`, `code`, `description`, `type`, `is_active`, `created_at`, `updated_at`)

SELECT * FROM yyfcolmy_practice1_0.`store_units`;

INSERT INTO yyfcolmy_practice_brigita.goods_received_note_items (`id`, `grn_id`, `item_id`, `store_unit_id`, `dispensing_unit_id`, `conversion_factor`, `store_quantity`, `store_unit_cost`, `batch_number`, `manufacture_date`, `expiry_date`, `received_quantity`, `unit_cost`, `total_cost`, `discount_percentage`, `discount_amount`, `tax_percentage`, `tax_amount`, `net_amount`, `notes`, `created_at`, `updated_at`)
SELECT dt.dt_id, grn.id, dt.material, 24, 26, dt.cfactor, dt.rqty, dt.pcost, dt.bnumber, NULL, 
CASE
    WHEN dt.xdate IS NULL
         OR TRIM(CAST(dt.xdate AS CHAR)) = ''
         OR CAST(dt.xdate AS CHAR) = '0000-00-00'
    THEN '1900-01-01'
    ELSE STR_TO_DATE(CAST(dt.xdate AS CHAR), '%Y-%m-%d')
END, dt.tiqty, dt.uncost, dt.tpcost, NULL, NULL, NULL, NULL, dt.tpcost, NULL, dt.con, NOW()
FROM yyfcolmy_medcom.grn_dt dt
INNER JOIN yyfcolmy_practice_brigita.goods_received_notes grn
ON CAST(grn.grn_number AS CHAR) = CAST(dt.grno AS CHAR);

INSERT IGNORE INTO yyfcolmy_practice_brigita.`icd_10` (`id`, `code`, `description`, `category`, `chapter`, `subcategory`, `mtuha_diagnosis`, `is_active`, `notes`, `created_at`, `updated_at`)

SELECT NULL, icd_code, icd_name, NULL, NULL, NULL, NULL, 1, NULL, NOW(), NOW()  FROM yyfcolmy_medcom.`icd_mst`;

INSERT INTO yyfcolmy_practice_brigita.icd_diagnoses
(`id`, `consultation_id`, `icd_code`, `description`, `type`, `category`, `subcategory`, `added_by`, `created_at`, `updated_at`)
SELECT NULL, c.id, ci.icd_code, i.description,
    CASE
        WHEN ci.icd_type = 'p' THEN 'provisional'
        ELSE 'final'
    END, NULL, NULL, ci.CreatedBy, ci.createdon, NOW()
FROM yyfcolmy_medcom.cons_icd ci
INNER JOIN yyfcolmy_practice_brigita.consultations c
    ON c.visit_id = ci.v_id
INNER JOIN yyfcolmy_practice_brigita.icd_10 i
    ON i.code = ci.icd_code;

INSERT INTO yyfcolmy_practice_brigita.`service_categories` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`)

SELECT id, Description, NULL, status, NOW(), NOW() FROM yyfcolmy_medcom.`service_types`;

INSERT INTO yyfcolmy_practice_brigita.`past_medical_history` (`id`, `patient_id`, `allergies`, `chronic_conditions`)

SELECT NULL, src2.id, NULL, familyHistory   FROM yyfcolmy_medcom.`cons_history` AS src1
INNER JOIN yyfcolmy_practice_brigita.patients AS src2
ON src1.pat_id = src2.legacy_mrn;

INSERT INTO yyfcolmy_practice_brigita.`store_locations` (`id`, `name`, `code`, `description`, `type`, `manager_name`, `manager_contact`, `can_request`, `can_issue`, `can_receive`, `requires_approval`, `sort_order`, `is_active`, `created_at`, `updated_at`)

SELECT s_id, s_description, CONCAT('section_', s_id), s_description,
CASE
	WHEN s_id = 1 THEN 'laboratory'
	WHEN s_id = 2 THEN 'radiology'
	WHEN s_id = 3 THEN 'nursing'
	WHEN s_id = 4 THEN 'dispensing'
	WHEN s_id = 5 THEN 'nursing'
	ELSE 'store'
END, NULL, NULL, 1, 0, 1, 1, 0, 1, NOW(), NOW()
FROM yyfcolmy_medcom.`section`;

INSERT INTO yyfcolmy_practice_brigita.`medical_services` (`id`, `name`, `description`, `service_category_id`, `requires_sample`, `requires_form`, `form_type`, `result_template_id`, `sample_type`, `turnaround_time_hours`, `preparation_instructions`, `is_active`, `created_at`, `updated_at`, `min_value`, `max_value`, `unit`)

SELECT svsvid, svdescription, NULL, stype, 0, 
CASE
	WHEN test_form > 1 THEN 1
    ELSE 0
END, NULL, NULL, NULL, NULL, NULL, 1, NOW(), NOW(), range1, range2, unit
FROM yyfcolmy_medcom.`services`;


INSERT IGNORE INTO yyfcolmy_practice_brigita.`investigations` (`id`, `patient_id`, `consultation_id`, `doctor_id`, `medical_service_id`, `quantity`, `cash_amount`, `insurance_covered_amount`, `notes`, `clinical_data`, `batches_used`, `priority`, `status`, `is_paid`, `is_discount`, `discount_percent`, `payment_method`, `amount_paid`, `paid_at`, `paid_by`, `visit_id`, `folio_item_id`, `ordered_at`, `ordered_by`, `collected_at`, `resulted_at`, `cancelled_at`, `cancelled_by`, `collected_by`, `resulted_by`, `created_at`, `updated_at`)

SELECT src1.id, src2.id, src3.id, src4.doctor_id, src1.svcode, src1.svqty, src1.svprice, src1.svcovered, NULL, NULL, NULL, 'routine', 
CASE
	WHEN src1.plstatus = 1 THEN 'draft'
	WHEN src1.plstatus = 2 THEN 'ordered'
	WHEN src1.plstatus = 3 THEN 'collected'
	WHEN src1.plstatus = 4 THEN 'processing'
	WHEN src1.plstatus = 5 THEN 'reported'
	WHEN src1.plstatus = 6 THEN 'reported'
    ELSE 'cancelled'
END AS status_lab, 
CASE
	WHEN src1.plstatus >= 2 THEN 1
    ELSE 0
END AS status_paid, 0, 0, 1, src1.svprice, TIMESTAMP(src1.cashon, src1.cashat) AS paidat, src1.cashier, src1.visit_id, src1.FolioItemID, TIMESTAMP(src1.createdon, src1.createdat) AS oat, src1.createdby, TIMESTAMP(src1.collectedon, src1.collectedat) AS colat, TIMESTAMP(src1.resultedon, src1.resultedat) AS resat, NULL, NULL, src1.collectedby, src1.resultedby, src1.createdon, NOW()
FROM yyfcolmy_medcom.`pat_lab` AS src1
INNER JOIN yyfcolmy_practice_brigita.patients AS src2 ON src1.pat_id = src2.legacy_mrn
INNER JOIN yyfcolmy_practice_brigita.consultations AS src3 ON src1.visit_id = src3.visit_id
INNER JOIN yyfcolmy_practice_brigita.doctors AS src4 ON src1.createdby = src4.doctor_id;
-- WHERE src1.id BETWEEN 1 AND 50000;
-- WHERE src1.id BETWEEN 50001 AND 100000
-- WHERE src1.id BETWEEN 100001 AND 150000
-- WHERE src1.id BETWEEN 150001 AND 200000
-- WHERE src1.id BETWEEN 200001 AND 250000

INSERT INTO yyfcolmy_practice_brigita.`investigation_consumables` (`id`, `medical_service_id`, `medication_id`, `quantity_required`, `is_optional`, `notes`, `is_active`, `created_at`, `updated_at`)

SELECT NULL, src1.t_id, d_id, qty, 0, NULL, status, NOW(), NOW() FROM yyfcolmy_medcom.`test_consumable` AS src1
INNER JOIN yyfcolmy_practice_brigita.medical_services AS src2 ON src1.t_id = src2.id
INNER JOIN yyfcolmy_practice_brigita.medications AS src3 ON src1.d_id = src3.id;
