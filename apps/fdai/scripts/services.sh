#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck disable=SC2145
# shellcheck disable=SC2053
# shellcheck source=./all_functions.sh
set +xe && scripts=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P ) && source "$scripts/all_functions.sh" "${BASH_SOURCE[0]}" && set -xe
set -xe
sudo mkdir /run/php || true
restart_web_services
ssh_restart
restart_databases
set +x
log_end_of_script
