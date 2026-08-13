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

// Login com Google (OAuth 2.0). Se vierem vazios, o botão simplesmente não
// aparece e o site continua funcionando com e-mail/celular + senha.
define('GOOGLE_CLIENT_ID',     '${GOOGLE_CLIENT_ID:-}');
define('GOOGLE_CLIENT_SECRET', '${GOOGLE_CLIENT_SECRET:-}');

// Envio de e-mail (recuperação de senha). A imagem php:8.2-apache NÃO tem
// servidor de e-mail, então mail() nunca funciona aqui — o envio é por SMTP.
define('SMTP_HOST', '${SMTP_HOST:-smtp.gmail.com}');
define('SMTP_PORT', '${SMTP_PORT:-587}');
define('SMTP_USER', '${SMTP_USER:-}');
define('SMTP_PASS', '${SMTP_PASS:-}');
define('SMTP_FROM', '${SMTP_FROM:-}');
define('SMTP_NOME', '${SMTP_NOME:-AgroAmigo ATERPEC}');

define('APP_SECRET',      '${APP_SECRET}');
define('APP_URL',         '${APP_URL}');
define('WHATSAPP_NUMERO', '${WHATSAPP_NUMERO:-}');
define('APP_ENV',         'production');
define('APP_DEBUG',       false);

ini_set('display_errors', '0');
ini_set('log_errors',     '1');
error_reporting(E_ALL);
PHPEOF

chown www-data:www-data /var/www/html/config.php
chmod 640 /var/www/html/config.php

# ── 3. Inicializa o Apache
exec "$@"
