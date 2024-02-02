#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck disable=SC2145
# shellcheck disable=SC2053
# shellcheck disable=SC2046
# shellcheck source=./all_functions.sh
SCRIPTS_FOLDER=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P ) && source "$SCRIPTS_FOLDER/all_functions.sh" "${BASH_SOURCE[0]}"
no_root

log_info "Dotfiles to dropbox"
link_to_logs_folder /var/log/jenkins/jenkins.log jenkins.log
exit 1

move_to_dropbox_and_link /home/$WSL_USER_NAME/.bash_history
move_to_dropbox_and_link /home/$WSL_USER_NAME/.bashrc
move_to_dropbox_and_link /home/$WSL_USER_NAME/.profile
#move_to_dropbox_and_link /home/$WSL_USER_NAME/lastpass.csv
move_to_dropbox_and_link /home/$WSL_USER_NAME/.bash_logout

log_info "Dotfiles to links folder"

#move_to_dropbox_and_link /www/server/panel/rewrite/nginx
exit 1

log_info "Symlinks to dotfiles folder"
link_to_links_folder "$QM_API"/public/dev/src/ionic ionic

link_to_links_folder /home/$WSL_USER_NAME/Dropbox Dropbox
link_to_links_folder /home/$WSL_USER_NAME/Downloads Downloads
#link_to_links_folder /opt
link_to_links_folder /var/lib/jenkins jenkins
#link_to_links_folder /www
link_to_links_folder /www/server server
link_to_links_folder /www/wwwroot wwwroot
link_to_links_folder /www/server/panel/rewrite/nginx rewrite
link_to_links_folder /www/server/panel/vhost vhost
exit 1

log_info "Symlinks to logs"
link_to_logs_folder /home/$WSL_USER_NAME/.xsession-errors .xsession-errors
#link_to_logs_folder /var/log
link_to_logs_folder /var/log/jenkins/jenkins.log jenkins.log
link_to_logs_folder /var/log/syslog syslog
link_to_logs_folder /www/wwwlogs wwwlogs
link_to_logs_folder /www/server/panel/logs panel-logs

log_end_of_script
