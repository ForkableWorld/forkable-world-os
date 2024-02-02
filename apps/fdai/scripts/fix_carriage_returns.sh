#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

cd "$(dirname "${BASH_SOURCE[0]}")"
# shellcheck source=./all_functions.sh
source "$(pwd -P)/all_functions.sh" "${BASH_SOURCE[0]}"

#######################################
# description
# Arguments:
#   1 Directory without slash at end
#######################################
function fix_carriage_returns() {
    find "$1"/ -type f -iname "*.sh" -print0 \
| xargs -I {} -0 sed -i'-backup.sh' 's/\r$//' "{}"
}

fix_carriage_returns "$QM_API/scripts"
#fix_carriage_returns $HOME

log_end_of_script
