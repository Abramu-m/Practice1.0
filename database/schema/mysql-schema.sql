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
  `route_name` varchar(255) NOT NULL,
  `route_code` varchar(10) NOT NULL,
  `route_abbreviation` varchar(10) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `requires_prescription` tinyint(1) NOT NULL DEFAULT 0,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `administration_routes_name_unique` (`route_name`),
  KEY `administration_routes_route_code_is_active_index` (`route_code`,`is_active`)
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
DROP TABLE IF EXISTS `consultation_fees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultation_fees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `doctor_id` bigint(20) unsigned NOT NULL,
  `patient_category_id` bigint(20) unsigned NOT NULL,
  `visit_type_id` bigint(20) unsigned NOT NULL,
  `fee_amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_consultation_fee` (`doctor_id`,`patient_category_id`,`visit_type_id`),
  KEY `consultation_fees_patient_category_id_foreign` (`patient_category_id`),
  KEY `consultation_fees_visit_type_id_foreign` (`visit_type_id`),
  KEY `consultation_fees_created_by_foreign` (`created_by`),
  CONSTRAINT `consultation_fees_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
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
DROP TABLE IF EXISTS `general_expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expense_number` varchar(50) NOT NULL,
  `expense_date` date NOT NULL,
  `transaction_type` enum('income','expense') NOT NULL DEFAULT 'expense',
  `expense_category` varchar(100) NOT NULL,
  `expense_subcategory` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `paid_to` varchar(100) DEFAULT NULL,
  `payee_contact` varchar(100) DEFAULT NULL,
  `payment_method` enum('cash','bank','cheque','mobile_money','other') NOT NULL DEFAULT 'cash',
  `payment_reference` varchar(100) DEFAULT NULL,
  `receipt_number` varchar(100) DEFAULT NULL,
  `patient_id` bigint(20) unsigned DEFAULT NULL,
  `visit_id` bigint(20) unsigned DEFAULT NULL,
  `medication_id` bigint(20) unsigned DEFAULT NULL,
  `staff_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('draft','pending_approval','approved','paid','cancelled') NOT NULL DEFAULT 'draft',
  `requested_by` bigint(20) unsigned NOT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `paid_by` bigint(20) unsigned DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `general_expenses_expense_number_unique` (`expense_number`),
  KEY `general_expenses_patient_id_foreign` (`patient_id`),
  KEY `general_expenses_visit_id_foreign` (`visit_id`),
  KEY `general_expenses_medication_id_foreign` (`medication_id`),
  KEY `general_expenses_staff_id_foreign` (`staff_id`),
  KEY `general_expenses_requested_by_foreign` (`requested_by`),
  KEY `general_expenses_approved_by_foreign` (`approved_by`),
  KEY `general_expenses_paid_by_foreign` (`paid_by`),
  KEY `general_expenses_expense_date_status_index` (`expense_date`,`status`),
  KEY `general_expenses_expense_category_expense_subcategory_index` (`expense_category`,`expense_subcategory`),
  KEY `general_expenses_payment_method_index` (`payment_method`),
  KEY `general_expenses_status_requested_by_approved_by_index` (`status`,`requested_by`,`approved_by`),
  CONSTRAINT `general_expenses_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `general_expenses_medication_id_foreign` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE SET NULL,
  CONSTRAINT `general_expenses_paid_by_foreign` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `general_expenses_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `general_expenses_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  CONSTRAINT `general_expenses_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `general_expenses_visit_id_foreign` FOREIGN KEY (`visit_id`) REFERENCES `patient_visits` (`id`) ON DELETE SET NULL
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
  CONSTRAINT `goods_received_note_items_dispensing_unit_id_foreign` FOREIGN KEY (`dispensing_unit_id`) REFERENCES `store_units` (`id`) ON DELETE CASCADE,
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
DROP TABLE IF EXISTS `icd_10`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `icd_10` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `icd_10_code_unique` (`code`),
  KEY `icd_10_code_index` (`code`),
  KEY `icd_10_is_active_index` (`is_active`),
  KEY `icd_10_category_is_active_index` (`category`,`is_active`)
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
  CONSTRAINT `icd_diagnoses_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`),
  CONSTRAINT `icd_diagnoses_consultation_id_foreign` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE CASCADE
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
  `patient_id` bigint(20) unsigned NOT NULL,
  `consultation_id` bigint(20) unsigned DEFAULT NULL,
  `doctor_id` bigint(20) unsigned DEFAULT NULL,
  `medical_service_id` bigint(20) unsigned NOT NULL,
  `quantity` smallint(6) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `insurance_covered_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL,
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
DROP TABLE IF EXISTS `medical_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medical_services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `service_category_id` bigint(20) unsigned DEFAULT NULL,
  `requires_sample` tinyint(1) NOT NULL DEFAULT 0,
  `requires_form` tinyint(1) NOT NULL DEFAULT 0,
  `form_type` varchar(255) DEFAULT NULL,
  `result_template_id` bigint(20) unsigned DEFAULT NULL,
  `sample_type` varchar(255) DEFAULT NULL,
  `turnaround_time_hours` int(11) DEFAULT NULL,
  `preparation_instructions` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `min_value` decimal(10,4) DEFAULT NULL COMMENT 'Minimum reference value for lab tests',
  `max_value` decimal(10,4) DEFAULT NULL COMMENT 'Maximum reference value for lab tests',
  `unit` varchar(50) DEFAULT NULL COMMENT 'Unit of measurement (e.g., mg/dL, mmol/L, cells/μL)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `medical_services_code_unique` (`code`),
  KEY `medical_services_code_is_active_index` (`code`,`is_active`),
  KEY `medical_services_service_category_id_is_active_index` (`service_category_id`,`is_active`),
  KEY `medical_services_result_template_id_foreign` (`result_template_id`),
  CONSTRAINT `medical_services_result_template_id_foreign` FOREIGN KEY (`result_template_id`) REFERENCES `result_templates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medical_services_service_category_id_foreign` FOREIGN KEY (`service_category_id`) REFERENCES `service_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medical_services_pricing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medical_services_pricing` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `medical_service_id` bigint(20) unsigned NOT NULL,
  `patient_category_id` bigint(20) unsigned NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `markup_percentage` decimal(5,2) DEFAULT NULL,
  `discount_percentage` decimal(5,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `effective_from` date DEFAULT NULL,
  `effective_to` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `msp_unique_service_category_date` (`medical_service_id`,`patient_category_id`,`effective_from`),
  KEY `medical_services_pricing_patient_category_id_foreign` (`patient_category_id`),
  KEY `msp_service_category_idx` (`medical_service_id`,`patient_category_id`),
  KEY `msp_active_dates_idx` (`is_active`,`effective_from`,`effective_to`),
  CONSTRAINT `medical_services_pricing_medical_service_id_foreign` FOREIGN KEY (`medical_service_id`) REFERENCES `medical_services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medical_services_pricing_patient_category_id_foreign` FOREIGN KEY (`patient_category_id`) REFERENCES `patient_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medication_cash_sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medication_cash_sale_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
  KEY `medication_frequencies_is_active_display_order_index` (`is_active`,`display_order`),
  KEY `medication_frequencies_frequency_code_index` (`frequency_code`)
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
DROP TABLE IF EXISTS `medication_pricing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medication_pricing` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `medication_id` bigint(20) unsigned NOT NULL,
  `patient_category_id` bigint(20) unsigned NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `markup_percentage` decimal(5,2) DEFAULT NULL,
  `discount_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `effective_from` date DEFAULT NULL,
  `effective_to` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_medication_category_pricing` (`medication_id`,`patient_category_id`),
  KEY `medication_pricing_patient_category_id_foreign` (`patient_category_id`),
  KEY `med_pricing_med_cat_active_idx` (`medication_id`,`patient_category_id`,`is_active`),
  KEY `med_pricing_effective_idx` (`effective_from`,`effective_to`),
  CONSTRAINT `medication_pricing_medication_id_foreign` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medication_pricing_patient_category_id_foreign` FOREIGN KEY (`patient_category_id`) REFERENCES `patient_categories` (`id`) ON DELETE CASCADE
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
  `generic_name` varchar(255) DEFAULT NULL,
  `brand_name` varchar(255) DEFAULT NULL,
  `strength` varchar(255) DEFAULT NULL,
  `formulation_id` bigint(20) unsigned DEFAULT NULL,
  `dispensing_unit_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `reorder_level` decimal(10,2) NOT NULL DEFAULT 0.00,
  `maximum_stock_level` decimal(10,2) NOT NULL DEFAULT 0.00,
  `requires_prescription` tinyint(1) NOT NULL DEFAULT 1,
  `is_controlled` tinyint(1) NOT NULL DEFAULT 0,
  `storage_conditions` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medications_name_is_active_index` (`is_active`),
  KEY `medications_expiry_date_is_active_index` (`is_active`),
  KEY `medications_category_id_is_active_index` (`category_id`,`is_active`),
  KEY `medications_requires_prescription_is_active_index` (`requires_prescription`,`is_active`),
  KEY `medications_type_is_active_index` (`is_active`),
  KEY `medications_category_id_type_is_active_index` (`category_id`,`is_active`),
  KEY `medications_formulation_id_index` (`formulation_id`),
  KEY `medications_dispensing_unit_id_index` (`dispensing_unit_id`),
  CONSTRAINT `medications_dispensing_unit_id_foreign` FOREIGN KEY (`dispensing_unit_id`) REFERENCES `medication_units` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medications_formulation_id_foreign` FOREIGN KEY (`formulation_id`) REFERENCES `medication_formulations` (`id`) ON DELETE SET NULL
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
  KEY `nhif_claims_patient_visit_id_foreign` (`patient_visit_id`),
  KEY `nhif_claims_submitted_by_foreign` (`submitted_by`),
  KEY `nhif_claims_card_no_claim_year_claim_month_index` (`card_no`,`claim_year`,`claim_month`),
  KEY `nhif_claims_patient_id_patient_visit_id_index` (`patient_id`,`patient_visit_id`),
  KEY `nhif_claims_claim_status_index` (`claim_status`),
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
  CONSTRAINT `past_medical_history_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `patient_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `type` enum('cash','insurance') NOT NULL DEFAULT 'cash',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_categories_description_unique` (`description`),
  KEY `1` (`created_by`),
  CONSTRAINT `1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `patient_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_visits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
  `signature` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_visits_patient_foreign` (`patient`),
  KEY `patient_visits_visit_type_foreign` (`visit_type`),
  KEY `patient_visits_visit_category_foreign` (`visit_category`),
  KEY `patient_visits_doctor_foreign` (`doctor`),
  CONSTRAINT `patient_visits_doctor_foreign` FOREIGN KEY (`doctor`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE,
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
  `first_name` varchar(30) NOT NULL,
  `middle_name` varchar(30) DEFAULT NULL,
  `last_name` varchar(30) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female','other') NOT NULL DEFAULT 'other',
  `contact` varchar(100) DEFAULT NULL,
  `residence` varchar(30) DEFAULT NULL,
  `occupation` varchar(90) DEFAULT NULL,
  `nida` varchar(32) DEFAULT NULL,
  `patient_category` bigint(20) unsigned NOT NULL,
  `card_number` varchar(30) DEFAULT NULL,
  `membership_number` varchar(30) DEFAULT NULL,
  `vote` varchar(30) DEFAULT NULL,
  `SchemeID` int(11) DEFAULT NULL,
  `ProductCode` varchar(30) DEFAULT NULL,
  `PackageID` int(11) DEFAULT NULL,
  `HasSupplementary` enum('Yes','No') NOT NULL DEFAULT 'No',
  `SchemeName` varchar(90) DEFAULT NULL,
  `mtuha_new` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_by` bigint(20) unsigned NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patients_patient_category_foreign` (`patient_category`),
  KEY `patients_created_by_foreign` (`created_by`),
  CONSTRAINT `patients_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patients_patient_category_foreign` FOREIGN KEY (`patient_category`) REFERENCES `patient_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_receipts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
DROP TABLE IF EXISTS `prescription_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prescription_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `prescription_id` bigint(20) unsigned NOT NULL,
  `medication_id` bigint(20) unsigned NOT NULL,
  `quantity_prescribed` decimal(10,2) NOT NULL,
  `quantity_dispensed` decimal(10,2) NOT NULL DEFAULT 0.00,
  `batch_numbers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of batch numbers used' CHECK (json_valid(`batch_numbers`)),
  `dispensed_from_location_id` bigint(20) unsigned DEFAULT NULL,
  `dispensed_by` bigint(20) unsigned DEFAULT NULL,
  `dispensed_at` timestamp NULL DEFAULT NULL,
  `unit_cost_at_dispensing` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prescription_items_medication_id_foreign` (`medication_id`),
  KEY `prescription_items_prescription_id_medication_id_index` (`prescription_id`,`medication_id`),
  KEY `prescription_items_dispensed_from_location_id_index` (`dispensed_from_location_id`),
  KEY `prescription_items_dispensed_at_index` (`dispensed_at`),
  KEY `prescription_items_dispensed_by_index` (`dispensed_by`),
  CONSTRAINT `prescription_items_dispensed_by_foreign` FOREIGN KEY (`dispensed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prescription_items_dispensed_from_location_id_foreign` FOREIGN KEY (`dispensed_from_location_id`) REFERENCES `store_locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prescription_items_medication_id_foreign` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prescription_items_prescription_id_foreign` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `prescriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prescriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
  `unit_price` decimal(10,2) NOT NULL,
  `insurance_covered_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL,
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
DROP TABLE IF EXISTS `procedure_consumables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `procedure_consumables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `procedure_name` varchar(255) NOT NULL,
  `procedure_code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `item_type` enum('medication','consumable') NOT NULL DEFAULT 'consumable',
  `item_id` bigint(20) unsigned NOT NULL,
  `quantity_required` decimal(10,2) NOT NULL,
  `is_optional` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `procedure_consumables_procedure_code_unique` (`procedure_code`),
  KEY `procedure_consumables_procedure_code_is_active_index` (`procedure_code`,`is_active`),
  KEY `procedure_consumables_item_type_item_id_index` (`item_type`,`item_id`)
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
DROP TABLE IF EXISTS `store_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `store_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `generic_name` varchar(255) DEFAULT NULL,
  `brand_name` varchar(255) DEFAULT NULL,
  `strength` varchar(255) DEFAULT NULL,
  `formulation` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `unit_id` bigint(20) unsigned NOT NULL,
  `type` enum('medication','consumable','equipment','other') NOT NULL DEFAULT 'medication',
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `minimum_stock_level` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reorder_level` decimal(10,2) NOT NULL DEFAULT 0.00,
  `maximum_stock_level` decimal(10,2) NOT NULL DEFAULT 0.00,
  `track_expiry` tinyint(1) NOT NULL DEFAULT 1,
  `track_batch` tinyint(1) NOT NULL DEFAULT 1,
  `requires_prescription` tinyint(1) NOT NULL DEFAULT 0,
  `is_controlled` tinyint(1) NOT NULL DEFAULT 0,
  `storage_conditions` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_items_code_unique` (`code`),
  KEY `store_items_unit_id_foreign` (`unit_id`),
  KEY `store_items_is_active_type_index` (`is_active`,`type`),
  KEY `store_items_category_id_is_active_index` (`category_id`,`is_active`),
  CONSTRAINT `store_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `store_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `store_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `store_units` (`id`) ON DELETE CASCADE
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
DROP TABLE IF EXISTS `store_stock_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `store_stock_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_type` enum('medication','consumable') NOT NULL DEFAULT 'medication',
  `item_id` bigint(20) unsigned NOT NULL,
  `store_location_id` bigint(20) unsigned NOT NULL,
  `batch_number` varchar(255) NOT NULL,
  `manufacture_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `received_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `current_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reserved_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `available_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','expired','damaged','quarantine') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_stock_batches_store_location_id_foreign` (`store_location_id`),
  KEY `store_stock_batches_item_type_item_id_store_location_id_index` (`item_type`,`item_id`,`store_location_id`),
  KEY `store_stock_batches_batch_number_item_type_item_id_index` (`batch_number`,`item_type`,`item_id`),
  KEY `store_stock_batches_expiry_date_status_index` (`expiry_date`,`status`),
  CONSTRAINT `store_stock_batches_store_location_id_foreign` FOREIGN KEY (`store_location_id`) REFERENCES `store_locations` (`id`) ON DELETE CASCADE
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
  `batch_id` bigint(20) unsigned DEFAULT NULL,
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
  KEY `store_stock_movements_batch_id_foreign` (`batch_id`),
  KEY `store_stock_movements_created_by_foreign` (`created_by`),
  KEY `idx_stock_movements_item_location` (`item_type`,`item_id`,`store_location_id`),
  KEY `store_stock_movements_movement_type_transaction_type_index` (`movement_type`,`transaction_type`),
  KEY `store_stock_movements_reference_number_reference_id_index` (`reference_number`,`reference_id`),
  KEY `store_stock_movements_from_location_id_foreign` (`from_location_id`),
  KEY `store_stock_movements_to_location_id_foreign` (`to_location_id`),
  CONSTRAINT `store_stock_movements_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `store_stock_batches` (`id`) ON DELETE SET NULL,
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
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
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
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `visit_diagnoses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visit_diagnoses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `visit_id` bigint(20) unsigned NOT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `icd_10_id` bigint(20) unsigned NOT NULL,
  `diagnosis_type` enum('provisional','final') NOT NULL,
  `clinical_notes` text DEFAULT NULL,
  `sequence` int(11) NOT NULL DEFAULT 1,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `doctor_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `visit_diagnoses_icd_10_id_foreign` (`icd_10_id`),
  KEY `visit_diagnoses_doctor_id_foreign` (`doctor_id`),
  KEY `visit_diagnoses_visit_id_diagnosis_type_index` (`visit_id`,`diagnosis_type`),
  KEY `visit_diagnoses_patient_id_diagnosis_type_index` (`patient_id`,`diagnosis_type`),
  KEY `visit_diagnoses_is_primary_index` (`is_primary`),
  CONSTRAINT `visit_diagnoses_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE,
  CONSTRAINT `visit_diagnoses_icd_10_id_foreign` FOREIGN KEY (`icd_10_id`) REFERENCES `icd_10` (`id`) ON DELETE CASCADE,
  CONSTRAINT `visit_diagnoses_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `visit_diagnoses_visit_id_foreign` FOREIGN KEY (`visit_id`) REFERENCES `patient_visits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `visit_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visit_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `visit_types_description_unique` (`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vital_signs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vital_signs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
