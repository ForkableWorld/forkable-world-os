<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\UserTag;
use App\Slim\Controller\GetController;
use App\Slim\View\Request\UserTag\GetUserTagRequest as GetUserTagRequest;
use App\Types\QMStr;
use App\Utils\APIHelper;
use App\Utils\QMAPIValidator;
use App\Variables\QMUserTag;
class GetUserTagController extends GetController {
	public function get(){
		$this->setCacheControlHeader(5 * 60);
		$allowedParams = [
			'userTaggedVariableId',
			'userTagVariableId',
		];
		$requestParams = $this->getApp()->request->params();
		$requestParams = QMStr::properlyFormatRequestParams($requestParams);
		QMAPIValidator::validateParams($allowedParams, array_keys($requestParams), 'user_tags/user_tags_get');
		/** @var GetUserTagRequest $request */
		$request = $this->getRequest(GetUserTagRequest::class);
		$userTags = QMUserTag::getUserTags($request->getUserId(), $requestParams);
		if(APIHelper::apiVersionIsAbove(3)){
			return $this->writeJsonWithGlobalFields(200, ['userTags' => $userTags], JSON_NUMERIC_CHECK);
		} else{
			$this->writeJsonWithoutGlobalFields(200, $userTags, JSON_NUMERIC_CHECK);
		}
	}
}
