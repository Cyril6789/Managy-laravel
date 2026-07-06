<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Livewire\Admin\SupportList;
use App\Livewire\Admin\SupportThread;
use App\Models\SupportTicket;

/**
 * Cross-société supervision of the "Assistance" tickets, reserved to the
 * platform super-admin. Their tenancy is null, so the {@see SupportTicket}
 * global scope is inactive and these pages legitimately span every société.
 *
 * The pages host the Livewire components ({@see SupportList}
 * and {@see SupportThread}), which perform every action
 * (reply, status / urgency change) without a page reload.
 */
class SupportController extends Controller
{
    public function index()
    {
        return view('admin.support.index');
    }

    public function show(SupportTicket $ticket)
    {
        // The super-admin's tenancy is null, so route-model binding already
        // resolves the ticket across every société.
        return view('admin.support.show', compact('ticket'));
    }
}
