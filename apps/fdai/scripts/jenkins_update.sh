#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck source=./all_functions.sh
set +xe && scripts=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P ) && source "$scripts/all_functions.sh" "${BASH_SOURCE[0]}" && set -xe
# shellcheck source=./jenkins_backup.sh
source "$scripts"/jenkins_backup.sh
sudo service jenkins stop
cd /usr/share/jenkins || exit 1
sudo mv jenkins.war jenkins.war.old
sudo wget https://updates.jenkins-ci.org/latest/jenkins.war
jenkins_permissions
sudo service jenkins start
log_end_of_script
