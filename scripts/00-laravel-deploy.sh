#!/usr/bin/env bash
# scripts/00-laravel-deploy.sh
# Script de démarrage exécuté automatiquement par Supervisor

set -e

echo "══════════════════════════════════════════"
echo "🚀 Démarrage de l'application Laravel..."
echo "══════════════════════════════════════════"

cd /var/www/html

# ── 0. Préparer storage/cache AVANT Artisan ───────────────────────
echo "🔐 Préparation des dossiers storage/cache..."
mkdir -p storage/framework/{cache,sessions,views} storage/app/public storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# ── 1. Installer les dépendances Composer ─────────────────────────
echo "📦 Installation des dépendances Composer..."
composer install --no-dev --optimize-autoloader --no-interaction

# ── 2. Vérifier la clé applicative ────────────────────────────────
if [ -z "$APP_KEY" ]; then
    echo "❌ APP_KEY manquant. Définis APP_KEY dans les variables Render."
    exit 1
fi

# ── 3. Optimisation Laravel ───────────────────────────────────────
echo "⚡ Optimisation de la configuration..."

# Temporairement forcer debug pour voir le vrai 500
export APP_DEBUG=true

# Force session/cache en database si le dashboard Render a un mauvais override
# La table 'sessions' existe grâce à la migration create_sessions_table
if [ -z "$SESSION_DRIVER" ] || [ "$SESSION_DRIVER" = "file" ]; then
    export SESSION_DRIVER=database
    echo "ℹ️  SESSION_DRIVER forcé à: database"
fi
if [ -z "$CACHE_STORE" ] || [ "$CACHE_STORE" = "file" ]; then
    export CACHE_STORE=database
    echo "ℹ️  CACHE_STORE forcé à: database"
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache


# ── 4. Migrations (activées par défaut) ───────────────────────────
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "🗄️  Exécution des migrations..."
    php artisan migrate --force
else
    echo "⏭️  Migrations ignorées (RUN_MIGRATIONS=false)"
fi

# ── 5. Lien de storage ────────────────────────────────────────────
echo "🔗 Création du lien storage..."
php artisan storage:link || true

echo "✅ Application prête !"
