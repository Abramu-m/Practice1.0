<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6.2a — one-off backfill of the `uuid` column on every syncable /
 * FK-target table (see docs/phase6.2-bidirectional-sync-design.md §3).
 * Safe to re-run: only fills rows where uuid IS NULL.
 */
class SyncBackfillUuids extends Command
{
    protected $signature = 'sync:backfill-uuids {--table= : Only backfill this table}';

    protected $description = 'Backfill the uuid column for existing rows on syncable and FK-target tables';

    public function handle(): int
    {
        $tables = array_merge(
            array_keys(config('sync.tables', [])),
            config('sync.reference_tables', []),
        );

        if ($only = $this->option('table')) {
            $tables = array_intersect($tables, [$only]);
        }

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'uuid')) {
                $this->warn("Skipping {$table}: no uuid column");
                continue;
            }

            $missing = DB::table($table)->whereNull('uuid')->count();
            if ($missing === 0) {
                $this->line("{$table}: already backfilled");
                continue;
            }

            DB::statement("UPDATE `{$table}` SET uuid = UUID() WHERE uuid IS NULL");
            $this->info("{$table}: backfilled {$missing} row(s)");
        }

        return self::SUCCESS;
    }
}
