<?php

namespace App\Support;

/**
 * Catalogue of granular permissions (replaces the legacy rights_staff "modul|num" system).
 * Admins ("gérant") bypass all of these — see User::hasPermission().
 */
final class Permissions
{
    // Customers
    public const CLIENTS_VIEW = 'clients.view';

    public const CLIENTS_MANAGE = 'clients.manage';

    public const CLIENTS_REMISES = 'clients.remises';     // free travel + per-client discounts

    // Interventions
    public const INTERVENTIONS_VIEW = 'interventions.view';

    public const INTERVENTIONS_CREATE = 'interventions.create';

    public const INTERVENTIONS_MANAGE = 'interventions.manage';

    public const INTERVENTIONS_VIEW_ALL = 'interventions.view_all';        // incl. archived / others'

    public const INTERVENTIONS_DECLOTURE = 'interventions.decloture';

    public const INTERVENTIONS_FACTURATION = 'interventions.facturation';

    public const INTERVENTIONS_ASSIGN = 'interventions.assign';

    public const INTERVENTIONS_RISTOURNE = 'interventions.ristourne';     // grant a discount at restitution

    // Calendar / tasks
    public const CALENDAR_VIEW = 'calendar.view';

    public const CALENDAR_MANAGE = 'calendar.manage';

    public const TASKS_VIEW = 'tasks.view';

    public const TASKS_MANAGE = 'tasks.manage';

    // Communication
    public const MESSAGES_SEND = 'messages.send';

    // Maintenance pack
    public const MAINTENANCE_VIEW = 'maintenance.view';

    public const MAINTENANCE_MANAGE = 'maintenance.manage';

    // Stats / logs
    public const STATS_VIEW = 'stats.view';

    public const LOGS_VIEW = 'logs.view';

    public const SATISFACTION_VIEW = 'satisfaction.view';

    // Administration
    public const STAFF_MANAGE = 'staff.manage';

    public const SETTINGS_MANAGE = 'settings.manage';

    public const AUTOMATISMES_MANAGE = 'automatismes.manage';

    /**
     * Grouped catalogue for the staff-rights UI and seeding.
     *
     * @return array<string, array<string, string>>
     */
    public static function catalog(): array
    {
        return [
            'Clients' => [
                self::CLIENTS_VIEW => 'Voir les clients',
                self::CLIENTS_MANAGE => 'Créer / modifier les clients',
                self::CLIENTS_REMISES => 'Gérer la gratuité de déplacement et les remises client',
            ],
            'Interventions' => [
                self::INTERVENTIONS_VIEW => 'Voir les interventions',
                self::INTERVENTIONS_CREATE => 'Créer une intervention',
                self::INTERVENTIONS_MANAGE => 'Modifier les interventions',
                self::INTERVENTIONS_VIEW_ALL => 'Voir toutes les interventions (archivées incluses)',
                self::INTERVENTIONS_DECLOTURE => 'Déclôturer une intervention',
                self::INTERVENTIONS_FACTURATION => 'Gérer la facturation',
                self::INTERVENTIONS_ASSIGN => 'Affecter des techniciens aux interventions',
                self::INTERVENTIONS_RISTOURNE => 'Accorder une ristourne sur une intervention',
            ],
            'Agenda & tâches' => [
                self::CALENDAR_VIEW => 'Voir le calendrier',
                self::CALENDAR_MANAGE => 'Gérer les rendez-vous',
                self::TASKS_VIEW => 'Voir les tâches',
                self::TASKS_MANAGE => 'Gérer les tâches',
            ],
            'Communication' => [
                self::MESSAGES_SEND => 'Envoyer des SMS / e-mails aux clients',
            ],
            'Pack maintenance' => [
                self::MAINTENANCE_VIEW => 'Voir les soldes de maintenance',
                self::MAINTENANCE_MANAGE => 'Gérer les mouvements de maintenance',
            ],
            'Suivi' => [
                self::STATS_VIEW => 'Voir les statistiques',
                self::LOGS_VIEW => 'Voir les journaux',
                self::SATISFACTION_VIEW => 'Voir les enquêtes de satisfaction',
            ],
            'Administration' => [
                self::STAFF_MANAGE => 'Gérer les techniciens et leurs droits',
                self::SETTINGS_MANAGE => 'Gérer les paramètres et listes',
                self::AUTOMATISMES_MANAGE => 'Gérer les automatismes',
            ],
        ];
    }

    /** @return list<string> */
    public static function all(): array
    {
        $keys = [];
        foreach (self::catalog() as $group) {
            $keys = array_merge($keys, array_keys($group));
        }

        return $keys;
    }
}
