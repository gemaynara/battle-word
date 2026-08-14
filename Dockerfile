# =========================================================
# Stage 1 — Build do React/Vite
# =========================================================
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./

RUN npm ci

COPY resources ./resources
COPY vite.config.ts tsconfig.json ./

RUN npm run build


# =========================================================
# Stage 2 — Laravel + Apache + Reverb
# =========================================================
FROM php:8.3-apache

ENV PORT=${PORT:-10000}

# Dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    ca-certificates \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    supervisor \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        bcmath \
        intl \
        zip \
        opcache \
        gd \
        pcntl \
    && a2dismod mpm_event mpm_worker || true \
    && a2enmod \
        mpm_prefork \
        rewrite \
        headers \
        proxy \
        proxy_http \
        proxy_wstunnel \
    && rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar Laravel
COPY . .

# Instalar dependências PHP
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

# Copiar build do React/Vite
COPY --from=frontend /app/public/build ./public/build

# Configurar Apache
RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf

RUN sed -i \
    's/<VirtualHost \*:80>/<VirtualHost *:10000>/' \
    /etc/apache2/sites-available/000-default.conf

# Laravel public
RUN sed -i \
    's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#' \
    /etc/apache2/sites-available/000-default.conf

# Permissões Laravel
RUN mkdir -p \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache

# Configuração Apache
COPY docker/apache.conf \
    /etc/apache2/sites-available/000-default.conf

# Configuração Supervisor
COPY docker/supervisord.conf \
    /etc/supervisor/conf.d/supervisord.conf

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 10000

CMD ["/usr/local/bin/entrypoint.sh"]
