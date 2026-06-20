<?php

namespace App\Http\Controllers;

use App\Support\Permissions;

/**
 * The two "en cours" reception desks. The actual list + actions live in their
 * Livewire components; these endpoints just gate access by permission and render
 * the page shell.
 */
class ReceptionController extends Controller
{
    public function commandes()
    {
        $this->authorize(Permissions::COMMANDES_RECEPTION);

        return view('reception.commandes');
    }

    public function sousTraitances()
    {
        $this->authorize(Permissions::SOUS_TRAITANCES_RECEPTION);

        return view('reception.sous-traitances');
    }
}
