<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\InterventionLog;
use App\Support\Permissions;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize(Permissions::LOGS_VIEW);

        $appLogs = ActivityLog::with('user')->latest()->limit(200)->get();
        $interLogs = InterventionLog::with(['user', 'intervention'])->latest()->limit(200)->get();

        return view('logs.index', compact('appLogs', 'interLogs'));
    }

    /**
     * Undo a soft deletion recorded in the journal, restoring the subject and
     * every child that was cascaded with it. Both the log (route binding) and
     * the subject query stay under the société global scope, so a tenant can
     * only ever restore its own data.
     */
    public function restore(Request $request, ActivityLog $log)
    {
        $this->authorize(Permissions::AUDIT_RESTORE);

        abort_unless($log->isRestorable(), 404);

        $this->restoreRecord($log->subject_type, $log->subject_id);

        foreach ($log->changes['cascaded'] ?? [] as $child) {
            $this->restoreRecord($child['type'] ?? null, $child['id'] ?? null);
        }

        $log->update(['undone_at' => now(), 'undone_by' => $request->user()->id]);

        return back()->with('success', 'Élément restauré.');
    }

    /** Restore a single soft-deleted record if it exists and is still trashed. */
    private function restoreRecord(?string $type, int|string|null $id): void
    {
        if (! $type || ! $id || ! class_exists($type) || ! method_exists($type, 'restore')) {
            return;
        }

        $record = $type::withTrashed()->find($id);

        if ($record && $record->trashed()) {
            $record->restore();
        }
    }
}
