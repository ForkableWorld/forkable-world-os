#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck disable=SC2164
my_path="${BASH_SOURCE[0]}"
cd "$(dirname "${my_path}")";
scripts=$( pwd -P )
# shellcheck source=./all_functions.sh
source "$scripts/all_functions.sh" "${BASH_SOURCE[0]}"
etc_copy_and_restart
log_end_of_script
