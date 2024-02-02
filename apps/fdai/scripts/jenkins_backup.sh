#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# sudo apt-get install -y curl git && curl https://gist.githubusercontent.com/mikepsinn/560bbb656cc31751f7dd2bfe5b53adae/raw/jenkins_backup_entire_folder.sh | bash -s PUT_GIT_ACCESS_TOKEN_HERE
# shellcheck disable=SC2046
# shellcheck source=./all_functions.sh
source "$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )/all_functions.sh" "${BASH_SOURCE[0]}"
clone_jenkins_backup_repo
sudo chmod -R 744 "$backup_folder"
own_folder "$backup_folder"
exclude_str=$(rsync_exclude_string "${JENKINS_EXCLUDED_FOLDERS[@]}")
rsync_folder_without_permissions_including_deletion /var/lib/jenkins "${backup_folder}" "${exclude_str}"
echo "Copying plugin files because the directories aren't needed"
rsync_files_non_recursive_including_deletion /var/lib/jenkins/plugins "${backup_folder}"/plugins
# git_commit_all "${JENKINS_BACKUP_REPO_PATH}" master "Jenkins Full Backup (Entire Folder)"
log_end_of_script
exit 0
