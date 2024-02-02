<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\MeasurementSource;
use App\Exceptions\QMException;
use App\Exceptions\UnauthorizedException;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Storage\DB\Writable;
use App\Utils\APIHelper;
use PDO;
/** Measurements sources
 * @package App\Slim\Controller * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="1.2",
 *     resourcePath="/measurementSources",
 *     description="Applications or devices which record measurements",
 *     produces="['application/json']"
 * )
 * @SWG\Model(
 *     id="MeasurementSource",
 *     @SWG\Property(
 *         name="name", type="string", required=true, description="Name of the application or device."
 *     )
 * )
 */
class CreateMeasurementSourceController extends PostController {
	/**
	 * Error constants.
	 */
	public const ERROR_ADD_SOURCES_ADMIN_ONLY = 'Only admins can add sources';
	/**
	 * POST /measurementSources
	 * DEMO APPLICATION:  Not yet in production.
	 * EXAMPLE USAGE IN CODE:  Not yet in use.
	 * EXAMPLE REQUEST:  https://quantimo.do/api/measurementSources
	 * EXAMPLE REQUEST PAYLOAD:  [{"measurementSources":"name":"Fitbit"}]
	 * Feature: Add a new application or device to the `quantimodo`.`sources` table.
	 * In order to allow automated registration by developers and device manufacturers.
	 * As an application developer
	 * I want to fill out a form and be able to submit and access measurements in the database
	 * Scenario: Application developer fills out a form with their name
	 * s     * Given the name is not already present in the `quantimodo`.`sources` table
	 * And the devloper fills out all required fields
	 * When the developer presses submit
	 * And the name field is filled out
	 * And the name is not already in the `quantimodo`.`sources` table
	 * Then a new record is created in the `quantimodo`.`sources` table
	 * And the name field in the new record matches the name the posted payload
	 * @SWG\Api(
	 *     path="measurementSources",
	 *     description="Add a new application or device",
	 *     @SWG\Operations(
	 *         @SWG\Operation(
	 *             method="POST",
	 *             summary="Create measurement sources",
	 *             notes="Set measurement source",
	 *             nickname="MeasurementSources::post",
	 *             @SWG\Parameters(
	 *                 @SWG\Parameter(
	 *                     name="MeasurementSources",
	 *                     description="An array of measurement source apps that you want to add to QuantiModo",
	 *                     paramType="body",
	 *                     required=true,
	 *                     type="array",
	 *                     @SWG\Items("MeasurementSource"),
	 *                 )
	 *             ),
	 *             @SWG\Authorizations(oauth2={
	 *                 {"scope": "basic", "description": "Clear cache TODO: What's this mean?"}
	 *             }),
	 *             @SWG\ResponseMessages(
	 *                 @SWG\ResponseMessage(code=401, message="Not authenticated")
	 *             )
	 *         )
	 *     )
	 * )
	 */
	public function post(){
		$app = $this->getApp();// If the authenticated user isn't an admin he cannot add new sources
		$user = QMAuth::getQMUserIfSet();
		if(!$user->isAdmin()){
			throw new UnauthorizedException(self::ERROR_ADD_SOURCES_ADMIN_ONLY);
		}
		$measurementSources = $app->getRequestJsonBodyAsArray(false, true);
		$numMeasurementSources = count($measurementSources);
		if(!$numMeasurementSources){
			throw new QMException(400, "No data was sent with the request");
		}
		$sqlInsert = 'INSERT IGNORE INTO `sources` (client_id, name, created_at, updated_at) ';
		$valuesArray = [];
		for($i = 0; $i < $numMeasurementSources; $i++){
			$valuesArray[] = "(:clientId, :name$i, NOW(), NOW())";
		}
		$sqlInsert .= 'VALUES' . implode(',', $valuesArray);
		// Prepare query
		$dbh = Writable::pdo();
		$query = $dbh->prepare($sqlInsert);
		$i = 0;
		foreach($measurementSources as $measurementSource){
			$query->bindParam(":name$i", $measurementSource['name']);
			$query->bindParam(":clientId", BaseClientIdProperty::fromMemory());
			$i++;
		}
		$query->execute();
		$sql = 'SELECT name, created_at, updated_at FROM `sources`';
		$measurementSources = $dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		if(APIHelper::apiVersionIsAbove(3)){
			return $this->writeJsonWithGlobalFields(201, ['measurementSources' => $measurementSources], JSON_NUMERIC_CHECK);
		} else{
			$this->writeJsonWithoutGlobalFields(201, $measurementSources, JSON_NUMERIC_CHECK);
		}
	}
}
