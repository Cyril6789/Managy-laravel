@extends('layouts.app')
@section('title', $client->nomComplet())

@section('content')
    @if(auth()->user()->society?->invoice_enabled && $outstandingInvoiceCount > 0)
        <div class="mb-6 flex items-start justify-between gap-4 rounded-xl border border-amber-300 bg-amber-50 px-5 py-4 text-amber-950 shadow-sm dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-100" role="status">
            <div class="flex min-w-0 items-start gap-3">
                <x-icon name="clock" class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" />
                <div>
                    <p class="font-semibold">{{ $outstandingInvoiceCount }} {{ $outstandingInvoiceCount > 1 ? 'factures sont en attente de règlement' : 'facture est en attente de règlement' }}</p>
                    <p class="mt-0.5 text-sm text-amber-800 dark:text-amber-200">Solde total restant : {{ number_format($outstandingInvoiceBalance, 2, ',', ' ') }} €</p>
                </div>
            </div>
            <button type="button" class="shrink-0 text-sm font-semibold text-amber-800 hover:underline dark:text-amber-200" x-data x-on:click="document.querySelector('[data-client-tabs]')?.scrollIntoView({ behavior: 'smooth' }); document.querySelector('[data-invoices-tab]')?.click()">Voir les factures</button>
        </div>
    @endif

    <x-page-header :title="$client->nomComplet()" :subtitle="$client->type === 'professionnel' ? 'Professionnel' : 'Particulier'">
        <x-slot:actions>
            @can(\App\Support\Permissions::INTERVENTIONS_CREATE)
                <x-button variant="secondary" :href="route('interventions.create', ['client_id' => $client->id])"><x-icon name="plus" class="h-4 w-4" /> Intervention</x-button>
            @endcan
            @can(\App\Support\Permissions::CLIENTS_MANAGE)
                <x-button variant="secondary" :href="route('clients.edit', $client)">Modifier</x-button>
                <form action="{{ route('clients.archive', $client) }}" method="POST">@csrf @method('PATCH')
                    <x-button variant="secondary" type="submit">{{ $client->archived_at ? 'Réactiver' : 'Archiver' }}</x-button>
                </form>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <x-card title="Coordonnées">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">E-mail</dt><dd class="text-right">{{ $client->email ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Fixe</dt><dd>{{ $client->telephone_fixe ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Mobile</dt><dd>{{ $client->telephone_mobile ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Adresse</dt><dd class="text-right">{{ $client->adresseComplete() ?: '—' }}</dd></div>
                    @if ($client->siret)<div class="flex justify-between gap-3"><dt class="text-gray-500">SIRET</dt><dd>{{ $client->siret }}</dd></div>@endif
                    @if ($client->type === 'particulier' && $client->companies->isNotEmpty())
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500">Contact de</dt>
                            <dd class="text-right">
                                @foreach ($client->companies as $company)
                                    <a class="text-brand-600 hover:underline" href="{{ route('clients.show', $company) }}">{{ $company->nomComplet() }}</a>@if (! $loop->last), @endif
                                @endforeach
                            </dd>
                        </div>
                    @endif
                    @can(\App\Support\Permissions::CLIENTS_REMISES)
                        @if ($client->deplacement_gratuit)<div class="flex justify-between gap-3"><dt class="text-gray-500">Déplacement</dt><dd class="font-medium text-green-600">Gratuit</dd></div>@endif
                        @if ($client->remise_prestations)<div class="flex justify-between gap-3"><dt class="text-gray-500">Remise prestations</dt><dd>{{ rtrim(rtrim(number_format($client->remise_prestations,2),'0'),'.') }} %</dd></div>@endif
                        @if ($client->remise_pieces)<div class="flex justify-between gap-3"><dt class="text-gray-500">Remise pièces</dt><dd>{{ rtrim(rtrim(number_format($client->remise_pieces,2),'0'),'.') }} %</dd></div>@endif
                    @endcan
                </dl>
                @if ($client->notes)
                    <div class="mt-4 rounded-lg bg-gray-50 p-3 text-sm text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $client->notes }}</div>
                @endif
            </x-card>

            @if ($client->type === 'professionnel')
                <livewire:contact-manager :company="$client" />
            @endif

            @can(\App\Support\Permissions::MAINTENANCE_VIEW)
                <x-card title="Pack maintenance">
                    @if ($aPackMaintenance)
                        <p class="text-3xl font-bold {{ $soldeMaintenance < 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($soldeMaintenance, 2) }} h</p>
                        <a href="{{ route('maintenance.show', $client) }}" class="text-sm text-brand-600 hover:underline">Voir l'historique →</a>
                    @else
                        <p class="text-sm text-gray-500">Ce client n'a pas encore de pack maintenance.</p>
                    @endif

                    @can(\App\Support\Permissions::MAINTENANCE_MANAGE)
                        <form action="{{ route('maintenance.store', $client) }}" method="POST" class="mt-4 space-y-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                            @csrf
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Nouveau mouvement</p>
                            <x-field label="Opération" name="sens">
                                <x-select name="sens">
                                    <option value="credit">Créditer (+) des heures</option>
                                    <option value="debit">Débiter (−) des heures</option>
                                </x-select>
                            </x-field>
                            <x-field label="Nombre d'heures" name="heures">
                                <x-input name="heures" type="number" step="0.25" min="0.25" placeholder="ex. 10" required />
                            </x-field>
                            <x-field label="Description" name="description">
                                <x-input name="description" placeholder="ex. contrat annuel" />
                            </x-field>
                            <x-button type="submit" class="w-full">Enregistrer le mouvement</x-button>
                        </form>
                    @endcan
                </x-card>
            @endcan
        </div>

        <div class="lg:col-span-2" x-data="{ tab: 'inter' }" data-client-tabs>
            <div class="mb-4 flex gap-1 border-b border-gray-200 dark:border-gray-800">
                <button @click="tab='inter'" :class="tab==='inter' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-4 py-2 text-sm font-medium">Interventions ({{ $interventions->count() }})</button>
                <button @click="tab='comm'" :class="tab==='comm' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-4 py-2 text-sm font-medium">Communications ({{ $messages->count() }})</button>
                @if(auth()->user()->society?->invoice_enabled)
                    <button @click="tab='invoices'" :class="tab==='invoices' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-4 py-2 text-sm font-medium" data-invoices-tab>Factures ({{ $invoices->count() }})</button>
                @endif
            </div>

            <x-card :padding="false" x-show="tab==='inter'">
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($interventions as $i)
                        <a href="{{ route('interventions.show', $i) }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <div class="min-w-0">
                                <p class="font-medium">{{ $i->reference }}</p>
                                <p class="truncate text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($i->panne, 60) ?: '—' }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                @if ($i->statut)<x-badge :color="$i->statut->couleur">{{ $i->statut->nom }}</x-badge>@endif
                                <span class="text-xs text-gray-400">{{ $i->opened_at?->format('d/m/Y') }}</span>
                            </div>
                        </a>
                    @empty
                        <x-empty-state icon="wrench" title="Aucune intervention" />
                    @endforelse
                </div>
            </x-card>

            <x-card :padding="false" x-show="tab==='comm'" x-cloak>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($messages as $msg)
                        <div class="px-5 py-3 text-sm">
                            <div class="mb-1 flex items-center justify-between gap-2">
                                <span class="flex items-center gap-2">
                                    <x-badge :color="$msg->canal === 'sms' ? '#2563eb' : '#7c3aed'">{{ strtoupper($msg->canal) }}</x-badge>
                                    <span class="text-gray-500">→ {{ $msg->destinataire }}</span>
                                    @if ($msg->intervention)<a href="{{ route('interventions.show', $msg->intervention) }}" class="text-xs text-brand-600 hover:underline">{{ $msg->intervention->reference }}</a>@endif
                                </span>
                                <span class="text-xs text-gray-400">{{ $msg->created_at?->format('d/m/Y H:i') }}</span>
                            </div>
                            @if ($msg->sujet)<p class="font-medium">{{ $msg->sujet }}</p>@endif
                            <p class="whitespace-pre-line text-gray-600 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($msg->corps, 200) }}</p>
                        </div>
                    @empty
                        <x-empty-state icon="bell" title="Aucune communication" message="Les SMS et e-mails envoyés à ce client apparaîtront ici." />
                    @endforelse
                </div>
            </x-card>

            @if(auth()->user()->society?->invoice_enabled)
                <x-card :padding="false" x-show="tab==='invoices'" x-cloak>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($invoices as $invoice)
                            @php
                                $paymentStatus = $invoice->paymentStatus();
                                $statusColor = ['paid' => '#16a34a', 'partial' => '#ea580c', 'pending' => '#d97706'][$paymentStatus];
                                $invoiceTotal = $invoice->vat_enabled ? $invoice->total_ttc : $invoice->total_ht;
                            @endphp
                            <div class="grid gap-4 border-l-4 px-5 py-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center" style="border-left-color: {{ $statusColor }};">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $invoice->number }}</p>
                                        <x-badge :color="$statusColor">{{ $invoice->paymentStatusLabel() }}</x-badge>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Émise le {{ $invoice->issued_at->format('d/m/Y') }}{{ $invoice->intervention ? ' · Intervention '.$invoice->intervention->reference : ' · Facture libre' }}</p>
                                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm">
                                        <span><span class="text-gray-500">Total</span> <strong>{{ number_format($invoiceTotal, 2, ',', ' ') }} € {{ $invoice->vat_enabled ? 'TTC' : 'HT' }}</strong></span>
                                        @if($paymentStatus !== 'pending')<span><span class="text-gray-500">Réglé</span> <strong class="text-green-700 dark:text-green-400">{{ number_format($invoice->paidAmount(), 2, ',', ' ') }} €</strong></span>@endif
                                        @if($paymentStatus !== 'paid')<span><span class="text-gray-500">Reste</span> <strong class="text-amber-700 dark:text-amber-400">{{ number_format($invoice->balanceDue(), 2, ',', ' ') }} €</strong></span>@endif
                                    </div>
                                </div>
                                <x-button class="w-full justify-center sm:w-auto" type="button" variant="secondary" x-on:click="Livewire.dispatch('open-invoice-viewer', { invoiceId: {{ $invoice->id }} })">Ouvrir la facture</x-button>
                            </div>
                        @empty
                            <x-empty-state icon="list" title="Aucune facture" />
                        @endforelse
                    </div>
                </x-card>
            @endif
        </div>
    </div>
    @if(auth()->user()->society?->invoice_enabled)
        <livewire:manual-invoice :launcher="false" />
    @endif
@endsection
