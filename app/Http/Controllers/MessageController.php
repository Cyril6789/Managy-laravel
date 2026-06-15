<?php

namespace App\Http\Controllers;

use App\Models\ClientMessage;
use App\Models\Intervention;
use App\Models\InterventionLog;
use App\Models\Setting;
use App\Services\SmsSender;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * Sends an SMS or e-mail to the customer from within an intervention.
 */
class MessageController extends Controller
{
    public function store(Request $request, Intervention $intervention, SmsSender $sms)
    {
        $this->authorize(Permissions::MESSAGES_SEND);

        $data = $request->validate([
            'canal' => ['required', Rule::in(['sms', 'email'])],
            'sujet' => ['nullable', 'string', 'max:255'],
            'corps' => ['required', 'string'],
        ]);

        $client = $intervention->client;

        if ($data['canal'] === 'sms') {
            $sms->send($client, $data['corps'], $intervention);
        } else {
            $this->sendEmail($intervention, $data['sujet'] ?? 'Votre intervention', $data['corps']);
        }

        InterventionLog::create([
            'intervention_id' => $intervention->id,
            'user_id' => Auth::id(),
            'texte' => 'a envoyé un '.($data['canal'] === 'sms' ? 'SMS' : 'e-mail').' au client',
            'created_at' => now(),
        ]);

        return back()->with('success', 'Message envoyé.');
    }

    private function sendEmail(Intervention $intervention, string $sujet, string $corps): void
    {
        $client = $intervention->client;
        $to = $client?->email;
        $statut = 'envoye';

        if (! $to) {
            $statut = 'echec';
        } else {
            try {
                Mail::raw($corps, function ($m) use ($to, $sujet) {
                    $m->to($to)->subject($sujet)
                        ->from(Setting::get('company_email', config('mail.from.address')), Setting::get('company_name'));
                });
            } catch (\Throwable $e) {
                Log::error('Email failed: '.$e->getMessage());
                $statut = 'echec';
            }
        }

        ClientMessage::create([
            'client_id' => $client?->id,
            'intervention_id' => $intervention->id,
            'user_id' => Auth::id(),
            'canal' => 'email',
            'destinataire' => (string) $to,
            'sujet' => $sujet,
            'corps' => $corps,
            'statut' => $statut,
            'sent_at' => $statut === 'envoye' ? now() : null,
        ]);
    }
}
