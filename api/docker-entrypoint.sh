#!/bin/sh
set -e

# O codigo vem por bind mount, entao o vendor/ nao pode ser instalado no build.
if [ ! -f vendor/autoload.php ]; then
  echo "> composer install"
  composer install --no-interaction --prefer-dist --no-progress
fi

if [ ! -f .env ]; then
  echo "> .env a partir do .env.example"
  cp .env.example .env
fi

# Clone novo vem com APP_KEY vazio; sem chave o Laravel nao sobe.
if ! grep -q '^APP_KEY=base64:' .env; then
  echo "> key:generate"
  php artisan key:generate --force
fi

# O depends_on com healthcheck ja garante que o MySQL aceita conexao.
echo "> migrate"
php artisan migrate --force

echo "> serve em 0.0.0.0:8000"
# --no-reload: sem ele o artisan serve fica reiniciando sozinho a cada
# mudanca detectada no bind mount do Windows, e derruba o servidor em loop.
# Ele tambem e o que habilita o PHP_CLI_SERVER_WORKERS.
exec php artisan serve --host=0.0.0.0 --port=8000 --no-reload
