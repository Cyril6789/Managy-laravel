<?php

namespace App\Services;

use App\Models\Automatisme;
use App\Models\ClientMessage;
use App\Models\Intervention;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Runs configured automatisms (auto SMS / e-mail to the customer) on intervention
 * events. Replaces the legacy automatismes.class.php.
 */
class AutomatismeRunner
{
    public function __construct(private SmsSender $sms) {}

    public function fire(string $evenement, Intervention $intervention): void
    {
        $rules = Automatisme::where('actif', true)
            ->where('evenement', $evenement)
            ->where(function ($q) use ($intervention) {
                $q->whereNull('statut_id')->orWhere('statut_id', $intervention->statut_id);
            })
            ->get();

        foreach ($rules as $rule) {
            $client = $intervention->client;
            if (! $client) {
                continue;
            }

            $body = $this->render($rule->modele, $intervention);
            $recipient = $intervention->recipientClient();

            if ($rule->canal === 'sms') {
                $this->sms->send($client, $body, $intervention, $recipient);
            } else {
                $this->sendEmail($intervention, $rule->sujet ?? 'Suivi de votre intervention', $body, $recipient?->email);
            }
        }
    }

    private function sendEmail(Intervention $intervention, string $sujet, string $body, ?string $to = null): void
    {
        $client = $intervention->client;
        $to ??= $client?->email;

        if (! $to) {
            return;
        }

        try {
            Mail::raw($body, function ($m) use ($to, $sujet) {
                $m->to($to)->subject($sujet)
                    ->from(Setting::get('company_email', config('mail.from.address')), Setting::get('company_name'));
            });
            $statut = 'envoye';
        } catch (\Throwable $e) {
            Log::error('Automatisme email failed: '.$e->getMessage());
            $statut = 'echec';
        }

        ClientMessage::create([
            'client_id' => $client->id,
            'intervention_id' => $intervention->id,
            'user_id' => Auth::id(),
            'canal' => 'email',
            'destinataire' => $to,
            'sujet' => $sujet,
            'corps' => $body,
            'statut' => $statut,
            'sent_at' => $statut === 'envoye' ? now() : null,
        ]);
    }

    private function render(string $template, Intervention $intervention): string
    {
        $client = $intervention->client;

        return strtr($template, [
            '{reference}' => $intervention->reference ?? (string) $intervention->id,
            '{client}' => $client?->nomComplet() ?? '',
            '{statut}' => $intervention->statut?->nom ?? '',
            '{lien}' => route('public.intervention', $intervention->public_token),
            '{entreprise}' => Setting::get('company_name', ''),
        ]);
    }
}
