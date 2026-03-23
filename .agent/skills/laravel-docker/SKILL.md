---
name: laravel-docker
description: >
  Config Docker complète et optimisée pour Laravel (10–12) sur PHP 8.4 avec Nginx, MySQL,
  Redis et Laravel Reverb (WebSockets), pour les environnements dev (WSL2) et prod (VPS).
  Utiliser ce skill dès que l'utilisateur parle de Docker avec Laravel, de conteneurisation,
  de docker-compose, de lenteur Docker, de déploiement conteneurisé, ou demande comment
  faire tourner son app Laravel dans Docker — même s'il ne mentionne pas explicitement
  "Docker" mais parle de "conteneurs", "services", "stack", ou "déploiement automatisé".
---

# Laravel Docker — Config complète et optimisée

## Stack cible
- **PHP** : 8.4-fpm-alpine (multi-stage build)
- **Framework** : Laravel 10 / 11 / 12
- **Serveur** : Nginx 1.27-alpine
- **Base de données** : MySQL 8.4
- **Cache / Sessions / Queues** : Redis 7.4
- **WebSockets** : Laravel Reverb
- **Environnement dev** : Windows 11 + WSL2
- **Process manager** : Supervisor (prod)

---

## RÈGLE N°1 — Projet dans WSL2 natif (obligatoire)

> C'est la cause n°1 de lenteur Docker sur Windows. À vérifier avant tout.

```bash
pwd
# ✅ Correct  → /home/jean/projets/mon-site
# ❌ Lent     → /mnt/c/Users/Jean/projets/mon-site
```

Si le projet est dans `/mnt/c/`, le migrer :

```bash
cp -r /mnt/c/Users/Jean/projets/mon-site ~/projets/mon-site
cd ~/projets/mon-site
```

---

## Structure des fichiers

```
mon-site/
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   ├── php/
│   │   ├── Dockerfile
│   │   ├── php.ini
│   │   └── opcache.ini
│   └── supervisor/
│       └── supervisord.conf
├── docker-compose.yml           ← base commune (dev + prod)
├── docker-compose.override.yml  ← dev (actif par défaut)
└── docker-compose.prod.yml      ← prod
```

---

## Les 3 causes de lenteur Docker — et leurs solutions

| Cause | Solution |
|---|---|
| Projet dans `/mnt/c/` (I/O Windows lent) | Migrer dans `~/projets/` (WSL2 natif) |
| `vendor/` et `node_modules/` en bind mount | **Volumes nommés dédiés** (`vendor_cache`, `node_modules_cache`) |
| OPcache absent ou mal configuré | `opcache.ini` strict en prod, `validate_timestamps=1` en dev |

> Le gain le plus visible est le **volume nommé pour `vendor/`** : sans ça, chaque requête
> PHP autoloade des centaines de fichiers via le bind mount WSL2 → catastrophique.

---

## Fichiers de configuration

Pour chaque fichier, se référer aux sections ci-dessous ou aux fichiers de référence :

- **Dockerfile** → voir [references/dockerfile.md](references/dockerfile.md)
- **docker-compose** (base + override dev + prod) → voir [references/compose.md](references/compose.md)
- **Nginx** → voir [references/nginx.md](references/nginx.md)
- **Supervisor** → voir [references/supervisor.md](references/supervisor.md)
- **php.ini + opcache.ini** → voir ci-dessous (courts, inclus ici)

---

## `docker/php/php.ini`

```ini
memory_limit = 256M
max_execution_time = 60
upload_max_filesize = 50M
post_max_size = 50M
date.timezone = Africa/Abidjan

; Sessions Redis
session.save_handler = redis
session.save_path = "tcp://redis:6379"
```

## `docker/php/opcache.ini`

```ini
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0   ; prod : jamais revalider
opcache.save_comments = 1
opcache.enable_cli = 0
```

> En dev, `docker-compose.override.yml` surcharge `validate_timestamps=1` automatiquement.

---

## Commandes du quotidien

```bash
# ── DEV ─────────────────────────────────────────────
# Premier lancement
docker compose up -d --build

# Artisan / Composer / npm
docker compose exec app php artisan migrate
docker compose exec app composer install
docker compose exec app npm run dev       # HMR Vite

# Logs
docker compose logs -f app
docker compose logs -f reverb

# Rebuild après modif Dockerfile
docker compose up -d --build app

# ── PROD ─────────────────────────────────────────────
# Lancement
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build

# Déploiement (mise à jour)
git pull
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build app
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize
```

---

## `.env` minimal attendu

```env
APP_ENV=local
APP_KEY=           # générer avec : php artisan key:generate
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

BROADCAST_CONNECTION=reverb
REVERB_HOST=reverb
REVERB_PORT=8080
REVERB_SCHEME=http

# En prod → REVERB_SCHEME=https, REVERB_HOST=ws.monsite.com
```

---

## Checklist de démarrage

```
□ Projet dans ~/projets/ (WSL2 natif, pas /mnt/c/)
□ .env configuré (DB, Redis, Reverb)
□ docker compose up -d --build
□ docker compose exec app php artisan key:generate
□ docker compose exec app php artisan migrate
□ docker compose exec app php artisan optimize:clear  (dev)
□ Accès sur http://localhost
□ Redis Insight sur http://localhost:5540 (dev)
```
