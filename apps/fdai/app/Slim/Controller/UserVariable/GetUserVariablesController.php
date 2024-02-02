<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
namespace App\Slim\Controller\UserVariable;
use App\Slim\Controller\Variable\GetVariablesController;
use App\Slim\Middleware\QMAuth;
class GetUserVariablesController extends GetVariablesController {
	public function get(){
		$userId = QMAuth::id(true);
		$variables = $this->getUserVariables($userId);
		return $this->unsetNullTagPropertiesAnalyzeAddChartsAndReturnVariables($variables);
	}
}
