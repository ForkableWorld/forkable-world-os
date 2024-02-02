#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck source=./all_functions.sh
source "$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )/all_functions.sh" "${BASH_SOURCE[0]}"

bash <(curl -Ss https://my-netdata.io/kickstart.sh) --claim-token Dg3tCklcaCbjzwYfTWRdXvPlpyYoyREElo4D-_deWFr9-hOM_OsTUfqIUNfI0rmhopJdWY23RwXX4NG6fnAz7Ca-fOYpbI5-t6RDCEmsf9iCtualPa6pFS2iI2BMCRAXbIcpEDI --claim-rooms 67a92ea0-ba5f-4c38-8043-d65c2e960a6b --claim-url https://app.netdata.cloud
cp configs/etc-global/netdata/netdata.conf /etc/netdata/netdata.conf
sudo service netdata restart

log_end_of_script
