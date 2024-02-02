<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use App\Logging\QMLog;
use App\Slim\QMSlim;
use Illuminate\Http\Request;
class SlimController extends Controller {
	public function any(Request $request){
		//$request->server
		if(strpos($_SERVER["REQUEST_URI"], "?") !== false){
			$parts = explode("?", $_SERVER["REQUEST_URI"]);
			$_SERVER['PATH_INFO'] = $parts[0];
			$_SERVER['QUERY_STRING'] = $parts[1];
		}
		$app = new QMSlim(false);
		QMLog::phpErrorSettings(); // Don't start before new App\Slim\Application()
		//header('Content-Type: application/json');
		$app->run();
		//exit();
	}
}
