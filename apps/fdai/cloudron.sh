#!/bin/bash
set -xe
repo_dir=/app/data/curedao-api
su - www-data
cd /app/data/curedao-api
git pull
npm install
composer install
php scripts/php/migrate.php

#sudo -u www-data /usr/bin/php $repo_dir/artisan queue:work --queue=high,standard,low --sleep=3 --tries=3 &
sudo -u www-data /usr/bin/php /app/data/curedao-api/artisan queue:work --sleep=3 --tries=3 &





