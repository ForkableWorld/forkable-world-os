#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck disable=SC2086
cd "$(dirname "${BASH_SOURCE[0]}")"
# shellcheck source=./all_functions.sh
source "$(pwd -P)/all_functions.sh" "${BASH_SOURCE[0]}"
ssl_path=$QM_API/configs/etc-global/nginx/ssl
#apt_update
#install_unattended letsencrypt
#echo "After you press enter, get the values and to https://dash.cloudflare.com/52e6cea8444378116bd4a9c8834e1b27/quantimo.do/dns and replace existing acme challenges"
#read -p "Press enter to get the values"
#sudo certbot certonly --manual --preferred-challenges=dns --email m@quantimo.do --server https://acme-v02.api.letsencrypt.org/directory --agree-tos -d quantimo.do -d *.quantimo.do
output_enable
sudo cp -Lr /etc/letsencrypt/live/quantimo.do $ssl_path/
cp $ssl_path/quantimo.do/privkey.pem $ssl_path/wildcard.quantimo.do.key
rm $ssl_path/wildcard.quantimo.do-combined.crt
cat $ssl_path/quantimo.do/cert.pem $ssl_path/quantimo.do/chain.pem > $ssl_path/wildcard.quantimo.do-combined.crt
output_disable
etc_copy_and_restart
log_in_box "Check for nginx config errors:"
sudo nginx -t
