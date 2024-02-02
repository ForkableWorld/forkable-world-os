#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck disable=SC2145
# shellcheck disable=SC2053
# shellcheck disable=SC2046
# shellcheck source=./all_functions.sh
source "$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )/all_functions.sh" "${BASH_SOURCE[0]}"

source "$SCRIPTS_FOLDER"/env.sh

#export PATH="/home/user/.yarn/bin:/usr/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:$PATH"
export RENOVATE_CONFIG_FILE="$QM_API/scripts/renovate-config.js"
export RENOVATE_TOKEN=$GITHUB_ACCESS_TOKEN # GitHub, GitLab, Azure DevOps
export GITHUB_COM_TOKEN=$GITHUB_ACCESS_TOKEN # Delete this if using github.com

nvm install 14
nvm use 14
npm install renovate
renovate
log_end_of_script
