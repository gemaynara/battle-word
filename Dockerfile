# =========================================================
# Stage 1 — Build do React/Vite
# =========================================================
FROM node:22-alpine AS frontend

ARG VITE_REVERB_APP_KEY=word-battle-key
ARG VITE_REVERB_HOST=battle-word-production.up.railway.app
ARG VITE_REVERB_PORT=443
ARG VITE_REVERB_SCHEME=https
ARG VITE_APP_NAME=WordBattle

ENV VITE_REVERB_APP_KEY=${VITE_REVERB_APP_KEY}
ENV VITE_REVERB_HOST=${VITE_REVERB_HOST}
ENV VITE_REVERB_PORT=${VITE_REVERB_PORT}
ENV VITE_REVERB_SCHEME=${VITE_REVERB_SCHEME}
ENV VITE_APP_NAME=${VITE_APP_NAME}

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

ARG PORT=10000
ENV PORT=${PORT}

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

# Criar .env vazio (Railway injeta vars via ambiente)
RUN touch /var/www/html/.env

# Copiar build do React/Vite
COPY --from=frontend /app/public/build ./public/build

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

# Configurar Apache para escutar na PORT do Railway
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf
RUN echo 'Listen 10000' > /etc/apache2/ports.conf

# Configuração Apache (VirtualHost)
COPY docker/apache.conf \
    /etc/apache2/sites-available/000-default.conf

# Configuração Supervisor
COPY docker/supervisord.conf \
    /etc/supervisor/conf.d/supervisord.conf

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 10000

CMD ["/usr/local/bin/entrypoint.sh"]
