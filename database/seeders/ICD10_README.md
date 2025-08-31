ICD-10 Import README

Purpose
- Provide a seeder and instructions to populate the `icd_10` table with ICD-10 codes.

How it works
- The seeder `Database\\Seeders\\Icd10Seeder` expects a CSV with header columns: `code,description,category,subcategory,notes`.
- You can either place a CSV at `database/seeders/data/icd10.csv` or set an environment variable `ICD10_CSV_URL` pointing to a raw CSV URL (for example, a raw file on GitHub).

Where to get ICD-10 data
- Official full ICD-10 license/csv sources vary by country. A commonly used open source compilation is the WHO ICD-10 dataset or curated CSV datasets on GitHub. Example search terms: "ICD-10 CSV download".
- Make sure the CSV columns match the header expected above.

Running the seeder
1. (Optional) Copy the full CSV to `database/seeders/data/icd10.csv`.
2. Or set `ICD10_CSV_URL` in your `.env` to a CSV raw URL.
3. Run the seeder:

    php artisan db:seed --class=Database\\Seeders\\Icd10Seeder

Notes and caveats
- The seeder does simple validation and will skip rows missing `code` or `description`.
- The seeder inserts in chunks to avoid large single inserts.
- For very large official datasets, consider using MySQL's LOAD DATA INFILE for speed or pre-processing into SQL inserts.
