<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Correlation;
use App\Exceptions\NoUserVariableRelationshipsToAggregateException;
use App\Slim\View\Request\Correlation\GetAggregatedCorrelationRequest;
use App\Utils\APIHelper;
class GetAggregatedCorrelationController extends GetCorrelationController {
	/**
	 * @throws NoUserVariableRelationshipsToAggregateException
	 */
	public function get(){
		$this->setCacheControlHeader(5 * 60);
		/** @var GetAggregatedCorrelationRequest $request */
		$request = $this->getRequest();
		$requestParams = $request->params();
		$effectOrCause = $request->getPublicEffectOrCause();
		if(!$effectOrCause){
			$effectOrCause = $request->getEffectOrCause();
		}
		if($effectOrCause === 'effect'){
			$requestParams['effectVariableName'] = $request->getSearch();
		}
		if($effectOrCause === 'cause'){
			$requestParams['causeVariableName'] = $request->getSearch();
		}
		if(isset($requestParams['effectOrCause'])){
			unset($requestParams['effectOrCause']);
		}
		if(isset($requestParams['causeOrEffect'])){
			unset($requestParams['causeOrEffect']);
		}
		if(isset($requestParams['publicEffectOrCause'])){
			unset($requestParams['publicEffectOrCause']);
		}
		$correlations = $this->getAggregatedCorrelations($requestParams);
		if(APIHelper::apiVersionIsAbove(3)){
			return $this->writeJsonWithGlobalFields(200, ['user_variable_relationships' => $correlations]);
		} else{
			$this->writeJsonWithoutGlobalFields(200, $correlations);
		}
	}
}
