FROM php:8.2-apache

# Instala a extensão PDO + PostgreSQL
RUN apt-get update \
    && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Habilita mod_rewrite para .htaccess
RUN a2enmod rewrite

# Permite AllowOverride All no diretório raiz
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Copia todos os arquivos do projeto (config.php está no .dockerignore)
COPY . /var/www/html/

# Permissões corretas
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Garante que o diretório de uploads existe
RUN mkdir -p /var/www/html/uploads/fichas \
    && chown -R www-data:www-data /var/www/html/uploads

# Entrypoint: configura a porta do Render e gera o config.php
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 10000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
