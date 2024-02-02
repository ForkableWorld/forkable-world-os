<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Correlation;
use App\VariableRelationships\UserVariableRelationshipListExplanationResponseBody;
use App\Slim\Controller\GetController;
class GetCorrelationExplanationsController extends GetController {
	public function get(){
		$this->setCacheControlHeader(5 * 60);
		$requestParams = $this->getApp()->request->params();
		return $this->writeJsonWithGlobalFields(200,
			UserVariableRelationshipListExplanationResponseBody::getUserVariableRelationshipsExplanationArray($requestParams));
	}
}
