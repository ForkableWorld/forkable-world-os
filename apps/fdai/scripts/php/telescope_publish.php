<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

use App\Console\Kernel;
use App\Logging\ConsoleLog;
require_once __DIR__ . '/bootstrap_script.php';
ConsoleLog::info("Publishing with php script to make sure TELESCOPE_ENABLED is true upon bootstrap...");
Kernel::artisan("telescope:publish");
