#!/bin/bash

#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

original_location=${1:-/www/server/redis/redis.conf}

DROPBOX_FOLDER=~/Dropbox
new_location=$DROPBOX_FOLDER$original_location
if [[ -L "$original_location" ]]; then
  echo "Skipping $original_location because it's already a symlink"
  return;
fi
echo "Moving $original_location to $new_location$original_location..."
#set_owners_on_file_or_folder $original_location
mkdir -p $(dirname "$new_location")
sudo mv "$original_location" "$new_location"
echo "Creating symlink from original location $original_location to the new location: ${new_location}"
# ln -s source_file symbolic_link
# https://unix.stackexchange.com/questions/440905/what-is-the-meaning-of-ln-st-in-linux
sudo ln -sT "${new_location}" "$original_location"
