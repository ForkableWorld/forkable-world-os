#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

cd "$(dirname "${BASH_SOURCE[0]}")"
# shellcheck source=./all_functions.sh
source "$(pwd -P)/all_functions.sh" "${BASH_SOURCE[0]}"
phpstan=/usr/local/bin/phpstan
if [ -f "$phpstan" ]; then
    log_info "$phpstan already installed"
else
  log_info "installing $phpstan..."
  wget -O phpstan.phar https://github.com/phpstan/phpstan/raw/master/phpstan.phar
  chmod a+x phpstan.phar
  sudo mv phpstan.phar /usr/local/bin/phpstan
fi
go_to_repo_root
output_enable
phpstan analyse app
