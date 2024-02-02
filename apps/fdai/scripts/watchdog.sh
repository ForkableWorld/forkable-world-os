#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck source=./all_functions.sh
source "$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )/all_functions.sh" "${BASH_SOURCE[0]}"
# Service watchdog script
# Put in crontab to automatically restart services (and optionally email you) if they die for some reason.
# Note: You need to run this as root otherwise you won't be able to restart services.
#
# Example crontab usage:
#
# Strict check for apache2 service every 5 minutes, pipe results to /dev/null
# */5 * * * * sh /root/watchdog.sh apache2 "" > /dev/null
#
# "Loose" check for mysqld every 5 minutes, second parameter is the name of the service
# to restart, in case the application and service names differ. Also emails a report to admin@domain.com
# about the restart.
# */5 * * * * sh /root/watchdog.sh mysqld mysql admin@domain.com > /dev/null

# Common daemon names:
# Apache:
# apache2 - Debian/Ubuntu
# httpd - RHEL/CentOS/Fedora
# ---
# MySQL:
# mysql - Debian/Ubuntu
# mysqld - RHEL/CentOS/Fedora
# ---
# Service name
DATE=$(date +%Y-%m-%d--%H-%M-%S)
SERVICE_NAME="$1"
SERVICE_RESTART_NAME="$2"
EXTRA_PGREP_PARAMS="-x" #Extra parameters to pgrep, for example -x is good to do exact matching
MAIL_TO="$3" #Email to send restart notifications to

#path to pgrep command, for example /usr/bin/pgrep
PGREP="pgrep"

#Check if we have have a second param
if [ -z "$SERVICE_RESTART_NAME" ]; then
    restart="sudo service ${SERVICE_NAME} restart" #No second param
else
    restart="sudo service ${SERVICE_RESTART_NAME} restart" #Second param
fi

pids=$($PGREP ${EXTRA_PGREP_PARAMS} "${SERVICE_NAME}")

#if we get no pids, service is not running
if [ "$pids" == "" ]; then
  $restart
  if [ -z "$MAIL_TO" ]; then
     echo "$DATE : ${SERVICE_NAME} restarted - no email report configured."
  else
     echo "$DATE : Performing restart of ${SERVICE_NAME}" | mail -s "Service failure: ${SERVICE_NAME}" "${MAIL_TO}"
  fi
  exit 1
else
  echo "$DATE : Service ${SERVICE_NAME} is still working!"
fi

log_end_of_script
