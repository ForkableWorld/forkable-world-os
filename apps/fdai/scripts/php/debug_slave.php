<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

use App\Computers\PhpUnitComputer;
putenv("APP_ENV=production-remote");
require_once __DIR__.'/bootstrap_script.php';
$name = "phpunit-2GB-23";
$node = PhpUnitComputer::find($name);

