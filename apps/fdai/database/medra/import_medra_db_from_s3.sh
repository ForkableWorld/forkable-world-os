#!/bin/bash
# shellcheck source=./../../scripts/all_functions.sh
set +xe && mysql_folder=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P ) && source "$mysql_folder/../../scripts/all_functions.sh" "${BASH_SOURCE[0]}" && set -xe

if [ -z ${DEV_PASS} ]; then
    export DEV_PASS=root
    export MYSQL_USER=root
fi

echo "Turn off strict MySQL and disable FOREIGN_KEY_CHECKS"
mysql --host=127.0.0.1 -u root  --password=${DEV_PASS} quantimodo <<EOF
    SET GLOBAL event_scheduler = ON;
    SET @@global.sql_mode= '';
    SET @@global.FOREIGN_KEY_CHECKS=0;
EOF

echo "Downloading Meddra database"
wget --no-check-certificate --no-proxy "https://s3.amazonaws.com/quantimodo/medra.zip"

unzip meddra.zip

echo "Importing schema and data... This could take a few minutes."
mysql --host=127.0.0.1 -u root --password=${DEV_PASS} quantimodo <MEDDRA.DB.import.script.sql

echo "Enable FOREIGN_KEY_CHECKS"
mysql --host=127.0.0.1 -u root  --password=${DEV_PASS} quantimodo <<EOF
    SET @@global.FOREIGN_KEY_CHECKS=0;
EOF

log_end
