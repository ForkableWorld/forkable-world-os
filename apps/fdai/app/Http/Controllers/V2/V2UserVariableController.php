<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\V2;
use App\Http\Controllers\Controller;
use App\Http\Requests\Variable\CreateUserVariableRequest;
use App\Services\UserVariableService;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Quantimodo\Api\Exceptions\BadRequestException;
use Quantimodo\Api\Middleware\QMAuth;
/** Class UserVariableController
 * @package App\Http\Controllers
 */
class V2UserVariableController extends Controller {
	/**
	 * @param Request $request
	 * @param UserVariableService $userVariableService
	 * @param Guard $auth
	 * @param Container $container
	 * @return JsonResponse
	 * @SWG\Get(
	 *      path="/userVariables",
	 *      summary="Get all UserVariables",
	 *      tags={"UserVariable"},
	 *      description="Get all UserVariables",
	 *      produces={"application/json"},
	 *      @SWG\Parameter(
	 *          name="access_token",
	 *          in="query",
	 *          description="User's OAuth2 access token",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="client_id",
	 *          in="query",
	 *          description="The ID of the client application which last created or updated this user variable",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="parent_id",
	 *          in="query",
	 *          description="ID of the parent variable if this variable has any parent",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="variable_id",
	 *          in="query",
	 *          description="ID of variable",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="user_id",
	 *          in="query",
	 *          description="User ID",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="default_unit_id",
	 *          in="query",
	 *          description="D of unit to use for this variable",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="minimum_allowed_value",
	 *          in="query",
	 *          description="Minimum reasonable value for this variable (uses default unit)",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="maximum_allowed_value",
	 *          in="query",
	 *          description="Maximum reasonable value for this variable (uses default unit)",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="filling_value",
	 *          in="query",
	 *          description="Value for replacing null measurements",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="join_with",
	 *          in="query",
	 *          description="The Variable this Variable should be joined with. If the variable is joined with some
	 *     other variable then it is not shown to user in the list of variables", required=false, type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="onset_delay",
	 *          in="query",
	 *          description="Estimated number of seconds that pass before a stimulus produces a perceivable effect",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="duration_of_action",
	 *          in="query",
	 *          description="Estimated duration of time following the onset delay in which a stimulus produces a
	 *     perceivable effect", required=false, type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="variable_category_id",
	 *          in="query",
	 *          description="ID of variable category",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="updated",
	 *          in="query",
	 *          description="updated",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="public",
	 *          in="query",
	 *          description="Is variable public",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="cause_only",
	 *          in="query",
	 *          description="A value of 1 indicates that this variable is generally a cause in a causal relationship.
	 *     An example of a causeOnly variable would be a variable such as Cloud Cover which would generally not be
	 *     influenced by the behaviour of the user", required=false, type="boolean"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="filling_type",
	 *          in="query",
	 *          description="0 -> No filling, 1 -> Use filling-value",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="number_of_raw_measurements",
	 *          in="query",
	 *          description="Number of measurements",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="number_of_processed_daily_measurements",
	 *          in="query",
	 *          description="Number of processed measurements",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="measurements_at_last_analysis",
	 *          in="query",
	 *          description="Number of measurements at last analysis",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="last_unit_id",
	 *          in="query",
	 *          description="ID of last Unit",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="last_original_unit_id",
	 *          in="query",
	 *          description="ID of last original Unit",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="last_original_value",
	 *          in="query",
	 *          description="Last original value which is stored",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="last_value",
	 *          in="query",
	 *          description="Last Value",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="last_original_value",
	 *          in="query",
	 *          description="Last original value which is stored",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="number_of_correlations",
	 *          in="query",
	 *          description="Number of user_variable_relationships for this variable",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="status",
	 *          in="query",
	 *          description="status",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="error_message",
	 *          in="query",
	 *          description="error_message",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="last_successful_update_time",
	 *          in="query",
	 *          description="When this variable or its settings were last updated",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="standard_deviation",
	 *          in="query",
	 *          description="Standard deviation",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="variance",
	 *          in="query",
	 *          description="Variance",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="minimum_recorded_value",
	 *          in="query",
	 *          description="Minimum recorded value of this variable",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="maximum_recorded_value",
	 *          in="query",
	 *          description="Maximum recorded value of this variable",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="mean",
	 *          in="query",
	 *          description="Mean",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="median",
	 *          in="query",
	 *          description="Median",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="most_common_original_unit_id",
	 *          in="query",
	 *          description="Most common Unit ID",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="most_common_value",
	 *          in="query",
	 *          description="Most common value",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="number_of_unique_daily_values",
	 *          in="query",
	 *          description="Number of unique daily values",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="number_of_changes",
	 *          in="query",
	 *          description="Number of changes",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="skewness",
	 *          in="query",
	 *          description="Skewness",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="kurtosis",
	 *          in="query",
	 *          description="Kurtosis",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="latitude",
	 *          in="query",
	 *          description="Latitude",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="longitude",
	 *          in="query",
	 *          description="Longitude",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="location",
	 *          in="query",
	 *          description="Location",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="created_at",
	 *          in="query",
	 *          description="When the record was first created. Use ISO 8601 datetime format ",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="updated_at",
	 *          in="query",
	 *          description="When the record was last updated. Use ISO 8601 datetime format ",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="outcome",
	 *          in="query",
	 *          description="Outcome variables (those with `outcome` == 1) are variables for which a human would
	 *     generally want to identify the influencing factors.  These include symptoms of illness, physique, mood,
	 *     cognitive performance, etc.  Generally correlation calculations are only performed on outcome variables",
	 *     required=false, type="boolean"
	 *      ),
	 * @SWG\Parameter(
	 *          name="sources",
	 *          in="query",
	 *          description="Comma-separated list of source names to limit variables to those sources",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="earliest_source_time",
	 *          in="query",
	 *          description="Earliest source time",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="latest_source_time",
	 *          in="query",
	 *          description="Latest source time",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="earliest_tagged_measurement_time",
	 *          in="query",
	 *          description="Earliest measurement time",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="latest_tagged_measurement_time",
	 *          in="query",
	 *          description="Latest measurement time",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="earliest_filling_time",
	 *          in="query",
	 *          description="Earliest filling time",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="latest_filling_time",
	 *          in="query",
	 *          description="Latest filling time",
	 *          required=false,
	 *          type="integer"
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
	 *                  @SWG\Items(ref="#/definitions/UserVariable")
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
	 *      path="/application/userVariables",
	 *      summary="Get all UserVariables",
	 *      tags={"Application Endpoints"},
	 *      description="Get all UserVariables",
	 *      produces={"application/json"},
	 *      @SWG\Parameter(
	 *          name="access_token",
	 *          in="query",
	 *          description="User's OAuth2 access token",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="client_id",
	 *          in="query",
	 *          description="The ID of the client application which last created or updated this user variable",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="parent_id",
	 *          in="query",
	 *          description="ID of the parent variable if this variable has any parent",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="variable_id",
	 *          in="query",
	 *          description="ID of variable",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="default_unit_id",
	 *          in="query",
	 *          description="D of unit to use for this variable",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="minimum_allowed_value",
	 *          in="query",
	 *          description="Minimum reasonable value for this variable (uses default unit)",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="maximum_allowed_value",
	 *          in="query",
	 *          description="Maximum reasonable value for this variable (uses default unit)",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="filling_value",
	 *          in="query",
	 *          description="Value for replacing null measurements",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="join_with",
	 *          in="query",
	 *          description="The Variable this Variable should be joined with. If the variable is joined with some
	 *     other variable then it is not shown to user in the list of variables", required=false, type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="onset_delay",
	 *          in="query",
	 *          description="Estimated number of seconds that pass before a stimulus produces a perceivable effect",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="duration_of_action",
	 *          in="query",
	 *          description="Estimated duration of time following the onset delay in which a stimulus produces a
	 *     perceivable effect", required=false, type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="variable_category_id",
	 *          in="query",
	 *          description="ID of variable category",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="updated",
	 *          in="query",
	 *          description="updated",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="public",
	 *          in="query",
	 *          description="Is variable public",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="cause_only",
	 *          in="query",
	 *          description="A value of 1 indicates that this variable is generally a cause in a causal relationship.
	 *     An example of a causeOnly variable would be a variable such as Cloud Cover which would generally not be
	 *     influenced by the behaviour of the user", required=false, type="boolean"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="filling_type",
	 *          in="query",
	 *          description="0 -> No filling, 1 -> Use filling-value",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="number_of_raw_measurements",
	 *          in="query",
	 *          description="Number of measurements",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="number_of_processed_daily_measurements",
	 *          in="query",
	 *          description="Number of processed measurements",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="measurements_at_last_analysis",
	 *          in="query",
	 *          description="Number of measurements at last analysis",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="last_unit_id",
	 *          in="query",
	 *          description="ID of last Unit",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="last_original_unit_id",
	 *          in="query",
	 *          description="ID of last original Unit",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="last_original_value",
	 *          in="query",
	 *          description="Last original value which is stored",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="last_value",
	 *          in="query",
	 *          description="Last Value",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="last_original_value",
	 *          in="query",
	 *          description="Last original value which is stored",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="number_of_correlations",
	 *          in="query",
	 *          description="Number of user_variable_relationships for this variable",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="status",
	 *          in="query",
	 *          description="status",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="error_message",
	 *          in="query",
	 *          description="error_message",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="last_successful_update_time",
	 *          in="query",
	 *          description="When this variable or its settings were last updated",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="standard_deviation",
	 *          in="query",
	 *          description="Standard deviation",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="variance",
	 *          in="query",
	 *          description="Variance",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="minimum_recorded_value",
	 *          in="query",
	 *          description="Minimum recorded value of this variable",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="maximum_recorded_value",
	 *          in="query",
	 *          description="Maximum recorded value of this variable",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="mean",
	 *          in="query",
	 *          description="Mean",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="median",
	 *          in="query",
	 *          description="Median",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="most_common_original_unit_id",
	 *          in="query",
	 *          description="Most common Unit ID",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="most_common_value",
	 *          in="query",
	 *          description="Most common value",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="number_of_unique_daily_values",
	 *          in="query",
	 *          description="Number of unique daily values",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="number_of_changes",
	 *          in="query",
	 *          description="Number of changes",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="skewness",
	 *          in="query",
	 *          description="Skewness",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="kurtosis",
	 *          in="query",
	 *          description="Kurtosis",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="latitude",
	 *          in="query",
	 *          description="Latitude",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="longitude",
	 *          in="query",
	 *          description="Longitude",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="location",
	 *          in="query",
	 *          description="Location",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="created_at",
	 *          in="query",
	 *          description="When the record was first created. Use ISO 8601 datetime format ",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="updated_at",
	 *          in="query",
	 *          description="When the record was last updated. Use ISO 8601 datetime format ",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="outcome",
	 *          in="query",
	 *          description="Outcome variables (those with `outcome` == 1) are variables for which a human would
	 *     generally want to identify the influencing factors.  These include symptoms of illness, physique, mood,
	 *     cognitive performance, etc.  Generally correlation calculations are only performed on outcome variables",
	 *     required=false, type="boolean"
	 *      ),
	 * @SWG\Parameter(
	 *          name="sources",
	 *          in="query",
	 *          description="Comma-separated list of source names to limit variables to those sources",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="earliest_source_time",
	 *          in="query",
	 *          description="Earliest source time",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="latest_source_time",
	 *          in="query",
	 *          description="Latest source time",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="earliest_tagged_measurement_time",
	 *          in="query",
	 *          description="Earliest measurement time",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="latest_tagged_measurement_time",
	 *          in="query",
	 *          description="Latest measurement time",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="earliest_filling_time",
	 *          in="query",
	 *          description="Earliest filling time",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="latest_filling_time",
	 *          in="query",
	 *          description="Latest filling time",
	 *          required=false,
	 *          type="integer"
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
	 *                  @SWG\Items(ref="#/definitions/UserVariable")
	 *              )
	 *          )
	 *      )
	 * )
	 */
	public function index(Request $request, UserVariableService $userVariableService, Guard $auth,
		Container $container){
		$filters = $this->getRequest()->all();
		$users = $container->make('oauthService')->getApplicationUsers();
		$filters['user_id'] = QMAuth::getAuthenticatedUserId();
		if(is_array($users)){
			$filters['user_id'] = $users;
		}
		$userVariables = $userVariableService->all($filters);
		$userVariablesArr = [];
		foreach($userVariables as $userVariable){
			if(is_array($users)){
				$userVariablesArr[$userVariable->user_id][] = $userVariable->toNamesArray();
			} else{
				$userVariablesArr[] = $userVariable->toNamesArray();
			}
		}
		return new JsonResponse([
			'success' => true,
			'data' => $userVariablesArr,
		]);
	}
	/**
	 * @param CreateUserVariableRequest $request
	 * @param UserVariableService $userVariableService
	 * @param Guard $auth
	 * @return JsonResponse
	 * @SWG\Post(
	 *      path="/userVariables",
	 *      summary="Store UserVariable",
	 *      tags={"UserVariable"},
	 *      description="Users can change things like the display name for a variable. They can also change the
	 *     parameters used in analysis of that variable such as the expected duration of action for a variable to have
	 *     an effect, the estimated delay before the onset of action. In order to filter out erroneous data, they are
	 *     able to set the maximum and minimum reasonable daily values for a variable.", produces={"application/json"},
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
	 *          description="UserVariable that should be stored",
	 *          required=false,
	 *          @SWG\Schema(ref="#/definitions/UserVariable")
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
	 *                  ref="#/definitions/UserVariable"
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
	public function store(CreateUserVariableRequest $request, UserVariableService $userVariableService, Guard $auth){
		$userVariable = $userVariableService->create(array_merge($this->getRequest()->all(),
			['user_id' => QMAuth::getAuthenticatedUserId()]));
		return new JsonResponse([
			'success' => true,
			'data' => $userVariable->toArray(),
		]);
	}
	/**
	 * @param int $id
	 * @param UserVariableService $userVariableService
	 * @param Guard $auth
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Get(
	 *      path="/userVariables/{id}",
	 *      summary="Get UserVariable",
	 *      tags={"UserVariable"},
	 *      description="Get UserVariable",
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
	 *          description="id of UserVariable",
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
	 *                  ref="#/definitions/UserVariable"
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
	public function show($id, UserVariableService $userVariableService, Guard $auth){
		$userVariable = $userVariableService->getWithRelations(QMAuth::getAuthenticatedUserId(), $id);
		if(empty($userVariable)){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => $userVariable->toNamesArray(),
		]);
	}
}
