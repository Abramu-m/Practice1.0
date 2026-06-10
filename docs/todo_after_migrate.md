//// commands to run after migration

php artisan finance:backfill-legacy-transactions
  - backfills financial_transactions for patient_visits / investigations / prescriptions
    that were imported via raw SQL (no observers fired, so no ledger rows exist)
  - safe to re-run (skips source rows that already have a financial_transactions entry)
  - --dry-run            preview counts/totals, no writes
  - --only=visits,investigations,prescriptions   limit to specific sources
  - --chunk=2000         rows per chunk



////tables
cds_rule_categories
cds_rule_types
medication_units
goods_received_note_items (also, store_quantity vs rec. qty)
DROP general_expenses, fk_test(s), store_items, store_stock_batches, visit_diagnoses, prescription_items, procedure_consumables



/////////////////Logic
1. if service requires form, check if it has, if not prompt to add
2. ADD inv on VISIT, check form logik



 