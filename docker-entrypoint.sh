#!/bin/bash
set -e

# ── 1. Ajusta Apache para a porta que o Render define ($PORT)
PORT="${PORT:-10000}"
sed -i "s|Listen 80|Listen ${PORT}|g" /etc/apache2/ports.conf
sed -i "s|<VirtualHost \*:80>|<VirtualHost *:${PORT}>|g" \
       /etc/apache2/sites-available/000-default.conf

# ── 2. Gera o config.php a partir das variáveis de ambiente
cat > /var/www/html/config.php << PHPEOF
<?php
declare(strict_types=1);

define('DB_HOST',      '${DB_HOST}');
define('DB_PORT',      '${DB_PORT:-5432}');
define('DB_NAME',      '${DB_NAME:-postgres}');
define('DB_USER',      '${DB_USER:-postgres}');
define('DB_PASS',      '${DB_PASS}');
define('DB_SSL',       '${DB_SSL:-require}');

define('SUPABASE_URL', '${SUPABASE_URL}');
define('SUPABASE_KEY', '${SUPABASE_KEY}');

define('APP_SECRET',   '${APP_SECRET}');
define('APP_URL',      '${APP_URL}');
define('APP_ENV',      'production');
define('APP_DEBUG',    false);

ini_set('display_errors', '0');
ini_set('log_errors',     '1');
error_reporting(E_ALL);
PHPEOF

chown www-data:www-data /var/www/html/config.php
chmod 640 /var/www/html/config.php

# ── 3. Inicializa o Apache
exec "$@"
