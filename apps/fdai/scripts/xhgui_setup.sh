#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck disable=SC2046,SC2086,SC2053,SC2046
SCRIPTS_FOLDER=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )
# shellcheck source=./all_functions.sh
source "$SCRIPTS_FOLDER/all_functions.sh" "${BASH_SOURCE[0]}"
xhgui_repo=$QM_API/repos/mikepsinn/xhgui
git_clone_if_necessary https://github.com/mikepsinn/xhgui.git $xhgui_repo
cd $xhgui_repo
php install.php
folder_to_link "$xhgui_repo/webroot" "$QM_API/public/xhgui"
copy_folder_over_another "$QM_API/overrides/repos/mikepsinn/xhgui" "$xhgui_repo"
log_end_of_script
