#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

cd "$(dirname "${BASH_SOURCE[0]}")"
# shellcheck source=./all_functions.sh
source "$(pwd -P)/all_functions.sh" "${BASH_SOURCE[0]}"
output_enable
cp -R "$QM_API"/overrides/global/* .
output_disable
env_folder="QM_API/$(get_app_env)/overrides";
if folder_exists "$env_folder"; then
    output_enable
    cp -R "$env_folder"/* .
    output_disable
  else
    log_info "$env_folder does not exist so we just copied the global envs"
fi
