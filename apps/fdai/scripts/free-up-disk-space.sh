#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck source=./all_functions.sh
source "$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )/all_functions.sh" "${BASH_SOURCE[0]}"

echo "Stopping mysql in case it is holding on to any deleted files"
sudo service mysql stop || true

echo "Emptying trash"
sudo rm -rf ~/.local/share/Trash/*

# I think this is commented because it logs people out maybe?
#echo "Emptying php session folder"
#sudo rm -rf /var/lib/php/session/*

echo "Deleting tmp files older than 1 day"
sudo find /tmp/* -mtime +1 -exec rm -rf {} \; || true

echo "These deleted files that are still open and may be taking up disk space: "
sudo lsof | grep deleted

sudo service mysql start || true
log_end_of_script
