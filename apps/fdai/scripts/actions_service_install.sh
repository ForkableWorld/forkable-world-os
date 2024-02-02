#!/usr/bin/env bash

# fail on unset variables and command errors
set -eu -o pipefail # -x: is for debugging
set -x

sudo apt-get update
sudo apt-get install -y jq

sudo usermod -aG docker $USER
echo "You'll probably need to reboot for the docker group to take effect."

owner=${OWNER:-mikepsinn}
pat_token=${PAT_TOKEN:-$1}
if [ -z "${pat_token}" ]; then
  echo "Please set the PAT_TOKEN environment variable or pass it as the first argument to this script."
  exit 1
fi

#######################################
# description
# Globals:
#   owner
#   pat_token
#   USER
# Arguments:
#   1 - personal access token
#######################################
install() {
  local repo=$1
  local folder=/home/$USER/actions-runners/$repo
  mkdir "/home/$USER/actions-runners/" || true
  mkdir "$folder" || true
  cd "$folder" || exit 1
  curl -o actions-runner-linux-x64-2.299.1.tar.gz -L https://github.com/actions/runner/releases/download/v2.299.1/actions-runner-linux-x64-2.299.1.tar.gz || true
  tar xzf ./actions-runner-linux-x64-2.299.1.tar.gz || true

  echo "Cleaning up actions runner..."
  sudo ./svc.sh uninstall || true
  rm -rf .runner || true
  ./config.sh remove || true

  local name="gcp-$(hostname)-$repo"
  local auth_token=$(curl -s -X POST -H "authorization: token ${pat_token}" "https://api.github.com/repos/${owner}/${repo}/actions/runners/registration-token" | jq -r .token)

  echo "Configuring up actions runner $name for repo $repo..."
  ./config.sh \
    --url "https://github.com/${owner}/${repo}" \
    --token "${auth_token}" \
    --name "$name" \
    --unattended \
    --work _work \
    --labels cypress,fast,medium
    #--ephemeral \

  echo "Installing actions runner service..."
  sudo ./svc.sh install
  echo "Starting actions runner service..."
  sudo ./svc.sh start
}

install curedao-api
install cd-ionic

