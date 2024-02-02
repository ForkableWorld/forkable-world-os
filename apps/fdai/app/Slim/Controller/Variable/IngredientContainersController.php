<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Variable;
use App\Models\UserVariable;
use App\Slim\Controller\GetController;
use App\Slim\View\Request\QMRequest;
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
class IngredientContainersController extends GetController {
	public function get(){
		$uv = UserVariable::fromRequest();
		$results = $uv->searchEligibleIngredientContainers(QMRequest::getSearchPhrase());
		return $this->writeJsonWithoutGlobalFields(200, $results->toArray());
	}
}
