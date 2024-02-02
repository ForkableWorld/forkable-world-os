#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

cd "$(dirname "${BASH_SOURCE[0]}")"
# shellcheck source=./all_functions.sh
source "$(pwd -P)/all_functions.sh" "${BASH_SOURCE[0]}"
# Install MySQL
echo "mysql-server mysql-server/root_password password $PW" | debconf-set-selections
echo "mysql-server mysql-server/root_password_again password $PW" | debconf-set-selections
apt-get install -y mysql-server

# Configure MySQL Password Lifetime
echo "default_password_lifetime = 0" >>/etc/mysql/mysql.conf.d/mysqld.cnf

# Configure MySQL Remote Access

mysql --user="root" --password="$PW" -e "GRANT ALL ON *.* TO root@'0.0.0.0' IDENTIFIED BY '$PW' WITH GRANT OPTION;"
service mysql restart

mysql --user="root" --password="$PW" -e "CREATE USER '$WSL_USER_NAME'@'0.0.0.0' IDENTIFIED BY '$PW';"
mysql --user="root" --password="$PW" -e "GRANT ALL ON *.* TO '$WSL_USER_NAME'@'0.0.0.0' IDENTIFIED BY '$PW' WITH GRANT OPTION;"
mysql --user="root" --password="$PW" -e "GRANT ALL ON *.* TO '$WSL_USER_NAME'@'%' IDENTIFIED BY '$PW' WITH GRANT OPTION;"
mysql --user="root" --password="$PW" -e "FLUSH PRIVILEGES;"
mysql --user="root" --password="$PW" -e "CREATE DATABASE homestead character set UTF8mb4 collate utf8mb4_bin;"
