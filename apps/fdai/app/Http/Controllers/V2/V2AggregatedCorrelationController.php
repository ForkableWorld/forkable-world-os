<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\V2;
use App\Http\Controllers\Controller;
use App\Services\AggregatedCorrelationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Quantimodo\Api\Exceptions\BadRequestException;
/** Class AggregatedCorrelationController
 * @package App\Http\Controllers
 */
class V2AggregatedCorrelationController extends Controller {
	/**
	 * @param Request $request
	 * @param AggregatedCorrelationService $aggregatedCorrelationService
	 * @return JsonResponse
	 * @SWG\Get(
	 *      path="/aggregatedCorrelations",
	 *      summary="Get all AggregatedCorrelations",
	 *      tags={"AggregatedCorrelation"},
	 *      description="Get all AggregatedCorrelations",
	 *      produces={"application/json"},
	 *      @SWG\Parameter(
	 *          name="access_token",
	 *          in="query",
	 *          description="User's OAuth2 access token",
	 *          required=false,
	 *          type="string"
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
	 *          description="Variable ID of the predictor variable for which the user desires user_variable_relationships",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="effect_variable_id",
	 *          in="query",
	 *          description="Variable ID of the outcome variable for which the user desires user_variable_relationships",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="onset_delay",
	 *          in="query",
	 *          description="User estimated (or default number of seconds) after cause measurement before a perceivable
	 *     effect is observed", required=false, type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="duration_of_action",
	 *          in="query",
	 *          description="Number of seconds over which the predictor variable event is expected to produce a
	 *     perceivable effect following the onset delay", required=false, type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="number_of_pairs",
	 *          in="query",
	 *          description="Number of predictor/outcome data points used in the analysis",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="value_predicting_high_outcome",
	 *          in="query",
	 *          description="Predictor daily aggregated measurement value that predicts an above average effect
	 *     measurement value (in default unit for predictor variable)", required=false, type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="value_predicting_low_outcome",
	 *          in="query",
	 *          description="Predictor daily aggregated measurement value that predicts a below average effect
	 *     measurement value (in default unit for outcome variable)", required=false, type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="optimal_pearson_product",
	 *          in="query",
	 *          description="Optimal Pearson Product",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="number_of_users",
	 *          in="query",
	 *          description="Number of users whose data was used in this aggregation",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="number_of_correlations",
	 *          in="query",
	 *          description="Number of correlational analyses used in this aggregation",
	 *          required=false,
	 *          type="integer"
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
	 *          description="Abbreviated unit name for the predictor variable",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="cause_unit_id",
	 *          in="query",
	 *          description="Unit ID for the predictor variable",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="cause_changes",
	 *          in="query",
	 *          description="Number of times that the predictor time series changes",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="effect_changes",
	 *          in="query",
	 *          description="Number of times that the predictor time series changes",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="aggregate_qm_score",
	 *          in="query",
	 *          description="Aggregated QM Score which is directly proportional with the relevance of each predictor or
	 *     outcome", required=false, type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="created_at",
	 *          in="query",
	 *          description="Date at which the analysis was first performed",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="updated_at",
	 *          in="query",
	 *          description="Date at which the analysis was last updated",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="status",
	 *          in="query",
	 *          description="Indicates whether an analysis is up to date (UPDATED), needs to be updated (WAITING), or
	 *     had an error (ERROR)", required=false, type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="error_message",
	 *          in="query",
	 *          description="Message describing any problems encountered during the analysis",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="last_successful_update_time",
	 *          in="query",
	 *          description="Last analysis time",
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
	 *                  @SWG\Items(ref="#/definitions/AggregatedCorrelation")
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
	public function index(Request $request, AggregatedCorrelationService $aggregatedCorrelationService){
		$aggregatedCorrelations = $aggregatedCorrelationService->all($this->getRequest()->all());
		$aggregatedCorrelationsArr = [];
		foreach($aggregatedCorrelations as $aggregatedCorrelation){
			$aggregatedCorrelationsArr[] = $aggregatedCorrelation->getLogMetaData();
		}
		return new JsonResponse([
			'success' => true,
			'data' => $aggregatedCorrelationsArr,
		]);
	}
	/**
	 * @param AggregatedCorrelationService $aggregatedCorrelationService
	 * @param int $id
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Get(
	 *      path="/aggregatedCorrelations/{id}",
	 *      summary="Get AggregatedCorrelation",
	 *      tags={"AggregatedCorrelation"},
	 *      description="Get AggregatedCorrelation",
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
	 *          description="id of AggregatedCorrelation",
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
	 *                  ref="#/definitions/AggregatedCorrelation"
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
	public function show($id, AggregatedCorrelationService $aggregatedCorrelationService){
		$aggregatedCorrelation = $aggregatedCorrelationService->getWithRelations($id);
		if(empty($aggregatedCorrelation)){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => $aggregatedCorrelation->toArray(),
		]);
	}
	/**
	 * @param int $id
	 * @param AggregatedCorrelationService $aggregatedCorrelationService
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Delete(
	 *      path="/aggregatedCorrelations/{id}",
	 *      summary="Delete AggregatedCorrelation",
	 *      tags={"AggregatedCorrelation"},
	 *      description="Delete AggregatedCorrelation",
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
	 *          description="id of AggregatedCorrelation",
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
	public function destroy($id, AggregatedCorrelationService $aggregatedCorrelationService){
		$result = $aggregatedCorrelationService->delete($id);
		if($result === false){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => "AggregatedCorrelation deleted successfully",
		]);
	}
}
