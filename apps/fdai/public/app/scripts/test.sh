#! /bin/bash
set +x
set -e
PARENT_SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/$(basename "${BASH_SOURCE[0]}")" && SCRIPT_FOLDER=$(dirname "${PARENT_SCRIPT_PATH}")
# shellcheck source=./log_start.sh
cd "${SCRIPT_FOLDER}" && cd .. && export IONIC_PATH="$PWD" && source "$IONIC_PATH"/scripts/log_start.sh "${BASH_SOURCE[0]}"
sudo bash "${SCRIPT_FOLDER}/output_commit_message_and_env.sh"
# shellcheck source=./no-root.sh
source "${SCRIPT_FOLDER}/no-root.sh"
# shellcheck source=./mocha.sh
#source "${SCRIPT_FOLDER}/mocha.sh"
#if [[ ${GIT_BRANCH} = "origin/develop" ]]; then bash "${SCRIPT_FOLDER}"/commit-build.sh && exit 0; fi
#if [[ ${GIT_BRANCH} != *"feature"* && ${GIT_BRANCH} != *"renovate"* ]]; then exit 0; fi
# shellcheck source=./heroku.sh
cd "$IONIC_PATH"
# shellcheck source=./cypress_install.sh
source "${SCRIPT_FOLDER}/doppler.sh"
# shellcheck source=./nvm.sh
source "$SCRIPT_FOLDER/nvm.sh" 16.13.0
#set +x
npm install typescript -g
#doppler run --command="npm install"
#doppler run --command="npm run configure:app"
# shellcheck source=./cypress_install.sh
source "${SCRIPT_FOLDER}/cypress_install.sh"
set -x
cd "$IONIC_PATH"
mkdir cypress/reports/mocha || true
mkdir cypress/reports/mocha/assets || true
doppler run --command="npm run test:mocha"
doppler run --command="node tests/start-server-and-test.js"
#doppler run --command="npm run heroku:deploy"
echo "installing vercel..." && npm i -g vercel
#doppler run --command="printenv"
echo "deploying to vercel..."
VERCEL_TOKEN=$(doppler run --command="echo \$VERCEL_TOKEN")
DEPLOYMENT_URL=$(vercel -t "$VERCEL_TOKEN" --yes)
# shellcheck source=./ghost-inspector.sh
doppler run --command="START_URL=$DEPLOYMENT_URL npx ts-node ts/gi-runner-ionic.ts"
# shellcheck source=./log_end.sh
source "${IONIC_PATH}/scripts/log_end.sh" "${BASH_SOURCE[0]}"
