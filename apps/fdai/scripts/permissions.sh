#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck source=./all_functions.sh
source "$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )/all_functions.sh" "${BASH_SOURCE[0]}"
jenkins_permissions
assign_user_groups
jenkins_restart
sudo chown -R $WSL_USER_NAME:$NGINX_USER /www/wwwroot/
log_end_of_script
