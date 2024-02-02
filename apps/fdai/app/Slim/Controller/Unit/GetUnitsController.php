<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Unit;
use App\Slim\Controller\GetController;
use App\Slim\Model\QMUnit;
use App\Utils\APIHelper;
/** Variable measurement units
 * @package App\Slim\Controller * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="1.2",
 *     resourcePath="/units",
 *     description="Variable measurement units",
 *     produces="['application/json']"
 * )
 * @SWG\Model(
 *     id="ConversionStep",
 *     @SWG\Property(
 *         name="operation", type="string", enum="['MULTIPLY', 'ADD']", required=true, description=""
 *     ),
 *     @SWG\Property(
 *         name="value", type="double", required=true, description=""
 *     ),
 * )
 * @SWG\Model(
 *     id="Unit",
 *     @SWG\Property(
 *         name="name", type="string", required=true, description="Unit name"
 *     ),
 *     @SWG\Property(
 *         name="abbreviatedName", type="string", required=true, description="Unit abbreviation"
 *     ),
 *     @SWG\Property(
 *         name="category", type="string", enum="['Distance', 'Duration', 'Energy', 'Frequency', 'Miscellany',
 *     'Pressure', 'Proportion', 'Rating', 'Temperature', 'Volume', 'Weight']", required=true, description="Unit
 *     category"
 *     ),
 *     @SWG\Property(
 *         name="minimum", type="double", enum="['number', '-Infinity']", required=true, description="Unit minimum
 *     value"
 *     ),
 *     @SWG\Property(
 *         name="maximum", type="double", enum="['number', 'Infinity']", required=true, description="Unit maximum
 *     value"
 *     ),
 *     @SWG\Property(
 *         name="conversionSteps", type="array", @SWG\Items("ConversionStep"), required=true, description="Conversion
 *     steps list"
 *     )
 * )
 */
class GetUnitsController extends GetController {
	/**
	 * Get a list of available units from the database
	 * DEMO APPLICATION:  https://quantimo.do/correlate/
	 * EXAMPLE USAGE IN CODE:
	 * https://github.com/mikepsinn/QuantiModo-WPLMS/blob/befbfece31ee0a79c60ef621496ceaddfe4a52d7/wp-content/themes/wplms-qm/js/correlate.js#L591
	 * EXAMPLE REQUEST:  https://quantimo.do/api/units
	 * @SWG\Api(
	 *     path="units",
	 *     description="Get Units",
	 *     @SWG\Operations(
	 *         @SWG\Operation(
	 *             method="GET",
	 *             summary="Get all available units",
	 *             notes="Get all available units",
	 *             type="array",
	 *             @SWG\Items("Unit"),
	 *             nickname="Units::get",
	 *             @SWG\Parameters(
	 *                 @SWG\Parameter(
	 *                     name="unitName",
	 *                     description="Unit name",
	 *                     paramType="query",
	 *                     required=false,
	 *                     type="string"
	 *                 ),
	 *                 @SWG\Parameter(
	 *                     name="unitAbbreviatedName",
	 *                     description="Unit abbreviation",
	 *                     paramType="query",
	 *                     required=false,
	 *                     type="string"
	 *                 ),
	 *                 @SWG\Parameter(
	 *                     name="categoryName",
	 *                     description="Unit category",
	 *                     paramType="query",
	 *                     required=false,
	 *                     type="string"
	 *                 )
	 *             ),
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
		$unitArray = QMUnit::getUnits();
		if(APIHelper::apiVersionIsAbove(3)){
			return $this->writeJsonWithGlobalFields(200, ['units' => $unitArray]);
		} else{
			$this->writeJsonWithoutGlobalFields(200, $unitArray, JSON_NUMERIC_CHECK);
		}
	}
}
