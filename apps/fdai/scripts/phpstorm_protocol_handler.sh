#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

cd "$(dirname "${BASH_SOURCE[0]}")"
# shellcheck source=./all_functions.sh
source "$(pwd -P)/all_functions.sh" "${BASH_SOURCE[0]}"

log_message "https://github.com/aik099/PhpStormProtocol"

output_enable
cd /www/wwwroot
gh repo clone sanduhrs/phpstorm-url-handler
cd phpstorm-url-handler
sudo cp phpstorm-url-handler /usr/bin/phpstorm-url-handler
sudo desktop-file-install phpstorm-url-handler.desktop
sudo update-desktop-database

output_disable
log_end_of_script
