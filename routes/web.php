<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\AutomatismeController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Intervention\CommandeController;
use App\Http\Controllers\Intervention\InterventionPrestationController;
use App\Http\Controllers\Intervention\MessageController as InterventionChatController;
use App\Http\Controllers\Intervention\SousTraitanceController;
use App\Http\Controllers\InterventionController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\PublicInterventionController;
use App\Http\Controllers\Public\PublicSatisfactionController;
use App\Http\Controllers\ReceptionController;
use App\Http\Controllers\SatisfactionController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\StickyNoteController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// ----- Public (no auth) ------------------------------------------------------
Route::get('/logo-entreprise', [MediaController::class, 'logo'])->name('company.logo');
Route::get('/suivi/{token}', [PublicInterventionController::class, 'show'])->name('public.intervention');
Route::get('/satisfaction/{token}', [PublicSatisfactionController::class, 'show'])->name('public.satisfaction');
Route::post('/satisfaction/{token}', [PublicSatisfactionController::class, 'store'])->name('public.satisfaction.store');

// ----- Guest auth ------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

// ----- Authenticated ---------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/recherche', SearchController::class)->name('search');

    // Profile
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/mot-de-passe', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Clients
    Route::get('adresse/recherche', [\App\Http\Controllers\AddressController::class, 'search'])->name('adresse.search');
    Route::get('clients/recherche', [ClientController::class, 'search'])->name('clients.search');
    Route::post('clients/rapide', [ClientController::class, 'quickStore'])->name('clients.quick-store');
    Route::resource('clients', ClientController::class);
    Route::patch('clients/{client}/archive', [ClientController::class, 'archive'])->name('clients.archive');

    // Réception (commandes / sous-traitances en cours) — handled without opening the intervention
    Route::get('/commandes-en-cours', [ReceptionController::class, 'commandes'])->name('reception.commandes');
    Route::get('/sous-traitances-en-cours', [ReceptionController::class, 'sousTraitances'])->name('reception.sous_traitances');

    // Interventions
    Route::get('/facturation', [InterventionController::class, 'facturationIndex'])->name('facturation.index');
    Route::get('interventions/contexte-client/{client}', [InterventionController::class, 'clientContext'])->name('interventions.client_context');
    Route::resource('interventions', InterventionController::class);
    Route::get('interventions/{intervention}/impression/{type}', [InterventionController::class, 'print'])->name('interventions.print')->where('type', 'depot|rapport');
    Route::get('interventions/{intervention}/sous-traitance/{sousTraitance}/feuille', [InterventionController::class, 'sousTraitanceSheet'])->name('interventions.sst_sheet');
    Route::patch('interventions/{intervention}/rapport', [InterventionController::class, 'saveRapport'])->name('interventions.rapport');
    Route::post('interventions/{intervention}/affectation', [InterventionController::class, 'assign'])->name('interventions.assign');
    Route::post('interventions/{intervention}/statut', [InterventionController::class, 'updateStatut'])->name('interventions.statut');
    Route::post('interventions/{intervention}/rdv', [InterventionController::class, 'updateRdv'])->name('interventions.rdv');
    Route::post('interventions/{intervention}/prise-en-charge', [InterventionController::class, 'togglePriseEnCharge'])->name('interventions.pec');
    Route::post('interventions/{intervention}/finaliser', [InterventionController::class, 'finaliser'])->name('interventions.finaliser');
    Route::post('interventions/{intervention}/annuler-finalisation', [InterventionController::class, 'annulerFinalisation'])->name('interventions.annuler_finalisation');
    Route::post('interventions/{intervention}/restituer', [InterventionController::class, 'restituer'])->name('interventions.restituer');
    Route::post('interventions/{intervention}/decloturer', [InterventionController::class, 'decloturer'])->name('interventions.decloturer');
    Route::post('interventions/{intervention}/facturation', [InterventionController::class, 'toggleFacturation'])->name('interventions.facturation');
    Route::post('interventions/{intervention}/message-client', [MessageController::class, 'store'])->name('interventions.message_client');

    // Intervention sub-resources
    Route::post('interventions/{intervention}/chat', [InterventionChatController::class, 'store'])->name('interventions.chat.store');
    Route::resource('interventions.prestations', InterventionPrestationController::class)->only(['store', 'destroy'])->shallow();
    Route::resource('interventions.commandes', CommandeController::class)->only(['store', 'update', 'destroy'])->shallow();
    Route::resource('interventions.sous-traitances', SousTraitanceController::class)->only(['store', 'update', 'destroy'])->shallow()->parameters(['sous-traitances' => 'sousTraitance']);

    // Technician availability & absences (planning board)
    Route::get('/disponibilites', fn () => view('disponibilites.index'))->name('disponibilites.index');

    // Calendar
    Route::get('/calendrier', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendrier/evenements', [CalendarController::class, 'events'])->name('calendar.events');
    Route::post('/calendrier/evenements', [CalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendrier/evenements/{event}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendrier/evenements/{event}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

    // Tasks
    Route::resource('tasks', TaskController::class)->except(['show', 'create', 'edit']);
    Route::post('tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');

    // Sticky notes
    Route::post('/post-it', [StickyNoteController::class, 'store'])->name('sticky.store');
    Route::put('/post-it/{stickyNote}', [StickyNoteController::class, 'update'])->name('sticky.update');
    Route::delete('/post-it/{stickyNote}', [StickyNoteController::class, 'destroy'])->name('sticky.destroy');

    // Notifications
    Route::post('/notifications/lire-tout', [NotificationController::class, 'readAll'])->name('notifications.read_all');
    Route::get('/notifications/{notification}/lire', [NotificationController::class, 'read'])->name('notifications.read');

    // Maintenance pack
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::get('/maintenance/{client}', [MaintenanceController::class, 'show'])->name('maintenance.show');
    Route::post('/maintenance/{client}', [MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::delete('/maintenance/mouvement/{movement}', [MaintenanceController::class, 'destroy'])->name('maintenance.destroy');

    // Stats / logs / satisfaction
    Route::get('/statistiques', StatsController::class)->name('stats.index');
    Route::get('/journaux', LogController::class)->name('logs.index');
    Route::get('/satisfaction', [SatisfactionController::class, 'index'])->name('satisfaction.index');

    // Administration
    Route::resource('staff', StaffController::class);
    Route::resource('automatismes', AutomatismeController::class)->except('show');

    Route::get('/parametres', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/parametres/entreprise', [SettingsController::class, 'updateCompany'])->name('settings.company');
    Route::put('/parametres/sms', [SettingsController::class, 'updateSms'])->name('settings.sms');
    Route::put('/parametres/smtp', [SettingsController::class, 'updateSmtp'])->name('settings.smtp');
    Route::put('/parametres/automatisation', [SettingsController::class, 'updateAutomation'])->name('settings.automation');
    Route::put('/parametres/facturation', [SettingsController::class, 'updateBilling'])->name('settings.billing');
    // Generic reference-list CRUD (materiels, systemes, antivirus, prestations, statuts, modeles...)
    Route::post('/parametres/{type}', [SettingsController::class, 'storeReference'])->name('settings.reference.store');
    Route::put('/parametres/{type}/{id}', [SettingsController::class, 'updateReference'])->name('settings.reference.update');
    Route::delete('/parametres/{type}/{id}', [SettingsController::class, 'destroyReference'])->name('settings.reference.destroy');
});
