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
}
