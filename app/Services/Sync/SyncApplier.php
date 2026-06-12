<?php

namespace App\Services\Sync;

use App\Models\Concerns\SyncTrackingGuard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6.2 — applies a batch of incoming sync_outbox-shaped changes
 * (whether received via POST /api/sync/receive or pulled via
 * GET /api/sync/changes). See docs/phase6.2-bidirectional-sync-design.md §7.
 */
class SyncApplier
{
    /**
     * @param array $changes list of ['table_name','record_uuid','operation','payload','origin_site']
     * @return array{applied:int,conflicts:int,skipped:int,deferred:int}
     */
    public function applyBatch(array $changes): array
    {
        $applied = 0;
        $conflicts = 0;
        $skipped = 0;
        $deferred = [];

        SyncTrackingGuard::run(function () use ($changes, &$applied, &$conflicts, &$skipped, &$deferred) {
            foreach ($this->orderByDependency($changes) as $change) {
                match ($this->applyChange($change)) {
                    'applied' => $applied++,
                    'conflict' => $conflicts++,
                    'deferred' => $deferred[] = $change,
                    default => $skipped++,
                };
            }

            // One retry pass for changes whose FK targets arrived later in the batch.
            $stillDeferred = [];
            foreach ($deferred as $change) {
                match ($this->applyChange($change)) {
                    'applied' => $applied++,
                    'conflict' => $conflicts++,
                    'deferred' => $stillDeferred[] = $change,
                    default => $skipped++,
                };
            }
            $deferred = $stillDeferred;
        });

        return [
            'applied' => $applied,
            'conflicts' => $conflicts,
            'skipped' => $skipped,
            'deferred' => count($deferred),
        ];
    }

    /**
     * Group the incoming batch by table and process in config('sync.tables')
     * order, so FK *_uuid columns referencing earlier tables in the same
     * batch are already resolvable (§7.1).
     */
    private function orderByDependency(array $changes): array
    {
        $grouped = [];
        foreach ($changes as $change) {
            $grouped[$change['table_name'] ?? '']['rows'][] = $change;
        }

        $ordered = [];
        foreach (array_keys(config('sync.tables', [])) as $table) {
            foreach ($grouped[$table]['rows'] ?? [] as $change) {
                $ordered[] = $change;
            }
            unset($grouped[$table]);
        }

        foreach ($grouped as $rest) {
            foreach ($rest['rows'] ?? [] as $change) {
                $ordered[] = $change;
            }
        }

        return $ordered;
    }

    /**
     * Apply a single incoming change. Returns 'applied', 'conflict',
     * 'deferred' (an FK *_uuid target wasn't found yet), or 'skipped'
     * (unknown table/malformed change).
     */
    private function applyChange(array $change): string
    {
        $table = $change['table_name'] ?? null;
        $tableConfig = $table ? config("sync.tables.{$table}") : null;

        if (!$tableConfig || !Schema::hasTable($table) || empty($change['record_uuid'])) {
            return 'skipped';
        }

        $payload = $change['payload'] ?? [];
        if (is_string($payload)) {
            $payload = json_decode($payload, true) ?? [];
        }

        foreach ($tableConfig['foreign_keys'] ?? [] as $column => $referencedTable) {
            $uuidKey = "{$column}_uuid";
            if (!array_key_exists($uuidKey, $payload)) {
                continue;
            }

            $fkUuid = $payload[$uuidKey];
            unset($payload[$uuidKey]);

            if ($fkUuid === null) {
                $payload[$column] = null;
                continue;
            }

            $localId = DB::table($referencedTable)->where('uuid', $fkUuid)->value('id');
            if ($localId === null) {
                return 'deferred';
            }

            $payload[$column] = $localId;
        }

        $recordUuid = $change['record_uuid'];
        $operation = $change['operation'] ?? 'update';
        $existing = DB::table($table)->where('uuid', $recordUuid)->first();

        if ($operation === 'delete') {
            if ($existing) {
                DB::table($table)->where('uuid', $recordUuid)->delete();
            }

            return 'applied';
        }

        unset($payload['id']);
        $payload['uuid'] = $recordUuid;

        if (!$existing) {
            DB::table($table)->insert($payload);

            return 'applied';
        }

        if ($tableConfig['conflict_check'] ?? false) {
            $remoteSite = $change['origin_site'] ?? config('sync.remote_site_id');
            $lastSync = DB::table('sync_state')->where('remote_site', $remoteSite)->value('last_pull_at');

            $localUpdatedAt = $existing->updated_at ?? null;
            $incomingUpdatedAt = $payload['updated_at'] ?? null;

            if ($lastSync && $localUpdatedAt && $localUpdatedAt > $lastSync) {
                if ($incomingUpdatedAt && $incomingUpdatedAt > $localUpdatedAt) {
                    DB::table($table)->where('uuid', $recordUuid)->update($payload);

                    return 'applied';
                }

                DB::table('sync_conflicts')->insert([
                    'table_name' => $table,
                    'record_uuid' => $recordUuid,
                    'local_payload' => json_encode($existing),
                    'incoming_payload' => json_encode($payload),
                    'detected_at' => now(),
                ]);

                return 'conflict';
            }
        }

        DB::table($table)->where('uuid', $recordUuid)->update($payload);

        return 'applied';
    }
}
