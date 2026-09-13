FROM php:8.2-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html

# System libraries + PHP extensions required by MirzaBot.
RUN apt-get update && apt-get install -y --no-install-recommends \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        gd \
        zip \
        mysqli \
        pdo_mysql \
        intl \
        soap \
        bcmath \
        sockets \
        mbstring \
    && a2enmod rewrite headers expires \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy the project first, then install locked dependencies.
COPY . /var/www/html/

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader \
    && chown -R www-data:www-data /var/www/html

# Railway supplies the public HTTP port through $PORT.
RUN printf '#!/bin/sh\nset -e\na2dismod mpm_event mpm_worker >/dev/null 2>&1 || true\nrm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* 2>/dev/null || true\na2enmod mpm_prefork >/dev/null 2>&1 || true\nPORT="${PORT:-80}"\nif [ "$PORT" != "80" ]; then\n  sed -ri "s/^Listen [0-9]+$/Listen ${PORT}/" /etc/apache2/ports.conf\n  sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf\nfi\nexec apache2-foreground\n' > /usr/local/bin/railway-start \
    && chmod +x /usr/local/bin/railway-start

EXPOSE 80

CMD ["/usr/local/bin/railway-start"]
