<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Phase 6.2b — review queue for sync_conflicts rows raised by SyncApplier
 * when an incoming change collides with a local edit (last-write-wins
 * couldn't resolve automatically). See docs/phase6.2-bidirectional-sync-design.md §7.
 */
class SyncConflictController extends Controller
{
    public function index()
    {
        $conflicts = DB::table('sync_conflicts')
            ->orderByRaw('resolved_at IS NOT NULL, detected_at DESC')
            ->get()
            ->map(function ($row) {
                $row->local_payload = json_decode($row->local_payload, true) ?? [];
                $row->incoming_payload = json_decode($row->incoming_payload, true) ?? [];
                return $row;
            });

        return view('admin.sync.conflicts.index', compact('conflicts'));
    }

    public function resolve(Request $request, int $id)
    {
        $request->validate([
            'resolution' => 'required|in:kept_local,kept_incoming,merged',
        ]);

        $conflict = DB::table('sync_conflicts')->where('id', $id)->first();
        abort_if(!$conflict, 404);
        abort_if($conflict->resolved_at, 409, 'This conflict has already been resolved.');

        $resolution = $request->input('resolution');

        if ($resolution === 'kept_incoming') {
            $payload = json_decode($conflict->incoming_payload, true) ?? [];
            unset($payload['id']);

            DB::table($conflict->table_name)->where('uuid', $conflict->record_uuid)->update($payload);
        }

        DB::table('sync_conflicts')->where('id', $id)->update([
            'resolution' => $resolution,
            'resolved_at' => now(),
            'resolved_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.sync.conflicts.index')->with('success', 'Conflict resolved.');
    }
}
