<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\MeasurementSource;
use App\Slim\Controller\GetController;
use App\Storage\DB\Writable;
use App\Utils\APIHelper;
use PDO;
/** Measurements sources
 * @package App\Slim\Controller * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="1.2",
 *     resourcePath="/measurementSources",
 *     description="Measurement sources",
 *     produces="['application/json']"
 * )
 * @SWG\Model(
 *     id="MeasurementSource",
 *     @SWG\Property(
 *         name="name", type="string", required=true, description="Name of the application or device."
 *     )
 * )
 */
class ListMeasurementSourceController extends GetController {
	/**
	 * GET /measurementSources
	 * PURPOSE:  List all apps and devices in the `quantimodo`.`sources` table
	 * EXAMPLE USAGE:  ?
	 * EXAMPLE REQUEST:  https://quantimo.do/api/measurementSources
	 * @SWG\Api(
	 *     path="measurementSources",
	 *     description="Get measurement sources",
	 *     @SWG\Operations(
	 *         @SWG\Operation(
	 *             method="GET",
	 *             summary="Get measurement sources",
	 *             notes="",
	 *             nickname="MeasurementSources::get",
	 *             type="array",
	 *             @SWG\Items("MeasurementSource"),
	 *             @SWG\Authorizations(oauth2={
	 *                 {"scope": "basic", "description": "Clear cache"}
	 *             }),
	 *             @SWG\ResponseMessages(
	 *                 @SWG\ResponseMessage(code=401, message="Not authenticated")
	 *             )
	 *         )
	 *     )
	 * )
	 */
	public function get(){
		$databaseConnection = Writable::pdo();
		$result = $databaseConnection->query('SELECT name FROM `sources`');
		// Construct initial array and start looping through results
		$measurementSourcesArray = [];
		while($row = $result->fetch(PDO::FETCH_ASSOC)){
			$thisArray = [];
			$thisArray['name'] = $row['name'];
			$measurementSourcesArray[] = $thisArray;
		}
		if(APIHelper::apiVersionIsAbove(3)){
			return $this->writeJsonWithGlobalFields(200, ['measurementSourcesArray' => $measurementSourcesArray],
				JSON_NUMERIC_CHECK);
		} else{
			$this->writeJsonWithoutGlobalFields(200, $measurementSourcesArray, JSON_NUMERIC_CHECK);
		}
	}
}
