<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\UnitCategory;
use App\Slim\Controller\GetController;
use App\Slim\Model\QMUnitCategory;
use App\Utils\APIHelper;
/** Get unit categories
 * @package App\Slim\Controller * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="1.2",
 *     resourcePath="/unitCategories",
 *     description="Variable measurement units",
 *     produces="['application/json']"
 * )
 * @SWG\Model(
 *     id="UnitCategory",
 *     @SWG\Property(
 *         name="name", type="string", required=true, description="Category name"
 *     ),
 * )
 */
class GetUnitCategoriesController extends GetController {
	/**
	 * Get unit categories
	 * @SWG\Api(
	 *     path="unitCategories",
	 *     description="Get unit categories",
	 *     @SWG\Operations(
	 *         @SWG\Operation(
	 *             method="GET",
	 *             summary="Get unit categories",
	 *             notes="",
	 *             type="array",
	 *             @SWG\Items("UnitCategory"),
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
		if(APIHelper::apiVersionIsAbove(3)){
			return $this->writeJsonWithGlobalFields(200, ['unitCategories' => QMUnitCategory::getAsArray()]);
		} else{
			$this->writeJsonWithoutGlobalFields(200, QMUnitCategory::getAsArray());
		}
	}
}
