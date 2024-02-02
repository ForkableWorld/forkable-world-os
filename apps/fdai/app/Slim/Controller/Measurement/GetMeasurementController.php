<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Measurement;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Http\Parameters\IncludeTagsParam;
use App\Properties\Measurement\MeasurementUnitIdProperty;
use App\Slim\Controller\GetController;
use App\Slim\Model\Measurement\AnonymousMeasurement;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\QMMeasurementExtended;
use App\Slim\View\Request\Measurement\GetMeasurementRequest;
use App\Slim\View\Request\QMRequest;
use App\Utils\APIHelper;
/** Measurements
 * @package App\Slim\Controller * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="1.2",
 *     resourcePath="/measurements",
 *     description="Measurements",
 *     produces="['application/json']"
 * )
 * @SWG\Model(
 *     id="Measurement",
 *     @SWG\Property(
 *         name="variable", type="string", required=true, description="Original name of the variable."
 *     ),
 *     @SWG\Property(
 *         name="source", type="string", required=true, description="The application or device that the measurement was
 *     recorded with."
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
class GetMeasurementController extends GetController {
	/**
	 * GET /measurements
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
	 *     path="measurements",
	 *     description="Get measurements for this user",
	 *     @SWG\Operations(
	 *         @SWG\Operation(
	 *             method="GET",
	 *             summary="Get measurements for this user",
	 *             notes="Get measurements for this user",
	 *             nickname="Measurements::get",
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
	 *                     name="source",
	 *                     description="The name of the source you want measurements for",
	 *                     paramType="query",
	 *                     required=false,
	 *                     type="string"
	 *                 ),
	 *                 @SWG\Parameter(
	 *                     name="unit",
	 *                     description="The unit your want the measurements in",
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
	 */
	public function get(){
		$this->setCacheControlHeader(5 * 60);  // TODO:  Figure out how we can increase this
		/** @var GetMeasurementRequest $req */
		if(!IncludeTagsParam::includeTags() && !QMRequest::getParam('groupingWidth')){
			// This slows down API requests to get tags all the time
			$measurements = QMMeasurement::getByRequest();
			/** @noinspection PhpUnhandledExceptionInspection */
			$measurements = $this->processMeasurements($measurements);
		} else{
			/** @var GetMeasurementRequest $req */
			$req = $this->getRequest(GetMeasurementRequest::class);
			$measurements = $req->handleGetRequest();
		}
		$this->returnMeasurements($measurements);
	}
	/**
	 * @param QMMeasurement[] $measurements
	 */
	protected function returnMeasurements(array $measurements){
		if(APIHelper::apiVersionIsAbove(3)){
			return $this->writeJsonWithGlobalFields(200, ['measurements' => $measurements], JSON_NUMERIC_CHECK);
		} else{
			$this->writeJsonWithoutGlobalFields(200, $measurements, JSON_NUMERIC_CHECK);
		}
	}
	/**
	 * @param QMMeasurement[] $measurements
	 * @return QMMeasurementExtended[]
	 */
	public function addExtendedPropertiesIfNecessary(array $measurements): array{
		$m = AnonymousMeasurement::getFirst($measurements);
		if($m && !isset($m->pngPath)){
			foreach($measurements as $key => $value){
				$measurements[$key] = $value->getExtended();
			}
		}
		foreach($measurements as $m){
			if(!$m->startTimeString){
				$m->startTimeString = $m->getStartAt();
			}
		}
		return $measurements;
	}
	/**
	 * @param QMMeasurement[] $measurements
	 * @return QMMeasurement[]
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	protected function convertUnitIfNecessary(array $measurements): array{
		if($unitId = MeasurementUnitIdProperty::fromRequest()){
			return QMMeasurement::convertToProvidedUnit($measurements, $unitId);
		}
		if($uv = $this->getUserVariable()){
			return $uv->convertMeasurementsToUserUnit($measurements);
		}
		return $measurements;
	}
	/**
	 * @param array $measurements
	 * @return QMMeasurement[]
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	protected function processMeasurements(array $measurements): array{
		$measurements = $this->addExtendedPropertiesIfNecessary($measurements);
		$measurements = $this->convertUnitIfNecessary($measurements);
		return $measurements;
	}
}
