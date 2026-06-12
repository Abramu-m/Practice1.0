<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Sync\SyncApplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Phase 6.2 — receiving side of bidirectional sync.
 * See docs/phase6.2-bidirectional-sync-design.md §6-7.
 */
class SyncController extends Controller
{
    public function __construct(private SyncApplier $applier)
    {
    }

    /**
     * Remote pushes its pending sync_outbox rows here.
     */
    public function receive(Request $request)
    {
        $raw = $request->getContent();

        if (!$this->verifySignature($request, $raw)) {
            return response()->json(['error' => 'invalid signature'], 403);
        }

        $data = json_decode($raw, true) ?? [];
        $changes = $data['changes'] ?? [];

        $result = $this->applier->applyBatch($changes);

        if (!empty($changes)) {
            $remoteSite = $data['site'] ?? config('sync.remote_site_id');
            DB::table('sync_state')->updateOrInsert(
                ['remote_site' => $remoteSite],
                ['last_pull_at' => now(), 'updated_at' => now(), 'created_at' => now()]
            );
        }

        return response()->json(['ok' => true, ...$result]);
    }

    /**
     * Remote pulls our pending sync_outbox rows from here.
     */
    public function changes(Request $request)
    {
        $queryString = $request->getQueryString() ?? '';

        if (!$this->verifySignature($request, $queryString)) {
            return response()->json(['error' => 'invalid signature'], 403);
        }

        $since = (int) $request->query('since', 0);
        $limit = (int) config('sync.batch_size', 500);

        $rows = DB::table('sync_outbox')
            ->where('id', '>', $since)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        return response()->json(['changes' => $rows]);
    }

    private function verifySignature(Request $request, string $signedPayload): bool
    {
        $secret = config('sync.secret');
        $signature = (string) $request->header('X-Sync-Signature', '');

        if (empty($secret) || $signature === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $signedPayload, $secret), $signature);
    }
}
