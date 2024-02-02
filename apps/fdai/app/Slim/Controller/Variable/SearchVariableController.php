<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Variable;
use App\Exceptions\UnauthorizedException;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\Variable\SearchVariableRequest;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use Illuminate\Http\JsonResponse;
/** Variables
 * @package App\Slim\Controller * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="1.2",
 *     resourcePath="/variables",
 *     description="Variables",
 *     produces="['application/json']"
 * )
 * @SWG\Model(
 *     id="Variable",
 *     @SWG\Property(
 *         name="name",
 *         type="string",
 *         required=true,
 *         description="User-defined display name."
 *     ),
 *     @SWG\Property(
 *         name="category",
 *         type="string",
 *         required=true,
 *         description="Variable category like Mood, Sleep, Physical Activity, Treatment, Symptom, etc."
 *     ),
 *     @SWG\Property(
 *         name="unit",
 *         type="string",
 *         required=true,
 *         description="Abbreviated name of the default unit for the variable"
 *     ),
 *     @SWG\Property(
 *         name="sources",
 *         type="string",
 *         required=true,
 *         description="Comma-separated list of sources for this variable"
 *     ),
 *     @SWG\Property(
 *         name="minimumValue",
 *         type="number",
 *         format="double",
 *         required=true,
 *         description="Minimum reasonable value for this variable (uses default unit)"
 *     ),
 *     @SWG\Property(
 *         name="maximumValue",
 *         type="number",
 *         format="double",
 *         required=true,
 *         description="Maximum reasonable value for this variable (uses default unit)"
 *     ),
 *     @SWG\Property(
 *         name="combinationOperation",
 *         type="string",
 *         required=true,
 *         enum="['MEAN','SUM']",
 *         description="How to aggregate measurements over time"
 *     ),
 *     @SWG\Property(
 *         name="fillingValue",
 *         type="number",
 *         format="double",
 *         required=true,
 *         description="Value for replacing null measurements"
 *     )
 * )
 */
class SearchVariableController extends GetVariablesController {
    /**
     * GET /variables/search/:search
     * PURPOSE: Provide auto-complete function in variable search boxes.
     * @SWG\Api(
     *     path="variables/search/{search}",
     *     description="Get variables by search query",
     *     @SWG\Operations(
     *         @SWG\Operation(
     *             method="GET",
     *             summary="Get variables by search query",
     *             notes="Get variables by search query",
     *             nickname="Variables::getSearch",
     *             type="array",
     *             @SWG\Items("Variable"),
     *             @SWG\Parameters(
     *                 @SWG\Parameter(
     *                     name="search",
     *                     description="Search query",
     *                     paramType="path",
     *                     required=false,
     *                     type="string"
     *                 ),
     *                 @SWG\Parameter(
     *                     name="categoryName",
     *                     description="Filter variables by category name.",
     *                     paramType="query",
     *                     required=false,
     *                     type="string"
     *                 ),
     *                 @SWG\Parameter(
     *                     name="source",
     *                     description="Filter variables by source name.",
     *                     paramType="query",
     *                     required=false,
     *                     type="string"
     *                 ),
     *                 @SWG\Parameter(
     *                     name="limit",
     *                     description="Search limit",
     *                     paramType="query",
     *                     required=false,
     *                     type="integer"
     *                 ),
     *                 @SWG\Parameter(
     *                     name="offset",
     *                     description="Search offset",
     *                     paramType="query",
     *                     required=false,
     *                     type="integer"
     *                 )
     *             ),
     *             @SWG\Authorizations(oauth2={
     *                 {"scope": "basic", "description": "Basic authorization"}
     *             }),
     *             @SWG\ResponseMessages(
     *                 @SWG\ResponseMessage(code=401, message="Not authenticated")
     *             )
     *         )
     *     )
     * )
     * @param null $q
     * @return JsonResponse
     */
	public function get($q = null){
		/** @var SearchVariableRequest $request */
		$request = $this->getRequest(SearchVariableRequest::class);
        if($q){
            $params = request()->all();
            $params['searchPhrase'] = $q;
        } else {
            $params = $request->params();
            $params['searchPhrase'] = $request->getSearch();
        }
		unset($params['client_id']);
		unset($params['clientId']);
		if(!QMAuth::getQMUserIfSet()){
			$userVariables = QMCommonVariable::getCommonVariables($params);
			return $this->returnVariables($userVariables);
		}
		try {
			$userVariables = QMUserVariable::getUserVariables(QMAuth::id(), $params, $exactMatch = false,
			                                              $useWritableConnection = false);
		} catch (UnauthorizedException $e) {
			$userVariables = [];
		}
		if($this->doNotReturnCommonVariables($params)){ // If we're searching for a variable to join,
			// we only want to return the user's variables that are compatible with the variable they're joining.
			return $this->returnVariables($userVariables);
		}
		$combined = $this->addCommonVariablesIfNecessary($userVariables, $params);
		return $this->returnVariables($combined);
	}
	private function doNotReturnCommonVariables(array $params){
		return isset($params['userTagVariableId']) || 
		       isset($params['userTaggedVariableId']) ||
		       isset($params['parentUserTagVariableId']) ||
		       isset($params['childUserTagVariableId']) ||
		       isset($params['joinVariableId']);
	}
}
