#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

cd "$(dirname "${BASH_SOURCE[0]}")"
# shellcheck source=./all_functions.sh
source "$(pwd -P)/all_functions.sh" "${BASH_SOURCE[0]}"
non_interactive
env_copy staging
# Update Package List
apt_update
etckeeper_setup

# Update System Packages
apt-get upgrade -y

# Force Locale
echo "LC_ALL=en_US.UTF-8" >>/etc/default/locale
locale-gen en_US.UTF-8

# Install Some PPAs
apt-get install -y software-properties-common curl

apt-add-repository ppa:nginx/development -y
apt-add-repository ppa:ondrej/php -y
apt-add-repository ppa:chris-lea/redis-server -y

sudo tee /etc/apt/sources.list.d/pgdg.list <<END
deb http://apt.postgresql.org/pub/repos/apt/ bionic-pgdg main
END

apt_key_add https://www.postgresql.org/media/keys/ACCC4CF8.asc

# Install Some Basic Packages
apt-get install -y build-essential dos2unix gcc git libmcrypt4 libpcre3-dev libpng-dev chrony unzip make python2.7-dev \
  python-pip re2c supervisor unattended-upgrades whois vim libnotify-bin pv cifs-utils mcrypt bash-completion zsh \
  graphviz avahi-daemon tshark imagemagick


# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install Nginx
apt-get install -y --allow-downgrades --allow-remove-essential --allow-change-held-packages \
  nginx

# Setup Some PHP-FPM Options
echo "opcache.revalidate_freq = 0" >>/etc/php/7.4/mods-available/opcache.ini

service nginx restart
service php7.4-fpm restart

# Add Vagrant User To WWW-Data
usermod -a -G $NGINX_USER $WSL_USER_NAME
id $WSL_USER_NAME
groups $WSL_USER_NAME

# Install Node
install_nvm $WSL_USER_NAME

# Install SQLite
apt-get install -y sqlite3 libsqlite3-dev

mysql57_setup

# Install Postgres
apt-get install -y postgresql-11 postgresql-server-dev-11

# Configure Postgres Remote Access

sed -i "s/#listen_addresses = 'localhost'/listen_addresses = '*'/g" /etc/postgresql/11/main/postgresql.conf
echo "host    all             all             10.0.2.2/32               md5" | tee -a /etc/postgresql/11/main/pg_hba.conf
sudo -u postgres psql -c "CREATE ROLE homestead LOGIN PASSWORD '$PW' SUPERUSER INHERIT NOCREATEDB NOCREATEROLE NOREPLICATION;"
sudo -u postgres /usr/bin/createdb --echo --owner=homestead homestead
service postgresql restart

# Install Memcached & Beanstalk
apt-get install -y redis-server memcached beanstalkd

# Configure Beanstalkd
sed -i "s/#START=yes/START=yes/" /etc/default/beanstalkd
/etc/init.d/beanstalkd start

# Install & Configure MailHog
wget --quiet -O /usr/local/bin/mailhog https://github.com/mailhog/MailHog/releases/download/v0.2.1/MailHog_linux_amd64
chmod +x /usr/local/bin/mailhog

sudo tee /etc/systemd/system/mailhog.service <<EOL
[Unit]
Description=Mailhog
After=network.target

[Service]
User=$WSL_USER_NAME
ExecStart=/usr/bin/env /usr/local/bin/mailhog > /dev/null 2>&1 &

[Install]
WantedBy=multi-user.target
EOL

systemctl daemon-reload
systemctl enable mailhog

# Configure Supervisor
systemctl enable supervisor.service
service supervisor start

# Install ngrok
wget https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-amd64.zip
unzip ngrok-stable-linux-amd64.zip -d /usr/local/bin
rm -rf ngrok-stable-linux-amd64.zip

# Install wp-cli
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
mv wp-cli.phar /usr/local/bin/wp

# Install Drush Launcher.
curl --silent --location https://github.com/drush-ops/drush-launcher/releases/download/0.6.0/drush.phar --output drush.phar
chmod +x drush.phar
mv drush.phar /usr/local/bin/drush
drush self-update

# Install Drupal Console Launcher.
curl --silent --location https://drupalconsole.com/installer --output drupal.phar
chmod +x drupal.phar
mv drupal.phar /usr/local/bin/drupal

# Install & Configure Postfix
echo "postfix postfix/mailname string homestead.test" | debconf-set-selections
echo "postfix postfix/main_mailer_type string 'Internet Site'" | debconf-set-selections
apt-get install -y postfix
sed -i "s/relayhost =/relayhost = [localhost]:1025/g" /etc/postfix/main.cf
/etc/init.d/postfix reload

# Clean Up
apt -y autoremove
apt -y clean
chown -R $WSL_USER_NAME:$WSL_USER_NAME /home/$WSL_USER_NAME
chown -R $WSL_USER_NAME:$WSL_USER_NAME /usr/local/bin

# Add Composer Global Bin To Path
printf "\nPATH=\"$(sudo su - $WSL_USER_NAME -c 'composer config -g home 2>/dev/null')/vendor/bin:\$PATH\"\n" | tee -a /home/$WSL_USER_NAME/.profile

# Enable Swap Memory
/bin/dd if=/dev/zero of=/var/swap.1 bs=1M count=1024
/sbin/mkswap /var/swap.1
/sbin/swapon /var/swap.1

set_php_cli_version
install_filebeat_for_logz_io
install_nginx_amplify
imagemagick_install
home_dev_copy $WSL_USER_NAME /home/$WSL_USER_NAME
sudo mkdir /home/$WSL_USER_NAME/.homestead-features || true
# shellcheck source=./provision_homestead_features.sh
source $QM_API/scripts/provision_homestead_features.sh

log_end_of_script
