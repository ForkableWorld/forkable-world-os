#!/bin/bash
set -xe
echo "From https://gist.github.com/ellefsen/882dd309523e3aa58c0697a78acfb92b"
wget https://dl.google.com/cloudsql/cloud_sql_proxy.linux.386
mv cloud_sql_proxy.linux.386 cloud_sql_proxy
chmod +x cloud_sql_proxy
