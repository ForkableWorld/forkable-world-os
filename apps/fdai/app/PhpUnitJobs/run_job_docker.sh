#!/bin/bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck disable=SC2086

#export JOB_NAME=ReminderNotificationGenerator
#export FOLDER_NAME=Reminders
#HILLSBORO=1

my_path="${BASH_SOURCE[0]}"
cd "$(dirname "${my_path}")"
jobs_folder=$( pwd -P)
# shellcheck source=./../../scripts/all_functions.sh
source "$jobs_folder/../../scripts/all_functions.sh" "${BASH_SOURCE[0]}"
validate_variable_set JOB_NAME "Please set JOB_NAME env"
validate_variable_set FOLDER_NAME "Please set FOLDER_NAME env"
# shellcheck source=./../../scripts/synchronize_server_time.sh
if [[ -n ${SYNCHRONIZE_SERVER_TIME:-} ]]; then synchronize_server_time; fi
[ "${HILLSBORO:-}" = 1 ] && export APP_ENV=production-remote || export APP_ENV=production

log_lightsail_links
output_commit_message_and_env
#composer_install
docker-compose run "composer install"
#php $QM_API/scripts/php/env.php "${APP_ENV}"
phpunit_j_unit_file="build/junit.xml"
go_to_repo_root
clean_folder build
output_enable
phpunit_options="--stop-on-error --stop-on-failure --log-junit $phpunit_j_unit_file"
#printenv
class='App\\PhpUnitJobs\\'${FOLDER_NAME}'\\'${JOB_NAME}'Job'
#cmd="doppler run -- vendor/phpunit/phpunit/phpunit --configuration phpunit.xml $phpunit_options --filter \"$class\" --test-suffix ${JOB_NAME}Job.php $QM_API/app/PhpUnitJobs/$FOLDER_NAME"
cmd="vendor/phpunit/phpunit/phpunit --configuration phpunit.xml $phpunit_options --filter \"$class\" --test-suffix ${JOB_NAME}Job.php app/PhpUnitJobs/$FOLDER_NAME"
docker compose run local bash -c "$cmd" || exit 1
output_disable
assert_file_exists "$phpunit_j_unit_file"
log_info "Touching $phpunit_j_unit_file to deal with Clock on this slave is out of sync with the master error" && touch $phpunit_j_unit_file
log_end_of_script
exit 0
