<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Variable;
use App\Slim\Controller\GetController;
use App\Slim\View\Request\Variable\SearchVariableRequest;
use App\Variables\QMCommonVariable;
class GetCommonVariableController extends GetController {
	public function get(){
		$this->setCacheControlHeader(86400);
		/** @var SearchVariableRequest $request */
		$request = $this->getRequest(SearchVariableRequest::class);
		$requestParams = $request->params();
		$requestParams['searchPhrase'] = $request->getSearch();
		$variables = QMCommonVariable::getCommonVariablesExactMatchOrFallbackToNonExact($requestParams);
		$this->returnVariables($variables);
	}
}
