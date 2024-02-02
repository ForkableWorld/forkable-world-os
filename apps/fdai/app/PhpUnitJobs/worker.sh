#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

my_path="${BASH_SOURCE[0]}"
cd "$(dirname "${my_path}")"
jobs_folder=$( pwd -P)
# shellcheck source=./../../scripts/all_functions.sh
source "$jobs_folder/../../scripts/all_functions.sh" "${BASH_SOURCE[0]}"
validate_variable_set RELEASE_STAGE_QUEUE "Please set RELEASE_STAGE_QUEUE env"
log_info "=== Running Worker ==="
if [ -z "${DB_DATABASE}" ]; then
  [ "${HILLSBORO:-}" = "1" ] && APP_ENV=${RELEASE_STAGE_QUEUE}-remote || APP_ENV=${RELEASE_STAGE_QUEUE}
  cp "${QM_API}"/.env."${APP_ENV}" "${QM_API}"/.env
fi
composer_install
#php artisan queue:work redis --tries=2 --queue=${RELEASE_STAGE_QUEUE}
php artisan queue:work
log_end_of_script
