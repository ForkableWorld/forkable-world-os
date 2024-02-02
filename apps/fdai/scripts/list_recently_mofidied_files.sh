#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck disable=SC2145
# shellcheck disable=SC2053
# shellcheck disable=SC2046
# shellcheck source=./all_functions.sh
SCRIPTS_FOLDER=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P ) && source "$SCRIPTS_FOLDER/all_functions.sh" "${BASH_SOURCE[0]}"

path=/www/server

while :
do
  sudo find $path -type f -mmin -1
  echo "Sleep for 30 seconds. Ctrl-C to stop"
  sleep 30
done
log_end_of_script
