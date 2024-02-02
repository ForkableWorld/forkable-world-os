<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\V2;
use App\Http\Controllers\Controller;
use App\Http\Requests\Variable\CreateVariableCategoryRequest;
use App\Http\Requests\Variable\UpdateVariableCategoryRequest;
use App\Services\VariableCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Quantimodo\Api\Exceptions\BadRequestException;
/** Class VariableCategoryController
 * @package App\Http\Controllers
 */
class V2VariableCategoryController extends Controller {
	/**
	 * @param Request $request
	 * @param VariableCategoryService $variableCategoryService
	 * @return JsonResponse
	 * @SWG\Get(
	 *      path="/variableCategories",
	 *      summary="Get all VariableCategories",
	 *      tags={"VariableCategory"},
	 *      description="The variable categories include Activity, Causes of Illness, Cognitive Performance,
	 *     Conditions, Environment, Foods, Location, Miscellaneous, Mood, Nutrition, Physical Activity, Physique,
	 *     Sleep, Social Interactions, Symptoms, Treatments, Vital Signs, and Work.", produces={"application/json"},
	 *      @SWG\Parameter(
	 *          name="access_token",
	 *          in="query",
	 *          description="User's OAuth2 access token",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="name",
	 *          in="query",
	 *          description="Name of the category",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="filling_value",
	 *          in="query",
	 *          description="Value for replacing null measurements",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="maximum_allowed_value",
	 *          in="query",
	 *          description="Maximum recorded value of this category",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="minimum_allowed_value",
	 *          in="query",
	 *          description="Minimum recorded value of this category",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="duration_of_action",
	 *          in="query",
	 *          description="Estimated number of seconds following the onset delay in which a stimulus produces a
	 *     perceivable effect", required=false, type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="onset_delay",
	 *          in="query",
	 *          description="Estimated number of seconds that pass before a stimulus produces a perceivable effect",
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
	 *          name="updated",
	 *          in="query",
	 *          description="updated",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="cause_only",
	 *          in="query",
	 *          description="A value of 1 indicates that this category is generally a cause in a causal relationship.
	 *     An example of a causeOnly category would be a category such as Work which would generally not be influenced
	 *     by the behaviour of the user", required=false, type="boolean"
	 *      ),
	 * @SWG\Parameter(
	 *          name="public",
	 *          in="query",
	 *          description="Is category public",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 * @SWG\Parameter(
	 *          name="outcome",
	 *          in="query",
	 *          description="outcome",
	 *          required=false,
	 *          type="boolean"
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
	 *          name="image_url",
	 *          in="query",
	 *          description="Image URL",
	 *          required=false,
	 *          type="string"
	 *      ),
	 * @SWG\Parameter(
	 *          name="default_unit_id",
	 *          in="query",
	 *          description="ID of the default unit for the category",
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
	 *                  @SWG\Items(ref="#/definitions/VariableCategory")
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
	public function index(Request $request, VariableCategoryService $variableCategoryService){
		$variableCategories = $variableCategoryService->all($this->getRequest()->all());
		$variableCategoriesArr = [];
		foreach($variableCategories as $category){
			$variableCategoriesArr[] = $category->toNamesArray();
		}
		return new JsonResponse([
			'success' => true,
			'data' => $variableCategoriesArr,
		]);
	}
	/**
	 * @param CreateVariableCategoryRequest $request
	 * @param VariableCategoryService $variableCategoryService
	 * @return JsonResponse
	 * @SWG\Post(
	 *      path="/variableCategories",
	 *      summary="Store VariableCategory",
	 *      tags={"VariableCategory"},
	 *      description="Store VariableCategory",
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
	 *          description="VariableCategory that should be stored",
	 *          required=false,
	 *          @SWG\Schema(ref="#/definitions/VariableCategory")
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
	 *                  ref="#/definitions/VariableCategory"
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
	public function store(CreateVariableCategoryRequest $request, VariableCategoryService $variableCategoryService){
		$variableCategory = $variableCategoryService->create($this->getRequest()->all());
		return new JsonResponse([
			'success' => true,
			'data' => $variableCategory->toArray(),
		]);
	}
	/**
	 * @param VariableCategoryService $variableCategoryService
	 * @param int $id
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Get(
	 *      path="/variableCategories/{id}",
	 *      summary="Get VariableCategory",
	 *      tags={"VariableCategory"},
	 *      description="Get VariableCategory",
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
	 *          description="id of VariableCategory",
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
	 *                  ref="#/definitions/VariableCategory"
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
	public function show($id, VariableCategoryService $variableCategoryService){
		$variableCategory = $variableCategoryService->getWithRelations($id);
		if(empty($variableCategory)){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => $variableCategory->toNamesArray(),
		]);
	}
	/**
	 * @param int $id
	 * @param UpdateVariableCategoryRequest $request
	 * @param VariableCategoryService $variableCategoryService
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Put(
	 *      path="/variableCategories/{id}",
	 *      summary="Update VariableCategory",
	 *      tags={"VariableCategory"},
	 *      description="Update VariableCategory",
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
	 *          description="id of VariableCategory",
	 *          type="integer",
	 *          required=true,
	 *          in="path"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="VariableCategory that should be updated",
	 *          required=false,
	 *          @SWG\Schema(ref="#/definitions/VariableCategory")
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
	public function update($id, UpdateVariableCategoryRequest $request,
		VariableCategoryService $variableCategoryService){
		$result = $variableCategoryService->updateRich($this->getRequest()->all(), $id);
		if($result === false){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => "VariableCategory updated successfully",
		]);
	}
	/**
	 * @param int $id
	 * @param VariableCategoryService $variableCategoryService
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Delete(
	 *      path="/variableCategories/{id}",
	 *      summary="Delete VariableCategory",
	 *      tags={"VariableCategory"},
	 *      description="Delete VariableCategory",
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
	 *          description="id of VariableCategory",
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
	public function destroy($id, VariableCategoryService $variableCategoryService){
		$result = $variableCategoryService->delete($id);
		if($result === false){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => "VariableCategory deleted successfully",
		]);
	}
}
