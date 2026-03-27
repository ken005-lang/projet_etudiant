# Dockerfile — Optimisé pour Render.com avec PHP 8.4
# syntax = docker/dockerfile:experimental

# ── Build Arguments ────────────────────────────────────────────────
ARG NODE_VERSION=22

# ════════════════════════════════════════════════════════════════════
# STAGE 1 — Build des assets JS/CSS avec Node.js
# ════════════════════════════════════════════════════════════════════
FROM node:${NODE_VERSION}-alpine AS assets

WORKDIR /app

COPY package*.json ./
RUN npm ci --no-audit --no-fund

COPY resources/ ./resources/
COPY vite.config.js ./
COPY postcss.config.js ./
COPY tailwind.config.js ./
COPY .env.example .env

RUN npm run build

# ════════════════════════════════════════════════════════════════════
# STAGE 2 — Application PHP 8.4 + Nginx + Supervisor
# ════════════════════════════════════════════════════════════════════
FROM php:8.4-fpm-bookworm

# ── Installation des dépendances système ──────────────────────────
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        unzip \
        nginx \
        supervisor \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libicu-dev \
        libonig-dev \
        libpq-dev \
        default-libmysqlclient-dev \
        $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

# ── Configuration et installation des extensions PHP ───────────────
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pdo_mysql \
        mbstring \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
        sockets \
        pcntl

# ── Extension Redis ───────────────────────────────────────────────
RUN pecl install redis && docker-php-ext-enable redis

# ── Composer ──────────────────────────────────────────────────────
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# ── Copy du projet ────────────────────────────────────────────────
WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev

# ── Copy des assets buildés depuis le stage Node ──────────────────
COPY --from=assets /app/public/build ./public/build

# ── Configuration Nginx ───────────────────────────────────────────
COPY conf/nginx/nginx-site.conf /etc/nginx/sites-available/default

# ── Configuration Supervisor ──────────────────────────────────────
COPY conf/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# ── Script de démarrage ───────────────────────────────────────────
COPY scripts/00-laravel-deploy.sh /var/www/html/scripts/00-laravel-deploy.sh
RUN chmod +x /var/www/html/scripts/00-laravel-deploy.sh

# ── Permissions ───────────────────────────────────────────────────
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && mkdir -p /var/log/supervisor \
    && chown -R www-data:www-data /var/log/supervisor

# ── Variables d'environnement Laravel ─────────────────────────────
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr
ENV PHP_ERRORS_STDERR=1

EXPOSE 10000

# ── Point d'entrée ────────────────────────────────────────────────
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
