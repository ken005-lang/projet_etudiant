# Build Stage: Compile Assets
FROM node:20-alpine AS build-assets
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Production Stage: PHP & Nginx
FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apk update && apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    sqlite-dev \
    icu-dev \
    oniguruma-dev \
    libzip-dev \
    linux-headers

# Install PHP extensions
RUN docker-php-ext-install \
    bcmath \
    gd \
    intl \
    opcache \
    pcntl \
    posix \
    pdo_sqlite \
    sockets \
    mbstring \
    exif \
    zip

# Copy project files
COPY --chown=www-data:www-data . .
COPY --from=build-assets /app/public/build ./public/build

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# Setup storage & database
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache database /var/log/supervisor
RUN touch database/database.sqlite
RUN chown -R www-data:www-data storage bootstrap/cache database /var/log/supervisor

# Copy configurations
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# Fix line endings for scripts/configs (common issue on Windows hosts)
RUN apk add --no-cache dos2unix && \
    dos2unix /etc/supervisord.conf /etc/nginx/http.d/default.conf

# Expose ports
EXPOSE 80 8080

# Start Supervisor
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
