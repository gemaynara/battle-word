#!/bin/bash
set -e

PORT="${PORT:-10000}"
export PORT

echo "==> PORT=$PORT"

# Gerar APP_KEY se não existir
if [ -z "$APP_KEY" ]; then
    echo "==> Generating APP_KEY..."
    php /var/www/html/artisan key:generate --force
fi

# Cache config
php /var/www/html/artisan config:cache || true
php /var/www/html/artisan route:cache || true
php /var/www/html/artisan view:cache || true

# Rodar migrations
php /var/www/html/artisan migrate --force || true

# Importar dicionário pt-BR (se tabela estiver vazia)
WORD_COUNT=$(php /var/www/html/artisan tinker --execute="echo \App\Models\DictionaryWord::count();" 2>/dev/null | tail -1)
if [ "$WORD_COUNT" = "0" ] || [ -z "$WORD_COUNT" ]; then
    echo "==> Importing pt-BR dictionary..."
    php /var/www/html/artisan dictionary:import database/data/lexico.txt --min-length=3 --max-length=12 || true
    echo "==> Dictionary import complete"
fi

# Rodar seeder de achievements
php /var/www/html/artisan db:seed --class=AchievementSeeder --force || true

echo "==> Laravel setup complete, starting on port $PORT"

# Rodar queue worker em background
php /var/www/html/artisan queue:work --tries=3 --timeout=60 &

# Iniciar supervisor (PHP server + Reverb)
exec /usr/bin/supervisord -n
