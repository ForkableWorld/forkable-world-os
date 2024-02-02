<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller;
use App\Exceptions\UserVariableNotFoundException;
use App\Slim\Middleware\QMAuth;
use App\Utils\APIHelper;
use App\Variables\QMUserVariable;
class GetHighchartController extends GetController {
	/**
	 * @throws UserVariableNotFoundException
	 */
	public function get(){
		$this->setCacheControlHeader(5 * 60);
		$requestParams = request()->all();
		$userVariable = QMUserVariable::getByNameOrId(QMAuth::id(), $requestParams['variableName']);
		$charts = $userVariable->getChartGroup();
		$charts->getOrSetHighchartConfigs();
		if(APIHelper::apiVersionIsAbove(3)){
			return $this->writeJsonWithGlobalFields(200, ['charts' => $charts]);
		} else{
			$this->writeJsonWithoutGlobalFields(200, $charts);
		}
	}
}
