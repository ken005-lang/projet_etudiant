#!/usr/bin/env bash
# scripts/00-laravel-deploy.sh
# Script de démarrage exécuté automatiquement par Supervisor

set -e

echo "══════════════════════════════════════════"
echo "🚀 Démarrage de l'application Laravel..."
echo "══════════════════════════════════════════"

cd /var/www/html

# ── 1. Installer les dépendances Composer ─────────────────────────
echo "📦 Installation des dépendances Composer..."
composer install --no-dev --optimize-autoloader --no-interaction

# ── 2. Forcer HTTPS pour les assets ───────────────────────────────
echo "🔒 Configuration HTTPS..."
php artisan config:set --no-interaction app.url "$APP_URL" || true

# ── 3. Générer la clé si elle n'existe pas ────────────────────────
if [ -z "$APP_KEY" ]; then
    echo "🔑 Génération de la clé applicative..."
    php artisan key:generate --force
fi

# ── 4. Optimisation Laravel ───────────────────────────────────────
echo "⚡ Optimisation de la configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# ── 5. Migrations ─────────────────────────────────────────────────
echo "🗄️  Exécution des migrations..."
php artisan migrate --force

# ── 6. Lien de storage ────────────────────────────────────────────
echo "🔗 Création du lien storage..."
php artisan storage:link || true

# ── 7. Permissions storage
echo "🔐 Configuration des permissions..."
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/app/public
mkdir -p storage/logs
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "✅ Application prête !"
