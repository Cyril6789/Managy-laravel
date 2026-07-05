<?php

namespace App\Http\Controllers;

use App\Livewire\Support\TicketList;
use App\Livewire\Support\TicketThread;
use App\Models\SupportTicket;

/**
 * "Assistance" area for a société. Every user of the société can open a ticket
 * and see all the tickets of their tenant (any status) — the tenant scope on
 * {@see SupportTicket} guarantees the segregation automatically.
 *
 * The pages only host the Livewire components ({@see TicketList}
 * and {@see TicketThread}), which perform every action
 * (create, reply, close) without a page reload.
 */
class SupportController extends Controller
{
    public function index()
    {
        return view('support.index');
    }

    public function show(SupportTicket $ticket)
    {
        return view('support.show', compact('ticket'));
    }
}
