#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

this_folder=$(
  cd "$(dirname "${BASH_SOURCE[0]}")"
  pwd -P
)
# shellcheck source=./all_functions.sh
source "$this_folder/all_functions.sh" "${BASH_SOURCE[0]}"
echo '##### Print environment'
env | sort
export QM_API="$PWD/${REPO_TO_TEST}"
echo "HOSTNAME is ${HOSTNAME} and QM_API is $QM_API"
export TEST_PATH=$(echo ${TRAVIS_COMMIT_MESSAGE} | cut -f1 -d#)
export BRANCH=$(echo ${TRAVIS_COMMIT_MESSAGE} | cut -f2 -d#)
export SHA=$(echo ${TRAVIS_COMMIT_MESSAGE} | cut -f3 -d#)
app_name=qm-controllers
#Create a heroku app
heroku apps:create $app_name
heroku addons:create heroku-postgresql:hobby-dev --app $app_name
heroku addons:create heroku-redis:hobby-dev --app $app_name
heroku buildpacks:add heroku/php
heroku buildpacks:add heroku/nodejs
#2. Add Heroku git remote
heroku git:remote --app $app_name
#3. Set config parameters
#To operate correctly you need to set APP_KEY:
heroku config:set APP_KEY=$(php artisan --no-ansi key:generate --show)
heroku config:set APP_LOG=errorlog
#Configure additional parameters to utilise redis
#heroku config:set QUEUE_DRIVER=redis SESSION_DRIVER=redis CACHE_DRIVER=redis
#Optionally set your app's environment to development
heroku config:set APP_ENV=development LOG_LEVEL=debug
#4. Deploy to Heroku
git push heroku master

log_end_of_script
