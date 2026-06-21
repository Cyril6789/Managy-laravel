<p align="center">
  <strong>Managy</strong> — Logiciel de gestion d'interventions pour techniciens informatiques
</p>

<p align="center">
  Laravel 13 · Livewire 3 · Tailwind CSS v4 · Alpine.js · Mode clair / sombre
</p>

---

## Sommaire

1. [Présentation](#1-présentation)
2. [À qui s'adresse Managy](#2-à-qui-sadresse-managy)
3. [Fonctionnalités](#3-fonctionnalités)
4. [Gestion des droits](#4-gestion-des-droits)
5. [Stack technique](#5-stack-technique)
6. [Installation](#6-installation)
7. [Configuration de la base de données](#7-configuration-de-la-base-de-données)
8. [Configuration de l'application](#8-configuration-de-lapplication)
9. [Tâches planifiées (cron)](#9-tâches-planifiées-cron)
10. [Pages publiques client](#10-pages-publiques-client)
11. [Architecture du code](#11-architecture-du-code)
12. [Tests](#12-tests)
13. [Déploiement & test rapide](#13-déploiement--test-rapide)
14. [Licence & origine](#14-licence--origine)

---

## 1. Présentation

**Managy** est une application web métier qui pilote le quotidien d'un atelier ou
d'une société de dépannage informatique : prise en charge du matériel client,
suivi des **interventions** de bout en bout (diagnostic, commandes de pièces,
sous-traitance, rapport, facturation), **communication** automatisée avec le
client (SMS / e-mail), **agenda**, **tâches**, **pack de maintenance** et
**enquêtes de satisfaction**.


> 🔒 Aucune donnée n'est partagée avec l'extérieur : l'instance tourne pour une
> seule entreprise, sur son propre hébergement et sa propre base de données.

---

## 2. À qui s'adresse Managy

- **Ateliers de réparation informatique** (dépôt de matériel, restitution signée).
- **Sociétés d'infogérance / dépannage** intervenant en atelier **et à domicile**.
- **Techniciens itinérants** : création d'intervention, rapport et **signature
  client sur tablette / smartphone** directement sur le lieu d'intervention.
- **Gérants** souhaitant suivre l'activité, les statistiques et la satisfaction.

L'interface est **responsive** (utilisable sur mobile) et propose un **mode clair
et un mode sombre**.

---

## 3. Fonctionnalités

### 3.1 Interventions (cœur de l'application)

- Cycle de vie complet : création → planification → diagnostic → commandes /
  sous-traitance → rapport → restitution signée → facturation.
- **Statuts configurables** (couleur, verrouillage, statut de clôture).
- **Planification** atelier ou domicile, avec créneau de rendez-vous.
- **Prise en charge multi-techniciens** ; un administrateur peut **affecter ou
  retirer** des techniciens.
- **Prestations** réalisées (catalogue + durées), **commandes fournisseurs** et
  **sous-traitance** avec suivi de réception / retour — le **statut bascule
  automatiquement** en « en attente » tant qu'une pièce ou un retour est en cours.
- **Rapport technique en direct** (Livewire) : enregistrement automatique au fil
  de la saisie, avec modèles réutilisables (diagnostics récurrents, messages
  type, matériels ajoutés).
- **Restitution & clôture** avec **signature tactile du client** ; une **copie
  signée est envoyée par e-mail** au client (avec le solde de son pack maintenance).
- **2 fiches A4 imprimables** : fiche de **dépôt** (matériel, n°, QR code de
  suivi) et **rapport final** (rapport, message client, prestations, signature).
- **Tchat unifié** : une seule conversation client ↔ atelier, visible dans
  l'intervention et sur la page de suivi publique.
- **Journal d'activité** par intervention.

### 3.2 Clients & contacts

- Clients **professionnels** (sociétés) et **particuliers**.
- **Contacts (salariés)** rattachés à une société, gérés depuis la fiche
  entreprise : ajout / modification / suppression.
- À la création d'une intervention sur une société, on choisit le **contact
  destinataire** des SMS / e-mails (facultatif) — l'intervention reste rattachée
  à la société. Un particulier ou un contact peut aussi être sélectionné
  directement.
- **Sélecteur client en direct** (Livewire) : recherche instantanée, **création
  et modification de la fiche client dans une fenêtre** sans quitter la page.
- **Pack maintenance** par client : crédit d'heures, **débit automatique** des
  heures saisies à la clôture, **alerte sous un seuil configurable** (visible dès
  la création d'une intervention et sur la fiche).
- **Historique des communications** (tous SMS / e-mails) sur la fiche client.
- Archivage des clients.

### 3.3 Communication

- **SMS** via un fournisseur configurable (`log` pour test, SMSMode, SMSFactor).
- **E-mails** transactionnels via SMTP configurable dans l'application.
- **Automatismes** : envois automatiques SMS / e-mail déclenchés par un événement
  (création, changement de statut, changement de RDV, réception de commande,
  retour de sous-traitance, restitution).
- **Automatismes planifiés autour du rendez-vous** : la veille, X minutes avant
  (« un technicien arrive »), ou après le RDV (ex. SMS de satisfaction 3 h après).
  Variables disponibles : `{reference}`, `{client}`, `{statut}`, `{lien}`,
  `{entreprise}`, `{date_rdv}`, `{heure_rdv}`.

### 3.4 Organisation

- **Agenda** mensuel regroupant rendez-vous et interventions planifiées.
- **Tâches** (à faire / en cours / terminées, échéances, suivi des heures).
- **Post-it** personnels sur le tableau de bord.
- **Notifications** internes (changements d'intervention, messages client…).
- **Recherche** globale (clients, interventions).

### 3.5 Pilotage

- **Tableau de bord** : KPIs, rendez-vous du jour (avec ville pour le domicile),
  interventions en cours, tâches.
- **Statistiques** : interventions par mois, **heures par technicien** (sur les
  interventions clôturées), chiffre d'affaires estimé.
- **Satisfaction** : enquêtes envoyées au client, note moyenne et répartition.
- **Journaux** d'activité (connexions, actions, historique des interventions).

### 3.6 Paramétrage

- Coordonnées de l'entreprise et **logo** (repris dans la barre latérale et les
  impressions).
- Configuration **SMS** et **SMTP**.
- **Listes métier** : types de matériel, systèmes d'exploitation, antivirus,
  prestations.
- **Statuts** d'intervention et **automatisation** des statuts (commande /
  sous-traitance) + **seuil d'alerte maintenance**.
- **Modèles** de rapports, de commentaires et de matériels ajoutés.

---

## 4. Gestion des droits

Managy distingue deux profils :

- **Administrateur (« gérant »)** : accès complet, contourne toutes les
  vérifications de droits.
- **Technicien** : accès **fin par permission**.

Les permissions sont **granulaires** et regroupées par domaine. Elles sont
définies dans `app/Support/Permissions.php` et attribuées par technicien depuis
**Techniciens → Modifier**. Sans le droit « voir toutes les interventions », un
technicien ne voit que les interventions qui lui sont **assignées**.

| Domaine          | Exemples de droits                                                                 |
|------------------|------------------------------------------------------------------------------------|
| Clients          | Voir / créer-modifier les clients                                                  |
| Interventions    | Voir, créer, modifier, voir toutes, déclôturer, facturation, affecter des techniciens |
| Agenda & tâches  | Voir / gérer le calendrier et les tâches                                           |
| Communication    | Envoyer des SMS / e-mails aux clients                                               |
| Pack maintenance | Voir les soldes, gérer les mouvements                                               |
| Suivi            | Statistiques, journaux, satisfaction                                               |
| Administration   | Gérer les techniciens et droits, les paramètres, les automatismes                  |

Techniquement, chaque permission est exposée comme un **Gate Laravel** ; les
administrateurs sont court-circuités via `Gate::before`.

---

## 5. Stack technique

| Composant        | Détail                                   |
|------------------|------------------------------------------|
| Framework        | Laravel 13                               |
| PHP              | **8.4+** (requis par Laravel 13 / Symfony) |
| Composants live  | Livewire 3                               |
| Front            | Tailwind CSS v4 (Vite), Alpine.js        |
| Base de données  | MySQL 8 / MariaDB (SQLite possible en dev) |
| PDF / QR         | endroid/qr-code (QR de suivi)            |
| Auth             | Sessions Laravel, droits par Gates       |

---

## 6. Installation

### Prérequis

- PHP **8.4+** avec les extensions usuelles (`pdo`, `mbstring`, `openssl`,
  `gd` ou `imagick` recommandé, `pdo_mysql` ou `pdo_sqlite`).
- **Composer 2**.
- **Node.js 20+** et **npm**.
- Un serveur MySQL/MariaDB (ou SQLite pour un test rapide).

### Étapes

```bash
# 1. Récupérer le projet et installer les dépendances
composer install
npm install

# 2. Environnement
cp .env.example .env
php artisan key:generate

# 3. Base de données (éditer le .env, voir section 7) puis migrer + données initiales
php artisan migrate --seed

# 4. Lien de stockage public (logos, signatures)
php artisan storage:link

# 5. Compiler les assets front
npm run build        # ou `npm run dev` en développement

# 6. Démarrer
php artisan serve
```

### Compte par défaut

Après `migrate --seed` :

- **Identifiant :** `admin`
- **Mot de passe :** `password`

> ⚠️ Changez ce mot de passe immédiatement (**Mon profil → Mot de passe**).
> Les données de démonstration (clients/interventions) ne sont insérées qu'en
> environnement `local`.

---

## 7. Configuration de la base de données

Managy part **d'une base vierge et normalisée** : aucune reprise de l'ancienne
base n'est nécessaire, `php artisan migrate` crée l'intégralité du schéma.

### MySQL / MariaDB (recommandé en production)

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=managy
DB_USERNAME=managy
DB_PASSWORD=motdepasse
```

Créez la base puis lancez les migrations :

```sql
CREATE DATABASE managy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan migrate --seed
```

### SQLite (test rapide, par défaut)

```dotenv
DB_CONNECTION=sqlite
```

```bash
touch database/database.sqlite
php artisan migrate --seed
```

### Mise à jour d'une instance existante

```bash
git pull
composer install
php artisan migrate        # applique uniquement les nouvelles migrations
npm run build
```

---

## 8. Configuration de l'application

Tout se règle dans **Paramètres** (réservé aux droits d'administration), sans
toucher au code :

- **Entreprise** : nom, coordonnées, SIRET, TVA et **logo** (utilisé dans la barre
  latérale et sur les impressions / e-mails).
- **SMS** : fournisseur (`log` = journalisation sans envoi réel, `SMSMode`,
  `SMSFactor`), expéditeur, clé API, signature.
- **E-mail (SMTP)** : hôte, port, utilisateur (facultatif), mot de passe,
  chiffrement (TLS/SSL), adresse et nom d'expéditeur. Tant qu'aucun hôte n'est
  renseigné, la configuration du fichier `.env` est utilisée.
- **Listes métier** : types de matériel, systèmes d'exploitation, antivirus,
  prestations (avec durée par défaut).
- **Statuts** : libellés, couleurs, et **automatisation** — statut appliqué quand
  une commande / sous-traitance est en cours, statut rétabli après réception, et
  **seuil d'alerte** du pack maintenance.
- **Modèles** : rapports types, commentaires types, matériels ajoutés types
  (proposés en un clic lors de la saisie d'une intervention).

---

## 9. Tâches planifiées (cron)

Certaines automatisations s'exécutent en **arrière-plan** :

- **Automatismes planifiés autour du rendez-vous** (rappels avant, satisfaction
  après…) via la commande `managy:run-automatismes`.

Le planificateur Laravel exécute cette commande **toutes les 5 minutes**
(voir `routes/console.php`). Il suffit d'ajouter **une seule entrée cron** sur le
serveur, qui déclenche le planificateur chaque minute :

```cron
* * * * * cd /chemin/vers/managy && php artisan schedule:run >> /dev/null 2>&1
```

En développement, on peut lancer le planificateur en continu :

```bash
php artisan schedule:work
```

Ou tester la commande directement :

```bash
php artisan managy:run-automatismes
```

> Le système enregistre chaque envoi planifié (table `automatisme_runs`) pour ne
> **jamais envoyer deux fois** le même rappel à la même intervention.

---

## 10. Pages publiques client

Deux pages sont accessibles **sans authentification**, via un **jeton non
devinable** :

- **Suivi d'intervention en direct** (`/suivi/{token}`) : le client suit
  l'avancement (étapes, matériel, message de l'équipe) et **échange par tchat**
  avec l'atelier (la conversation est la même que celle de l'intervention).
- **Enquête de satisfaction** (`/satisfaction/{token}`) : note + commentaire.

Le lien de suivi est aussi encodé dans le **QR code** de la fiche de dépôt.

---

## 11. Architecture du code

```
app/
  Console/Commands/         # Commandes artisan (managy:run-automatismes…)
  Http/Controllers/         # Contrôleurs (interventions, clients, agenda, paramètres…)
    Intervention/           # Sous-ressources d'une intervention
    Public/                 # Pages publiques (suivi client, satisfaction)
  Livewire/                 # Composants temps réel : ClientPicker, ContactPicker,
                            # ContactManager, InterventionPanel, InterventionReport, ClientChat
  Models/                   # Modèles Eloquent du domaine
  Services/                 # Notifier, SmsSender (multi-fournisseur), AutomatismeRunner
  Support/                  # Permissions, InterventionStatus, Qr
database/
  migrations/               # Schéma normalisé (mono-entreprise)
  seeders/                  # Données de référence + jeu de démo (local)
resources/views/
  components/               # Composants Blade (UI) + fiches imprimables
  layouts/                  # app (privé), public, auth
  livewire/                 # Vues des composants Livewire
  partials/                 # Barre latérale, en-tête, notifications, post-it
routes/
  web.php                   # Routes web
  console.php               # Planificateur (cron applicatif)
```

Quelques principes :

- **Mono-entreprise** : plus de `compte_principal`, schéma normalisé.
- **Droits** centralisés (`Permissions`) et appliqués via des Gates.
- **Communication** isolée dans des services (`SmsSender`, `AutomatismeRunner`,
  `Notifier`) afin de rester indépendante du fournisseur.

---

## 12. Tests

Une suite de tests couvre le rendu de chaque écran, les composants Livewire, les
flux métier (maintenance, signature, contacts, automatismes planifiés) :

```bash
php artisan test
```

Formatage du code (Laravel Pint) :

```bash
./vendor/bin/pint
```

---

## 13. Déploiement & test rapide

### GitHub Codespaces (test en quelques minutes)

Le dépôt contient un **devcontainer** (PHP 8.4 + Node). À la création d'un
Codespace, dépendances, base SQLite, données de démo et assets sont installés
automatiquement et le serveur démarre seul. Il reste à rendre le **port 8000
public** (onglet *Ports*) puis à ouvrir l'URL. Connexion : `admin / password`.

### Production (serveur)

1. Servir le dossier `public/` (Nginx/Apache), document root sur `public/`.
2. `composer install --no-dev --optimize-autoloader` puis `npm ci && npm run build`.
3. Configurer le `.env` (base de données, `APP_ENV=production`, `APP_DEBUG=false`,
   `APP_URL`).
4. `php artisan migrate --force` puis `php artisan storage:link`.
5. Ajouter l'entrée **cron** du planificateur (voir section 9).
6. (Recommandé) `php artisan config:cache route:cache view:cache`.

---

## 14. Licence & origine

