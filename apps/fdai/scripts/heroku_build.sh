#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck source=./all_functions.sh
tests_folder=$(
  cd "$(dirname "${BASH_SOURCE[0]}")"
  pwd -P
) && source "$tests_folder/../../all_functions.sh" "${BASH_SOURCE[0]}"
echo '##### Print environment'
env | sort
export QM_API="$PWD/${REPO_TO_TEST}"
export TESTS_TRAVIS="$QM_API/tests/travis"
echo "HOSTNAME is ${HOSTNAME} and QM_API is $QM_API"
export TEST_PATH=$(echo ${TRAVIS_COMMIT_MESSAGE} | cut -f1 -d#)
export BRANCH=$(echo ${TRAVIS_COMMIT_MESSAGE} | cut -f2 -d#)
export SHA=$(echo ${TRAVIS_COMMIT_MESSAGE} | cut -f3 -d#)
source ${TESTS_TRAVIS}/update-status.sh --sha=${SHA} \
  --repo=mikepsinn/${REPO_TO_TEST} \
  --status=pending \
  --message="Running ${TEST_PATH} tests on Travis..." \
  --context="${TEST_PATH}" \
  --url=https://travis-ci.org/${TRAVIS_REPO_SLUG}/builds/${TRAVIS_BUILD_ID}
#### halt script on error
#set -x
echo "Checking out revision ${SHA}"
if [[ ! -d ${REPO_TO_TEST} ]]; then echo "Repo not found so cloning" && git clone -b ${BRANCH} --single-branch https://${GITHUB_ACCESS_TOKEN}:x-oauth-basic@github.com/mikepsinn/${REPO_TO_TEST}.git ${REPO_TO_TEST}; fi
cd ${REPO_TO_TEST} && git stash && git pull origin ${BRANCH}
ls
export DB_URL=${JAWSDB_URL}
export TEST_DB_URL=${JAWSDB_URL}
ENV_COMMAND="export DB_URL=${TEST_DB_URL} && "
mkdir "${QM_API}"/phpunit || true
echo "Copying .env.testing to .env"
cp "${QM_API}"/.env.testing "${QM_API}"/.env
composer self-update || true
composer install --prefer-dist --optimize-autoloader
if [[ ${TEST_PATH} == "AppSettingsModel" ]]; then # Don't have to install mongo extension twice if we run these 2 fast tests together
  vendor/phpunit/phpunit/phpunit --stop-on-error --stop-on-failure --configuration phpunit.xml --log-junit phpunit/${TEST_PATH}.xml tests/SlimTests/AppSettings
  vendor/phpunit/phpunit/phpunit --stop-on-error --stop-on-failure --configuration phpunit.xml --log-junit phpunit/${TEST_PATH}.xml tests/SlimTests/Model
else
  vendor/phpunit/phpunit/phpunit --stop-on-error --stop-on-failure --configuration phpunit.xml --log-junit phpunit/${TEST_PATH}.xml tests/SlimTests/${TEST_PATH}
fi

source ${TESTS_TRAVIS}/update-status.sh --sha=${SHA} \
  --repo=mikepsinn/${REPO_TO_TEST} \
  --status=success \
  --message="${TEST_PATH} tests successful on Travis!" \
  --context="${TEST_PATH}" \
  --url=https://travis-ci.org/${TRAVIS_REPO_SLUG}/builds/${TRAVIS_BUILD_ID}

log_end_of_script
