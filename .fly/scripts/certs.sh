#!/usr/bin/env bash
# .fly/scripts/certs.sh
# Exécuté au démarrage de chaque Machine

set -e

echo "🚀 Démarrage de l'application Laravel..."

# ── 1. Optimisation Laravel ───────────────────────────────────────
echo "📦 Optimisation de la configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# ── 2. Migrations automatiques ────────────────────────────────────
# IMPORTANT : uniquement sur le process 'web', pas sur 'worker'
if [ "$FLY_PROCESS_GROUP" = "web" ] || [ -z "$FLY_PROCESS_GROUP" ]; then
    echo "🗄️  Exécution des migrations..."
    php artisan migrate --force --isolated || true
fi

# ── 3. Lien de storage ────────────────────────────────────────────
echo "🔗 Création du lien storage..."
php artisan storage:link || true

# ── 4. Création des répertoires nécessaires ───────────────────────
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/storage/framework/{cache,sessions,views,testing}
mkdir -p /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

echo "✅ Application prête !"
