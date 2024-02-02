<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Variable;
use App\Slim\Controller\GetController;
use App\Slim\View\Request\Variable\SearchVariableRequest;
use App\Variables\QMCommonVariable;
use InvalidArgumentException;
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
class SearchPublicVariableController extends GetController {
	/**
	 * GET /public/variables/search/:search
	 * Get top 5 PUBLIC variables with the most user_variable_relationships and containing the search characters.
	 * 'effectOrCause' parameter allows us to specify which column in the `user_variable_relationships` table will be searched.
	 * Choices are "effect" or "cause".
	 * @SWG\Api(
	 *     path="public/variables/search/{search}",
	 *     description="Autocomplete search list containing the top 5 PUBLIC variables with the most user_variable_relationships",
	 *     @SWG\Operations(
	 *         @SWG\Operation(
	 *             method="GET",
	 *             summary="Get top 5 PUBLIC variables with the most user_variable_relationships and containing the search
	 *     characters.", notes="For example, search for Over and since Overall Mood has a lot of user_variable_relationships, it
	 *     should be in the autocomplete list.", nickname="Variables::getPublicSearch", type="array",
	 *             @SWG\Items("Variable"),
	 *             @SWG\Parameters(
	 *                 @SWG\Parameter(
	 *                     name="search",
	 *                     description="Search query",
	 *                     paramType="path",
	 *                     required=true,
	 *                     type="string"
	 *                 ),
	 *                 @SWG\Parameter(
	 *                     name="effectOrCause",
	 *                     description="Allows us to specify which column in the `user_variable_relationships` table will be searched.
	 *     Choices are effect or cause.", paramType="query", required=true, type="string"
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
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function get(){
		/** @var SearchVariableRequest $request */
		$request = $this->getRequest(SearchVariableRequest::class);
		$variables = QMCommonVariable::searchPublicVariablesAndPrivateExactMatches($request);
		$this->returnVariables($variables);
	}
}
