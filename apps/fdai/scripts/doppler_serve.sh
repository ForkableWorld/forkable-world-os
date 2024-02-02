#!/bin/bash
set -xe
doppler run --command="export APP_URL=http://127.0.0.1:8000 && php artisan serve &"
