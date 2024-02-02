<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\V2;
use App\Exceptions\BadRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Variable\CreateVariableRequest;
use App\Http\Requests\Variable\UpdateVariableRequest;
use App\Models\Variable;
use App\Services\VariableService;
use Exception;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
/** Class VariableController
 * @package App\Http\Controllers
 */
class V2VariableController extends Controller {
	/**
	 * @param Request $request
	 * @param VariableService $variableService
	 * @return JsonResponse
	 * @SWG\Get(
	 *      path="/variables",
	 *      summary="Get all Variables",
	 *      tags={"Variable"},
	 *      description="Get all Variables",
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
	 *          in="query",
	 *          description="id",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="client_id",
	 *          in="query",
	 *          description="The ID of the client application which last created or updated this common variable",
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
	 *          name="name",
	 *          in="query",
	 *          description="User-defined variable display name",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="variable_category_id",
	 *          in="query",
	 *          description="Variable category ID",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="default_unit_id",
	 *          in="query",
	 *          description="ID of the default unit for the variable",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="combination_operation",
	 *          in="query",
	 *          description="How to combine values of this variable (for instance, to see a summary of the values over
	 *     a month) SUM or MEAN", required=false, type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="filling_value",
	 *          in="query",
	 *          description="Value for replacing null measurements",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="maximum_allowed_value",
	 *          in="query",
	 *          description="Maximum reasonable value for this variable (uses default unit)",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="minimum_allowed_value",
	 *          in="query",
	 *          description="Minimum reasonable value for this variable (uses default unit)",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="onset_delay",
	 *          in="query",
	 *          description="Estimated number of seconds that pass before a stimulus produces a perceivable effect",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="duration_of_action",
	 *          in="query",
	 *          description="Estimated number of seconds following the onset delay in which a stimulus produces a
	 *     perceivable effect", required=false, type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="public",
	 *          in="query",
	 *          description="Is variable public",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="cause_only",
	 *          in="query",
	 *          description="A value of 1 indicates that this variable is generally a cause in a causal relationship.
	 *     An example of a causeOnly variable would be a variable such as Cloud Cover which would generally not be
	 *     influenced by the behaviour of the user", required=false, type="boolean"
	 *      ),
	 * @SWG\Parameter(
	 *          name="most_common_value",
	 *          in="query",
	 *          description="Most common value",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="most_common_original_unit_id",
	 *          in="query",
	 *          description="Most common Unit",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="standard_deviation",
	 *          in="query",
	 *          description="Standard Deviation",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="variance",
	 *          in="query",
	 *          description="Average variance for this variable based on all user data",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="mean",
	 *          in="query",
	 *          description="Mean for this variable based on all user data",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="median",
	 *          in="query",
	 *          description="Median for this variable based on all user data",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="number_of_raw_measurements",
	 *          in="query",
	 *          description="Number of measurements for this variable based on all user data",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="number_of_unique_values",
	 *          in="query",
	 *          description="Number of unique values for this variable based on all user data",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="skewness",
	 *          in="query",
	 *          description="Skewness for this variable based on all user data",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="kurtosis",
	 *          in="query",
	 *          description="Kurtosis for this variable based on all user data",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="status",
	 *          in="query",
	 *          description="status",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="error_message",
	 *          in="query",
	 *          description="error_message",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="last_successful_update_time",
	 *          in="query",
	 *          description="When this variable or its settings were last updated",
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
	 *          name="product_url",
	 *          in="query",
	 *          description="Product URL",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="image_url",
	 *          in="query",
	 *          description="Image URL",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="price",
	 *          in="query",
	 *          description="Price",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="number_of_user_variables",
	 *          in="query",
	 *          description="Number of users who have data for this variable",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="outcome",
	 *          in="query",
	 *          description="Outcome variables (those with `outcome` == 1) are variables for which a human would
	 *     generally want to identify the influencing factors.  These include symptoms of illness, physique, mood,
	 *     cognitive performance, etc.  Generally correlation calculations are only performed on outcome variables.",
	 *     required=false, type="boolean"
	 *      ),
	 * @SWG\Parameter(
	 *          name="minimum_recorded_value",
	 *          in="query",
	 *          description="Minimum recorded value of this variable based on all user data",
	 *          required=false,
	 *          type="number"
	 *      ),
	 * @SWG\Parameter(
	 *          name="maximum_recorded_value",
	 *          in="query",
	 *          description="Maximum recorded value of this variable based on all user data",
	 *          required=false,
	 *          type="number"
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
	 *          description="Sort records by a given field name. If the field name is prefixed with '-', it will sort
	 *     in descending order.", required=false, type="string"
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
	 *                  @SWG\Items(ref="#/definitions/Variable")
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
	public function index(Request $request, VariableService $variableService){
        $variables = Variable::getByRequest();
		foreach($variables as $variable){
			$variablesArr[] = $variable->toNamesArray();
		}
		return new JsonResponse([
			'success' => true,
			'data' => $variablesArr,
		]);
	}
	/**
	 * @param CreateVariableRequest $request
	 * @param VariableService $variableService
	 * @param Guard $auth
	 * @return JsonResponse
	 * @SWG\Post(
	 *      path="/variables",
	 *      summary="Store Variable",
	 *      tags={"Variable"},
	 *      description="Allows the client to create a new variable in the `variables` table.",
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
	 *          description="Variable that should be stored",
	 *          required=false,
	 *          @SWG\Schema(ref="#/definitions/Variable")
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
	 *                  ref="#/definitions/Variable"
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
	public function store(CreateVariableRequest $request, VariableService $variableService, Guard $auth){
		$inputs = $this->getRequest()->all();
		$inputs['user_id'] = QMAuth::id();
		$variable = $variableService->create($inputs);
		return new JsonResponse([
			'success' => true,
			'data' => $variable->toArray(),
		]);
	}
	/**
	 * @param int $id
	 * @param VariableService $variableService
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Get(
	 *      path="/variables/{id}",
	 *      summary="Get Variable",
	 *      tags={"Variable"},
	 *      description="Get Variable",
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
	 *          description="id of Variable",
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
	 *                  ref="#/definitions/Variable"
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
	public function show($id, VariableService $variableService){
		$variable = $variableService->getWithRelations($id);
		if(empty($variable)){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => $variable->toNamesArray(),
		]);
	}
	/**
	 * @param int $id
	 * @param UpdateVariableRequest $request
	 * @param VariableService $variableService
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Put(
	 *      path="/variables/{id}",
	 *      summary="Update Variable",
	 *      tags={"Variable"},
	 *      description="Update Variable",
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
	 *          description="id of Variable",
	 *          type="integer",
	 *          required=true,
	 *          in="path"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="Variable that should be updated",
	 *          required=false,
	 *          @SWG\Schema(ref="#/definitions/Variable")
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
	public function update($id, UpdateVariableRequest $request, VariableService $variableService){
		$result = $variableService->updateRich($this->getRequest()->all(), $id);
		if($result === false){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => "Variable updated successfully",
		]);
	}

    /**
     * @param int $id
     * @param VariableService $variableService
     * @return JsonResponse
     * @throws Exception
     * @SWG\Delete(
     *      path="/variables/{id}",
     *      summary="Delete Variable",
     *      tags={"Variable"},
     *      description="Delete Variable",
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
     *          description="id of Variable",
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
	public function destroy($id, VariableService $variableService){
		$result = $variableService->delete($id);
		if($result === false){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => "Variable deleted successfully",
		]);
	}
}
