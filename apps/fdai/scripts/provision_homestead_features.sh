#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck disable=SC2086
cd $QM_API || exit
bash scripts/homestead/features/docker.sh
# Doesn't work bash scripts/homestead/features/elasticsearch.sh
#bash scripts/homestead/features/golang.sh
bash scripts/homestead/features/grafana.sh
bash scripts/homestead/features/heroku.sh
bash scripts/homestead/features/meilisearch.sh
bash scripts/homestead/features/minio.sh
bash scripts/homestead/features/mongodb.sh
bash scripts/homestead/features/ohmyzsh.sh
#bash scripts/homestead/features/php5.6.sh
#bash scripts/homestead/features/php7.0.sh
#bash scripts/homestead/features/php7.1.sh
#bash scripts/homestead/features/php7.2.sh
#bash scripts/homestead/features/php7.3.sh
bash scripts/homestead/features/php7.4.sh
bash scripts/homestead/features/php8.0.sh
bash scripts/homestead/features/php8.1.sh
# Needs sudo npm bash scripts/homestead/features/pm2.sh
bash scripts/homestead/features/python.sh
# Doesn't work bash scripts/homestead/features/rvm.sh
#bash scripts/homestead/features/solr.sh
#bash scripts/homestead/features/trader.sh
bash scripts/homestead/features/webdriver.sh
bash scripts/homestead/install-xhgui.sh
bash scripts/homestead/restart-webserver.sh
#bash scripts/homestead/create-minio-bucket.sh
#bash scripts/homestead/create-mongo.sh
bash scripts/homestead/create-mysql.sh
bash scripts/homestead/cron-schedule.sh
