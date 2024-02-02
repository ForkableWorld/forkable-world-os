#!/usr/bin/env bash
set -e

#echo '------ start.sh Environmental Variables  ------'
#printenv

role=${CONTAINER_ROLE}

echo -e "

*********************************************************************************

==> Starting \"robsontenorio/laravel\" image for CONTAINER_ROLE = \"$role\" ...

  APP (default)    => App webserver (nginx + php-fpm).
  JOBS             => Queued jobs + scheduled commands (schedule:run).
  ALL              => APP + JOBS

*********************************************************************************

"
set -xe
echo "==> Starting \"$role\" role with APP_ENV = \"$APP_ENV\" ..."
#printenv
echo "--- start.sh: Copying supervisor config files to /etc..."
cp -r /var/www/html/config/etc/* /etc/
cd /var/www/html && chmod -R 777 storage bootstrap/cache

#php -r "xdebug_info();"

echo "--- start.sh: with role $role..."
set +x # Don't log the DOPPLER_TOKEN
if [ "$role" = "APP" ]; then
	set -xe
	apachectl -D FOREGROUND
	set +x
elif [ "$role" = "JOBS" ]; then
	set -xe
	doppler run --command="apachectl -D FOREGROUND" &
	supervisord -c /etc/supervisord.conf
	supervisorctl status
	set +x
elif [ "$role" = "CYPRESS" ]; then
	set -xe
	apt-get update
	apt-get install -y libgtk2.0-0 libgtk-3-0 libgbm-dev libnotify-dev libgconf-2-4 libnss3 libxss1 libasound2 libxtst6 xauth xvfb
	cypress install
	npm run cy:serve:run
	set +x
else
	if [ "$DOPPLER_TOKEN" ]; then
		echo "DOPPLER_TOKEN is set"
		#doppler secrets --only-names --debug
		set -xe
		# doppler run --command="supervisord -c /etc/supervisord.conf" --debug &
		doppler run --command="apachectl -D FOREGROUND"
		set +x
	else
		echo "DOPPLER_TOKEN is not set so just using existing environment!"
		set -xe
		# supervisord -c /etc/supervisord.conf &
		apachectl -D FOREGROUND 
		set +x
	fi
	# supervisord -c /etc/supervisord.conf
	# supervisorctl status
fi


