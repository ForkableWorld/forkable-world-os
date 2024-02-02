<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
use App\Storage\DB\Writable;
//putenv("APP_ENV=local");
require_once __DIR__ . '/../../scripts/php/bootstrap_script.php';
Writable::migrate();
