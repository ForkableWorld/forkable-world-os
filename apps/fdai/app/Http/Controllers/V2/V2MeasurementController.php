<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\V2;
use App\Http\Controllers\Controller;
use App\Http\Requests\Measurement\CreateMeasurementRequest;
use App\Http\Requests\Measurement\UpdateMeasurementRequest;
use App\Models\Measurement;
use App\Models\User;
use App\Services\MeasurementService;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LGeoIP;
use Quantimodo\Api\Exceptions\BadRequestException;
use Quantimodo\Api\Middleware\QMAuth;
use Quantimodo\Api\Model\GeoLocation;
/** Class MeasurementController
 * @package App\Http\Controllers
 */
class V2MeasurementController extends Controller {
	/**
	 * @param Guard $auth
	 * @param MeasurementService $measurementService
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Post(
	 *      path="/measurements/request_csv",
	 *      summary="Post Request for Measurements CSV",
	 *      tags={"Measurement"},
	 *      description="Use this endpoint to schedule a CSV export containing all user measurements to be emailed to
	 *     the user within 24 hours.", produces={"application/json"},
	 *      @SWG\Parameter(
	 *          name="access_token",
	 *          in="query",
	 *          description="User's OAuth2 access token",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Response(
	 *          response=200,
	 *          description="successful operation",
	 *          @SWG\Schema(type="integer")
	 *      ),
	 *      security={
	 *          {
	 *              "quantimodo_oauth2": {"basic"}
	 *          }
	 *      }
	 * )
	 */
	public function postCsvExportRequest(Guard $auth, MeasurementService $measurementService){
		/** @var User $user */
		$user = Auth::user();
		// TODO: Set clientId
		$clientId = null;
		$exportId = $measurementService->createExportRequestRecord($user, 'user', $clientId, 'csv');
		if(is_numeric($exportId)){
			return new JsonResponse([
				'success' => true,
				'message' => "Measurement export request submitted successfully",
				'status' => 201,
				'exportId' => $exportId,
			]);
		}
		return new JsonResponse([
			'success' => false,
			'message' => "Export request failed!  Please create a support ticket at http://help.quantimo.do.",
			'status' => 400,
		]);
	}
	/**
	 * @param Guard $auth
	 * @param MeasurementService $measurementService
	 * @return JsonResponse
	 * @throws BadRequestException
	 */
	public function postXlsExportRequest(Guard $auth, MeasurementService $measurementService){
		/** @var User $user */
		$user = Auth::user();
		// TODO: Set clientId
		$clientId = null;
		$exportId = $measurementService->createExportRequestRecord($user, 'user', $clientId, 'xls');
		if(is_numeric($exportId)){
			return new JsonResponse([
				'success' => true,
				'message' => "Measurement export request submitted successfully",
				'status' => 201,
				'exportId' => $exportId,
			]);
		}
		return new JsonResponse([
			'success' => false,
			'message' => "Export request failed!  Please create a support ticket at http://help.quantimo.do.",
			'status' => 400,
		]);
	}
	/**
	 * @param Guard $auth
	 * @param MeasurementService $measurementService
	 * @return JsonResponse
	 * @throws BadRequestException
	 */
	public function postPdfExportRequest(Guard $auth, MeasurementService $measurementService){
		/** @var User $user */
		$user = Auth::user();
		// TODO: Set clientId
		$clientId = null;
		$exportId = $measurementService->createExportRequestRecord($user, 'user', $clientId, 'pdf');
		if(is_numeric($exportId)){
			return new JsonResponse([
				'success' => true,
				'message' => "Measurement export request submitted successfully",
				'status' => 201,
				'exportId' => $exportId,
			]);
		}
		return new JsonResponse([
			'success' => false,
			'message' => "Export request failed!  Please create a support ticket at http://help.quantimo.do.",
			'status' => 400,
		]);
	}
	/**
	 * @param Request $request
	 * @param MeasurementService $measurementService
	 * @param Guard $auth
	 * @param Container $container
	 * @return JsonResponse
	 * @throws BindingResolutionException
	 * @SWG\Get(
	 *      path="/measurements",
	 *      summary="Get measurements for this user",
	 *      tags={"Measurement"},
	 *      description="Measurements are any value that can be recorded like daily steps, a mood rating, or apples
	 *     eaten.", produces={"application/json"},
	 *      @SWG\Parameter(
	 *          name="access_token",
	 *          in="query",
	 *          description="User's OAuth2 access token",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="user_id",
	 *          in="query",
	 *          description="ID of user that owns this measurement",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="client_id",
	 *          in="query",
	 *          description="The ID of the client application which originally stored the measurement",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="connector_id",
	 *          in="query",
	 *          description="The id for the connector data source from which the measurement was obtained",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="variable_id",
	 *          in="query",
	 *          description="ID of the variable for which we are creating the measurement records",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="source_name",
	 *          in="query",
	 *          description="Application or device used to record the measurement values",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="start_time",
	 *          in="query",
	 *          description="start time for the measurement event. Use ISO 8601 datetime format ",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="value",
	 *          in="query",
	 *          description="The value of the measurement after conversion to the default unit for that variable",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="unit_id",
	 *          in="query",
	 *          description="The default unit id for the variable",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="original_value",
	 *          in="query",
	 *          description="Unconverted value of measurement as originally posted (before conversion to default
	 *     unit)",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="original_unit_id",
	 *          in="query",
	 *          description="Unit id of the measurement as originally submitted",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="duration",
	 *          in="query",
	 *          description="Duration of the event being measurement in seconds",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="note",
	 *          in="query",
	 *          description="An optional note the user may include with their measurement",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="latitude",
	 *          in="query",
	 *          description="Latitude at which the measurement was taken",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="longitude",
	 *          in="query",
	 *          description="Longitude at which the measurement was taken",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="location",
	 *          in="query",
	 *          description="Optional human readable name for the location where the measurement was recorded",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="created_at",
	 *          in="query",
	 *          description="When the record was first created. Use ISO 8601 datetime format ",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="updated_at",
	 *          in="query",
	 *          description="When the record was last updated. Use ISO 8601 datetime format ",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="error",
	 *          in="query",
	 *          description="An error message if there is a problem with the measurement",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="limit",
	 *          in="query",
	 *          description="The LIMIT is used to limit the number of results returned. So if you have 1000 results,
	 *     but only want to the first 10, you would set this to 10 and offset to 0. The maximum limit is 200 records.",
	 *     required=false, type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="offset",
	 *          in="query",
	 *          description="OFFSET says to skip that many rows before beginning to return rows to the client. OFFSET 0
	 *     is the same as omitting the OFFSET clause. If both OFFSET and LIMIT appear, then OFFSET rows are skipped
	 *     before starting to count the LIMIT rows that are returned.", required=false, type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="sort",
	 *          in="query",
	 *          description="Sort by given field. If the field is prefixed with '-', it will sort in descending
	 *     order.",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Response(
	 *          response=200,
	 *          description="successful operation",
	 *          @SWG\Schema(
	 *              type="object",
	 *              @SWG\Property(
	 *                  property="success",
	 *                  type="boolean"
	 *              ),
	 *              @SWG\Property(
	 *                  property="data",
	 *                  type="array",
	 *                  @SWG\Items(ref="#/definitions/Measurement")
	 *              )
	 *          )
	 *      ),
	 *      security={
	 *          {
	 *              "quantimodo_oauth2": {"basic"}
	 *          }
	 *      }
	 * )
	 * @SWG\Get(
	 *      path="/application/measurements",
	 *      summary="Get measurements for all users using your application",
	 *      tags={"Application Endpoints"},
	 *      description="Measurements are any value that can be recorded like daily steps, a mood rating, or apples
	 *     eaten.", produces={"application/json"},
	 *      @SWG\Parameter(
	 *          name="access_token",
	 *          in="query",
	 *          description="Application's OAuth2 access token",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="client_id",
	 *          in="query",
	 *          description="The ID of the client application which originally stored the measurement",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="connector_id",
	 *          in="query",
	 *          description="The id for the connector data source from which the measurement was obtained",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="variable_id",
	 *          in="query",
	 *          description="ID of the variable for which we are creating the measurement records",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="source_name",
	 *          in="query",
	 *          description="Application or device used to record the measurement values",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="start_time",
	 *          in="query",
	 *          description="start time for the measurement event. Use ISO 8601 datetime format ",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="value",
	 *          in="query",
	 *          description="The value of the measurement after conversion to the default unit for that variable",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="unit_id",
	 *          in="query",
	 *          description="The default unit id for the variable",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="original_value",
	 *          in="query",
	 *          description="Unconverted value of measurement as originally posted (before conversion to default
	 *     unit)",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="original_unit_id",
	 *          in="query",
	 *          description="Unit id of the measurement as originally submitted",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="duration",
	 *          in="query",
	 *          description="Duration of the event being measurement in seconds",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="note",
	 *          in="query",
	 *          description="An optional note the user may include with their measurement",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="latitude",
	 *          in="query",
	 *          description="Latitude at which the measurement was taken",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="longitude",
	 *          in="query",
	 *          description="Longitude at which the measurement was taken",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="location",
	 *          in="query",
	 *          description="Optional human readable name for the location where the measurement was recorded",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="created_at",
	 *          in="query",
	 *          description="When the record was first created. Use ISO 8601 datetime format ",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="updated_at",
	 *          in="query",
	 *          description="When the record was last updated. Use ISO 8601 datetime format ",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="error",
	 *          in="query",
	 *          description="An error message if there is a problem with the measurement",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="limit",
	 *          in="query",
	 *          description="The LIMIT is used to limit the number of results returned. So if you have 1000 results,
	 *     but only want to the first 10, you would set this to 10 and offset to 0. The maximum limit is 200 records.",
	 *     required=false, type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="offset",
	 *          in="query",
	 *          description="OFFSET says to skip that many rows before beginning to return rows to the client. OFFSET 0
	 *     is the same as omitting the OFFSET clause. If both OFFSET and LIMIT appear, then OFFSET rows are skipped
	 *     before starting to count the LIMIT rows that are returned.", required=false, type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="sort",
	 *          in="query",
	 *          description="Sort by given field. If the field is prefixed with '-', it will sort in descending
	 *     order.",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Response(
	 *          response=200,
	 *          description="successful operation",
	 *          @SWG\Schema(
	 *              type="object",
	 *              @SWG\Property(
	 *                  property="success",
	 *                  type="boolean"
	 *              ),
	 *              @SWG\Property(
	 *                  property="data",
	 *                  type="array",
	 *                  @SWG\Items(ref="#/definitions/Measurement")
	 *              )
	 *          )
	 *      ),
	 * )
	 */
	public function index(Request $request, MeasurementService $measurementService, Guard $auth, Container $container){
		$filters = $this->getRequest()->all();
		$users = $container->make('oauthService')->getApplicationUsers();
		$filters['user_id'] = QMAuth::getAuthenticatedUserId();
		if(is_array($users)){
			$filters['user_id'] = $users;
		}
		$measurements = $measurementService->all($filters);
		$measurementsArr = [];
		foreach($measurements as $measurement){
			if(is_array($users)){
				$measurementsArr[$measurement->user_id][] = $measurement->toNamesArray();
			} else{
				$measurementsArr[] = $measurement->toNamesArray();
			}
		}
		return new JsonResponse([
			'success' => true,
			'data' => $measurementsArr,
		]);
	}
	/**
	 * @param CreateMeasurementRequest $request
	 * @param MeasurementService $measurementService
	 * @param Guard $auth
	 * @return JsonResponse
	 * @SWG\Definition (
	 *      definition="MeasurementValue",
	 *      required={"start_time", "value"},
	 *      @SWG\Property(
	 *          property="start_time",
	 *          description="When the measurement event occurred . Use ISO 8601 datetime format ",
	 *          type="string"
	 *      ),
	 *      @SWG\Property(
	 *          property="value",
	 *          description="Value for the measurement",
	 *          type="number",
	 *          format="float"
	 *      ),
	 *      @SWG\Property(
	 *          property="note",
	 *          description="An optional note the user may include with their measurement",
	 *          type="string"
	 *      )
	 * )
	 * @SWG\Definition (
	 *      definition="MeasurementPost",
	 *      required={"variable_id", "source_name", "unit_id", "measurements"},
	 *      @SWG\Property(
	 *          property="variable_id",
	 *          description="ID of the variable for the measurement as obtained from the GET variables endpoint",
	 *          type="integer",
	 *          format="int32"
	 *      ),
	 *      @SWG\Property(
	 *          property="unit_id",
	 *          description="Unit id for the measurement value as obtained from the GET units endpoint",
	 *          type="integer",
	 *          format="int32"
	 *      ),
	 *      @SWG\Property(
	 *          property="measurements",
	 *          description="measurements",
	 *          type="array",
	 *          @SWG\Items(ref="#/definitions/MeasurementValue")
	 *
	 *      )
	 * )
	 * @SWG\Post(
	 *      path="/measurements",
	 *      summary="Post a new set or update existing measurements to the database",
	 *      tags={"Measurement"},
	 *      description="You can submit or update multiple measurements in a measurements sub-array.  If the variable
	 *     these measurements correspond to does not already exist in the database, it will be automatically added.",
	 *     produces={"application/json"},
	 *      @SWG\Parameter(
	 *          name="access_token",
	 *          in="query",
	 *          description="User's OAuth2 access token",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="Measurement that should be stored",
	 *          required=false,
	 *          @SWG\Schema(ref="#/definitions/MeasurementPost")
	 *      ),
	 *      @SWG\Response(
	 *          response=200,
	 *          description="successful operation",
	 *          @SWG\Schema(
	 *              type="object",
	 *              @SWG\Property(
	 *                  property="success",
	 *                  type="boolean"
	 *              ),
	 *              @SWG\Property(
	 *                  property="data",
	 *                  type="array",
	 *                  @SWG\Items(ref="#/definitions/Measurement")
	 *              )
	 *          )
	 *      ),
	 *      security={
	 *          {
	 *              "quantimodo_oauth2": {"basic"}
	 *          }
	 *      }
	 * )
	 */
	public function store(CreateMeasurementRequest $request, MeasurementService $measurementService, Guard $auth){
		$input = $this->getRequest()->all();
		$variableData = [
			'variable_id' => $input['variable_id'],
			'source_name' => $input['source_name'],
			'unit_id' => $input['unit_id'],
			'user_id' => QMAuth::getAuthenticatedUserId(),
		];
		$location = GeoLocation::getLocationFromArrayOrRequest();
		if($location['default'] === false){
			$variableData['latitude'] = $location['lat'];
			$variableData['longitude'] = $location['lon'];
			$variableData['location'] = $location['city'] . '/' . $location['country'];
		}
		$createdMeasurements = [];
		foreach($input['measurements'] as $measurementArray){
			$measurementArrayWithVariableData = array_merge($variableData, $measurementArray);
			$measurement = Measurement::create($measurementArrayWithVariableData);
			$createdMeasurements[] = $measurement->toArray();
		}
		return new JsonResponse([
			'success' => true,
			'data' => $createdMeasurements,
		]);
	}
	/**
	 * @param int $id
	 * @param MeasurementService $measurementService
	 * @param Guard $auth
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Get(
	 *      path="/measurements/{id}",
	 *      summary="Get Measurement",
	 *      tags={"Measurement"},
	 *      description="Get Measurement",
	 *      produces={"application/json"},
	 *      @SWG\Parameter(
	 *          name="access_token",
	 *          in="query",
	 *          description="User's OAuth2 access token",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="id",
	 *          description="id of Measurement",
	 *          type="integer",
	 *          required=true,
	 *          in="path"
	 *      ),
	 *      @SWG\Response(
	 *          response=200,
	 *          description="successful operation",
	 *          @SWG\Schema(
	 *              type="object",
	 *              @SWG\Property(
	 *                  property="success",
	 *                  type="boolean"
	 *              ),
	 *              @SWG\Property(
	 *                  property="data",
	 *                  ref="#/definitions/Measurement"
	 *              )
	 *          )
	 *      ),
	 *      security={
	 *          {
	 *              "quantimodo_oauth2": {"basic"}
	 *          }
	 *      }
	 * )
	 */
	public function show($id, MeasurementService $measurementService, Guard $auth){
		$measurement = $measurementService->getWithRelationsOfUser($id, QMAuth::getAuthenticatedUserId());
		if(!$measurement){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => $measurement->toNamesArray(),
		]);
	}
	/**
	 * @param int $id
	 * @param UpdateMeasurementRequest $request
	 * @param MeasurementService $measurementService
	 * @param Guard $auth
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Put(
	 *      path="/measurements/{id}",
	 *      summary="Update Measurement",
	 *      tags={"Measurement"},
	 *      description="Update Measurement",
	 *      produces={"application/json"},
	 *      @SWG\Parameter(
	 *          name="access_token",
	 *          in="query",
	 *          description="User's OAuth2 access token",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="id",
	 *          description="id of Measurement",
	 *          type="integer",
	 *          required=true,
	 *          in="path"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="Measurement that should be updated",
	 *          required=false,
	 *          @SWG\Schema(ref="#/definitions/Measurement")
	 *      ),
	 *      @SWG\Response(
	 *          response=200,
	 *          description="successful operation",
	 *          @SWG\Schema(
	 *              type="object",
	 *              @SWG\Property(
	 *                  property="success",
	 *                  type="boolean"
	 *              ),
	 *              @SWG\Property(
	 *                  property="data",
	 *                  type="string"
	 *              )
	 *          )
	 *      ),
	 *      security={
	 *          {
	 *              "quantimodo_oauth2": {"basic"}
	 *          }
	 *      }
	 * )
	 */
	public function update($id, UpdateMeasurementRequest $request, MeasurementService $measurementService, Guard $auth){
		$result =
			$measurementService->updateRichOfUser($this->getRequest()->all(), $id, QMAuth::getAuthenticatedUserId());
		if($result === false){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => "Measurement updated successfully",
		]);
	}
	/**
	 * @param int $id
	 * @param MeasurementService $measurementService
	 * @param Guard $auth
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Delete(
	 *      path="/measurements/{id}",
	 *      summary="Delete Measurement",
	 *      tags={"Measurement"},
	 *      description="Delete Measurement",
	 *      produces={"application/json"},
	 *      @SWG\Parameter(
	 *          name="access_token",
	 *          in="query",
	 *          description="User's OAuth2 access token",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="id",
	 *          description="id of Measurement",
	 *          type="integer",
	 *          required=true,
	 *          in="path"
	 *      ),
	 *      @SWG\Response(
	 *          response=200,
	 *          description="successful operation",
	 *          @SWG\Schema(
	 *              type="object",
	 *              @SWG\Property(
	 *                  property="success",
	 *                  type="boolean"
	 *              ),
	 *              @SWG\Property(
	 *                  property="data",
	 *                  type="string"
	 *              )
	 *          )
	 *      ),
	 *      security={
	 *          {
	 *              "quantimodo_oauth2": {"basic"}
	 *          }
	 *      }
	 * )
	 */
	public function destroy($id, MeasurementService $measurementService, Guard $auth){
		$result = $measurementService->deleteOfUser($id, QMAuth::getAuthenticatedUserId());
		if($result === false){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => "Measurement deleted successfully",
		]);
	}
}
