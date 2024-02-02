#!/bin/bash
set -e
role=${CONTAINER_ROLE:-app}
env=${APP_ENV:-production}
cd /var/www/html
cp docker/xdebug-local.ini /etc/php/8.1/cli/conf.d/99-xdebug.ini
if [ "$env" != "local" ]; then
    	echo "APP_ENV is $env so caching config and disabling xdebug"
      # Don't cache environmental variables or you can't change in Cloud Run => php artisan config:cache && \
      php artisan config:clear && \
      ##php artisan route:cache && \
      php artisan route:clear && \  #  Fix forLaravel\SerializableClosure\Exceptions\InvalidSignatureException
                                    # Your serialized closure might have been modified, or it's unsafe to be unserialized.
      php artisan view:cache && \
      cp docker/xdebug-production.ini /etc/php/8.1/cli/conf.d/99-xdebug.ini
fi
if [ "$role" = "app" ]; then
    exec apache2-foreground
elif [ "$role" = "worker" ]; then
    echo "Running the queue worker..."
    php /var/www/html/artisan queue:work --verbose --tries=3 --timeout=90
elif [ "$role" = "test" ]; then
    echo "Running the tests..."
    php /var/www/html/artisan phpunit
elif [ "$role" = "scheduler" ]; then
    while true; do
      php /var/www/html/artisan schedule:run --verbose --no-interaction &
      sleep 60
  done
else
    echo "Could not match the container role \"$role\""
    exit 1
fi
