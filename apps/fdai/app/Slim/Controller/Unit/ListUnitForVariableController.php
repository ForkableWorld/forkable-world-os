<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Unit;
use App\Exceptions\QMException;
use App\Slim\Controller\GetController;
use App\Slim\Model\QMUnit;
use App\Slim\View\Request\Unit\ListUnitForVariableRequest;
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
class ListUnitForVariableController extends GetController {
	/**
	 * Get a list of all possible units to use for a given variable.
	 * @todo This has to go, seems much more logical to handle this client side.
	 * DEMO APPLICATION: https://quantimo.do/correlate/ when you click the "+" Symbol to add a measurement
	 * EXAMPLE USAGE IN CODE:
	 *     https://github.com/mikepsinn/QuantiModo-WPLMS/blob/687d0478c6dab7af9e23a6e71923f0ccfe2c5a62/wp-content/themes/wplms-qm/js/bargraph.js#L261
	 *     EXAMPLE REQUEST:  https://quantimo.do/api/unitsVariable?variable=Back%20Pain Feature:
	 * @SWG\Api(
	 *     path="unitsVariable",
	 *     description="Get a list of all possible units to use for a given variable",
	 *     @SWG\Operations(
	 *         @SWG\Operation(
	 *             method="GET",
	 *             summary="Get all available units for variable",
	 *             notes="Get all available units for variable",
	 *             type="array",
	 *             @SWG\Items("Unit"),
	 *             nickname="Units::getVariable",
	 *             @SWG\Parameters(
	 *                 @SWG\Parameter(
	 *                     name="variable",
	 *                     description="Variable name",
	 *                     paramType="query",
	 *                     required=true,
	 *                     type="string",
	 *                     defaultValue="Back Pain"
	 *                 ),
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
		/** @var ListUnitForVariableRequest $request */
		$request = $this->getRequest();
		if($request->getVariableName()){
			$units = QMUnit::getUnitsForVariableByVariableNameOrId($request->getVariableName());
		}
		if(!isset($units)){
			throw new QMException(QMException::CODE_BAD_REQUEST, 'Please provide valid variableName');
		}
		if(APIHelper::apiVersionIsAbove(3)){
			return $this->writeJsonWithGlobalFields(200, ['units' => $units], JSON_NUMERIC_CHECK);
		} else{
			$this->writeJsonWithoutGlobalFields(200, $units, JSON_NUMERIC_CHECK);
		}
	}
}
