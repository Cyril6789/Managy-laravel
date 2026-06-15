# Managy — Gestion d'interventions informatiques

Application métier pour techniciens informatiques : gestion des **interventions**,
des **clients**, du **matériel**, de l'**agenda**, des **tâches**, du **pack
maintenance** et de la **satisfaction client**, avec suivi en direct côté client.

Réécriture complète en **Laravel 13** (PHP 8.3+) d'une ancienne application PHP,
avec interface **Tailwind CSS v4 + Alpine.js** et **mode clair / sombre**.

> ℹ️ Cette version est **mono-entreprise** : chaque société télécharge et installe
> sa propre instance. Toute la logique SaaS de l'application d'origine (multi-comptes
> `compte_principal`, inscription, paiement/Paypal, landing page, super-administration,
> activation de modules payants) a été **supprimée**. Toutes les fonctionnalités
> métier — y compris celles qui étaient des modules optionnels — sont désormais
> **natives et activées par défaut**.

---

## Stack technique

| Composant       | Version / outil                          |
|-----------------|------------------------------------------|
| Framework       | Laravel 13                               |
| PHP             | 8.3+                                      |
| Front           | Tailwind CSS v4 (Vite), Alpine.js        |
| Base de données | MySQL 8 / MariaDB (SQLite en dev)        |
| Auth            | Sessions Laravel, droits par Gates       |

---

## Installation

```bash
# 1. Dépendances
composer install
npm install

# 2. Environnement
cp .env.example .env
php artisan key:generate

# 3. Base de données — éditez le .env puis :
php artisan migrate --seed

# 4. Assets front
npm run build      # ou `npm run dev` en développement

# 5. Lancer
php artisan serve
```

### Configuration de la base de données

Par défaut le projet utilise **SQLite** (idéal pour tester). Pour une mise en
production, configurez MySQL dans le `.env` :

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=managy
DB_USERNAME=managy
DB_PASSWORD=secret
```

Le schéma part **d'une base vierge et normalisée** : aucune reprise de l'ancienne
base n'est nécessaire. `php artisan migrate` crée toutes les tables.

### Compte par défaut

Après `migrate --seed`, un administrateur est créé :

- **Identifiant :** `admin`
- **Mot de passe :** `password`

> Pensez à changer ce mot de passe immédiatement (Mon profil → Mot de passe).
> Les données de démonstration (clients/interventions) ne sont insérées qu'en
> environnement `local`.

---

## Fonctionnalités

### Métier
- **Interventions** : création, planification (RDV atelier/domicile), statuts
  configurables, prise en charge multi-techniciens, prestations, commandes
  fournisseurs, sous-traitance, tchat interne, journal d'activité, restitution/
  clôture, déclôture, suivi de facturation.
- **Clients** : professionnels & particuliers, contacts rattachés à une société,
  archivage, historique des interventions.
- **Suivi client en direct** : chaque intervention dispose d'un **lien sécurisé**
  (jeton non devinable) permettant au client de suivre l'avancement et la
  répartition de son matériel, sans compte ni connexion.
- **Pack maintenance** : solde d'heures par client, crédits / consommations.
- **Agenda** : vue mensuelle regroupant rendez-vous et interventions planifiées.
- **Tâches** : à faire / en cours / terminées, échéances, suivi des heures.
- **Satisfaction** : enquête envoyée au client (note + commentaire) et tableau de
  bord des retours.

### Communication
- **SMS** aux clients via un fournisseur configurable (`log` pour test, SMSMode,
  SMSFactor).
- **E-mails** transactionnels (notifications, mot de passe oublié, messages client).
- **Automatismes** : envoi automatique de SMS / e-mail sur événement (création,
  changement de statut, changement de RDV, réception de commande, retour de
  sous-traitance, restitution).

### Administration
- **Techniciens** et **droits granulaires** (les administrateurs/« gérants »
  disposent de tous les droits).
- **Paramètres** : coordonnées de l'entreprise, configuration SMS, listes métier
  (types de matériel, systèmes d'exploitation, antivirus, prestations), statuts
  d'intervention, modèles de rapports/commentaires.
- **Journaux** d'activité (connexions, actions, historique des interventions).
- **Statistiques** : interventions par mois, heures par technicien, CA estimé.

---

## Tests

```bash
php artisan test
```

Une suite de **tests de fumée** vérifie que chaque écran de l'application répond
correctement (`tests/Feature/SmokeTest.php`).

---

## Architecture

```
app/
  Http/Controllers/         # Contrôleurs (interventions, clients, agenda, …)
    Intervention/           # Sous-ressources d'une intervention
    Public/                 # Pages publiques (suivi client, satisfaction)
  Models/                   # Modèles Eloquent du domaine
  Services/                 # Notifier, SmsSender, AutomatismeRunner
  Support/Permissions.php   # Catalogue des droits
database/
  migrations/               # Schéma normalisé (single-tenant)
  seeders/                  # Données de référence + démo
resources/views/
  components/               # Composants Blade (UI)
  layouts/                  # app (privé), public, auth
  partials/                 # Sidebar, header, flash, post-it
```

Le code PHP d'origine est conservé pour référence dans l'historique Git
(branche `master`) ; il n'est pas inclus dans cette branche.
