<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every business table carries a society_id so the global scope can isolate
     * tenants. Pure pivots (intervention_user, company_contact, user_permissions)
     * are scoped through their parents and left untouched.
     */
    private array $tables = [
        'clients',
        'materiels',
        'systeme_exploitations',
        'antivirus',
        'statuts',
        'prestations',
        'rapport_types',
        'commentaire_types',
        'materiel_ajoute_types',
        'interventions',
        'intervention_prestations',
        'commandes',
        'sous_traitances',
        'intervention_pieces',
        'intervention_messages',
        'intervention_logs',
        'intervention_photos',
        'maintenance_movements',
        'events',
        'tasks',
        'sticky_notes',
        'satisfactions',
        'automatismes',
        'automatisme_runs',
        'client_messages',
        'activity_logs',
        'app_notifications',
        'public_messages',
        'message_types',
        'technician_absences',
    ];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            if (! Schema::hasTable($name) || Schema::hasColumn($name, 'society_id')) {
                continue;
            }

            Schema::table($name, function (Blueprint $table) {
                $table->foreignId('society_id')->nullable()->after('id')
                    ->constrained('societies')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $name) {
            if (! Schema::hasTable($name) || ! Schema::hasColumn($name, 'society_id')) {
                continue;
            }

            Schema::table($name, function (Blueprint $table) {
                $table->dropConstrainedForeignId('society_id');
            });
        }
    }
};
