#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck source=./all_functions.sh
source "$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )/all_functions.sh" "${BASH_SOURCE[0]}"
# shellcheck disable=SC2145
# shellcheck disable=SC2053
output_disable
echo "==== Restarting WEB services for $HOSTNAME ===="
restart_web_services
log_end_of_script
