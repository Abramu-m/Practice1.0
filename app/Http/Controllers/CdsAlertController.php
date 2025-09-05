<?php

namespace App\Http\Controllers;

use App\Models\CdsAlert;
use App\Models\CdsAlertAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CdsAlertController extends Controller
{
    public function acknowledge(Request $request, CdsAlert $alert)
    {
        $data = $request->validate([
            'action' => 'required|in:accept,override,dismiss',
            'reason' => 'nullable|string|max:500',
        ]);

        CdsAlertAction::create([
            'cds_alert_id' => $alert->id,
            'action' => $data['action'],
            'reason' => $data['reason'] ?? null,
            'user_id' => Auth::id(),
        ]);

        // If accepted or dismissed, mark as resolved
        if (in_array($data['action'], ['accept', 'dismiss', 'override'])) {
            $alert->status = 'resolved';
            $alert->resolved_at = now();
            $alert->save();
        }

        return response()->json([
            'success' => true,
            'alert_id' => $alert->id,
            'status' => $alert->status,
        ]);
    }
}
