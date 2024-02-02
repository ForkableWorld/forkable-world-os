#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck disable=SC2046
# shellcheck source=./all_functions.sh
#scripts=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P ) && source "$SCRIPTS_FOLDER/all_functions.sh" "${BASH_SOURCE[0]}"
#sudo phpenmod -s cli xdebug
source /home/vagrant/.bash_profile
printenv
enter_command_here="php artisan optimize"
export PHP_IDE_CONFIG=serverName=127.0.0.1
set -x
php -dxdebug.mode=debug -dxdebug.client_host=127.0.0.1 -dxdebug.client_port=9003 -dxdebug.start_with_request=yes $enter_command_here
