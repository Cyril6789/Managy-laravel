<?php

namespace App\Http\Controllers\Intervention;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use App\Services\Notifier;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Internal team chat within an intervention (ex chat_inter).
 */
class MessageController extends Controller
{
    public function store(Request $request, Intervention $intervention)
    {
        $this->authorize(Permissions::INTERVENTIONS_VIEW);

        $data = $request->validate(['message' => ['required', 'string']]);

        $intervention->messages()->create([
            'user_id' => Auth::id(),
            'message' => $data['message'],
        ]);

        Notifier::interventionChanged($intervention, 'Nouveau message dans le tchat');

        return back();
    }
}
