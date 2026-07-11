# Sous-domaines par société

Chaque société SaaS est accessible sur `https://{slug}.managy.fr`. Aucun conteneur, routeur Traefik ou enregistrement DNS n'est créé par société : le wildcard couvre automatiquement tous les slugs présents en base.

## DNS OVH

Créer un enregistrement wildcard :

```text
Type: A
Sous-domaine: *
Cible: IP_PUBLIQUE_DU_VPS
```

Conserver également l'enregistrement du domaine racine `managy.fr` vers la même IP.

## Variables Laravel de production

```dotenv
APP_URL=https://managy.fr
SAAS_DOMAIN=managy.fr
SAAS_SCHEME=https
SAAS_RESERVED_SUBDOMAINS=www,admin,api,mail

SESSION_DOMAIN=.managy.fr
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

Après modification :

```bash
php artisan optimize:clear
php artisan config:cache
```

## Traefik

Un routeur couvre le domaine central et tous les tenants :

```yaml
labels:
  - traefik.enable=true
  - traefik.http.routers.managy.rule=Host(`managy.fr`) || HostRegexp(`^[a-z0-9-]+\\.managy\\.fr$`)
  - traefik.http.routers.managy.entrypoints=websecure
  - traefik.http.routers.managy.tls=true
  - traefik.http.routers.managy.tls.certresolver=ovh
  - traefik.http.routers.managy.tls.domains[0].main=managy.fr
  - traefik.http.routers.managy.tls.domains[0].sans=*.managy.fr
  - traefik.http.services.managy.loadbalancer.server.port=80
```

Le certificat wildcard Let's Encrypt exige un challenge DNS. Le resolver Traefik doit donc être configuré avec l'API OVH et `dnsChallenge.provider=ovh`.

## SSO

Les callbacks Microsoft et Google peuvent rester sur le domaine central :

```dotenv
AZURE_REDIRECT_URI=https://managy.fr/auth/sso/microsoft/callback
GOOGLE_REDIRECT_URI=https://managy.fr/auth/sso/google/callback
```

La session partagée via `.managy.fr` conserve le tenant pendant l'aller-retour OAuth, puis Laravel renvoie l'utilisateur vers son sous-domaine.

## Comportement applicatif

- `managy.fr/login/{slug}` reste compatible et redirige vers `{slug}.managy.fr/login`.
- Un slug inconnu renvoie une 404.
- Une société désactivée renvoie une 403.
- Un super-admin utilisant un sous-domaine est renvoyé vers `managy.fr/admin`.
- Un utilisateur authentifié sur le domaine central est renvoyé vers le sous-domaine de sa société.
- Les slugs réservés (`www`, `admin`, `api`, `mail` par défaut) ne sont pas attribués automatiquement.
