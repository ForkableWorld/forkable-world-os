#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck source=./all_functions.sh
source "$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )/all_functions.sh" "${BASH_SOURCE[0]}"
mkdir -p build
wget https://github.com/Halleck45/PhpMetrics/raw/master/build/metrics.phar
php metrics.phar --report-html=build/phpmetrics.html --report-xml=build/phpmetrics.xml --violations-xml=build/violations.xml

log_end_of_script
