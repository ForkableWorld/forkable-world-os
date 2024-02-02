#!/usr/bin/env bash
set -xe
SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/$(basename "${BASH_SOURCE[0]}")"
export IONIC=$(dirname "${SCRIPT_PATH}")
echo "======================="
echo "Building Ionic Web App"
echo "======================="
cd "${IONIC}" && CURRENT_GIT_HASH=$(git rev-parse) && lastCommitBuilt=$(cat "${IONIC}/log/ionic-last-commit-built")
echo "Previous IONIC commit built ${lastCommitBuilt} and CURRENT_GIT_HASH ${CURRENT_GIT_HASH}"
if [[ -z "$lastCommitBuilt" || ${CURRENT_GIT_HASH} != "${lastCommitBuilt}" || ${REBUILD} == "1" ]]
    then
        cd "${IONIC}" || exit
        echo "==== RUNNING npm install silently FOR IONIC APP ===="
        cd "${IONIC}" && npm install --silent
        echo "==== RUNNING npm run configure:app FOR IONIC APP ===="
        npm run configure:app
        if [[ ! -f success ]]; then
            echo "===== IONIC BUILD FAILURE: Ionic success file does not exist so build did not complete! ====="
            exit 1
        fi
        echo "${CURRENT_GIT_HASH}" > "${BUILDER}/log/ionic-last-commit-built"
    else
        echo "Already built ${CURRENT_GIT_HASH}";
fi
