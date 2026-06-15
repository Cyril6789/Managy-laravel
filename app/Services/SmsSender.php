<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientMessage;
use App\Models\Intervention;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends SMS to customers through a configurable provider and logs every message.
 * The legacy app hard-wired several providers; here the provider is a setting.
 */
class SmsSender
{
    public function send(Client $client, string $message, ?Intervention $intervention = null): ClientMessage
    {
        $destinataire = $client->telephone_mobile ?: $client->telephone_fixe;
        $signature = Setting::get('sms_signature');
        $corps = trim($message.($signature ? "\n".$signature : ''));

        $statut = 'envoye';
        try {
            if ($destinataire) {
                $this->dispatch($destinataire, $corps);
            } else {
                $statut = 'echec';
            }
        } catch (\Throwable $e) {
            Log::error('SMS sending failed: '.$e->getMessage());
            $statut = 'echec';
        }

        return ClientMessage::create([
            'client_id' => $client->id,
            'intervention_id' => $intervention?->id,
            'user_id' => Auth::id(),
            'canal' => 'sms',
            'destinataire' => (string) $destinataire,
            'corps' => $corps,
            'statut' => $statut,
            'sent_at' => $statut === 'envoye' ? now() : null,
        ]);
    }

    private function dispatch(string $to, string $body): void
    {
        $provider = Setting::get('sms_provider', 'log');
        $apiKey = Setting::get('sms_api_key');
        $sender = Setting::get('sms_sender', 'MANAGY');

        match ($provider) {
            'smsmode' => Http::asForm()->post('https://rest.smsmode.com/sms/v1/messages', [
                'accessToken' => $apiKey,
                'recipient' => $to,
                'body' => $body,
                'from' => $sender,
            ])->throw(),
            'smsfactor' => Http::withToken((string) $apiKey)->post('https://api.smsfactor.com/send', [
                'to' => $to,
                'text' => $body,
                'sender' => $sender,
            ])->throw(),
            // Default "log" driver: no external call, useful for local/dev.
            default => Log::info("[SMS->{$to}] {$body}"),
        };
    }
}
