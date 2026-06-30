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
