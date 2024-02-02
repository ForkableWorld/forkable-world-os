#!/bin/bash
set -xe
#cd "$(dirname "${BASH_SOURCE[0]}")" || exit

#sudo apt-get install build-essential cmake
#sudo apt-get install libpq-dev
#sudo apt-get install libmysqlclient-dev
#sudo apt-get install libmariadbclient-dev-compat
#cd build || exit
#cmake .. && make && make install

sudo apt-get update
sudo apt-get install -y pgloader

source_url="mysql://root:__PASSWORD__@demo-db-cluster.cluster-corrh0fp2kuj.us-east-1.rds.amazonaws.com/qm_production"
supabase_url="postgresql://postgres:__PASSWORD__@db.cbdvqiqgmpdcuvtoehvg.supabase.co:6543/postgres/global_data3"
gcp_url="postgresql://replicator:__PASSWORD__@35.238.0.4:5432/quantimodo_test"
target_url=$gcp_url

pgloader -v -d $source_url $target_url
