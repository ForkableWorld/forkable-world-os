<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
use App\Storage\DB\TestDB;
putenv("APP_ENV=testing");
require_once __DIR__ . '/../../scripts/php/bootstrap_script.php';
TestDB::generateSeeds();
