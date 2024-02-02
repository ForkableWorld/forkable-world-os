<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\VariableCategory;
use App\Slim\Controller\GetController;
use App\Variables\QMVariableCategory;
/** Get variable categories
 * @package App\Slim\Controller * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="1.2",
 *     resourcePath="//variableCategories",
 *     description="Variable categories",
 *     produces="['application/json']"
 * )
 * @SWG\Model(
 *     id="VariableCategory",
 *     @SWG\Property(
 *         name="name", type="string", required=true, description="Category name"
 *     ),
 * )
 */
class GetVariableCategoryController extends GetController {
	/**
	 * Get all variable categories
	 * @SWG\Api(
	 *     path="variableCategories",
	 *     description="Get variable categories",
	 *     @SWG\Operations(
	 *         @SWG\Operation(
	 *             method="GET",
	 *             summary="Get unit categories",
	 *             notes="",
	 *             type="array",
	 *             @SWG\Items("VariableCategory"),
	 *             nickname="UnitCategories::get",
	 *             @SWG\Authorizations(oauth2={
	 *                 {"scope": "basic", "description": "Get list of user connectors"}
	 *             }),
	 *             @SWG\ResponseMessages(
	 *                 @SWG\ResponseMessage(code=401, message="Not authenticated")
	 *             )
	 *         )
	 *     )
	 * )
	 */
	public function get(){
		$this->setCacheControlHeader(86400);
		$variableCategoriesArray = QMVariableCategory::getVariableCategories();
		$variableCategoriesJson = json_encode($variableCategoriesArray);
		echo $variableCategoriesJson;
	}
}
