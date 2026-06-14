FROM php:8.2-apache

# System libraries + PHP extensions required by CodeIgniter 4
RUN apt-get update && apt-get install -y \
    libicu-dev libonig-dev libzip-dev zip unzip git libpng-dev libjpeg-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install intl mysqli pdo_mysql mbstring gd zip \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache: enable URL rewriting and point docroot at CI4's public/ folder
RUN a2enmod rewrite
RUN a2dismod mpm_event mpm_worker 2>/dev/null; a2enmod mpm_prefork
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY . /var/www/html

# Install PHP dependencies (non-fatal: app also ships the framework in system/)
RUN composer install --no-dev --no-interaction --optimize-autoloader --ignore-platform-reqs || true

# Writable dir permissions
RUN chown -R www-data:www-data /var/www/html/writable 2>/dev/null || true \
 && chmod -R 775 /var/www/html/writable 2>/dev/null || true

# Listen on the port Railway provides
RUN sed -ri -e 's!Listen 80!Listen ${PORT}!' /etc/apache2/ports.conf \
 && sed -ri -e 's!:80>!:${PORT}>!' /etc/apache2/sites-available/*.conf
ENV PORT=8080
EXPOSE 8080
CMD ["apache2-foreground"]
