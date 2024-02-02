<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
use App\Computers\PhpUnitComputer;
require_once __DIR__ . '/php/bootstrap_script.php';
foreach(PhpUnitComputer::all() as $c){
	$c->fixWorkspacePermissions();
}
