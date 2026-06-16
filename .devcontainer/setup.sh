#!/usr/bin/env bash
# One-time provisioning for a GitHub Codespace: installs dependencies, prepares
# the SQLite database with demo data, and builds front-end assets. The server
# itself is started by the devcontainer "postStartCommand".
set -euo pipefail

cd "$(dirname "$0")/.."

# Environment file + application key
[ -f .env ] || cp .env.example .env

# PHP dependencies
composer install --no-interaction --prefer-dist --no-progress

php artisan key:generate --force

# SQLite database (zero external service needed for a quick test)
touch database/database.sqlite
php artisan migrate --seed --force

# Public storage symlink (for uploaded logos, etc.)
php artisan storage:link || true

# Front-end assets
npm install
npm run build

echo "✅ Managy prêt — l'application va démarrer sur le port 8000 (admin / password)."
