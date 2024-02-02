#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck source=./all_functions.sh
source "$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )/all_functions.sh" "${BASH_SOURCE[0]}"


sudo apt install djvulibre-bin
sudo apt install python3-pip
pip3 install pylzma
sudo apt-get install antiword unrtf untex
sudo apt install liblzma-dev
sudo pip3 install backports.lzma
sudo pip3 install mutagen
sudo add-apt-repository ppa:recoll-backports/recoll-1.15-on
sudo apt-get update
sudo apt-get install recoll
log_end_of_script
