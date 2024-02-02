#!/bin/bash
# shellcheck disable=SC2312
sudo su
sudo apt-get update && sudo apt-get install jq -y && \
sudo mkdir /actions-runners || true && \
sudo chown -R $USER:$USER /actions-runners && \
cd /actions-runners && \
export RUNNER_CFG_PAT=__PAT_HERE__ && \
export ORG=curedao && \
export CD_API=mikepsinn/curedao-api && \
export CD_MONOREPO=mikepsinn/curedao-monorepo && \

echo "See https://github.com/actions/runner/blob/main/docs/automate.md" && \
cd /actions-runners && sudo mkdir mike || true && cd mike && \
curl -s https://raw.githubusercontent.com/actions/runner/main/scripts/create-latest-svc.sh | bash -s curedao -n curedao -l mike && \
cd /actions-runners && sudo mkdir mikepsinn-curedao-api || true && cd mikepsinn-curedao-api && \
curl -s https://raw.githubusercontent.com/actions/runner/main/scripts/create-latest-svc.sh | bash -s mikepsinn/curedao-api -n mikepsinn-curedao-api -l mike && \
cd /actions-runners && sudo mkdir mikepsinn-curedao-monorepo || true && cd mikepsinn-curedao-monorepo && \
curl -s https://raw.githubusercontent.com/actions/runner/main/scripts/create-latest-svc.sh | bash -s mikepsinn/curedao-monorepo -n mikepsinn-curedao-monorepo -l mike
