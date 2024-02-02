#!/usr/bin/env bash
cd "$(dirname "${BASH_SOURCE[0]}")"
# shellcheck source=../scripts/all_functions.sh
source "$(pwd -P)/../scripts/all_functions.sh" "${BASH_SOURCE[0]}"

cd ${IONIC_PATH}
#npm install && npm run types
export API_ORIGIN="http://localhost:80"
export CUREDAO_CLIENT_ID="oauth_test_client"
npm run test:mocha

log_end_of_script
