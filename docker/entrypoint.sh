#!/bin/bash
set -e

PORT="${PORT:-10000}"

echo "==> PORT=$PORT"

# Garantir que só mpm_prefork está ativo
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*

# Configurar Apache na porta correta (Railway injeta PORT)
echo "Listen $PORT" > /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf

echo "==> Apache will listen on port $PORT"

# Gerar APP_KEY se não existir
if [ -z "$APP_KEY" ]; then
    echo "==> Generating APP_KEY..."
    php /var/www/html/artisan key:generate --force
fi

# Cache config
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

# Rodar migrations
php /var/www/html/artisan migrate --force || true

echo "==> Laravel setup complete"

# Rodar queue worker em background
php /var/www/html/artisan queue:work --tries=3 --timeout=60 &

# Iniciar supervisor (Apache + Reverb)
exec /usr/bin/supervisord -n
