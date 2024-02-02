<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\V2;
use App\Http\Controllers\Controller;
use App\Http\Requests\Correlation\CreateCorrelationRequest;
use App\Http\Requests\Correlation\UpdateCorrelationRequest;
use App\Services\CorrelationService;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Quantimodo\Api\Exceptions\BadRequestException;
use Quantimodo\Api\Middleware\QMAuth;
/** Class CorrelationController
 * @package App\Http\Controllers
 */
class V2CorrelationController extends Controller {
	/**
	 * @param Request $request
	 * @param CorrelationService $correlationService
	 * @param Guard $auth
	 * @return JsonResponse
	 * @SWG\Get(
	 *      path="/user_variable_relationships",
	 *      summary="Get all VariableRelationships",
	 *      tags={"UserVariableRelationship"},
	 *      description="Get all VariableRelationships",
	 *      produces={"application/json"},
	 *      @SWG\Parameter(
	 *          name="access_token",
	 *          in="query",
	 *          description="User's OAuth2 access token",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="timestamp",
	 *          in="query",
	 *          description="Time at which correlation was calculated",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="user_id",
	 *          in="query",
	 *          description="ID of user that owns this correlation",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="correlation",
	 *          in="query",
	 *          description="Pearson correlation coefficient between cause and effect measurements",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="cause_variable_id",
	 *          in="query",
	 *          description="variable ID of the predictor variable for which the user desires user_variable_relationships",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="effect_variable_id",
	 *          in="query",
	 *          description="variable ID of the outcome variable for which the user desires user_variable_relationships",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="onset_delay",
	 *          in="query",
	 *          description="User estimated or default time after cause measurement before a perceivable effect is
	 *     observed", required=false, type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="duration_of_action",
	 *          in="query",
	 *          description="Time over which the cause is expected to produce a perceivable effect following the onset
	 *     delay", required=false, type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="number_of_pairs",
	 *          in="query",
	 *          description="Number of points that went into the correlation calculation",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="value_predicting_high_outcome",
	 *          in="query",
	 *          description="cause value that predicts an above average effect value (in default unit for predictor
	 *     variable)", required=false, type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="value_predicting_low_outcome",
	 *          in="query",
	 *          description="cause value that predicts a below average effect value (in default unit for predictor
	 *     variable)", required=false, type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="optimal_pearson_product",
	 *          in="query",
	 *          description="Optimal Pearson Product",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="vote",
	 *          in="query",
	 *          description="Vote",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="statistical_significance",
	 *          in="query",
	 *          description="A function of the effect size and sample size",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="cause_unit",
	 *          in="query",
	 *          description="Unit of the predictor variable",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="cause_unit_id",
	 *          in="query",
	 *          description="Unit ID of the predictor variable",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="cause_changes",
	 *          in="query",
	 *          description="Cause changes",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="effect_changes",
	 *          in="query",
	 *          description="Effect changes",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="qm_score",
	 *          in="query",
	 *          description="QM Score",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="error",
	 *          in="query",
	 *          description="error",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="created_at",
	 *          in="query",
	 *          description="When the record was first created. Use ISO 8601 datetime format",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="updated_at",
	 *          in="query",
	 *          description="When the record in the database was last updated. Use ISO 8601 datetime format",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="reverse_pearson_correlation_coefficient",
	 *          in="query",
	 *          description="UserVariableRelationship when cause and effect are reversed. For any causal relationship, the forward
	 *     correlation should exceed the reverse correlation", required=false, type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="predictive_pearson_correlation_coefficient",
	 *          in="query",
	 *          description="Predictive Pearson UserVariableRelationship Coefficient",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="limit",
	 *          in="query",
	 *          description="Limit the number of results returned",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="offset",
	 *          in="query",
	 *          description="Records from give Offset",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="sort",
	 *          in="query",
	 *          description="Sort records by given field",
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
	 *                  @SWG\Items(ref="#/definitions/UserVariableRelationship")
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
	public function index(Request $request, CorrelationService $correlationService, Guard $auth){
		$filters = array_merge($this->getRequest()->all(), ['user_id' => QMAuth::getAuthenticatedUserId()]);
		$correlations = $correlationService->all($filters);
		$correlationsArr = [];
		foreach($correlations as $correlation){
			$correlationsArr[] = $correlation->toNamesArray();
		}
		return new JsonResponse([
			'success' => true,
			'data' => $correlationsArr,
		]);
	}
	/**
	 * @param CreateCorrelationRequest $request
	 * @param CorrelationService $correlationService
	 * @param Guard $auth
	 * @return JsonResponse
	 * @SWG\Post(
	 *      path="/user_variable_relationships",
	 *      summary="Store UserVariableRelationship",
	 *      tags={"UserVariableRelationship"},
	 *      description="Store UserVariableRelationship",
	 *      produces={"application/json"},
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
	 *          description="UserVariableRelationship that should be stored",
	 *          required=false,
	 *          @SWG\Schema(ref="#/definitions/UserVariableRelationship")
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
	 *                  ref="#/definitions/UserVariableRelationship"
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
	public function store(CreateCorrelationRequest $request, CorrelationService $correlationService, Guard $auth){
		$correlation = $correlationService->create(array_merge($this->getRequest()->all(),
			['user_id' => QMAuth::getAuthenticatedUserId()]));
		return new JsonResponse([
			'success' => true,
			'data' => $correlation->toArray(),
		]);
	}
	/**
	 * @param int $id
	 * @param CorrelationService $correlationService
	 * @param Guard $auth
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Get(
	 *      path="/user_variable_relationships/{id}",
	 *      summary="Get UserVariableRelationship Details",
	 *      tags={"UserVariableRelationship"},
	 *      description="Get UserVariableRelationship",
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
	 *          description="id of UserVariableRelationship",
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
	 *                  ref="#/definitions/UserVariableRelationship"
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
	public function show($id, CorrelationService $correlationService, Guard $auth){
		$correlation = $correlationService->getWithRelationsOfUser($id, QMAuth::getAuthenticatedUserId());
		if(empty($correlation)){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => $correlation->toNamesArray(),
		]);
	}
	/**
	 * @param int $id
	 * @param UpdateCorrelationRequest $request
	 * @param CorrelationService $correlationService
	 * @param Guard $auth
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Put(
	 *      path="/user_variable_relationships/{id}",
	 *      summary="Update UserVariableRelationship",
	 *      tags={"UserVariableRelationship"},
	 *      description="Update UserVariableRelationship",
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
	 *          description="id of UserVariableRelationship",
	 *          type="integer",
	 *          required=true,
	 *          in="path"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="UserVariableRelationship that should be updated",
	 *          required=false,
	 *          @SWG\Schema(ref="#/definitions/UserVariableRelationship")
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
	public function update($id, UpdateCorrelationRequest $request, CorrelationService $correlationService, Guard $auth){
		$result =
			$correlationService->updateRichOfUser($this->getRequest()->all(), $id, QMAuth::getAuthenticatedUserId());
		if($result === false){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => "User Variable Relationship updated successfully",
		]);
	}
	/**
	 * @param int $id
	 * @param CorrelationService $correlationService
	 * @param Guard $auth
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Delete(
	 *      path="/user_variable_relationships/{id}",
	 *      summary="Delete UserVariableRelationship",
	 *      tags={"UserVariableRelationship"},
	 *      description="Delete UserVariableRelationship",
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
	 *          description="id of UserVariableRelationship",
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
	public function destroy($id, CorrelationService $correlationService, Guard $auth){
		$result = $correlationService->deleteOfUser($id, QMAuth::getAuthenticatedUserId());
		if($result === false){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => "User Variable Relationship deleted successfully",
		]);
	}
}
