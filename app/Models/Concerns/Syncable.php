<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Phase 6.2 — change tracking for bidirectional sync.
 * See docs/phase6.2-bidirectional-sync-design.md §4.
 *
 * - Assigns a `uuid` to new rows.
 * - Writes a `sync_outbox` row on create/update/delete, with FK columns
 *   rewritten to `<col>_uuid` per config('sync.tables.<table>.foreign_keys').
 * - withoutSyncTracking() suppresses outbox writes while applying incoming
 *   sync changes, to avoid ping-pong loops.
 *
 * @mixin Model
 * @property string|null $uuid
 */
trait Syncable
{
    public static function bootSyncable(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        static::created(fn ($model) => $model->recordSyncOutbox('insert'));
        static::updated(fn ($model) => $model->recordSyncOutbox('update'));
        static::deleted(fn ($model) => $model->recordSyncOutbox('delete'));
    }

    /**
     * Run $callback without triggering sync_outbox writes (used while
     * applying incoming changes from the remote site).
     */
    public static function withoutSyncTracking(callable $callback): mixed
    {
        return SyncTrackingGuard::run($callback);
    }

    protected function recordSyncOutbox(string $operation): void
    {
        if (SyncTrackingGuard::isDisabled() || !config('sync.enabled')) {
            return;
        }

        $table = $this->getTable();
        $config = config("sync.tables.{$table}", []);

        $payload = $this->getAttributes();
        unset($payload['id']);

        foreach ($config['exclude'] ?? [] as $field) {
            unset($payload[$field]);
        }

        foreach ($config['foreign_keys'] ?? [] as $column => $referencedTable) {
            if (!array_key_exists($column, $payload)) {
                continue;
            }

            $localId = $payload[$column];
            unset($payload[$column]);

            $payload["{$column}_uuid"] = $localId === null
                ? null
                : DB::table($referencedTable)->where('id', $localId)->value('uuid');
        }

        DB::table('sync_outbox')->insert([
            'table_name' => $table,
            'record_uuid' => $this->uuid,
            'operation' => $operation,
            'payload' => json_encode($payload),
            'origin_site' => config('sync.site_id'),
            'created_at' => now(),
        ]);
    }
}
