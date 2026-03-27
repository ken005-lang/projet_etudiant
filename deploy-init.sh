#!/usr/bin/env bash
# deploy-init.sh — Script de déploiement initial complet
# Usage : bash deploy-init.sh

set -e

APP_NAME="mon-app"
REGION="cdg"
ORG="personal"

echo "🚀 Déploiement initial de ${APP_NAME} sur Fly.io (région: ${REGION})"

# ════════════════════════════════════════════════════════════════════
# ÉTAPE 1 — MySQL
# ════════════════════════════════════════════════════════════════════
echo ""
echo "── ÉTAPE 1 : Déploiement MySQL ──"

DB_APP="${APP_NAME}-db"
DB_PASSWORD=$(openssl rand -hex 16)
DB_ROOT_PASSWORD=$(openssl rand -hex 20)

echo "Création de l'app MySQL : ${DB_APP}"
fly apps create "${DB_APP}" --org "${ORG}" 2>/dev/null || echo "App ${DB_APP} existe déjà"

echo "Création du volume persistant"
fly volumes create mysql_data --size 5 --region "${REGION}" --app "${DB_APP}" --yes 2>/dev/null || echo "Volume existe déjà"

echo "Configuration des secrets MySQL"
fly secrets set \
    MYSQL_ROOT_PASSWORD="${DB_ROOT_PASSWORD}" \
    MYSQL_USER="laravel" \
    MYSQL_PASSWORD="${DB_PASSWORD}" \
    MYSQL_DATABASE="laravel" \
    --app "${DB_APP}"

echo "Déploiement de MySQL"
fly deploy --config fly/mysql/fly.toml --app "${DB_APP}"

echo "✅ MySQL déployé : ${DB_APP}.internal"
echo "DB_PASSWORD (à conserver) : ${DB_PASSWORD}"

# ════════════════════════════════════════════════════════════════════
# ÉTAPE 2 — Redis
# ════════════════════════════════════════════════════════════════════
echo ""
echo "── ÉTAPE 2 : Déploiement Redis ──"

REDIS_APP="${APP_NAME}-redis"
REDIS_PASSWORD=$(openssl rand -hex 16)

echo "Création de l'app Redis : ${REDIS_APP}"
fly apps create "${REDIS_APP}" --org "${ORG}" 2>/dev/null || echo "App ${REDIS_APP} existe déjà"

echo "Création du volume persistant"
fly volumes create redis_data --size 2 --region "${REGION}" --app "${REDIS_APP}" --yes 2>/dev/null || echo "Volume existe déjà"

echo "Configuration des secrets Redis"
fly secrets set REDIS_PASSWORD="${REDIS_PASSWORD}" --app "${REDIS_APP}"

echo "Déploiement de Redis"
fly deploy --config fly/redis/fly.toml --app "${REDIS_APP}"

echo "✅ Redis déployé : ${REDIS_APP}.internal"
echo "REDIS_PASSWORD (à conserver) : ${REDIS_PASSWORD}"

# ════════════════════════════════════════════════════════════════════
# ÉTAPE 3 — Application Laravel
# ════════════════════════════════════════════════════════════════════
echo ""
echo "── ÉTAPE 3 : Déploiement Application Laravel ──"

echo "Génération de la clé Laravel"
APP_KEY=$(php artisan key:generate --show --no-interaction)
REVERB_APP_KEY=$(openssl rand -hex 16)
REVERB_APP_SECRET=$(openssl rand -hex 32)

echo "Création de l'app Laravel : ${APP_NAME}"
fly apps create "${APP_NAME}" --org "${ORG}" 2>/dev/null || echo "App ${APP_NAME} existe déjà"

echo "Configuration des secrets"
fly secrets set \
    APP_KEY="${APP_KEY}" \
    DB_USERNAME="laravel" \
    DB_PASSWORD="${DB_PASSWORD}" \
    REDIS_PASSWORD="${REDIS_PASSWORD}" \
    REVERB_APP_KEY="${REVERB_APP_KEY}" \
    REVERB_APP_SECRET="${REVERB_APP_SECRET}" \
    --app "${APP_NAME}"

echo "Premier déploiement"
fly deploy --app "${APP_NAME}"

echo ""
echo "════════════════════════════════════════════"
echo "✅ Déploiement terminé !"
echo "   URL : https://${APP_NAME}.fly.dev"
echo "   Commande : fly open"
echo "════════════════════════════════════════════"
