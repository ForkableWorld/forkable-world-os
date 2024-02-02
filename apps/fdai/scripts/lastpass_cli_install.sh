#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

cd "$(dirname "${BASH_SOURCE[0]}")"
# shellcheck source=./all_functions.sh
source "$(pwd -P)/all_functions.sh" "${BASH_SOURCE[0]}"

sudo apt-get --no-install-recommends -y install bash-completion build-essential cmake libcurl4  libcurl4-openssl-dev  libssl-dev  libxml2 libxml2-dev libssl1.1 pkg-config ca-certificates xclip

sudo apt update
sudo apt-get install -y lastpass-cli
lpass --version
lpass login m@quantimo.do
lpass ls
log_message "lpass ls | grep YOUR_KEY"

git clone https://github.com/davidpicarra/lastpass-albert-extension.git  /usr/share/albert/org.albert.extension.python/modules/lastpass-albert-extension
log_message "lp gmail and press Enter to add the password to your clipboard OR keep holding the ALT key to get more options"

log_end_of_script
