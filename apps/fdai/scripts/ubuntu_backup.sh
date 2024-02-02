#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

cd "$(dirname "${BASH_SOURCE[0]}")"
# shellcheck source=./all_functions.sh
source "$(pwd -P)/all_functions.sh" "${BASH_SOURCE[0]}"

#sudo apt-add-repository -y ppa:teejee2008/ppa
#sudo apt-get update
#sudo apt-get install aptik
#log_message "See https://ubunlog.com/en/aptik-backup-tool/"

#sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 382003C2C8B7B4AB813E915B14E4942973C62A1B
sudo add-apt-repository "deb http://ppa.launchpad.net/nemh/systemback/ubuntu xenial main"
sudo apt update
sudo apt install systemback
log_message "see https://linuxhint.com/create-iso-current-installation-ubuntu/"

log_end_of_script
