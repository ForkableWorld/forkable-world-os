#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck source=./all_functions.sh
set +xe && scripts=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P ) && source "$scripts/all_functions.sh" "${BASH_SOURCE[0]}" && set -xe

no_root

if [ -z "$TO_COPY" ]; then echo "please export TO_COPY before sourcing this script"; exit 1; fi
if [ -z "$SERVER_PATH" ]; then echo "please export SERVER_PATH before sourcing this script (exclude trailing slash)"; exit 1; fi
if [ -z "$REPO_PATH" ]; then echo "please export SERVER_PATH before sourcing this script (exclude trailing slash)"; exit 1; fi

if [[ "$1" == "restore" ]]
    then
      echo "Copying from repo ($REPO_PATH) to server ($SERVER_PATH)";
    else
      echo "Copying from server ($SERVER_PATH) to repo ($REPO_PATH)";
fi

set -xe
for i in "${TO_COPY[@]}"
do
    if [[ "$1" == "restore" ]]
        then
            cp -R "$REPO_PATH/$i" "$SERVER_PATH"/"$i"
        else
            cp -R "$SERVER_PATH"/"$i" "$REPO_PATH/$i"
        fi
done

#composer install --no-dev
log_end_of_script
