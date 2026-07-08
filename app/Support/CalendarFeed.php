<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Intervention;
use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Builds a read-only iCalendar (RFC 5545) feed for a single technician so they
 * can subscribe to their agenda from Apple Calendar, Outlook or Google Calendar.
 *
 * The feed carries the interventions the technician is assigned to (title = the
 * client, location = the client's address, notes = the reported fault and other
 * useful details) plus the standalone appointments owned by the technician.
 */
class CalendarFeed
{
    public function __construct(private readonly User $user) {}

    public function build(): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Managy//Calendrier technicien//FR',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'.$this->escape('Agenda '.$this->user->fullName()),
            'X-WR-TIMEZONE:UTC',
        ];

        foreach ($this->interventions() as $intervention) {
            $lines = array_merge($lines, $this->interventionEvent($intervention));
        }

        foreach ($this->events() as $event) {
            $lines = array_merge($lines, $this->appointmentEvent($event));
        }

        $lines[] = 'END:VCALENDAR';

        // RFC 5545 mandates CRLF line endings, folded at 75 octets.
        return collect($lines)
            ->map(fn (string $line) => $this->fold($line))
            ->implode("\r\n")."\r\n";
    }

    /**
     * The ongoing interventions the technician is assigned to that carry a
     * scheduled slot. Closed ("terminées") jobs are excluded — the feed mirrors
     * the calendar page, which only shows interventions still in progress.
     * A rolling six-month floor keeps the feed light while preserving context.
     */
    private function interventions()
    {
        return Intervention::ouvertes()
            ->whereNotNull('rdv_debut')
            ->where('rdv_annule', false)
            ->where('rdv_debut', '>=', now()->subMonths(6))
            ->whereHas('techniciens', fn ($q) => $q->whereKey($this->user->id))
            ->with('client')
            ->orderBy('rdv_debut')
            ->get();
    }

    private function events()
    {
        return Event::query()
            ->where('user_id', $this->user->id)
            ->where('debut', '>=', now()->subMonths(6))
            ->with('client')
            ->orderBy('debut')
            ->get();
    }

    /** @return array<int, string> */
    private function interventionEvent(Intervention $i): array
    {
        $client = $i->client;
        $start = $i->rdv_debut;
        $end = $i->rdv_fin ?: $start->copy()->addHour();

        $summary = $client?->nomComplet() ?: ($i->reference ?: 'Intervention');
        if ($i->urgente) {
            $summary = '⚠ URGENT — '.$summary;
        }

        return $this->vevent(
            uid: 'intervention-'.$i->id.'@managy',
            start: $start,
            end: $end,
            summary: $summary,
            location: $this->interventionLocation($i),
            description: $this->interventionNotes($i),
            url: route('interventions.show', $i),
        );
    }

    /**
     * A home visit points to the client's address; an in-shop job simply reads
     * "Atelier" (the customer drops the device off at the workshop).
     */
    private function interventionLocation(Intervention $i): string
    {
        if (! $i->estDomicile()) {
            return 'Atelier';
        }

        return $i->client?->adresseComplete() ?: '';
    }

    /** Location = client address, notes = reported fault + intervention details. */
    private function interventionNotes(Intervention $i): string
    {
        $parts = [];

        if ($i->reference) {
            $parts[] = 'Intervention n° '.$this->preventAutoDetection($i->reference);
        }

        if ($i->panne) {
            $parts[] = 'Panne constatée : '.$i->panne;
        }

        if ($i->diagnostic) {
            $parts[] = 'Diagnostic : '.$i->diagnostic;
        }

        $contact = $i->client;
        if ($phone = ($contact?->telephone_mobile ?: $contact?->telephone_fixe)) {
            $parts[] = 'Téléphone : '.$phone;
        }

        if ($client = $i->client) {
            // How many jobs this client already went through (this one excluded).
            $passees = $client->interventions()
                ->whereKeyNot($i->id)
                ->whereNotNull('closed_at')
                ->count();
            $parts[] = 'Interventions passées : '.$passees;

            $parts[] = 'Solde pack maintenance : '.$this->formatHeures($client->soldeMaintenance());
        }

        if ($i->note) {
            $parts[] = 'Note : '.$i->note;
        }

        $parts[] = 'Fiche intervention : '.route('interventions.show', $i);

        return implode("\n", $parts);
    }

    /** Human-readable hours: "3 h", "1,5 h", "0 h". */
    private function formatHeures(float $heures): string
    {
        $formatted = rtrim(rtrim(number_format($heures, 2, ',', ' '), '0'), ',');

        return $formatted.' h';
    }

    /** @return array<int, string> */
    private function appointmentEvent(Event $e): array
    {
        $start = $e->debut;
        $end = $e->fin ?: $start->copy()->addHour();

        return $this->vevent(
            uid: 'event-'.$e->id.'@managy',
            start: $start,
            end: $end,
            summary: $e->titre,
            location: $e->client?->adresseComplete() ?: '',
            description: $e->description ?: '',
            url: null,
            allDay: (bool) $e->journee_entiere,
        );
    }

    /** @return array<int, string> */
    private function vevent(
        string $uid,
        CarbonInterface $start,
        CarbonInterface $end,
        string $summary,
        string $location,
        string $description,
        ?string $url,
        bool $allDay = false,
    ): array {
        $lines = [
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
        ];

        if ($allDay) {
            $lines[] = 'DTSTART;VALUE=DATE:'.$start->copy()->format('Ymd');
            $lines[] = 'DTEND;VALUE=DATE:'.$end->copy()->addDay()->format('Ymd');
        } else {
            $lines[] = 'DTSTART:'.$this->stamp($start);
            $lines[] = 'DTEND:'.$this->stamp($end);
        }

        $lines[] = 'SUMMARY:'.$this->escape($summary);

        if ($location !== '') {
            $lines[] = 'LOCATION:'.$this->escape($location);
        }

        if ($description !== '') {
            $lines[] = 'DESCRIPTION:'.$this->escape($description);
        }

        if ($url) {
            $lines[] = 'URL:'.$this->escape($url);
        }

        $lines[] = 'END:VEVENT';

        return $lines;
    }

    private function stamp(CarbonInterface $date): string
    {
        return $date->copy()->utc()->format('Ymd\THis\Z');
    }

    /**
     * iOS/macOS data detectors turn a reference like "2026-0004" into a tappable
     * phone number (and surface a "call" shortcut at the top of the event).
     * Weaving an invisible WORD JOINER (U+2060) between each character breaks the
     * digit run so the detector no longer matches it — the text still reads the
     * same, and the real phone number in the notes stays the only callable one.
     */
    private function preventAutoDetection(string $value): string
    {
        return implode("\u{2060}", mb_str_split($value));
    }

    /** Escape reserved iCalendar characters (RFC 5545 §3.3.11). */
    private function escape(string $value): string
    {
        return str_replace(
            ['\\', "\r\n", "\n", "\r", ';', ','],
            ['\\\\', '\n', '\n', '\n', '\;', '\,'],
            $value,
        );
    }

    /** Fold content lines to 75 octets with a leading space on continuations. */
    private function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }

        $folded = '';
        $current = '';
        foreach (mb_str_split($line) as $char) {
            if (strlen($current) + strlen($char) > 75) {
                $folded .= ($folded === '' ? '' : "\r\n ").$current;
                $current = '';
            }
            $current .= $char;
        }

        return $folded.($folded === '' ? '' : "\r\n ").$current;
    }
}
