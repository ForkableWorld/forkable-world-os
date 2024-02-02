<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Measurement;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\WrongUnitException;
use App\Slim\View\Request\Measurement\GetMeasurementRequest;
/** Measurements
 * @package App\Slim\Controller * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="1.2",
 *     resourcePath="/v1/measurements/daily",
 *     description="Daily Measurements",
 *     produces="['application/json']"
 * )
 * @SWG\Model(
 *     id="Measurement",
 *     @SWG\Property(
 *         name="variable", type="string", required=true, description="Original name of the variable."
 *     ),
 *     @SWG\Property(
 *         name="timestamp", type="integer", format="int64", required=true, description="Time at which the measurement
 *     event occurred."
 *     ),
 *     @SWG\Property(
 *         name="value", type="number", format="double", required=true, description="The value of the measurement
 *     event."
 *     ),
 *     @SWG\Property(
 *         name="unit", type="string", required=true, description="The abbreviated name for the unit in which the
 *     measurement value is returned."
 *     )
 * )
 */
class GetDailyMeasurementController extends GetMeasurementController {
	/**
	 * GET /v1/measurements/daily
	 * PURPOSE: Get filtered, aggregated, and filled measurements for a given variable for a given user.
	 * The can then be used for time-series visualization and other analysis.
	 * Feature: Get measurements from the database
	 *     In order graph a variable longitudinally
	 *     As a user
	 *     I want to see how different life variables change over time
	 *     Scenario: User goes to https://quantimo.do/analyze/
	 *       Given they entered user: quantimodo pw: quantimodo
	 *         And they have some Overall Mood measurements from MoodiModo
	 *        When the user selects Overall Mood from MoodiModo
	 *         And this request is sent:
	 * https://quantimo.do/api/measurements?variableName=Overall%20Mood&startTime=1263690300&endTime=1404621540&groupingWidth=86400&groupingTimezone=America%2FChicago&source=MoodiModo
	 * Then a json array is returned And it starts with something like this: [{"source":"MoodiModo","variable":"Overall
	 * Mood","timestamp":1334491200,"value":3,"unit":"\/5","humanTime":{"date":"2012-04-15
	 * 12:00:00","timezone_type":1,"timezone":"+00:00"}},{"source":"MoodiModo","variable":... And every element in the
	 * array has a unique timestamp And every timestamp is greater than the preceding timestamp Feature: Tagging of
	 * Variables with Proportional Ingredients In order to know total intake of specific nutrients when just tracking
	 * specific foods As a malnourished user I want to see user_variable_relationships between my nutrient intake and health metrics
	 * Scenario: UserVariableRelationship calculations are initiated for sugar variable Given the user has measurements for 2 cans
	 * of a "coke" variable And the coke variable is tagged with 30 g of sugar per can When measurements are obtained
	 * for the sugar Then there is also a 30 g measurement obtained for each can measurement
	 * @SWG\Api(
	 *     path="/v1/measurements/daily",
	 *     description="Get daily measurements for this user",
	 *     @SWG\Operations(
	 *         @SWG\Operation(
	 *             method="GET",
	 *             summary="Get daily measurements for this user",
	 *             notes="Get daily measurements for this user",
	 *             nickname="DailyMeasurements::get",
	 *             type="array",
	 *             @SWG\Items("Measurement"),
	 *             @SWG\Parameters(
	 *                 @SWG\Parameter(
	 *                     name="variableName",
	 *                     description="Name of the variable you want measurements for",
	 *                     paramType="query",
	 *                     required=true,
	 *                     type="string",
	 *                     defaultValue="Overall Mood"
	 *                 ),
	 *                 @SWG\Parameter(
	 *                     name="unit",
	 *                     description="The unit you want the measurements in",
	 *                     paramType="query",
	 *                     required=false,
	 *                     type="string"
	 *                 ),
	 *                 @SWG\Parameter(
	 *                     name="startTime",
	 *                     description="The lower limit of measurements returned (Epoch)",
	 *                     paramType="query",
	 *                     required=false,
	 *                     type="string"
	 *                 ),
	 *                 @SWG\Parameter(
	 *                     name="endTime",
	 *                     description="The upper limit of measurements returned (Epoch)",
	 *                     paramType="query",
	 *                     required=false,
	 *                     type="string"
	 *                 ),
	 *                 @SWG\Parameter(
	 *                     name="groupingWidth",
	 *                     description="The time (in seconds) over which measurements are grouped together",
	 *                     paramType="query",
	 *                     required=false,
	 *                     type="integer"
	 *                 ),
	 *                 @SWG\Parameter(
	 *                     name="groupingTimezone",
	 *                     description="The timezone you want the measurements to be in",
	 *                     paramType="query",
	 *                     required=false,
	 *                     type="string"
	 *                 )
	 *             ),
	 *             @SWG\Authorizations(oauth2={
	 *                 {"scope": "basic", "description": "Clear cache"}
	 *             }),
	 *             @SWG\ResponseMessages(
	 *                 @SWG\ResponseMessage(code=401, message="Not authenticated")
	 *             )
	 *         )
	 *     )
	 * )
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 * @throws WrongUnitException
	 */
	public function get(){
		$this->setCacheControlHeader(5 * 60);
		/** @var GetMeasurementRequest $req */
		$req = $this->getRequest(GetMeasurementRequest::class);
		$measurements = $req->getDailyMeasurements();
		$measurements = $this->processMeasurements($measurements);
		$this->returnMeasurements($measurements);
	}
}
