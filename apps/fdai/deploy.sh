#!/bin/bash
set -e

#echo '------ deploy.sh Environmental Variables  ------'
#printenv

echo "------ Start deploy tasks for COMMIT_SHA: $COMMIT_SHA ------"

if [ "$DOPPLER_TOKEN" ]; then
		echo "------ DOPPLER_TOKEN is set ------"
	else
		echo "------ DOPPLER_TOKEN is not set ------"
fi

if [ "$UNIT_DOPPLER_TOKEN" ]; then
		echo "------ UNIT_DOPPLER_TOKEN is set ------"
	else
		echo "------ UNIT_DOPPLER_TOKEN is not set ------"
fi

if [ "$STAGING_DOPPLER_TOKEN" ]; then
		echo "------ STAGING_DOPPLER_TOKEN is set ------"
	else
		echo "------ STAGING_DOPPLER_TOKEN is not set ------"
fi

echo '------ Set permissions on storage and bootstrap/cache folder  ------'
cd /var/www/html && mkdir bootstrap/cache || true && chmod -R 777 storage bootstrap/cache

echo '------ Install dependencies  ------'
export DOPPLER_TOKEN=$UNIT_DOPPLER_TOKEN
if [ "$APP_ENV" = "local" ]; then
      doppler run --command="composer install --prefer-dist --no-interaction --no-progress --ansi"
    else
    	echo '------ composer install --prefer-dist --no-interaction --no-progress --ansi --optimize-autoloader  ------'
      doppler run --command="composer install --prefer-dist --no-interaction --no-progress --ansi --optimize-autoloader"
      # Don't cache environmental variables or you can't change in Cloud Run => php artisan config:cache
      echo '------ php artisan view:cache  ------'
      doppler run --command="php artisan view:cache"
      # php artisan route:cache
      echo '------ php artisan route:clear  ------'
      doppler run --command="php artisan route:clear" #  Fix forLaravel\SerializableClosure\Exceptions\InvalidSignatureException
                              # Your serialized closure might have been modified, or it's unsafe to be unserialized.
fi

chmod a+x vendor/mikepsinn/php-highcharts-exporter/phantomjs

#echo '------ Building front-end  ------'
#npm install
#npm build

echo '------ php artisan storage:link  ------'
doppler run --command="php artisan storage:link"
#php artisan migrate --seed --force

#if [ "$APP_ENV" = "test" ]; then
#    phpunit
#fi



#if [ "$TEST_FOLDER" ]; then
#	echo "------ Running tests in $TEST_FOLDER ------"
#	doppler run --command="vendor/bin/phpunit --log-junit build/junit.xml --stop-on-error --stop-on-failure $TEST_FOLDER"
#	# exit  It should skip the others if they're already done
#fi
phpunit(){
	echo "------ Running tests in $1 ------"
	doppler run --command="vendor/bin/phpunit --log-junit build/junit.xml --stop-on-error --stop-on-failure $1"
}

export DOPPLER_TOKEN=$UNIT_DOPPLER_TOKEN
phpunit tests/UnitTests
phpunit tests/APIs
phpunit tests/ConnectorTests
phpunit tests/SlimTests

export DOPPLER_TOKEN=$STAGING_DOPPLER_TOKEN
phpunit tests/StagingUnitTests/A
phpunit tests/StagingUnitTests/B
phpunit tests/StagingUnitTests/C
phpunit tests/StagingUnitTests/D
phpunit tests/StagingUnitTests/Analyzable

if [ "$CYPRESS" ]; then
	export DOPPLER_TOKEN=$UNIT_DOPPLER_TOKEN
	npm install
	cypress install
	doppler run --command="npm run cy:serve:run"
fi


echo '------ Deploy tasks complete ------'
