#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck disable=SC2046
# shellcheck source=./all_functions.sh
source "$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )/all_functions.sh" "${BASH_SOURCE[0]}"

backup="$QM_API/configs/.idea.bak"
rm -rf "$backup"
#mkdir "$BACKUP"
cp -R "${QM_API}/.idea/." "$backup"

output_disable
log_end_of_script
