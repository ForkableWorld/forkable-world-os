#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck disable=SC2046
# shellcheck source=./all_functions.sh
source "$( cd "$(dirname "${BASH_SOURCE[0]}")" || exit ; pwd -P )/all_functions.sh" "${BASH_SOURCE[0]}"
git_config
jenkins_restore
log_end_of_script
