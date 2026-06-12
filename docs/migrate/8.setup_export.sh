#!/usr/bin/env bash
# Export "setup/configuration" tables (Section A of 7.online_migration_categorization.md)
# from the local XAMPP `practice` database, for import into the new online database.
#
# Run on the local XAMPP machine, against the local `practice` DB:
#   ./8.setup_export.sh > 8.setup_data.sql
#
# Then, on the new online database (after running `php artisan migrate --force`
# there to create an empty schema):
#   mysql -u<user> -p<online_db> < 8.setup_data.sql

set -euo pipefail

DB_USER="root"
DB_NAME="practice"

# Tables in FK-dependency order (parents before children) so the dump can be
# replayed even if FOREIGN_KEY_CHECKS is enforced during import.
TABLES=(
  users designations doctors facilities nhif_settings
  patient_categories visit_types age_groups patient_category_visit_type consultation_fees
  medication_units medication_formulations msd_codes medications medication_frequencies
  administration_routes drug_classes drug_class_medication
  lab_codes result_templates service_categories medical_services sample_types
  investigation_forms investigation_consumables
  mtuha_diagnoses icd_10 idsr_diagnoses idsr_icd_mapping
  store_locations store_units store_suppliers store_categories
  nhif_tariffs medication_insurance_map medical_service_insurance_map
  assemble_tariff jubilee_tariff shib_tariff strategies_tariff
  cds_rule_categories cds_rule_types cds_rules cds_rule_conditions cds_rule_parameters cds_dosage_limits
  referral_hospitals referral_departments
  system_settings blood_transfusion_report_rows hematology_report_rows clinical_chemistry_report_rows
  serology_report_rows microbiology_report_rows parasitology_report_rows medicine_dispensing_report_rows
)

mysqldump -u"$DB_USER" --no-create-info --complete-insert "$DB_NAME" "${TABLES[@]}"

# stock_quantity is live inventory, not setup data — it gets repopulated from
# Medcom-online via medication_ledger / store_locations_stock (Section B).
echo "UPDATE \`medications\` SET \`stock_quantity\` = 0;"
