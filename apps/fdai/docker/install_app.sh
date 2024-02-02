#!/bin/bash

COPY . /var/www/html/

mkdir -p /var/www/html/storage/app/public || true
mkdir -p /var/www/html/storage/logs || true
mkdir -p /var/www/html/storage/framework/cache || true
mkdir -p /var/www/html/storage/framework/views || true
mkdir -p /var/www/html/storage/framework/sessions || true

#COPY --from=build /usr/bin/composer /usr/bin/composer
#composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction
cd /var/www/html && \
  composer install --prefer-dist --no-interaction && \
  #php artisan config:cache && \
  #php artisan route:cache && \
  composer run-script cache-clear
chmod 777 -R /var/www/html/storage/
chown -R www-data:www-data /var/www/

COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

ln -sf /dev/stderr /var/www/html/storage/logs/laravel.log || true

ls -lah
