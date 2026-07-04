# Éditions & branches — `main` (autonome) vs `saas` (multi-tenant)

Le projet existe en **deux éditions** qui partagent le même code métier :

| Édition | Branche | Pour qui | Particularités |
|---|---|---|---|
| **Standalone** | `main` | Installation directe chez **un** client | Une seule entreprise, pas de `society_id`, pas d'espace super-admin |
| **SaaS** | `saas` | Plateforme **multi-entreprises** | Inscription de sociétés, isolation par `society_id`, espace `/admin`, landing publique |

> `saas` = `main` **+** la couche multi-tenant. Tout ce qui est commun vit sur `main`.

---

## 1. Sens des merges (la règle d'or)

```
   nouvelles fonctionnalités / corrections communes
                     │
                     ▼
                  ┌──────┐      git merge main      ┌──────┐
                  │ main │ ───────────────────────▶ │ saas │
                  └──────┘   (forward, toujours)    └──────┘
                     ▲                                  │
                     └──────────── JAMAIS ──────────────┘
```

- **Fonction commune ou bug commun** → on développe sur **`main`**, puis on reporte sur `saas` avec `git merge main` (ou `git cherry-pick`). Le déploiement touche alors **les deux** environnements.
- **Fonction liée au multi-tenant / super-admin** → **`saas` uniquement**.
- **On ne merge JAMAIS `saas` → `main`** : cela ferait fuiter le code multi-tenant dans l'édition autonome.

### Workflow concret

```bash
# Une feature/bugfix commune
git checkout main
git checkout -b feature/ma-feature
# ... code + commit ...
# PR vers main, merge.

# Propager vers le SaaS :
git checkout saas
git merge main          # report propre, sans conflit la plupart du temps
git push origin saas
```

---

## 2. « Comment être sûr de ne pas merger les deux ? »

Deux garde-fous, l'un côté Git, l'autre côté runtime :

1. **CI bloquante** — `.github/workflows/edition-guard.yml` échoue toute Pull Request vers `main` qui contient le marqueur SaaS (`config/saas.php → edition 'saas'`). 
   👉 Activez ce check comme **required** dans *Settings → Branches → Branch protection rules* de `main`. À partir de là, une fusion `saas → main` est mécaniquement impossible.

2. La détection est **par contenu** (pas par nom de branche) : même une branche dérivée de `saas` portant un autre nom sera bloquée si elle embarque le code SaaS.

---

## 3. « Comment être sûr que chaque environnement est sur la bonne branche ? »

Un **garde-fou d'édition au runtime** (`App\Http\Middleware\EnsureCorrectEdition`) :

- La branche **fige** son édition dans `config/saas.php` → `'edition'` (`'saas'` ici, `'standalone'` sur `main`).
- L'**environnement** déclare ce qu'il attend via `APP_EDITION` dans son `.env`.
- Si les deux ne correspondent pas, **l'application refuse de servir toute requête HTTP** (503) avec un message explicite.

Conséquences :

| Serveur | `.env` | Branche déployée par erreur | Résultat |
|---|---|---|---|
| Plateforme SaaS | `APP_EDITION=saas` | `main` (standalone) | ❌ 503 — bloqué |
| Client | `APP_EDITION=standalone` | `saas` (multi-tenant) | ❌ 503 — bloqué |
| Correct | `APP_EDITION` = édition de la branche | la bonne | ✅ sert normalement |

> Les commandes console (`artisan migrate`, `key:generate`, …) ne sont **pas** bloquées : un serveur mal déployé reste réparable en CLI.

### Mise en place côté `main`

L'édition autonome doit, symétriquement, déclarer la sienne. Sur `main` :

- `config/saas.php` (ou un petit `config/edition.php`) doit exposer `'edition' => 'standalone'`.
- Le `.env` des clients contient `APP_EDITION=standalone`.
- Le même middleware `EnsureCorrectEdition` doit être présent (il fait partie du socle commun).

> Sur la plateforme SaaS, `.env` contient `APP_EDITION=saas` (voir `.env.example`).

---

## 4. Récapitulatif déploiement

| | Plateforme SaaS | Installation client |
|---|---|---|
| Branche | `saas` | `main` |
| `.env` → `APP_EDITION` | `saas` | `standalone` |
| `SAAS_EMAIL_VERIFICATION` | `true` une fois le SMTP prêt | sans objet |
| Pipeline | déploie `saas` | déploie `main` |

---

## 5. SSO & groupes de permissions (par société)

Chaque gérant peut activer une connexion SSO et regrouper les droits, depuis
l'espace d'administration de sa société. Trois briques, indépendantes mais
conçues pour fonctionner ensemble.

### 5.1 Connexion SSO — *Paramètres → Connexion SSO*

- Une configuration **par société et par fournisseur** (`Microsoft Entra ID` ou
  `Google Workspace`), stockée dans `sso_connections` (le secret client est
  **chiffré** en base).
- Le gérant déclare l'application côté annuaire avec l'URL de redirection
  affichée (`/auth/sso/{provider}/callback`), saisit `client_id` / `client_secret`
  (+ `tenant_id` pour Entra), la liste des **domaines e-mail autorisés**, puis
  active la connexion.
- Sur la page de login, l'utilisateur saisit son e-mail pro et choisit son
  fournisseur : la société est **résolue depuis le domaine de l'e-mail**, portée
  en session, puis le callback provisionne le compte (si `auto_provision_users`)
  et connecte l'utilisateur. Techniquement : **Laravel Socialite** (+
  `socialiteproviders/microsoft-azure`).

### 5.2 Groupes de permissions — *Groupes de permissions*

- Couche **facultative** au-dessus des permissions attribuées directement : un
  groupe (ex. `Tech_Niv1`) porte une liste de permissions (`App\Support\Permissions`)
  et des utilisateurs. `User::hasPermission()` additionne droits directs **et**
  droits hérités des groupes.

### 5.3 Provisioning automatique via SSO

- Chaque groupe peut être associé à un ou plusieurs **groupes de sécurité** de
  l'annuaire (par **nom** — ex. `sg_managy_tech_niv1` — ou par **GUID**), table
  `sso_group_mappings`.
- À la connexion SSO, les groupes de sécurité de l'utilisateur sont lus
  (Microsoft Graph `me/transitiveMemberOf`) et `SsoGroupSynchronizer` le place
  automatiquement dans les groupes Managy correspondants. Seules les
  appartenances `source = "sso"` sont recalculées ; les affectations manuelles
  sont préservées.

> Exemple : l'AD ajoute un user à `sg_managy_tech_niv1` → il se connecte en SSO →
> il rejoint automatiquement `Tech_Niv1` et hérite de ses permissions.
>
> Google Workspace : la connexion et le provisioning fonctionnent, mais
> l'appartenance aux groupes n'est pas exposée dans le jeton OAuth — la synchro
> automatique des groupes reste donc propre à Microsoft Entra pour l'instant.
