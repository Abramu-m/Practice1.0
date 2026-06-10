//// commands to run after migration

php artisan medications:backfill-legacy-cash-sales
  - migrates medcom1_0.pat_pharm rows with visit_id=0/pat_id=NULL (legacy OTC/walk-in cash sales,
    excluded by 3.third.sql's INNER JOIN on consultations) into medication_cash_sales +
    medication_cash_sale_items, grouped by (refname, createdon)
  - safe to re-run (skips groups already tagged via the "Legacy OTC backfill —" notes marker)
  - --dry-run            preview counts/totals, no writes
  - --chunk=500          groups per transaction
  - run BEFORE finance:backfill-legacy-transactions --only=cash_sales

php artisan finance:backfill-legacy-transactions
  - backfills financial_transactions for patient_visits / investigations / prescriptions / medication_cash_sales
    that were imported via raw SQL or the cash-sales backfill above (no observers fired, so no ledger rows exist)
  - safe to re-run (skips source rows that already have a financial_transactions entry)
  - --dry-run            preview counts/totals, no writes
  - --only=visits,investigations,prescriptions,cash_sales   limit to specific sources
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



 