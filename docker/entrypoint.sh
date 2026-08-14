#!/bin/bash
set -e

export PORT="${PORT:-10000}"

# Garantir que só mpm_prefork está ativo
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*

# Rodar migrations
php /var/www/html/artisan migrate --force 2>/dev/null || true

# Rodar queue worker em background
php /var/www/html/artisan queue:work --tries=3 --timeout=60 &

# Iniciar supervisor (Apache + Reverb)
exec /usr/bin/supervisord -n
