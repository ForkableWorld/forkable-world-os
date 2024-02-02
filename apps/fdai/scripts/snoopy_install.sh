#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck disable=SC2046,SC2086,SC2053,SC2046
SCRIPTS_FOLDER=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )
# shellcheck source=./all_functions.sh
source "$SCRIPTS_FOLDER/all_functions.sh" "${BASH_SOURCE[0]}"
wget -O install-snoopy.sh https://github.com/a2o/snoopy/raw/install/install/install-snoopy.sh &&
chmod 755 install-snoopy.sh &&
sudo ./install-snoopy.sh stable
log_end_of_script
