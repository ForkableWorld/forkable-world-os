#!/bin/bash
set -xe
PARENT_SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/$(basename "${BASH_SOURCE[0]}")"
SCRIPT_FOLDER=$(dirname "${PARENT_SCRIPT_PATH}")
cd "${SCRIPT_FOLDER}" && cd .. && export IONIC_PATH="$PWD"
# shellcheck source=./log_start.sh
source "$IONIC_PATH/scripts/log_start.sh" "${BASH_SOURCE[0]}"
# shellcheck source=./no-root.sh
source "$SCRIPT_FOLDER/no-root.sh"
npm run heroku:deploy
# shellcheck source=./log_end.sh
source "$IONIC_PATH"/scripts/log_end.sh "${BASH_SOURCE[0]}"
