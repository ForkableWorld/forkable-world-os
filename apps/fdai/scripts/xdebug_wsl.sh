#!/usr/bin/env bash

#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck disable=SC2145
# shellcheck disable=SC2053

# shellcheck source=./all_functions.sh
set +xe && scripts=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P ) && source "$scripts/all_functions.sh" "${BASH_SOURCE[0]}" && set -xe


PHP_VERSION=7.4
set -x
sudo cp "${QM_API}"/configs/php/xdebug.ini /etc/php/${PHP_VERSION}/mods-available/xdebug.ini
sudo mkdir /run/php || true
sudo service nginx restart
sudo service php${PHP_VERSION}-fpm restart
sudo phpenmod -s cli xdebug


log_end_of_script
