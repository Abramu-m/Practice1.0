<?php

namespace App\Console\Commands;

use App\Services\Sync\SyncApplier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Phase 6.2 — push pending sync_outbox rows to the remote site and pull its
 * changes. See docs/phase6.2-bidirectional-sync-design.md §6.
 *
 * Scheduled via routes/console.php every five minutes. If the remote is
 * unreachable (expected for an offline clinic), this no-ops and exits
 * successfully rather than failing.
 */
class RunSync extends Command
{
    protected $signature = 'sync:run';

    protected $description = 'Push pending sync_outbox changes to the remote site and pull its changes';

    public function handle(SyncApplier $applier): int
    {
        if (!config('sync.enabled')) {
            $this->line('sync:run - SYNC_ENABLED is false, skipping');

            return self::SUCCESS;
        }

        $remote = rtrim((string) config('sync.remote_url'), '/');
        $secret = (string) config('sync.secret');
        $siteId = (string) config('sync.site_id');
        $remoteSiteId = (string) config('sync.remote_site_id');

        if (!$remote || !$secret || !$siteId || !$remoteSiteId) {
            $this->error('sync:run - sync.remote_url / sync.secret / sync.site_id / sync.remote_site_id must all be configured');

            return self::FAILURE;
        }

        if (!$this->isReachable($remote)) {
            Log::debug("sync:run - {$remote} unreachable, skipping");
            $this->line("sync:run - {$remote} unreachable, skipping");

            return self::SUCCESS;
        }

        $this->push($remote, $secret, $siteId, $remoteSiteId);
        $this->pull($applier, $remote, $secret, $remoteSiteId);

        return self::SUCCESS;
    }

    private function isReachable(string $remote): bool
    {
        try {
            return Http::timeout((int) config('sync.ping_timeout', 3))
                ->get("{$remote}/api/sync/ping")
                ->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function push(string $remote, string $secret, string $siteId, string $remoteSiteId): void
    {
        $limit = (int) config('sync.batch_size', 500);

        $rows = DB::table('sync_outbox')
            ->whereNull('synced_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        $changes = $rows->map(fn ($row) => [
            'table_name' => $row->table_name,
            'record_uuid' => $row->record_uuid,
            'operation' => $row->operation,
            'payload' => json_decode($row->payload, true),
            'origin_site' => $row->origin_site,
        ])->values()->all();

        $body = json_encode(['site' => $siteId, 'changes' => $changes]);
        $signature = hash_hmac('sha256', $body, $secret);

        $response = Http::timeout(30)
            ->withBody($body, 'application/json')
            ->withHeaders(['X-Sync-Signature' => $signature])
            ->post("{$remote}/api/sync/receive");

        if (!$response->successful()) {
            $this->error("sync:run - push failed: HTTP {$response->status()}");
            Log::warning('sync:run push failed', ['status' => $response->status(), 'body' => $response->body()]);

            return;
        }

        DB::table('sync_outbox')->whereIn('id', $rows->pluck('id'))->update(['synced_at' => now()]);

        DB::table('sync_state')->updateOrInsert(
            ['remote_site' => $remoteSiteId],
            ['last_push_at' => now(), 'updated_at' => now(), 'created_at' => now()]
        );

        $this->info("sync:run - pushed {$rows->count()} change(s)");
    }

    private function pull(SyncApplier $applier, string $remote, string $secret, string $remoteSiteId): void
    {
        $since = DB::table('sync_state')->where('remote_site', $remoteSiteId)->value('last_pull_outbox_id') ?? 0;

        $queryString = "since={$since}";
        $signature = hash_hmac('sha256', $queryString, $secret);

        $response = Http::timeout(30)
            ->withHeaders(['X-Sync-Signature' => $signature])
            ->get("{$remote}/api/sync/changes?{$queryString}");

        if (!$response->successful()) {
            $this->error("sync:run - pull failed: HTTP {$response->status()}");
            Log::warning('sync:run pull failed', ['status' => $response->status(), 'body' => $response->body()]);

            return;
        }

        $changes = $response->json('changes') ?? [];

        if (empty($changes)) {
            DB::table('sync_state')->updateOrInsert(
                ['remote_site' => $remoteSiteId],
                ['last_pull_at' => now(), 'updated_at' => now(), 'created_at' => now()]
            );

            return;
        }

        $result = $applier->applyBatch($changes);
        $maxId = max(array_column($changes, 'id'));

        DB::table('sync_state')->updateOrInsert(
            ['remote_site' => $remoteSiteId],
            [
                'last_pull_at' => now(),
                'last_pull_outbox_id' => $maxId,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $this->info("sync:run - pulled {$result['applied']} change(s), {$result['conflicts']} conflict(s), {$result['deferred']} deferred");
    }
}
