#!/bin/bash
set -e

PORT="${PORT:-10000}"

# Garantir que só mpm_prefork está ativo
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*

# Atualizar porta do Apache dinamicamente em TODOS os arquivos relevantes
sed -i "s/Listen [0-9]*/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:$PORT>/" /etc/apache2/sites-enabled/*.conf 2>/dev/null || true

# Testar config do Apache
apache2ctl configtest

# Rodar migrations
php /var/www/html/artisan migrate --force 2>/dev/null || true

# Rodar queue worker em background
php /var/www/html/artisan queue:work --tries=3 --timeout=60 &

# Iniciar supervisor (Apache + Reverb)
exec /usr/bin/supervisord -n
