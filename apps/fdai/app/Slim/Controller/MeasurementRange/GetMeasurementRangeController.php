<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\MeasurementRange;
use App\Models\Measurement;
use App\Slim\Controller\GetController;
use App\Slim\Middleware\QMAuth;
use App\Utils\APIHelper;
use InvalidArgumentException;
/** Measurements range
 * @package App\Slim\Controller * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="1.2",
 *     resourcePath="/measurementsRange",
 *     description="Measurements range",
 *     produces="['application/json']"
 * )
 * @SWG\Model(
 *     id="MeasurementsRange",
 *     @SWG\Property(
 *         name="lowerLimit", type="integer", required=true, description="The timestamp of the earliest measurement for
 *     a user."
 *     ),
 *     @SWG\Property(
 *         name="upperLimit", type="integer", required=true, description="The timestamp of the most recent measurement
 *     for a user."
 *     )
 * )
 */
class GetMeasurementRangeController extends GetController {
	/**
	 * GET /measurementsRange
	 * PURPOSE: Should return the timestamp of the oldest and newest measurement from a given source.
	 * DEMONSTRATED APPLICATION: https://quantimo.do/correlate/
	 * EXAMPLE USAGE IN CODE:
	 * EXAMPLE REQUEST: https://quantimo.do/api/measurementsRange
	 * EXAMPLE RESPONSE: {"lowerLimit":1263690300,"upperLimit":1403637840}
	 * TEST: Make sure that the lowerLimit equals the smallest `measurement timestamp for that user
	 *       and the upper limit equals the largest
	 * @SWG\Api(
	 *     path="measurementsRange",
	 *     description="Get measurements range for this user",
	 *     @SWG\Operations(
	 *         @SWG\Operation(
	 *             method="GET",
	 *             summary="Get measurements range for this user",
	 *             notes="Get measurements range for this user",
	 *             nickname="MeasurementsRange::getMeasurementsSourcesRange",
	 *             type="array",
	 *             @SWG\Items("MeasurementsRange"),
	 *             @SWG\Parameters(
	 *                 @SWG\Parameter(
	 *                     name="sources",
	 *                     description="Enter source name to limit to specific source (varchar)",
	 *                     paramType="query",
	 *                     required=false,
	 *                     type="string"
	 *                 ),
	 *                 @SWG\Parameter(
	 *                     name="user",
	 *                     description="If not specified, uses currently logged in user (bigint)",
	 *                     paramType="query",
	 *                     required=false,
	 *                     type="integer"
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
	 * @throws InvalidArgumentException
	 */
	public function get(){
		$this->setCacheControlHeader(5 * 60);
		$user = QMAuth::getQMUser()->l();
		$m = $user->measurements();
		$range['lowerLimit'] = $m->min(Measurement::FIELD_START_TIME);
		$range['upperLimit'] = $m->max(Measurement::FIELD_START_TIME);
		if(APIHelper::apiVersionIsAbove(3)){
			return $this->writeJsonWithGlobalFields(200, ['range' => $range]);
		} else{
			$this->writeJsonWithoutGlobalFields(200, $range);
		}
	}
}
