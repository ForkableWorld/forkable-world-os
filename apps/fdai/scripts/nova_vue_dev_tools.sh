#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

cd "$(dirname "${BASH_SOURCE[0]}")"
# shellcheck source=./all_functions.sh
source "$(pwd -P)/all_functions.sh" "${BASH_SOURCE[0]}"
cd "${QM_API}"/vendor/laravel/astral
mv webpack.mix.js.dist webpack.mix.js
npm i
npm run dev
# Is this necessary? rm -rf node_modules
cd -
php artisan astral:publish
