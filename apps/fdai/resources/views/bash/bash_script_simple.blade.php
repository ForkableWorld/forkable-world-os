<?php /** @var BashScriptFile $file */
use App\Files\Bash\BashScriptFile; ?>@verbatim#!/usr/bin/env bash
cd "$(dirname "${BASH_SOURCE[0]}")"
# shellcheck source=./all_functions.sh
source "$(pwd -P)/all_functions.sh" "${BASH_SOURCE[0]}"@endverbatim
{!! $file->getMainContent() !!}@verbatim

log_end_of_script@endverbatim
