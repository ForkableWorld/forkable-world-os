#!/usr/bin/env bash
# shellcheck disable=SC1017
cd "$(dirname "${BASH_SOURCE[0]}")"
# shellcheck source=./all_functions.sh
source "$(pwd -P)/all_functions.sh" "${BASH_SOURCE[0]}"

#ssh_restart
#sudo service bt restart
#log_info "Start services at http://127.0.0.1:7777/"
sail up
log_end_of_script

C:\Windows\system32\wsl.exe -e bash \\wsl$\Ubuntu-22.04\www\wwwroot\cd-api\scripts\sail_up.sh
