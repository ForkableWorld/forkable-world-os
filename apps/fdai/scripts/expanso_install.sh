#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

cd "$(dirname "${BASH_SOURCE[0]}")"
# shellcheck source=./all_functions.sh
source "$(pwd -P)/all_functions.sh" "${BASH_SOURCE[0]}"

sudo snap install espanso --classic
espanso start

# Make sure to have the $HOME/opt directory
mkdir -p $HOME/opt

# Download the latest Modulo AppImage in the $HOME/opt
wget https://github.com/federico-terzi/modulo/releases/latest/download/modulo-x86_64.AppImage -O $HOME/opt/modulo.AppImage

# Make it executable:
chmod u+x $HOME/opt/modulo.AppImage

# Create a link to make modulo available as "modulo"
sudo ln -s $HOME/opt/modulo.AppImage /usr/bin/modulo


log_end_of_script
