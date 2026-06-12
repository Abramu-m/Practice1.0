/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `administration_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `administration_routes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `route_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `administration_routes_name_unique` (`route_name`),
  UNIQUE KEY `administration_routes_uuid_unique` (`uuid`),
  KEY `administration_routes_route_code_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `age_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `age_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  `min_days` int(11) NOT NULL DEFAULT 0,
  `max_days` int(11) NOT NULL,
  `min_years` double DEFAULT NULL,
  `max_years` double DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `age_groups_label_unique` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `allergies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allergies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `medication_id` bigint(20) unsigned DEFAULT NULL,
  `substance_name` varchar(255) NOT NULL,
  `reaction` varchar(255) DEFAULT NULL,
  `severity` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `recorded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `allergies_patient_substance_active_unique` (`patient_id`,`substance_name`,`is_active`),
  UNIQUE KEY `allergies_uuid_unique` (`uuid`),
  KEY `allergies_patient_id_index` (`patient_id`),
  KEY `allergies_medication_id_index` (`medication_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assemble_tariff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assemble_tariff` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `item_code_index` (`item_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `blood_transfusion_report_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blood_transfusion_report_rows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `row_key` varchar(100) NOT NULL,
  `row_label` varchar(255) NOT NULL,
  `sort_order` tinyint(4) NOT NULL,
  `service_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`service_ids`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blood_transfusion_report_rows_row_key_unique` (`row_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cds_alert_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cds_alert_actions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cds_alert_id` bigint(20) unsigned NOT NULL,
  `action` varchar(255) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cds_alert_actions_cds_alert_id_index` (`cds_alert_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cds_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cds_alerts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint(20) unsigned NOT NULL,
  `visit_id` bigint(20) unsigned DEFAULT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `rule_key` varchar(255) NOT NULL,
  `rule_version` varchar(255) DEFAULT NULL,
  `severity` varchar(255) NOT NULL DEFAULT 'info',
  `message` varchar(255) NOT NULL,
  `rationale` text DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `status` varchar(255) NOT NULL DEFAULT 'open',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cds_alerts_patient_id_index` (`patient_id`),
  KEY `cds_alerts_visit_id_index` (`visit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cds_dosage_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cds_dosage_limits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `medication_id` bigint(20) unsigned DEFAULT NULL,
  `max_single_dose_adults` varchar(255) DEFAULT NULL COMMENT 'Max single dose for adults, in medication''s own unit (e.g. "500 mg", "10 ml")',
  `max_daily_dose_adults` varchar(255) DEFAULT NULL COMMENT 'Max daily dose for adults, in medication''s own unit',
  `max_duration_adults` varchar(255) DEFAULT NULL COMMENT 'Max treatment duration for adults (e.g. "7 days", "4 weeks")',
  `max_single_dose_children` varchar(255) DEFAULT NULL COMMENT 'Max single dose for children per kg body weight (e.g. "15 mg/kg")',
  `max_daily_dose_children` varchar(255) DEFAULT NULL COMMENT 'Max daily dose for children per kg body weight',
  `max_duration_children` varchar(255) DEFAULT NULL COMMENT 'Max treatment duration for children',
  `renal_function_adults` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON: {creatinine: {operator, value, unit}, egfr: {operator, value}, urea: {operator, value, unit}}' CHECK (json_valid(`renal_function_adults`)),
  `renal_function_children` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Same structure as renal_function_adults but for children' CHECK (json_valid(`renal_function_children`)),
  `liver_function_adults` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON: {alt: {operator, value}, ast: {operator, value}, alp: {operator, value}, ggt: {operator, value}}' CHECK (json_valid(`liver_function_adults`)),
  `liver_function_children` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Same structure as liver_function_adults but for children' CHECK (json_valid(`liver_function_children`)),
  `lab_results` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON array: [{inv_id, operator, value}]' CHECK (json_valid(`lab_results`)),
  `diagnoses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON array: [{operator, diagnosis}]' CHECK (json_valid(`diagnoses`)),
  `interactions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`interactions`)),
  `mg_per_kg` decimal(6,3) DEFAULT NULL COMMENT 'For pediatric dosing',
  `age_min_years` decimal(4,1) NOT NULL DEFAULT 0.0,
  `age_max_years` decimal(4,1) NOT NULL DEFAULT 150.0,
  `weight_min_kg` decimal(5,1) DEFAULT NULL,
  `weight_max_kg` decimal(5,1) DEFAULT NULL,
  `special_conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'eGFR ranges, hepatic function, etc.' CHECK (json_valid(`special_conditions`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cds_dosage_limits_is_active_index` (`is_active`),
  KEY `cdl_medication_id_active_index` (`medication_id`,`is_active`),
  CONSTRAINT `cds_dosage_limits_medication_id_foreign` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cds_rule_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cds_rule_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cds_rule_categories_name_unique` (`name`),
  KEY `cds_rule_categories_is_active_sort_order_index` (`is_active`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cds_rule_conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cds_rule_conditions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` bigint(20) unsigned NOT NULL,
  `field_name` varchar(100) NOT NULL COMMENT 'medication_name, patient_age, etc.',
  `operator` enum('equals','not_equals','contains','not_contains','greater_than','less_than','greater_equal','less_equal','in','not_in','regex') NOT NULL,
  `value` text NOT NULL,
  `value_type` enum('string','integer','float','boolean','array','json') NOT NULL DEFAULT 'string',
  `logical_operator` enum('AND','OR') NOT NULL DEFAULT 'AND',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cds_rule_conditions_rule_id_is_active_index` (`rule_id`,`is_active`),
  KEY `cds_rule_conditions_field_name_index` (`field_name`),
  CONSTRAINT `cds_rule_conditions_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `cds_rules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cds_rule_parameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cds_rule_parameters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` bigint(20) unsigned NOT NULL,
  `parameter_name` varchar(100) NOT NULL,
  `parameter_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`parameter_value`)),
  `parameter_type` enum('dosage_limit','age_range','weight_range','renal_adjustment','hepatic_adjustment','general') NOT NULL DEFAULT 'general',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cds_rule_parameters_rule_id_parameter_name_unique` (`rule_id`,`parameter_name`),
  KEY `cds_rule_parameters_parameter_type_index` (`parameter_type`),
  CONSTRAINT `cds_rule_parameters_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `cds_rules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cds_rule_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cds_rule_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `handler_class` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cds_rule_types_category_id_name_unique` (`category_id`,`name`),
  KEY `cds_rule_types_is_active_category_id_index` (`is_active`,`category_id`),
  CONSTRAINT `cds_rule_types_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `cds_rule_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cds_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cds_rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rule_type_id` bigint(20) unsigned NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` tinyint(3) unsigned NOT NULL DEFAULT 5 COMMENT '1=Low, 5=Medium, 10=Critical',
  `severity` enum('info','warning','critical') NOT NULL DEFAULT 'warning',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cds_rules_is_active_priority_index` (`is_active`,`priority`),
  KEY `cds_rules_rule_type_id_index` (`rule_type_id`),
  KEY `cds_rules_deleted_at_index` (`deleted_at`),
  KEY `cds_rules_updated_by_foreign` (`updated_by`),
  KEY `cds_rules_created_by_foreign` (`created_by`),
  CONSTRAINT `cds_rules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cds_rules_rule_type_id_foreign` FOREIGN KEY (`rule_type_id`) REFERENCES `cds_rule_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cds_rules_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clinical_chemistry_report_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clinical_chemistry_report_rows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `row_key` varchar(100) NOT NULL,
  `row_label` varchar(255) NOT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `service_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`service_ids`)),
  `param_name` varchar(100) DEFAULT NULL,
  `required_template_name` varchar(100) DEFAULT NULL,
  `abnormal_as_high` tinyint(1) NOT NULL DEFAULT 0,
  `track_low_high` tinyint(1) NOT NULL DEFAULT 0,
  `is_section_header` tinyint(1) NOT NULL DEFAULT 0,
  `is_configurable` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clinical_chemistry_report_rows_row_key_unique` (`row_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `consultation_fees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultation_fees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `doctor_id` bigint(20) unsigned NOT NULL,
  `patient_category_id` bigint(20) unsigned NOT NULL,
  `visit_type_id` bigint(20) unsigned NOT NULL,
  `cash_amount` decimal(10,2) NOT NULL,
  `covered_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_consultation_fee` (`doctor_id`,`patient_category_id`,`visit_type_id`),
  KEY `consultation_fees_patient_category_id_foreign` (`patient_category_id`),
  KEY `consultation_fees_visit_type_id_foreign` (`visit_type_id`),
  KEY `consultation_fees_created_by_foreign` (`created_by`),
  CONSTRAINT `consultation_fees_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consultation_fees_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE,
  CONSTRAINT `consultation_fees_patient_category_id_foreign` FOREIGN KEY (`patient_category_id`) REFERENCES `patient_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consultation_fees_visit_type_id_foreign` FOREIGN KEY (`visit_type_id`) REFERENCES `visit_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `consultations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `doctor_id` bigint(20) unsigned NOT NULL,
  `visit_id` bigint(20) unsigned DEFAULT NULL,
  `history_of_present_illness` text DEFAULT NULL,
  `provisional_diagnosis` text DEFAULT NULL,
  `final_diagnosis` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `followup_date` date DEFAULT NULL,
  `followup_instructions` text DEFAULT NULL,
  `status` enum('active','completed','cancelled') NOT NULL DEFAULT 'active',
  `consultation_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `consultations_uuid_unique` (`uuid`),
  KEY `cons_doctor_date_idx` (`doctor_id`,`consultation_date`),
  KEY `consultations_patient_id_consultation_date_index` (`patient_id`,`consultation_date`),
  KEY `consultations_visit_id_foreign` (`visit_id`),
  CONSTRAINT `consultations_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE,
  CONSTRAINT `consultations_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consultations_visit_id_foreign` FOREIGN KEY (`visit_id`) REFERENCES `patient_visits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `daily_cash_reconciliation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_cash_reconciliation` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reconciliation_date` date NOT NULL,
  `opening_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `system_cash_income` decimal(10,2) NOT NULL DEFAULT 0.00,
  `system_cash_expenses` decimal(10,2) NOT NULL DEFAULT 0.00,
  `system_expected_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `physical_cash_count` decimal(10,2) DEFAULT NULL,
  `cash_difference` decimal(10,2) DEFAULT NULL,
  `status` enum('open','balanced','variance_noted','closed') NOT NULL DEFAULT 'open',
  `reconciled_by` bigint(20) unsigned DEFAULT NULL,
  `reconciled_at` datetime DEFAULT NULL,
  `variance_reason` text DEFAULT NULL,
  `variance_action` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_cash_reconciliation_reconciliation_date_unique` (`reconciliation_date`),
  KEY `daily_cash_reconciliation_reconciled_by_foreign` (`reconciled_by`),
  KEY `daily_cash_reconciliation_created_by_foreign` (`created_by`),
  KEY `daily_cash_reconciliation_reconciliation_date_index` (`reconciliation_date`),
  KEY `daily_cash_reconciliation_status_index` (`status`),
  CONSTRAINT `daily_cash_reconciliation_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `daily_cash_reconciliation_reconciled_by_foreign` FOREIGN KEY (`reconciled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `designations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `designations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `designation_code` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `designations_designation_code_unique` (`designation_code`),
  UNIQUE KEY `designations_description_unique` (`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `doctors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doctors` (
  `doctor_id` bigint(20) unsigned NOT NULL,
  `designation` varchar(20) DEFAULT NULL,
  `mct_number` varchar(20) DEFAULT NULL,
  `specialization` varchar(30) DEFAULT NULL,
  `drsignature` varchar(120) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`doctor_id`),
  KEY `doctors_designation_foreign` (`designation`),
  KEY `doctors_created_by_foreign` (`created_by`),
  CONSTRAINT `doctors_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `doctors_designation_foreign` FOREIGN KEY (`designation`) REFERENCES `designations` (`designation_code`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `doctors_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `drug_class_medication`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drug_class_medication` (
  `drug_class_id` bigint(20) unsigned NOT NULL,
  `medication_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`drug_class_id`,`medication_id`),
  KEY `drug_class_medication_medication_id_foreign` (`medication_id`),
  CONSTRAINT `drug_class_medication_drug_class_id_foreign` FOREIGN KEY (`drug_class_id`) REFERENCES `drug_classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `drug_class_medication_medication_id_foreign` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `drug_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drug_classes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `drug_classes_name_unique` (`name`),
  UNIQUE KEY `drug_classes_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `facilities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slogan` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `locale` varchar(255) DEFAULT NULL,
  `postal` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `nhif_facility_code` varchar(255) DEFAULT NULL,
  `hfr_code` varchar(50) DEFAULT NULL,
  `in_charge` bigint(20) unsigned DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `facilities_in_charge_foreign` (`in_charge`),
  CONSTRAINT `facilities_in_charge_foreign` FOREIGN KEY (`in_charge`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `financial_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `financial_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `transaction_number` varchar(50) NOT NULL,
  `transaction_date` datetime NOT NULL,
  `transaction_type` enum('income','expense') NOT NULL,
  `category` varchar(100) NOT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `source_type` varchar(50) NOT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `patient_id` bigint(20) unsigned DEFAULT NULL,
  `visit_id` bigint(20) unsigned DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'cash',
  `payment_reference` varchar(100) DEFAULT NULL,
  `insurance_covered_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `patient_paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','completed','cancelled','refunded') NOT NULL DEFAULT 'completed',
  `created_by` bigint(20) unsigned NOT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `financial_transactions_transaction_number_unique` (`transaction_number`),
  UNIQUE KEY `financial_transactions_uuid_unique` (`uuid`),
  KEY `financial_transactions_visit_id_foreign` (`visit_id`),
  KEY `financial_transactions_created_by_foreign` (`created_by`),
  KEY `financial_transactions_approved_by_foreign` (`approved_by`),
  KEY `financial_transactions_transaction_date_index` (`transaction_date`),
  KEY `financial_transactions_transaction_type_category_index` (`transaction_type`,`category`),
  KEY `financial_transactions_source_type_source_id_index` (`source_type`,`source_id`),
  KEY `financial_transactions_patient_id_visit_id_index` (`patient_id`,`visit_id`),
  KEY `financial_transactions_status_transaction_date_index` (`status`,`transaction_date`),
  CONSTRAINT `financial_transactions_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `financial_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `financial_transactions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `financial_transactions_visit_id_foreign` FOREIGN KEY (`visit_id`) REFERENCES `patient_visits` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `goods_received_note_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `goods_received_note_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `grn_id` bigint(20) unsigned NOT NULL,
  `item_id` bigint(20) unsigned NOT NULL,
  `store_unit_id` bigint(20) unsigned NOT NULL,
  `dispensing_unit_id` bigint(20) unsigned NOT NULL,
  `conversion_factor` decimal(10,4) NOT NULL DEFAULT 1.0000,
  `store_quantity` decimal(10,2) NOT NULL COMMENT 'Quantity in store units (e.g., 10 boxes)',
  `store_unit_cost` decimal(10,4) NOT NULL COMMENT 'Cost per store unit (e.g., $50 per box)',
  `batch_number` varchar(255) NOT NULL,
  `manufacture_date` date DEFAULT NULL,
  `expiry_date` date NOT NULL,
  `received_quantity` decimal(10,2) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `discount_percentage` decimal(5,2) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `tax_percentage` decimal(5,2) DEFAULT NULL,
  `tax_amount` decimal(10,2) DEFAULT NULL,
  `net_amount` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `goods_received_note_items_grn_id_item_type_item_id_index` (`grn_id`,`item_id`),
  KEY `goods_received_note_items_store_unit_id_index` (`store_unit_id`),
  KEY `goods_received_note_items_dispensing_unit_id_index` (`dispensing_unit_id`),
  CONSTRAINT `goods_received_note_items_dispensing_unit_id_foreign` FOREIGN KEY (`dispensing_unit_id`) REFERENCES `medication_units` (`id`) ON DELETE CASCADE,
  CONSTRAINT `goods_received_note_items_grn_id_foreign` FOREIGN KEY (`grn_id`) REFERENCES `goods_received_notes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `goods_received_note_items_store_unit_id_foreign` FOREIGN KEY (`store_unit_id`) REFERENCES `store_units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `goods_received_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `goods_received_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `grn_number` varchar(255) NOT NULL,
  `grn_date` date NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `invoice_number` varchar(255) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `delivery_note_number` varchar(255) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `net_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','received','verified','posted','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `received_by` bigint(20) unsigned NOT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `posted_by` bigint(20) unsigned DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `goods_received_notes_grn_number_unique` (`grn_number`),
  KEY `goods_received_notes_received_by_foreign` (`received_by`),
  KEY `goods_received_notes_verified_by_foreign` (`verified_by`),
  KEY `goods_received_notes_posted_by_foreign` (`posted_by`),
  KEY `goods_received_notes_grn_date_status_index` (`grn_date`,`status`),
  KEY `goods_received_notes_supplier_id_status_index` (`supplier_id`,`status`),
  CONSTRAINT `goods_received_notes_posted_by_foreign` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `goods_received_notes_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `goods_received_notes_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `store_suppliers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `goods_received_notes_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hematology_report_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hematology_report_rows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `row_key` varchar(100) NOT NULL,
  `row_label` varchar(255) NOT NULL,
  `sort_order` tinyint(4) NOT NULL DEFAULT 0,
  `service_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`service_ids`)),
  `fbp_param_name` varchar(100) DEFAULT NULL,
  `required_template_name` varchar(100) DEFAULT NULL,
  `positive_results_only` tinyint(1) NOT NULL DEFAULT 0,
  `track_low_high` tinyint(1) NOT NULL DEFAULT 0,
  `is_section_header` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hematology_report_rows_row_key_unique` (`row_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `icd_10`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `icd_10` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `chapter` varchar(255) DEFAULT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `mtuha_diagnosis` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `icd_10_code_unique` (`code`),
  KEY `icd_10_code_index` (`code`),
  KEY `icd_10_is_active_index` (`is_active`),
  KEY `icd_10_category_is_active_index` (`category`,`is_active`),
  KEY `icd10_mtuha_diagnosis_fk` (`mtuha_diagnosis`),
  CONSTRAINT `icd10_mtuha_diagnosis_fk` FOREIGN KEY (`mtuha_diagnosis`) REFERENCES `mtuha_diagnoses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `icd_diagnoses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `icd_diagnoses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `consultation_id` bigint(20) unsigned NOT NULL,
  `icd_code` varchar(10) NOT NULL,
  `description` text NOT NULL,
  `type` enum('provisional','final') NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `added_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `icd_diagnoses_consultation_id_icd_code_type_unique` (`consultation_id`,`icd_code`,`type`),
  KEY `icd_diagnoses_added_by_foreign` (`added_by`),
  KEY `icd_diagnoses_consultation_id_type_index` (`consultation_id`,`type`),
  KEY `icd_diagnoses_icd_code_index` (`icd_code`),
  CONSTRAINT `icd_diagnoses_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`),
  CONSTRAINT `icd_diagnoses_consultation_id_foreign` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `idsr_diagnoses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `idsr_diagnoses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(90) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `idsr_icd_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `idsr_icd_mapping` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `idsr_diagnosis_id` bigint(20) unsigned NOT NULL,
  `icd_code` varchar(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idsr_icd_mapping_idsr_diagnosis_id_foreign` (`idsr_diagnosis_id`),
  KEY `idsr_icd_mapping_icd_code_index` (`icd_code`),
  CONSTRAINT `idsr_icd_mapping_idsr_diagnosis_id_foreign` FOREIGN KEY (`idsr_diagnosis_id`) REFERENCES `idsr_diagnoses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `investigation_consumables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investigation_consumables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `medical_service_id` bigint(20) unsigned DEFAULT NULL,
  `medication_id` bigint(20) unsigned NOT NULL,
  `quantity_required` decimal(10,2) NOT NULL,
  `is_optional` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `investigation_consumables_investigation_id_is_active_index` (`is_active`),
  KEY `investigation_consumables_item_type_item_id_index` (`medication_id`),
  KEY `investigation_consumables_medical_service_id_medication_id_index` (`medical_service_id`,`medication_id`),
  KEY `investigation_consumables_medical_service_id_item_type_index` (`medical_service_id`),
  CONSTRAINT `investigation_consumables_medical_service_id_foreign` FOREIGN KEY (`medical_service_id`) REFERENCES `medical_services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `investigation_consumptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investigation_consumptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `investigation_id` bigint(20) unsigned NOT NULL,
  `consumption_type` enum('investigation','procedure') NOT NULL DEFAULT 'investigation' COMMENT 'Type of consumption: investigation or procedure',
  `medication_id` bigint(20) unsigned NOT NULL,
  `batch_number` varchar(255) NOT NULL,
  `quantity_used` decimal(10,2) NOT NULL,
  `cost_per_unit` decimal(10,2) NOT NULL,
  `consumed_from_location_id` bigint(20) unsigned NOT NULL,
  `consumed_by` bigint(20) unsigned NOT NULL,
  `consumed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `investigation_consumptions_consumed_by_foreign` (`consumed_by`),
  KEY `investigation_consumptions_investigation_id_medication_id_index` (`investigation_id`,`medication_id`),
  KEY `investigation_consumptions_medication_id_batch_number_index` (`medication_id`,`batch_number`),
  KEY `investigation_consumptions_consumed_from_location_id_index` (`consumed_from_location_id`),
  KEY `investigation_consumptions_consumed_at_index` (`consumed_at`),
  CONSTRAINT `investigation_consumptions_consumed_by_foreign` FOREIGN KEY (`consumed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `investigation_consumptions_consumed_from_location_id_foreign` FOREIGN KEY (`consumed_from_location_id`) REFERENCES `store_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `investigation_consumptions_investigation_id_foreign` FOREIGN KEY (`investigation_id`) REFERENCES `investigations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `investigation_consumptions_medication_id_foreign` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `investigation_form_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investigation_form_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `investigation_id` bigint(20) unsigned NOT NULL,
  `form_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`form_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `investigation_form_data_investigation_id_foreign` (`investigation_id`),
  CONSTRAINT `investigation_form_data_investigation_id_foreign` FOREIGN KEY (`investigation_id`) REFERENCES `investigations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `investigation_forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investigation_forms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `blade_view` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `investigation_template_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investigation_template_results` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `investigation_id` bigint(20) unsigned DEFAULT NULL,
  `template_name` varchar(100) NOT NULL,
  `template_version` varchar(20) NOT NULL DEFAULT '1.0',
  `form_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`form_data`)),
  `form_status` enum('draft','preliminary','final') NOT NULL DEFAULT 'draft',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `reported_by` bigint(20) unsigned NOT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `investigation_template_results_investigation_id_index` (`investigation_id`),
  KEY `investigation_template_results_template_name_index` (`template_name`),
  KEY `investigation_template_results_reported_by_index` (`reported_by`),
  KEY `investigation_template_results_form_status_index` (`form_status`),
  KEY `investigation_template_results_verified_by_foreign` (`verified_by`),
  CONSTRAINT `investigation_template_results_investigation_id_foreign` FOREIGN KEY (`investigation_id`) REFERENCES `investigations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `investigation_template_results_reported_by_foreign` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`),
  CONSTRAINT `investigation_template_results_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `investigations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investigations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `consultation_id` bigint(20) unsigned DEFAULT NULL,
  `doctor_id` bigint(20) unsigned DEFAULT NULL,
  `medical_service_id` bigint(20) unsigned NOT NULL,
  `quantity` smallint(6) NOT NULL DEFAULT 1,
  `insurance_covered_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cash_amount` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `clinical_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`clinical_data`)),
  `batches_used` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON array storing batch info used during investigations: [{batch, expiry, location_id, location_name, quantity_used}]' CHECK (json_valid(`batches_used`)),
  `priority` enum('routine','urgent','stat') NOT NULL DEFAULT 'routine',
  `status` enum('draft','ordered','collected','processing','resulted','cancelled') NOT NULL DEFAULT 'draft',
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `is_discount` tinyint(1) NOT NULL DEFAULT 0,
  `discount_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(255) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `paid_by` bigint(20) unsigned DEFAULT NULL,
  `visit_id` bigint(20) unsigned DEFAULT NULL,
  `folio_item_id` varchar(50) DEFAULT NULL COMMENT 'Insurance folio reference',
  `ordered_at` timestamp NULL DEFAULT NULL,
  `ordered_by` bigint(20) unsigned DEFAULT NULL,
  `collected_at` timestamp NULL DEFAULT NULL,
  `resulted_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) unsigned DEFAULT NULL,
  `collected_by` bigint(20) unsigned DEFAULT NULL,
  `resulted_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `investigations_uuid_unique` (`uuid`),
  KEY `investigations_medical_service_id_foreign` (`medical_service_id`),
  KEY `investigations_visit_id_foreign` (`visit_id`),
  KEY `investigations_collected_by_foreign` (`collected_by`),
  KEY `investigations_resulted_by_foreign` (`resulted_by`),
  KEY `investigations_consultation_id_status_index` (`consultation_id`,`status`),
  KEY `investigations_doctor_id_created_at_index` (`doctor_id`,`created_at`),
  KEY `investigations_status_priority_index` (`status`,`priority`),
  KEY `investigations_patient_id_created_at_index` (`patient_id`,`created_at`),
  KEY `investigations_ordered_by_foreign` (`ordered_by`),
  KEY `investigations_paid_by_foreign` (`paid_by`),
  KEY `investigations_cancelled_by_foreign` (`cancelled_by`),
  KEY `investigations_is_paid_ordered_at_index` (`is_paid`,`ordered_at`),
  KEY `investigations_ordered_at_index` (`ordered_at`),
  KEY `investigations_status_index` (`status`),
  KEY `investigations_medical_service_id_ordered_at_index` (`medical_service_id`,`ordered_at`),
  CONSTRAINT `investigations_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `investigations_collected_by_foreign` FOREIGN KEY (`collected_by`) REFERENCES `users` (`id`),
  CONSTRAINT `investigations_consultation_id_foreign` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `investigations_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`),
  CONSTRAINT `investigations_medical_service_id_foreign` FOREIGN KEY (`medical_service_id`) REFERENCES `medical_services` (`id`),
  CONSTRAINT `investigations_ordered_by_foreign` FOREIGN KEY (`ordered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `investigations_paid_by_foreign` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `investigations_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `investigations_resulted_by_foreign` FOREIGN KEY (`resulted_by`) REFERENCES `users` (`id`),
  CONSTRAINT `investigations_visit_id_foreign` FOREIGN KEY (`visit_id`) REFERENCES `patient_visits` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jubilee_tariff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jubilee_tariff` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `item_code_index` (`item_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lab_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_codes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `coding_system` enum('loinc','snomed') NOT NULL,
  `code` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lab_codes_coding_system_code_unique` (`coding_system`,`code`),
  KEY `lab_codes_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medical_service_insurance_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medical_service_insurance_map` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `medical_service_id` bigint(20) unsigned NOT NULL,
  `patient_category_id` bigint(20) unsigned NOT NULL,
  `insurance_item_code` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ms_patient_category_unique` (`medical_service_id`,`patient_category_id`),
  KEY `medical_service_insurance_map_patient_category_id_foreign` (`patient_category_id`),
  CONSTRAINT `medical_service_insurance_map_medical_service_id_foreign` FOREIGN KEY (`medical_service_id`) REFERENCES `medical_services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medical_service_insurance_map_patient_category_id_foreign` FOREIGN KEY (`patient_category_id`) REFERENCES `patient_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medical_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medical_services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `service_category_id` bigint(20) unsigned DEFAULT NULL,
  `loinc_code_id` bigint(20) unsigned DEFAULT NULL,
  `snomed_code_id` bigint(20) unsigned DEFAULT NULL,
  `requires_sample` tinyint(1) NOT NULL DEFAULT 0,
  `requires_form` tinyint(1) NOT NULL DEFAULT 0,
  `form_type` varchar(255) DEFAULT NULL,
  `result_template_id` bigint(20) unsigned DEFAULT NULL,
  `multiple_parameters` tinyint(1) NOT NULL DEFAULT 0,
  `sample_type` varchar(255) DEFAULT NULL,
  `turnaround_time_hours` int(11) DEFAULT NULL,
  `preparation_instructions` text DEFAULT NULL,
  `selling_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `min_value` decimal(10,4) DEFAULT NULL COMMENT 'Minimum reference value for lab tests',
  `max_value` decimal(10,4) DEFAULT NULL COMMENT 'Maximum reference value for lab tests',
  `unit` varchar(50) DEFAULT NULL COMMENT 'Unit of measurement (e.g., mg/dL, mmol/L, cells/μL)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `medical_services_uuid_unique` (`uuid`),
  KEY `medical_services_code_is_active_index` (`is_active`),
  KEY `medical_services_service_category_id_is_active_index` (`service_category_id`,`is_active`),
  KEY `medical_services_result_template_id_foreign` (`result_template_id`),
  KEY `medical_services_loinc_code_id_foreign` (`loinc_code_id`),
  KEY `medical_services_snomed_code_id_foreign` (`snomed_code_id`),
  CONSTRAINT `medical_services_loinc_code_id_foreign` FOREIGN KEY (`loinc_code_id`) REFERENCES `lab_codes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medical_services_result_template_id_foreign` FOREIGN KEY (`result_template_id`) REFERENCES `result_templates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medical_services_service_category_id_foreign` FOREIGN KEY (`service_category_id`) REFERENCES `service_categories` (`id`),
  CONSTRAINT `medical_services_snomed_code_id_foreign` FOREIGN KEY (`snomed_code_id`) REFERENCES `lab_codes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medication_cash_sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medication_cash_sale_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `cash_sale_id` bigint(20) unsigned NOT NULL,
  `medication_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(8,2) NOT NULL,
  `dosage` varchar(255) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `batches_used` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`batches_used`)),
  `status` enum('pending','dispensed','cancelled') NOT NULL DEFAULT 'pending',
  `dispensing_type` enum('individual','batch') NOT NULL DEFAULT 'batch',
  `quantity_dispensed` decimal(8,2) NOT NULL DEFAULT 0.00,
  `dispensed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `medication_frequency_id` bigint(20) unsigned DEFAULT NULL,
  `administration_route_id` bigint(20) unsigned DEFAULT NULL,
  `duration_days` int(11) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `dispensed_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medication_cash_sale_items_uuid_unique` (`uuid`),
  KEY `medication_cash_sale_items_cash_sale_id_index` (`cash_sale_id`),
  KEY `medication_cash_sale_items_medication_id_index` (`medication_id`),
  KEY `medication_cash_sale_items_medication_frequency_id_foreign` (`medication_frequency_id`),
  KEY `medication_cash_sale_items_administration_route_id_foreign` (`administration_route_id`),
  KEY `medication_cash_sale_items_dispensed_by_foreign` (`dispensed_by`),
  KEY `medication_cash_sale_items_cancelled_by_foreign` (`cancelled_by`),
  CONSTRAINT `medication_cash_sale_items_administration_route_id_foreign` FOREIGN KEY (`administration_route_id`) REFERENCES `administration_routes` (`id`),
  CONSTRAINT `medication_cash_sale_items_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medication_cash_sale_items_cash_sale_id_foreign` FOREIGN KEY (`cash_sale_id`) REFERENCES `medication_cash_sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medication_cash_sale_items_dispensed_by_foreign` FOREIGN KEY (`dispensed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `medication_cash_sale_items_medication_frequency_id_foreign` FOREIGN KEY (`medication_frequency_id`) REFERENCES `medication_frequencies` (`id`),
  CONSTRAINT `medication_cash_sale_items_medication_id_foreign` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medication_cash_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medication_cash_sales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `sale_number` varchar(255) NOT NULL,
  `sale_type` enum('otc','external_prescription') NOT NULL DEFAULT 'otc',
  `external_prescription_details` text DEFAULT NULL,
  `patient_category_id` bigint(20) unsigned NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `final_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','dispensed','cancelled') NOT NULL DEFAULT 'pending',
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) unsigned NOT NULL,
  `dispensed_by` bigint(20) unsigned DEFAULT NULL,
  `dispensed_at` timestamp NULL DEFAULT NULL,
  `paid_by` bigint(20) unsigned DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) unsigned DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `refund_required` tinyint(1) NOT NULL DEFAULT 0,
  `payment_method` enum('cash','card','mobile_money') DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medication_cash_sales_sale_number_unique` (`sale_number`),
  UNIQUE KEY `medication_cash_sales_uuid_unique` (`uuid`),
  KEY `medication_cash_sales_patient_category_id_foreign` (`patient_category_id`),
  KEY `medication_cash_sales_created_by_foreign` (`created_by`),
  KEY `medication_cash_sales_dispensed_by_foreign` (`dispensed_by`),
  KEY `medication_cash_sales_paid_by_foreign` (`paid_by`),
  KEY `medication_cash_sales_sale_number_index` (`sale_number`),
  KEY `medication_cash_sales_status_index` (`status`),
  KEY `medication_cash_sales_created_at_index` (`created_at`),
  KEY `medication_cash_sales_cancelled_by_foreign` (`cancelled_by`),
  KEY `medication_cash_sales_is_paid_status_index` (`is_paid`,`status`),
  CONSTRAINT `medication_cash_sales_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medication_cash_sales_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `medication_cash_sales_dispensed_by_foreign` FOREIGN KEY (`dispensed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `medication_cash_sales_paid_by_foreign` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`),
  CONSTRAINT `medication_cash_sales_patient_category_id_foreign` FOREIGN KEY (`patient_category_id`) REFERENCES `patient_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medication_formulations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medication_formulations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medication_formulations_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medication_frequencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medication_frequencies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `frequency_name` varchar(100) NOT NULL,
  `frequency_code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `times_per_day` int(11) NOT NULL DEFAULT 1,
  `interval_hours` int(11) DEFAULT NULL,
  `administration_times` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`administration_times`)),
  `display_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medication_frequencies_frequency_name_unique` (`frequency_name`),
  UNIQUE KEY `medication_frequencies_frequency_code_unique` (`frequency_code`),
  UNIQUE KEY `medication_frequencies_uuid_unique` (`uuid`),
  KEY `medication_frequencies_is_active_display_order_index` (`is_active`,`display_order`),
  KEY `medication_frequencies_frequency_code_index` (`frequency_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medication_insurance_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medication_insurance_map` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `medication_id` bigint(20) unsigned NOT NULL,
  `patient_category_id` bigint(20) unsigned NOT NULL,
  `insurance_item_code` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `med_patient_category_unique` (`medication_id`,`patient_category_id`),
  KEY `medication_insurance_map_patient_category_id_foreign` (`patient_category_id`),
  CONSTRAINT `medication_insurance_map_medication_id_foreign` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medication_insurance_map_patient_category_id_foreign` FOREIGN KEY (`patient_category_id`) REFERENCES `patient_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medication_ledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medication_ledger` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `medication_id` bigint(20) unsigned NOT NULL,
  `grn_id` bigint(20) unsigned DEFAULT NULL,
  `grn_item_id` bigint(20) unsigned DEFAULT NULL,
  `batch_number` varchar(255) NOT NULL,
  `manufacture_date` date DEFAULT NULL,
  `expiry_date` date NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `quantity_received` decimal(10,2) NOT NULL,
  `status` enum('active','expired','exhausted','damaged') NOT NULL DEFAULT 'active',
  `location_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_medication_batch` (`medication_id`,`batch_number`),
  KEY `medication_ledger_location_id_foreign` (`location_id`),
  KEY `medication_ledger_medication_id_status_index` (`medication_id`,`status`),
  KEY `medication_ledger_batch_number_expiry_date_index` (`batch_number`,`expiry_date`),
  KEY `medication_ledger_grn_id_medication_id_index` (`grn_id`,`medication_id`),
  KEY `medication_ledger_expiry_date_status_index` (`expiry_date`,`status`),
  KEY `medication_ledger_grn_item_id_foreign` (`grn_item_id`),
  CONSTRAINT `medication_ledger_grn_id_foreign` FOREIGN KEY (`grn_id`) REFERENCES `goods_received_notes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medication_ledger_grn_item_id_foreign` FOREIGN KEY (`grn_item_id`) REFERENCES `goods_received_note_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medication_ledger_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `store_locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medication_ledger_medication_id_foreign` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medication_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medication_units` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(50) NOT NULL,
  `unit_code` varchar(10) NOT NULL,
  `unit_symbol` varchar(10) DEFAULT NULL,
  `unit_type` enum('weight','volume','dosage','form','other') NOT NULL DEFAULT 'other',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `description` text DEFAULT NULL,
  `base_conversion_factor` decimal(10,4) NOT NULL DEFAULT 1.0000,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medication_units_unit_name_unique` (`unit_name`),
  UNIQUE KEY `medication_units_unit_code_unique` (`unit_code`),
  KEY `medication_units_is_active_display_order_index` (`is_active`,`display_order`),
  KEY `medication_units_unit_code_index` (`unit_code`),
  KEY `medication_units_unit_type_index` (`unit_type`),
  KEY `medication_units_unit_type_is_active_index` (`unit_type`,`is_active`),
  KEY `medication_units_base_unit_id_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `generic_name` varchar(255) DEFAULT NULL,
  `brand_name` varchar(255) DEFAULT NULL,
  `strength` varchar(255) DEFAULT NULL,
  `formulation_id` bigint(20) unsigned DEFAULT NULL,
  `dispensing_unit_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `msd_code_id` bigint(20) unsigned DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `reorder_level` decimal(10,2) NOT NULL DEFAULT 0.00,
  `maximum_stock_level` decimal(10,2) NOT NULL DEFAULT 0.00,
  `requires_prescription` tinyint(1) NOT NULL DEFAULT 1,
  `is_controlled` tinyint(1) NOT NULL DEFAULT 0,
  `storage_conditions` varchar(255) DEFAULT NULL,
  `selling_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_tracer` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medications_uuid_unique` (`uuid`),
  KEY `medications_name_is_active_index` (`is_active`),
  KEY `medications_expiry_date_is_active_index` (`is_active`),
  KEY `medications_category_id_is_active_index` (`category_id`,`is_active`),
  KEY `medications_requires_prescription_is_active_index` (`requires_prescription`,`is_active`),
  KEY `medications_type_is_active_index` (`is_active`),
  KEY `medications_category_id_type_is_active_index` (`category_id`,`is_active`),
  KEY `medications_formulation_id_index` (`formulation_id`),
  KEY `medications_dispensing_unit_id_index` (`dispensing_unit_id`),
  KEY `medications_msd_code_id_foreign` (`msd_code_id`),
  CONSTRAINT `medications_dispensing_unit_id_foreign` FOREIGN KEY (`dispensing_unit_id`) REFERENCES `medication_units` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medications_formulation_id_foreign` FOREIGN KEY (`formulation_id`) REFERENCES `medication_formulations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medications_msd_code_id_foreign` FOREIGN KEY (`msd_code_id`) REFERENCES `msd_codes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medicine_dispensing_report_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medicine_dispensing_report_rows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `row_key` varchar(100) NOT NULL,
  `group_key` varchar(100) NOT NULL,
  `row_no` varchar(10) DEFAULT NULL,
  `row_no_rowspan` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `drug_label` varchar(255) DEFAULT NULL,
  `drug_rowspan` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `unit_label` varchar(50) NOT NULL,
  `medication_id` bigint(20) unsigned DEFAULT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medicine_dispensing_report_rows_row_key_unique` (`row_key`),
  KEY `medicine_dispensing_report_rows_medication_id_foreign` (`medication_id`),
  CONSTRAINT `medicine_dispensing_report_rows_medication_id_foreign` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `microbiology_report_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `microbiology_report_rows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `row_key` varchar(100) NOT NULL,
  `row_label` varchar(255) NOT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `service_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`service_ids`)),
  `show_total` tinyint(1) NOT NULL DEFAULT 1,
  `show_positive` tinyint(1) NOT NULL DEFAULT 1,
  `required_template_name` varchar(100) DEFAULT NULL,
  `param_name` varchar(100) DEFAULT NULL,
  `match_field` varchar(10) NOT NULL DEFAULT 'status',
  `match_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`match_values`)),
  `json_path` varchar(200) DEFAULT NULL,
  `json_path_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`json_path_values`)),
  `is_bold` tinyint(1) NOT NULL DEFAULT 0,
  `include_in_grand_total` tinyint(1) NOT NULL DEFAULT 1,
  `is_configurable` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `microbiology_report_rows_row_key_unique` (`row_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `msd_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msd_codes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `msd_codes_code_unique` (`code`),
  KEY `msd_codes_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mtuha_diagnoses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mtuha_diagnoses` (
  `id` int(11) NOT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mtuha_diagnoses_description_unique` (`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `nhif_claim_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nhif_claim_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `claim_no` varchar(255) NOT NULL,
  `claim_year` smallint(5) unsigned NOT NULL,
  `claim_month` tinyint(3) unsigned NOT NULL,
  `number_of_folios` int(11) DEFAULT NULL,
  `amount_claimed` decimal(15,2) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nhif_claim_batches_claim_no_unique` (`claim_no`),
  KEY `nhif_claim_batches_claim_year_claim_month_index` (`claim_year`,`claim_month`),
  KEY `nhif_claim_batches_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `nhif_claim_diseases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nhif_claim_diseases` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nhif_claim_id` bigint(20) unsigned NOT NULL,
  `folio_disease_id` char(36) DEFAULT NULL,
  `disease_code` varchar(255) NOT NULL,
  `disease_name` varchar(255) DEFAULT NULL,
  `icd_diagnosis_id` bigint(20) unsigned DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nhif_claim_diseases_icd_diagnosis_id_foreign` (`icd_diagnosis_id`),
  KEY `nhif_claim_diseases_nhif_claim_id_disease_code_index` (`nhif_claim_id`,`disease_code`),
  CONSTRAINT `nhif_claim_diseases_icd_diagnosis_id_foreign` FOREIGN KEY (`icd_diagnosis_id`) REFERENCES `icd_diagnoses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `nhif_claim_diseases_nhif_claim_id_foreign` FOREIGN KEY (`nhif_claim_id`) REFERENCES `nhif_claims` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `nhif_claim_errors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nhif_claim_errors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nhif_claim_id` bigint(20) unsigned DEFAULT NULL,
  `visit_id` varchar(255) DEFAULT NULL,
  `error_message` text NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `resolution_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nhif_claim_errors_nhif_claim_id_foreign` (`nhif_claim_id`),
  CONSTRAINT `nhif_claim_errors_nhif_claim_id_foreign` FOREIGN KEY (`nhif_claim_id`) REFERENCES `nhif_claims` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `nhif_claim_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nhif_claim_feedback` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nhif_claim_id` bigint(20) unsigned DEFAULT NULL,
  `submission_no` varchar(255) NOT NULL,
  `date_submitted` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `claim_year` int(11) NOT NULL,
  `claim_month` int(11) NOT NULL,
  `folio_no` int(11) NOT NULL,
  `card_no` varchar(255) NOT NULL,
  `authorization_no` varchar(255) DEFAULT NULL,
  `amount_claimed` decimal(10,2) NOT NULL,
  `remarks` text DEFAULT NULL,
  `nhif_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`nhif_response`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nhif_claim_feedback_submission_no_unique` (`submission_no`),
  KEY `nhif_claim_feedback_nhif_claim_id_foreign` (`nhif_claim_id`),
  KEY `nhif_claim_feedback_submission_no_date_submitted_index` (`submission_no`,`date_submitted`),
  CONSTRAINT `nhif_claim_feedback_nhif_claim_id_foreign` FOREIGN KEY (`nhif_claim_id`) REFERENCES `nhif_claims` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `nhif_claim_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nhif_claim_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nhif_claim_id` bigint(20) unsigned NOT NULL,
  `folio_item_id` char(36) DEFAULT NULL,
  `item_code` varchar(255) NOT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `other_details` text DEFAULT NULL,
  `item_quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `amount_claimed` decimal(10,2) NOT NULL,
  `approval_ref_no` varchar(255) DEFAULT NULL,
  `medical_service_id` bigint(20) unsigned DEFAULT NULL,
  `medication_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nhif_claim_items_medical_service_id_foreign` (`medical_service_id`),
  KEY `nhif_claim_items_medication_id_foreign` (`medication_id`),
  KEY `nhif_claim_items_nhif_claim_id_item_code_index` (`nhif_claim_id`,`item_code`),
  CONSTRAINT `nhif_claim_items_medical_service_id_foreign` FOREIGN KEY (`medical_service_id`) REFERENCES `medical_services` (`id`) ON DELETE SET NULL,
  CONSTRAINT `nhif_claim_items_medication_id_foreign` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE SET NULL,
  CONSTRAINT `nhif_claim_items_nhif_claim_id_foreign` FOREIGN KEY (`nhif_claim_id`) REFERENCES `nhif_claims` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `nhif_claims`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nhif_claims` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `folio_id` char(36) NOT NULL,
  `nhif_claim_batch_id` bigint(20) unsigned DEFAULT NULL,
  `claim_year` year(4) NOT NULL,
  `claim_month` tinyint(4) NOT NULL,
  `folio_no` int(11) NOT NULL,
  `serial_no` varchar(255) DEFAULT NULL,
  `card_no` varchar(255) NOT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `patient_visit_id` bigint(20) unsigned DEFAULT NULL,
  `authorization_no` varchar(255) DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `patient_type_code` varchar(255) NOT NULL DEFAULT 'OUT',
  `date_admitted` date DEFAULT NULL,
  `date_discharged` date DEFAULT NULL,
  `practitioner_no` varchar(255) DEFAULT NULL,
  `total_amount_claimed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `claim_status` varchar(255) NOT NULL DEFAULT 'draft',
  `submission_date` timestamp NULL DEFAULT NULL,
  `response_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_data`)),
  `submitted_by` bigint(20) unsigned DEFAULT NULL,
  `facility_code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nhif_claims_folio_id_unique` (`folio_id`),
  UNIQUE KEY `nhif_claims_patient_visit_id_unique` (`patient_visit_id`),
  KEY `nhif_claims_patient_visit_id_foreign` (`patient_visit_id`),
  KEY `nhif_claims_submitted_by_foreign` (`submitted_by`),
  KEY `nhif_claims_card_no_claim_year_claim_month_index` (`card_no`,`claim_year`,`claim_month`),
  KEY `nhif_claims_patient_id_patient_visit_id_index` (`patient_id`,`patient_visit_id`),
  KEY `nhif_claims_claim_status_index` (`claim_status`),
  KEY `nhif_claims_nhif_claim_batch_id_index` (`nhif_claim_batch_id`),
  CONSTRAINT `nhif_claims_nhif_claim_batch_id_foreign` FOREIGN KEY (`nhif_claim_batch_id`) REFERENCES `nhif_claim_batches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `nhif_claims_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nhif_claims_patient_visit_id_foreign` FOREIGN KEY (`patient_visit_id`) REFERENCES `patient_visits` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nhif_claims_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `nhif_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nhif_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `card_no` varchar(255) NOT NULL,
  `card_status` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `authorization_status` varchar(255) DEFAULT NULL,
  `authorization_no` varchar(255) DEFAULT NULL,
  `employer_no` varchar(255) DEFAULT NULL,
  `scheme_id` varchar(255) DEFAULT NULL,
  `product_code` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `patient_id` bigint(20) unsigned DEFAULT NULL,
  `verification_date` timestamp NULL DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nhif_members_card_no_unique` (`card_no`),
  KEY `nhif_members_verified_by_foreign` (`verified_by`),
  KEY `nhif_members_card_no_card_status_index` (`card_no`,`card_status`),
  KEY `nhif_members_patient_id_index` (`patient_id`),
  CONSTRAINT `nhif_members_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nhif_members_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `nhif_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nhif_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mode` varchar(255) NOT NULL DEFAULT 'test',
  `username` text DEFAULT NULL,
  `password` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `nhif_tariffs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nhif_tariffs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `facility_code` varchar(255) NOT NULL,
  `item_code` varchar(255) NOT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `scheme_id` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `is_restricted` tinyint(1) NOT NULL DEFAULT 0,
  `is_excluded` tinyint(1) NOT NULL DEFAULT 0,
  `excluded_for_products` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`excluded_for_products`)),
  `last_updated` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nhif_tariffs_facility_code_item_code_scheme_id_unique` (`facility_code`,`item_code`,`scheme_id`),
  KEY `nhif_tariffs_facility_code_scheme_id_index` (`facility_code`,`scheme_id`),
  KEY `nhif_tariffs_item_code_index` (`item_code`),
  KEY `nhif_tariffs_item_name_index` (`item_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `parasitology_report_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parasitology_report_rows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `row_key` varchar(100) NOT NULL,
  `row_label` varchar(255) NOT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `service_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`service_ids`)),
  `param_name` varchar(100) DEFAULT NULL,
  `required_template_name` varchar(100) DEFAULT NULL,
  `positive_statuses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`positive_statuses`)),
  `shows_total` tinyint(1) NOT NULL DEFAULT 1,
  `shows_positive` tinyint(1) NOT NULL DEFAULT 1,
  `is_section_header` tinyint(1) NOT NULL DEFAULT 0,
  `is_configurable` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parasitology_report_rows_row_key_unique` (`row_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `user_email` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `password_reset_requests_admin_id_index` (`admin_id`),
  KEY `password_reset_requests_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `past_medical_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `past_medical_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `allergies` text DEFAULT NULL,
  `chronic_conditions` text DEFAULT NULL,
  `previous_surgeries` text DEFAULT NULL,
  `family_history` text DEFAULT NULL,
  `social_history` text DEFAULT NULL,
  `smoking_status` enum('non_smoker','former_smoker','current_smoker') DEFAULT NULL,
  `alcohol_use` enum('none','occasional','moderate','heavy') DEFAULT NULL,
  `current_medications` text DEFAULT NULL,
  `immunization_history` text DEFAULT NULL,
  `reproductive_history` text DEFAULT NULL,
  `occupational_history` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `past_medical_history_patient_id_unique` (`patient_id`),
  UNIQUE KEY `past_medical_history_uuid_unique` (`uuid`),
  CONSTRAINT `past_medical_history_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `patient_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `description` varchar(50) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `type` enum('cash','insurance') NOT NULL DEFAULT 'cash',
  `tariffs_table` varchar(255) DEFAULT NULL,
  `copay_policy` enum('charge_patient','insurance_only') NOT NULL DEFAULT 'charge_patient',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_categories_description_unique` (`description`),
  UNIQUE KEY `patient_categories_uuid_unique` (`uuid`),
  KEY `patient_categories_created_by_foreign` (`created_by`),
  CONSTRAINT `patient_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `patient_categories_before_delete_set_patients_category`
BEFORE DELETE ON `patient_categories`
FOR EACH ROW
BEGIN
    UPDATE `patients` SET `patient_category` = 1 WHERE `patient_category` = OLD.`id`;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `patient_category_visit_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_category_visit_type` (
  `patient_category_id` bigint(20) unsigned NOT NULL,
  `visit_type_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`patient_category_id`,`visit_type_id`),
  KEY `patient_category_visit_type_visit_type_id_index` (`visit_type_id`),
  CONSTRAINT `patient_category_visit_type_patient_category_id_foreign` FOREIGN KEY (`patient_category_id`) REFERENCES `patient_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patient_category_visit_type_visit_type_id_foreign` FOREIGN KEY (`visit_type_id`) REFERENCES `visit_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `patient_referrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_referrals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `consultation_id` bigint(20) unsigned NOT NULL,
  `visit_id` bigint(20) unsigned NOT NULL,
  `referral_hospital_id` bigint(20) unsigned NOT NULL,
  `referral_department_id` bigint(20) unsigned NOT NULL,
  `letter_heading` varchar(255) NOT NULL,
  `letter_template` text DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `letter_closing` text DEFAULT NULL,
  `referral_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_referrals_uuid_unique` (`uuid`),
  KEY `patient_referrals_patient_id_foreign` (`patient_id`),
  KEY `patient_referrals_consultation_id_foreign` (`consultation_id`),
  KEY `patient_referrals_visit_id_foreign` (`visit_id`),
  KEY `patient_referrals_referral_hospital_id_foreign` (`referral_hospital_id`),
  KEY `patient_referrals_referral_department_id_foreign` (`referral_department_id`),
  KEY `patient_referrals_created_by_foreign` (`created_by`),
  CONSTRAINT `patient_referrals_consultation_id_foreign` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patient_referrals_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `patient_referrals_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patient_referrals_referral_department_id_foreign` FOREIGN KEY (`referral_department_id`) REFERENCES `referral_departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patient_referrals_referral_hospital_id_foreign` FOREIGN KEY (`referral_hospital_id`) REFERENCES `referral_hospitals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patient_referrals_visit_id_foreign` FOREIGN KEY (`visit_id`) REFERENCES `patient_visits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `patient_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_visits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `patient` bigint(20) unsigned NOT NULL,
  `visit_type` bigint(20) unsigned NOT NULL,
  `visit_date` datetime NOT NULL,
  `visit_category` bigint(20) unsigned NOT NULL,
  `doctor` bigint(20) unsigned DEFAULT NULL,
  `amount_cash` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount_covered` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sic_no` varchar(30) DEFAULT NULL,
  `authorization_no` varchar(30) DEFAULT NULL,
  `nhif_reference_no` varchar(30) DEFAULT NULL,
  `item_code` varchar(30) DEFAULT NULL,
  `folio_item_id` varchar(32) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  `visit_status` tinyint(4) NOT NULL DEFAULT 1,
  `post_status` tinyint(4) NOT NULL DEFAULT 0,
  `vital_status` tinyint(4) NOT NULL DEFAULT 1,
  `pitc_at` datetime DEFAULT NULL,
  `vitals_at` datetime DEFAULT NULL,
  `consulted_at` datetime DEFAULT NULL,
  `resulted_at` datetime DEFAULT NULL,
  `informed_at` timestamp NULL DEFAULT NULL,
  `informed_by` bigint(20) unsigned DEFAULT NULL,
  `signature` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_visits_uuid_unique` (`uuid`),
  KEY `patient_visits_patient_foreign` (`patient`),
  KEY `patient_visits_visit_type_foreign` (`visit_type`),
  KEY `patient_visits_visit_category_foreign` (`visit_category`),
  KEY `patient_visits_doctor_foreign` (`doctor`),
  KEY `patient_visits_created_at_index` (`created_at`),
  KEY `patient_visits_visit_date_index` (`visit_date`),
  KEY `patient_visits_informed_by_foreign` (`informed_by`),
  CONSTRAINT `patient_visits_doctor_foreign` FOREIGN KEY (`doctor`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE,
  CONSTRAINT `patient_visits_informed_by_foreign` FOREIGN KEY (`informed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `patient_visits_patient_foreign` FOREIGN KEY (`patient`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patient_visits_visit_category_foreign` FOREIGN KEY (`visit_category`) REFERENCES `patient_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patient_visits_visit_type_foreign` FOREIGN KEY (`visit_type`) REFERENCES `visit_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `patients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `first_name` varchar(30) NOT NULL,
  `middle_name` varchar(30) DEFAULT NULL,
  `last_name` varchar(30) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female','other') NOT NULL DEFAULT 'other',
  `contact` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `residence` varchar(30) DEFAULT NULL,
  `occupation` varchar(90) DEFAULT NULL,
  `nida` varchar(32) DEFAULT NULL,
  `patient_category` bigint(20) unsigned NOT NULL DEFAULT 1,
  `card_number` varchar(30) DEFAULT NULL,
  `membership_number` varchar(30) DEFAULT NULL,
  `vote` varchar(30) DEFAULT NULL,
  `SchemeID` int(11) DEFAULT NULL,
  `ProductCode` varchar(30) DEFAULT NULL,
  `PackageID` int(11) DEFAULT NULL,
  `HasSupplementary` enum('Yes','No') NOT NULL DEFAULT 'No',
  `SchemeName` varchar(90) DEFAULT NULL,
  `mtuha_new` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_by` bigint(20) unsigned NOT NULL DEFAULT 1,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `legacy_mrn` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patients_uuid_unique` (`uuid`),
  KEY `patients_patient_category_foreign` (`patient_category`),
  KEY `patients_created_by_foreign` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_receipts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `receipt_date` datetime NOT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `visit_id` bigint(20) unsigned DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `cash_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `insurance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `consultation_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `investigation_fees` decimal(10,2) NOT NULL DEFAULT 0.00,
  `medication_fees` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_fees` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cash','insurance','bank','mobile_money','other') NOT NULL DEFAULT 'cash',
  `payment_reference` varchar(100) DEFAULT NULL,
  `insurance_scheme` varchar(100) DEFAULT NULL,
  `insurance_number` varchar(100) DEFAULT NULL,
  `authorization_number` varchar(100) DEFAULT NULL,
  `status` enum('draft','printed','cancelled') NOT NULL DEFAULT 'draft',
  `printed_at` datetime DEFAULT NULL,
  `printed_by` bigint(20) unsigned DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `cancelled_by` bigint(20) unsigned DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_receipts_receipt_number_unique` (`receipt_number`),
  UNIQUE KEY `payment_receipts_uuid_unique` (`uuid`),
  KEY `payment_receipts_visit_id_foreign` (`visit_id`),
  KEY `payment_receipts_created_by_foreign` (`created_by`),
  KEY `payment_receipts_printed_by_foreign` (`printed_by`),
  KEY `payment_receipts_cancelled_by_foreign` (`cancelled_by`),
  KEY `payment_receipts_receipt_date_index` (`receipt_date`),
  KEY `payment_receipts_patient_id_visit_id_index` (`patient_id`,`visit_id`),
  KEY `payment_receipts_status_index` (`status`),
  CONSTRAINT `payment_receipts_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_receipts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `payment_receipts_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `payment_receipts_printed_by_foreign` FOREIGN KEY (`printed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_receipts_visit_id_foreign` FOREIGN KEY (`visit_id`) REFERENCES `patient_visits` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `prescriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prescriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `consultation_id` bigint(20) unsigned NOT NULL,
  `doctor_id` bigint(20) unsigned NOT NULL,
  `medication_id` bigint(20) unsigned NOT NULL,
  `dosage` varchar(100) NOT NULL,
  `administration_route_id` bigint(20) unsigned NOT NULL,
  `frequency_id` bigint(20) unsigned NOT NULL,
  `duration_days` smallint(6) NOT NULL,
  `quantity` decimal(8,2) NOT NULL,
  `quantity_dispensed` decimal(10,2) DEFAULT NULL,
  `batches_used` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Track which batches were used for dispensing with location info' CHECK (json_valid(`batches_used`)),
  `dispensing_type` enum('individual','batch') NOT NULL DEFAULT 'batch',
  `insurance_covered_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cash_amount` decimal(10,2) NOT NULL,
  `instructions` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `pharmacist_notes` text DEFAULT NULL,
  `status` enum('draft','prescribed','prepared','dispensed','cancelled') NOT NULL DEFAULT 'draft',
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `is_discount` tinyint(1) NOT NULL DEFAULT 0,
  `discount_percent` decimal(5,2) DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `paid_by` bigint(20) unsigned DEFAULT NULL,
  `visit_id` bigint(20) unsigned DEFAULT NULL,
  `folio_item_id` varchar(50) DEFAULT NULL COMMENT 'Insurance folio reference',
  `prescribed_at` timestamp NULL DEFAULT NULL,
  `prepared_at` timestamp NULL DEFAULT NULL,
  `dispensed_at` timestamp NULL DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `prepared_by` bigint(20) unsigned DEFAULT NULL,
  `dispensed_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prescriptions_uuid_unique` (`uuid`),
  KEY `prescriptions_medication_id_foreign` (`medication_id`),
  KEY `prescriptions_administration_route_id_foreign` (`administration_route_id`),
  KEY `prescriptions_frequency_id_foreign` (`frequency_id`),
  KEY `prescriptions_visit_id_foreign` (`visit_id`),
  KEY `prescriptions_prepared_by_foreign` (`prepared_by`),
  KEY `prescriptions_dispensed_by_foreign` (`dispensed_by`),
  KEY `prescriptions_consultation_id_status_index` (`consultation_id`,`status`),
  KEY `prescriptions_doctor_id_created_at_index` (`doctor_id`,`created_at`),
  KEY `prescriptions_status_created_at_index` (`status`,`created_at`),
  KEY `prescriptions_patient_id_created_at_index` (`patient_id`,`created_at`),
  KEY `prescriptions_paid_by_foreign` (`paid_by`),
  KEY `prescriptions_status_dispensed_at_index` (`status`,`dispensed_at`),
  KEY `prescriptions_status_updated_at_index` (`status`,`updated_at`),
  KEY `prescriptions_medication_id_dispensed_at_index` (`medication_id`,`dispensed_at`),
  CONSTRAINT `prescriptions_administration_route_id_foreign` FOREIGN KEY (`administration_route_id`) REFERENCES `administration_routes` (`id`),
  CONSTRAINT `prescriptions_consultation_id_foreign` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prescriptions_dispensed_by_foreign` FOREIGN KEY (`dispensed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `prescriptions_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`),
  CONSTRAINT `prescriptions_frequency_id_foreign` FOREIGN KEY (`frequency_id`) REFERENCES `medication_frequencies` (`id`),
  CONSTRAINT `prescriptions_medication_id_foreign` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`),
  CONSTRAINT `prescriptions_paid_by_foreign` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prescriptions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prescriptions_prepared_by_foreign` FOREIGN KEY (`prepared_by`) REFERENCES `users` (`id`),
  CONSTRAINT `prescriptions_visit_id_foreign` FOREIGN KEY (`visit_id`) REFERENCES `patient_visits` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `procedure_consumptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `procedure_consumptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `procedure_id` bigint(20) unsigned NOT NULL,
  `medication_id` bigint(20) unsigned NOT NULL,
  `batch_number` varchar(255) NOT NULL,
  `quantity_used` decimal(10,2) NOT NULL,
  `cost_per_unit` decimal(10,2) NOT NULL,
  `consumed_from_location_id` bigint(20) unsigned NOT NULL,
  `consumed_by` bigint(20) unsigned NOT NULL,
  `consumed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `procedure_consumptions_consumed_by_foreign` (`consumed_by`),
  KEY `procedure_consumptions_procedure_id_medication_id_index` (`procedure_id`,`medication_id`),
  KEY `procedure_consumptions_medication_id_batch_number_index` (`medication_id`,`batch_number`),
  KEY `procedure_consumptions_consumed_from_location_id_index` (`consumed_from_location_id`),
  KEY `procedure_consumptions_consumed_at_index` (`consumed_at`),
  CONSTRAINT `procedure_consumptions_consumed_by_foreign` FOREIGN KEY (`consumed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `procedure_consumptions_consumed_from_location_id_foreign` FOREIGN KEY (`consumed_from_location_id`) REFERENCES `store_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `procedure_consumptions_medication_id_foreign` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `procedure_consumptions_procedure_id_foreign` FOREIGN KEY (`procedure_id`) REFERENCES `medical_services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reconciliation_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reconciliation_runs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `run_type` enum('manual','auto','scheduled') NOT NULL DEFAULT 'manual',
  `triggered_by` bigint(20) unsigned DEFAULT NULL,
  `status` enum('completed','failed','in_progress') NOT NULL DEFAULT 'in_progress',
  `total_medications_checked` int(10) unsigned NOT NULL DEFAULT 0,
  `discrepancies_found` int(10) unsigned NOT NULL DEFAULT 0,
  `corrections_applied` int(10) unsigned NOT NULL DEFAULT 0,
  `duration_seconds` int(10) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reconciliation_runs_triggered_by_foreign` (`triggered_by`),
  KEY `reconciliation_runs_status_index` (`status`),
  KEY `reconciliation_runs_created_at_index` (`created_at`),
  CONSTRAINT `reconciliation_runs_triggered_by_foreign` FOREIGN KEY (`triggered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `referral_departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referral_departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `referral_hospital_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `referral_departments_uuid_unique` (`uuid`),
  KEY `referral_departments_referral_hospital_id_foreign` (`referral_hospital_id`),
  CONSTRAINT `referral_departments_referral_hospital_id_foreign` FOREIGN KEY (`referral_hospital_id`) REFERENCES `referral_hospitals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `referral_hospitals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referral_hospitals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `referral_hospitals_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `result_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `result_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Display name for the template',
  `code` varchar(255) NOT NULL COMMENT 'Unique code matching enum values',
  `description` varchar(255) DEFAULT NULL COMMENT 'Description of the template',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `result_templates_code_unique` (`code`),
  KEY `result_templates_code_index` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sample_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sample_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(10) NOT NULL,
  `description` text DEFAULT NULL,
  `container_type` varchar(255) DEFAULT NULL,
  `color_code` varchar(20) DEFAULT NULL,
  `volume_ml` decimal(8,2) DEFAULT NULL,
  `collection_instructions` text DEFAULT NULL,
  `storage_requirements` text DEFAULT NULL,
  `stability_hours` int(11) DEFAULT NULL,
  `requires_fasting` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sample_types_name_unique` (`name`),
  UNIQUE KEY `sample_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `serology_report_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `serology_report_rows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `row_key` varchar(100) NOT NULL,
  `row_label` varchar(255) NOT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `service_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`service_ids`)),
  `required_template_name` varchar(100) DEFAULT NULL,
  `positive_statuses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`positive_statuses`)),
  `cd4_filter` varchar(20) DEFAULT NULL,
  `is_configurable` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `serology_report_rows_row_key_unique` (`row_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `service_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_categories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `shib_tariff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shib_tariff` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `item_code_index` (`item_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_corrections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_corrections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `medication_id` bigint(20) unsigned NOT NULL,
  `location_id` bigint(20) unsigned DEFAULT NULL,
  `correction_type` enum('ledger','location_stock','auto','expired_status') NOT NULL,
  `field_corrected` varchar(100) NOT NULL,
  `old_value` decimal(12,4) DEFAULT NULL,
  `new_value` decimal(12,4) DEFAULT NULL,
  `difference` decimal(12,4) DEFAULT NULL,
  `reason` varchar(500) NOT NULL,
  `notes` text DEFAULT NULL,
  `corrected_by` bigint(20) unsigned DEFAULT NULL,
  `correction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('applied','reversed') NOT NULL DEFAULT 'applied',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_corrections_location_id_foreign` (`location_id`),
  KEY `stock_corrections_medication_id_correction_date_index` (`medication_id`,`correction_date`),
  KEY `stock_corrections_corrected_by_index` (`corrected_by`),
  CONSTRAINT `stock_corrections_corrected_by_foreign` FOREIGN KEY (`corrected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_corrections_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `store_locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_corrections_medication_id_foreign` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `store_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `store_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `store_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `store_locations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('store','dispensing','laboratory','nursing','radiology') NOT NULL,
  `manager_name` varchar(255) DEFAULT NULL,
  `manager_contact` varchar(255) DEFAULT NULL,
  `can_request` tinyint(1) NOT NULL DEFAULT 1,
  `can_issue` tinyint(1) NOT NULL DEFAULT 0,
  `can_receive` tinyint(1) NOT NULL DEFAULT 0,
  `requires_approval` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_locations_code_unique` (`code`),
  KEY `store_locations_is_active_type_index` (`is_active`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `store_locations_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `store_locations_stock` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `location_id` bigint(20) unsigned NOT NULL,
  `medication_id` bigint(20) unsigned NOT NULL,
  `requisition_id` bigint(20) unsigned DEFAULT NULL,
  `requisition_item_id` bigint(20) unsigned DEFAULT NULL,
  `batch_number` varchar(255) NOT NULL,
  `manufacture_date` date DEFAULT NULL,
  `expiry_date` date NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','expired','depleted') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_location_medication_batch_expiry` (`location_id`,`medication_id`,`batch_number`,`expiry_date`),
  KEY `store_locations_stock_location_id_medication_id_index` (`location_id`,`medication_id`),
  KEY `store_locations_stock_medication_id_batch_number_index` (`medication_id`,`batch_number`),
  KEY `store_locations_stock_expiry_date_status_index` (`expiry_date`,`status`),
  KEY `store_locations_stock_status_index` (`status`),
  KEY `store_locations_stock_requisition_medication_index` (`requisition_id`,`medication_id`),
  KEY `store_locations_stock_requisition_item_index` (`requisition_item_id`),
  KEY `store_locations_stock_manufacture_date_index` (`manufacture_date`),
  KEY `store_locations_stock_unit_cost_index` (`unit_cost`),
  CONSTRAINT `store_locations_stock_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `store_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `store_locations_stock_medication_id_foreign` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `store_locations_stock_requisition_id_foreign` FOREIGN KEY (`requisition_id`) REFERENCES `store_requisitions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `store_locations_stock_requisition_item_id_foreign` FOREIGN KEY (`requisition_item_id`) REFERENCES `store_requisition_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `store_requisition_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `store_requisition_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `requisition_id` bigint(20) unsigned NOT NULL,
  `item_type` enum('medication','consumable') NOT NULL DEFAULT 'medication',
  `item_id` bigint(20) unsigned NOT NULL,
  `requested_quantity` decimal(10,2) NOT NULL,
  `approved_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `issued_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `justification` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','issued','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_requisition_items_requisition_id_item_type_item_id_index` (`requisition_id`,`item_type`,`item_id`),
  CONSTRAINT `store_requisition_items_requisition_id_foreign` FOREIGN KEY (`requisition_id`) REFERENCES `store_requisitions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `store_requisitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `store_requisitions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `requisition_number` varchar(255) NOT NULL,
  `requisition_date` date NOT NULL,
  `requesting_location_id` bigint(20) unsigned NOT NULL,
  `issuing_location_id` bigint(20) unsigned NOT NULL,
  `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `required_date` date DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `total_estimated_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','submitted','approved','partially_issued','fully_issued','cancelled','rejected') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `requested_by` bigint(20) unsigned NOT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `issued_by` bigint(20) unsigned DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_requisitions_requisition_number_unique` (`requisition_number`),
  KEY `store_requisitions_issuing_location_id_foreign` (`issuing_location_id`),
  KEY `store_requisitions_requested_by_foreign` (`requested_by`),
  KEY `store_requisitions_approved_by_foreign` (`approved_by`),
  KEY `store_requisitions_issued_by_foreign` (`issued_by`),
  KEY `store_requisitions_requisition_date_status_index` (`requisition_date`,`status`),
  KEY `store_requisitions_requesting_location_id_status_index` (`requesting_location_id`,`status`),
  CONSTRAINT `store_requisitions_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `store_requisitions_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `store_requisitions_issuing_location_id_foreign` FOREIGN KEY (`issuing_location_id`) REFERENCES `store_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `store_requisitions_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `store_requisitions_requesting_location_id_foreign` FOREIGN KEY (`requesting_location_id`) REFERENCES `store_locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `store_stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `store_stock_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_type` enum('medication','consumable') NOT NULL DEFAULT 'medication',
  `item_id` bigint(20) unsigned NOT NULL,
  `store_location_id` bigint(20) unsigned NOT NULL,
  `from_location_id` bigint(20) unsigned DEFAULT NULL,
  `to_location_id` bigint(20) unsigned DEFAULT NULL,
  `movement_type` enum('in','out','transfer','adjustment','waste') NOT NULL DEFAULT 'in',
  `transaction_type` enum('purchase','dispensing','requisition','transfer','adjustment','waste','return','consumption','disposal') NOT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `batch_number` varchar(255) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `movement_date` datetime NOT NULL,
  `balance_before` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance_after` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_stock_movements_store_location_id_foreign` (`store_location_id`),
  KEY `store_stock_movements_created_by_foreign` (`created_by`),
  KEY `idx_stock_movements_item_location` (`item_type`,`item_id`,`store_location_id`),
  KEY `store_stock_movements_movement_type_transaction_type_index` (`movement_type`,`transaction_type`),
  KEY `store_stock_movements_reference_number_reference_id_index` (`reference_number`,`reference_id`),
  KEY `store_stock_movements_from_location_id_foreign` (`from_location_id`),
  KEY `store_stock_movements_to_location_id_foreign` (`to_location_id`),
  CONSTRAINT `store_stock_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `store_stock_movements_from_location_id_foreign` FOREIGN KEY (`from_location_id`) REFERENCES `store_locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `store_stock_movements_store_location_id_foreign` FOREIGN KEY (`store_location_id`) REFERENCES `store_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `store_stock_movements_to_location_id_foreign` FOREIGN KEY (`to_location_id`) REFERENCES `store_locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `store_suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `store_suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `tax_number` varchar(255) DEFAULT NULL,
  `license_number` varchar(255) DEFAULT NULL,
  `credit_limit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `credit_days` int(11) NOT NULL DEFAULT 0,
  `payment_terms` varchar(100) DEFAULT 'credit',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_suppliers_is_active_name_index` (`is_active`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `store_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `store_units` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('store','dispensing','both') NOT NULL DEFAULT 'both',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_units_code_unique` (`code`),
  KEY `store_units_is_active_type_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `strategies_tariff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `strategies_tariff` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `item_code_index` (`item_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sync_conflicts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_conflicts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `table_name` varchar(64) NOT NULL,
  `record_uuid` char(36) NOT NULL,
  `local_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`local_payload`)),
  `incoming_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`incoming_payload`)),
  `detected_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` bigint(20) unsigned DEFAULT NULL,
  `resolution` enum('kept_local','kept_incoming','merged') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sync_conflicts_resolved_at_index` (`resolved_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sync_outbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_outbox` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `table_name` varchar(64) NOT NULL,
  `record_uuid` char(36) NOT NULL,
  `operation` enum('insert','update','delete') NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `origin_site` varchar(32) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sync_outbox_synced_at_created_at_index` (`synced_at`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sync_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_state` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `remote_site` varchar(32) NOT NULL,
  `last_push_at` timestamp NULL DEFAULT NULL,
  `last_pull_at` timestamp NULL DEFAULT NULL,
  `last_pull_outbox_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sync_state_remote_site_unique` (`remote_site`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `systemic_examinations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `systemic_examinations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `consultation_id` bigint(20) unsigned NOT NULL,
  `visit_id` bigint(20) unsigned NOT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `examination_type` varchar(255) NOT NULL DEFAULT 'General',
  `general_findings` text DEFAULT NULL,
  `cardiovascular_system` text DEFAULT NULL,
  `respiratory_system` text DEFAULT NULL,
  `gastrointestinal_system` text DEFAULT NULL,
  `nervous_system` text DEFAULT NULL,
  `musculoskeletal_system` text DEFAULT NULL,
  `genitourinary_system` text DEFAULT NULL,
  `endocrine_system` text DEFAULT NULL,
  `skin_examination` text DEFAULT NULL,
  `psychiatric_assessment` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` bigint(20) unsigned NOT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `examinations_created_by_foreign` (`created_by`),
  KEY `examinations_updated_by_foreign` (`updated_by`),
  KEY `examinations_consultation_id_status_index` (`consultation_id`,`status`),
  KEY `systemic_examinations_visit_id_foreign` (`visit_id`),
  KEY `systemic_examinations_patient_id_foreign` (`patient_id`),
  CONSTRAINT `examinations_consultation_id_foreign` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `examinations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `examinations_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`),
  CONSTRAINT `systemic_examinations_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `systemic_examinations_visit_id_foreign` FOREIGN KEY (`visit_id`) REFERENCES `patient_visits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unfit_medications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unfit_medications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reference_number` varchar(255) NOT NULL,
  `medication_id` bigint(20) unsigned NOT NULL,
  `source_type` enum('ledger','location_stock') NOT NULL,
  `source_id` bigint(20) unsigned NOT NULL,
  `batch_number` varchar(255) NOT NULL,
  `expiry_date` date NOT NULL,
  `quantity_discarded` decimal(10,2) NOT NULL,
  `reason` enum('expired','damaged','recalled','contaminated','other') NOT NULL,
  `disposal_method` enum('incineration','return_supplier','secure_disposal','other') NOT NULL,
  `disposed_by` bigint(20) unsigned NOT NULL,
  `disposed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `verification_required` tinyint(1) NOT NULL DEFAULT 0,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unfit_medications_reference_number_unique` (`reference_number`),
  KEY `unfit_medications_disposed_by_foreign` (`disposed_by`),
  KEY `unfit_medications_verified_by_foreign` (`verified_by`),
  KEY `unfit_medications_medication_id_batch_number_index` (`medication_id`,`batch_number`),
  KEY `unfit_medications_source_type_source_id_index` (`source_type`,`source_id`),
  KEY `unfit_medications_disposed_at_index` (`disposed_at`),
  KEY `unfit_medications_verification_required_index` (`verification_required`),
  KEY `unfit_medications_reason_index` (`reason`),
  CONSTRAINT `unfit_medications_disposed_by_foreign` FOREIGN KEY (`disposed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `unfit_medications_medication_id_foreign` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `unfit_medications_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `role` enum('user','admin','doctor','nurse','receptionist','cashier','pharmacist','lab_technician','radiologist','super_admin') NOT NULL DEFAULT 'user',
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_super` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `users_before_delete_set_patients_created_by`
BEFORE DELETE ON `users`
FOR EACH ROW
BEGIN
    UPDATE `patients` SET `created_by` = 1 WHERE `created_by` = OLD.`id`;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `visit_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visit_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `nhif_visit_type_code` tinyint(3) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `visit_types_description_unique` (`description`),
  UNIQUE KEY `visit_types_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vital_signs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vital_signs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) DEFAULT NULL,
  `consultation_id` bigint(20) unsigned NOT NULL,
  `visit_id` bigint(20) unsigned NOT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `recorded_by` bigint(20) unsigned NOT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `pulse_rate` decimal(5,1) DEFAULT NULL COMMENT 'Pulse rate in bpm',
  `temperature` decimal(4,1) DEFAULT NULL COMMENT 'Body temperature in Celsius',
  `respiratory_rate` smallint(6) DEFAULT NULL COMMENT 'Respiratory rate per minute',
  `weight` decimal(5,1) DEFAULT NULL COMMENT 'Weight in kg',
  `height` decimal(5,1) DEFAULT NULL COMMENT 'Height in cm',
  `bmi` decimal(4,1) DEFAULT NULL COMMENT 'Body Mass Index',
  `oxygen_saturation` decimal(4,1) DEFAULT NULL COMMENT 'Oxygen saturation percentage',
  `systolic_bp` smallint(6) DEFAULT NULL COMMENT 'Systolic blood pressure',
  `diastolic_bp` smallint(6) DEFAULT NULL COMMENT 'Diastolic blood pressure',
  `muac` varchar(10) DEFAULT NULL COMMENT 'Mid-Upper Arm Circumference',
  `ofc` varchar(10) DEFAULT NULL COMMENT 'Occipital Frontal Circumference',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vital_signs_uuid_unique` (`uuid`),
  KEY `vital_signs_consultation_id_created_at_index` (`consultation_id`,`created_at`),
  KEY `vital_signs_visit_id_foreign` (`visit_id`),
  KEY `vital_signs_patient_id_foreign` (`patient_id`),
  KEY `vital_signs_recorded_by_foreign` (`recorded_by`),
  KEY `vital_signs_updated_by_foreign` (`updated_by`),
  CONSTRAINT `vital_signs_consultation_id_foreign` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vital_signs_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vital_signs_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vital_signs_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vital_signs_visit_id_foreign` FOREIGN KEY (`visit_id`) REFERENCES `patient_visits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'0001_01_01_000003_create_patient_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'0001_01_01_000004_create_patients_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_07_10_113608_create_visit_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_07_10_124904_create_designations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_07_11_034308_add_is_verified_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_07_11_040549_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_07_11_044359_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_07_11_050036_create_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_07_11_123213_create_doctors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_07_12_050939_create_consultation_fees_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_07_12_120249_create_patient_visits_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_07_13_132031_modify_doctors_table_to_use_standard_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_07_13_145641_create_improved_clinical_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_07_13_145938_migrate_data_to_improved_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_07_13_181547_update_tables_to_use_patient_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_07_13_182101_add_patient_id_to_consultation_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_07_13_182908_create_examinations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_07_14_041556_modify_examinations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_07_14_041606_create_past_medical_history_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_07_14_041614_create_icd_10_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_07_14_041623_create_visit_diagnoses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_07_14_042625_modify_consultations_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_07_14_042758_modify_consultations_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_07_14_045113_rename_examinations_to_systemic_examinations_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_07_14_051047_add_visit_id_to_vital_signs_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_07_14_102512_update_consultations_table_structure',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_07_14_102911_remove_physical_examination_from_consultations',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_07_14_104814_remove_obsolete_columns_from_systemic_examinations_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_07_14_105546_add_general_findings_to_systemic_examinations_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_07_14_135136_create_store_categories_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_07_14_135146_create_store_units_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_07_14_135156_create_store_suppliers_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_07_14_135203_create_store_locations_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_07_14_135211_create_store_items_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_07_14_135222_create_store_stock_batches_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_07_14_135230_create_store_stock_movements_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_07_14_135238_create_goods_received_notes_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_07_14_135536_create_goods_received_note_items_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_07_14_135546_create_store_requisitions_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2025_07_14_135553_create_store_requisition_items_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_07_14_135601_create_procedure_consumables_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2025_07_14_135608_create_investigation_consumables_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2025_07_14_135842_extend_medications_table_for_store',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2025_07_14_135859_create_store_consumables_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'xxxx_xx_xx_xxxxxx_add_foreign_keys_to_patients_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2025_07_15_040732_add_notes_to_vital_signs_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2025_07_15_045802_update_consultations_foreign_key_on_delete_cascade',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2025_07_15_052859_remove_consultation_fee_from_consultations_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2025_07_15_052865_create_icd_diagnoses_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2025_07_15_161724_add_ordered_by_to_investigations_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2025_07_15_164503_add_requires_form_to_medical_services_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2025_07_15_171701_add_clinical_data_to_investigations_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2025_07_16_070143_create_sample_types_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2025_07_16_150628_modify_medications_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2025_07_16_151000_create_medication_pricing_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2025_07_16_151001_create_medication_ledger_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2025_07_16_190619_create_medication_units_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2025_07_16_190610_create_medication_frequencies_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2025_07_16_190628_create_administration_routes_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2025_07_16_192555_modify_medication_frequencies_table_structure',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2025_07_16_192958_modify_medication_units_table_structure',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2025_07_18_000000_setup_default_categories_and_fix_medications',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2025_07_16_193455_modify_administration_routes_table_structure',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2025_07_18_000001_simplify_store_categories_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2025_07_18_000002_enhance_medications_for_unified_store',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2025_07_18_000003_drop_store_consumables_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2025_07_18_051021_remove_type_column_from_medications_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2025_07_18_051113_add_type_field_to_store_categories_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2025_07_18_052519_remove_redundant_columns_from_medications_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2025_07_19_041346_create_store_locations_stock_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2025_07_19_041357_create_unfit_medications_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2025_07_19_041410_create_investigation_consumptions_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2025_07_19_041422_create_procedure_consumptions_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2025_07_19_041554_enhance_prescription_items_tracking',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2025_07_19_042715_enhance_goods_received_note_items_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2025_07_19_042726_enhance_medication_ledger_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2025_07_19_042737_enhance_store_stock_movements_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2025_07_19_043130_cleanup_store_suppliers_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2025_07_19_174028_remove_name_column_from_medications_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2025_07_20_065743_create_medication_formulations_table',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2025_07_20_065938_add_formulation_id_to_medications_table',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2025_07_20_071006_populate_medication_formulation_ids',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2025_07_20_110947_remove_type_column_from_store_categories',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2025_07_20_180402_update_store_suppliers_payment_terms_nullable',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2025_01_21_000001_update_medication_ledger_table_structure',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2025_07_21_000001_modify_store_units_for_dual_units',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2025_07_21_152105_fix_grn_items_unit_tracking_constraints',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2025_07_21_175517_add_store_tracking_columns_to_grn_items',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2025_07_22_040012_drop_unit_price_and_unit_cost_from_medication_pricing_table',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2025_01_22_000001_modify_store_locations_stock_table',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2024_01_23_100000_add_dispensing_unit_to_medications_table',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2025_07_23_000001_create_financial_transactions_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2025_07_23_000002_create_general_expenses_table',36);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2025_07_23_000003_create_payment_receipts_table',37);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2025_07_23_000004_create_daily_cash_reconciliation_table',38);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2025_07_24_040844_add_payment_fields_to_investigations_table',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2025_07_24_040855_add_payment_fields_to_prescriptions_table',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2025_07_24_045324_add_missing_payment_fields_to_prescriptions_table',40);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2025_07_24_045443_update_prescription_status_enum',41);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2025_07_24_053835_fix_financial_transactions_column_sizes',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2025_07_24_093604_add_pharmacist_fields_to_prescriptions_table',43);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2025_07_24_113240_create_nhif_members_table',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2025_07_24_113301_create_nhif_claims_table',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2025_07_24_113335_create_nhif_claim_items_table',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2025_07_24_113508_create_nhif_claim_diseases_table',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2025_07_24_113520_create_nhif_tariffs_table',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2025_07_25_000001_remove_price_and_insurance_columns_from_medical_services',45);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2025_07_25_000002_rename_form_fields_to_form_type_in_medical_services',46);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2025_07_25_101331_add_result_template_to_medical_services_table',47);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2025_07_25_122042_create_investigation_template_results_table',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2025_07_25_123929_drop_investigation_results_table',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2025_07_25_162739_add_reference_values_to_medical_services_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2025_07_25_163712_create_medical_services_pricing_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2025_07_27_080501_modify_investigation_consumables_table_for_medical_services',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (125,'2025_07_27_101403_remove_item_type_from_investigation_consumables_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2025_07_28_093413_allow_null_investigation_id_in_template_results_table',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2025_07_30_043937_add_type_column_to_patient_categories_table',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2025_07_30_050549_modify_users_table_admin_columns',54);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'2025_08_02_050000_make_doctor_nullable_in_patient_visits',55);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (130,'2025_08_02_052945_make_consultation_id_nullable_in_investigations',56);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'2025_08_02_053306_make_doctor_id_nullable_in_investigations',57);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (132,'2025_08_03_143203_add_cancelled_fields_to_investigations_table',58);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2025_08_04_040048_modify_medical_services_result_template_to_enum',59);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2025_08_04_051307_update_result_template_enum_options',59);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2025_08_04_112358_update_store_locations_stock_unique_constraint',60);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2024_01_15_000000_create_medication_cash_sales_table',61);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (142,'2024_01_15_100000_create_medication_cash_sale_items_table',62);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'2025_08_07_093502_add_prescription_fields_to_medication_cash_sale_items_table',63);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2025_08_07_121616_add_cancellation_fields_to_medication_cash_sales',64);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2025_08_08_055044_add_is_paid_to_medication_cash_sales_table',65);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'2025_08_08_055919_remove_paid_status_from_medication_cash_sales',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2025_08_08_160000_add_batch_tracking_to_prescriptions',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2025_08_08_160354_add_batches_used_to_investigations_table',68);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (149,'2025_08_08_170000_add_consumption_type_to_investigation_consumptions',68);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (150,'2025_08_09_100130_update_store_locations_type_enum_add_radiology',69);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (151,'2025_08_09_121846_create_result_templates_table',70);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (152,'2025_08_09_172435_change_result_template_to_foreign_key_in_medical_services_table',71);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (153,'2025_08_13_154933_update_users_table_add_radiologist_role',72);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (154,'2025_08_14_200819_add_reference_number_to_unfit_medications_table',73);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (155,'2025_08_21_000001_add_indexes_to_nhif_tariffs',74);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (156,'2025_08_27_000000_add_code_and_flags_to_patient_categories',75);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (157,'2025_08_27_000001_remove_flags_from_patient_categories',75);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (158,'2025_08_28_000000_create_password_reset_requests_table',76);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (159,'2025_08_29_000000_add_chapter_to_icd10_table',77);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (160,'2025_08_29_181200_create_mtuha_diagnoses_table',78);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (161,'2025_08_30_000000_rename_catname_to_description_in_mtuha_diagnoses',79);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (162,'2025_08_30_001200_add_mtuha_diagnosis_to_icd_10_table',80);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (163,'2025_09_05_120000_create_cds_alerts_table',81);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (164,'2025_09_05_120100_create_cds_alert_actions_table',81);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (165,'2025_09_05_121000_create_allergies_table',82);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (166,'2025_09_05_130000_create_atc_codes_table',83);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (167,'2025_09_05_130100_create_drug_atc_map_table',83);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (168,'2025_09_06_000100_add_unique_index_allergies_table',84);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (169,'2025_09_30_083400_create_cds_rule_categories_table',84);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (170,'2025_09_30_083413_create_cds_rule_types_table',84);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (171,'2025_09_30_083419_create_cds_rules_table',84);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (172,'2025_09_30_083426_create_cds_rule_conditions_table',84);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (173,'2025_09_30_083440_create_cds_rule_parameters_table',84);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (174,'2025_09_30_083446_create_cds_medication_policies_table',84);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (175,'2025_09_30_083458_create_cds_dosage_limits_table',84);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (176,'2025_09_30_090000_drop_code_from_medical_services_table',85);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (177,'2025_09_30_100000_drop_unused_columns_from_result_templates_table',86);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (178,'2026_04_30_000001_add_medication_id_to_allergies_table',86);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (179,'2026_05_01_111246_redesign_cds_dosage_limits_table',87);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (180,'2026_05_01_142355_create_drug_classes_table',88);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (181,'2026_05_01_142356_create_drug_class_medication_table',89);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (182,'2026_05_01_143819_add_interactions_to_cds_dosage_limits_table',90);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (183,'2026_05_02_000000_drop_atc_tables',91);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (184,'2026_05_04_043320_add_email_to_patients_table',92);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (192,'2026_05_17_123000_modify_patients_created_by_foreign_key',93);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (193,'2026_05_19_000000_remove_columns_from_administration_routes',94);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (194,'2026_05_20_000000_add_column_to_patients',94);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (195,'2026_05_20_154507_change_dispensing_unit_fk_on_goods_received_note_items_table',94);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (196,'2026_05_21_050157_add_columns_to_medical_service_pricing_table',94);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (197,'2026_05_21_054349_alter_cash_and_insurance_price_default_in_medical_services_pricing',94);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (198,'2026_05_21_095809_add_cash_and_insurance_price_to_medication_pricing_table',94);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (199,'2026_05_26_043244_update_fee_amount_in_consultation_fees_table',95);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (200,'2026_05_26_081018_rename_total_price_to_cash_amount_in_investigations_table',96);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (201,'2026_05_26_081434_remove_unit_price_in_investigations_table',96);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (202,'2026_05_28_045651_update_prescriptions_drop_unit_price_and_rename_total_price',97);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (203,'2026_06_01_000000_create_referral_hospitals_table',98);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (204,'2026_06_01_000001_create_referral_departments_table',98);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (205,'2026_06_01_000002_create_patient_referrals_table',98);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (206,'2026_06_02_123808_create_medical_service_insurance_map_table',99);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (207,'2026_06_02_123809_create_medication_insurance_map_table',99);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (208,'2026_06_02_123810_add_pricing_fields_to_medical_services_table',99);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (209,'2026_06_02_123811_add_pricing_fields_to_medications_table',99);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (210,'2026_06_02_134724_add_tariffs_table_to_patient_categories_table',100);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (211,'2026_06_02_044744_create_nhif_claim_feedback_table',101);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (212,'2026_06_02_044745_create_nhif_claim_errors_table',101);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (213,'2026_06_03_071306_create_nhif_claim_batches_table',102);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (214,'2026_06_03_081756_add_nhif_claim_batch_id_to_nhif_claims_table',103);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (215,'2026_06_03_083635_make_claim_fields_nullable_in_nhif_claim_batches_table',104);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (216,'2026_06_03_155306_create_investigation_form_data_table',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (217,'2026_06_03_155847_create_investigation_forms_table',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (218,'2026_06_04_063812_add_unique_patient_visit_to_nhif_claims_table',106);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (219,'2026_06_04_000000_drop_deprecated_tables',107);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (220,'2026_06_04_100000_create_age_groups_table',108);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (221,'2026_06_04_100001_create_idsr_categories_table',108);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (222,'2026_06_04_000001_create_stock_corrections_table',109);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (223,'2026_06_04_000002_create_reconciliation_runs_table',109);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (224,'2026_06_05_100000_create_facilities_table',110);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (225,'2026_06_05_093814_add_is_tracer_to_medications_table',111);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (226,'2026_06_05_134500_add_performance_indexes_to_prescriptions_and_visits',112);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (227,'2026_06_05_150000_add_performance_indexes_to_investigations_table',113);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (228,'2026_06_05_164308_create_system_settings_table',114);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (229,'2026_06_05_200609_add_mrdt_malaria_result_template',115);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (230,'2026_06_06_060446_add_stool_spermiogram_anamnesis_result_templates',116);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (231,'2026_06_06_000001_drop_medication_and_medical_services_pricing_tables',117);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (232,'2026_06_07_000001_add_informed_fields_to_patient_visits_table',118);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (233,'2026_06_07_195053_create_nhif_settings_table',119);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (234,'2026_06_08_165550_add_hfr_code_to_facilities_table',120);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (239,'2026_06_08_180000_create_msd_codes_table',121);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (240,'2026_06_08_180001_add_msd_code_id_to_medications_table',121);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (241,'2026_06_08_180002_create_lab_codes_table',121);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (242,'2026_06_08_180003_add_lab_code_ids_to_medical_services_table',121);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (243,'2026_06_09_055951_create_blood_transfusion_report_rows_table',122);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (244,'2026_06_09_071629_create_hematology_report_rows_table',123);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (245,'2026_06_09_074222_add_required_template_to_hematology_report_rows',124);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (246,'2026_06_09_075031_add_positive_results_only_to_hematology_report_rows',125);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (247,'2026_06_09_100439_add_multiple_parameters_to_medical_services_table',126);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (248,'2026_06_10_080000_create_clinical_chemistry_report_rows_table',127);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (249,'2026_06_10_090000_create_serology_report_rows_table',128);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (250,'2026_06_10_100000_create_microbiology_report_rows_table',129);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (251,'2026_06_10_110000_create_parasitology_report_rows_table',130);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (252,'2026_06_10_120000_create_idsr_diagnoses_and_icd_mapping_tables',131);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (253,'2026_06_10_120000_add_indexes_for_malaria_weekly_report',132);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (254,'2026_06_10_110000_create_medicine_dispensing_report_rows_table',133);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (255,'2026_06_10_120000_add_medication_id_dispensed_at_index_to_prescriptions_table',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (256,'2026_06_10_130000_add_index_to_patient_visits_visit_date',135);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (257,'2026_06_10_150000_drop_idsr_categories_add_icd_diagnoses_index',136);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (258,'2026_06_10_160000_link_cd4_service_to_result_template',137);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (259,'2026_06_11_000001_add_in_charge_to_facilities_table',138);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (260,'2026_06_11_000002_add_signature_to_users_table',139);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (261,'2026_06_11_000003_add_lab_critical_cds_rule_type',140);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (262,'2026_06_11_000004_add_general_result_template',141);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (263,'2026_06_12_042538_create_patient_category_visit_type_table',142);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (264,'2026_06_12_044836_add_nhif_visit_type_code_to_visit_types_table',143);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (265,'2026_06_12_150000_add_uuid_to_sync_tables',144);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (266,'2026_06_12_150001_create_sync_outbox_table',144);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (267,'2026_06_12_150002_create_sync_state_table',144);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (268,'2026_06_12_150003_create_sync_conflicts_table',144);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (269,'2026_06_12_120000_drop_batch_id_from_store_stock_movements_table',145);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (270,'2026_06_12_160000_add_copay_policy_to_patient_categories_table',146);
