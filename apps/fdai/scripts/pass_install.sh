#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

cd "$(dirname "${BASH_SOURCE[0]}")"
# shellcheck source=./all_functions.sh
source "$(pwd -P)/all_functions.sh" "${BASH_SOURCE[0]}"
sudo apt-get install pass
pass init "qwerty Password Storage Key"
git_config
pass git init
./pass_import_lastpass.rb ~/lastpass.csv
log_end_of_script
