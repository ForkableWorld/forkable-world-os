<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\V2;
use App\Http\Controllers\Controller;
use App\Http\Requests\Unit\CreateUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Services\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Quantimodo\Api\Exceptions\BadRequestException;
/** Class UnitController
 * @package App\Http\Controllers
 */
class V2UnitController extends Controller {
	/**
	 * @param Request $request
	 * @param UnitService $unitService
	 * @return JsonResponse
	 * @SWG\Get(
	 *      path="/units",
	 *      summary="Get all available units",
	 *      tags={"Unit"},
	 *      description="Get all available units",
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
	 *          description="The ID of the client application which last created or updated this unit",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="name",
	 *          in="query",
	 *          description="Unit name",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="abbreviated_name",
	 *          in="query",
	 *          description="Unit abbreviation",
	 *          required=false,
	 *          type="string"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="category_id",
	 *          in="query",
	 *          description="Unit category ID",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="minimum_value",
	 *          in="query",
	 *          description="Minimum value permitted for this unit",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="maximum_value",
	 *          in="query",
	 *          description="Maximum value permitted for this unit",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="updated",
	 *          in="query",
	 *          description="updated",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="default_unit_id",
	 *          in="query",
	 *          description="ID of default unit for this units category",
	 *          required=false,
	 *          type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="multiply",
	 *          in="query",
	 *          description="Value multiplied to convert to default unit in this unit category",
	 *          required=false,
	 *          type="number"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="add",
	 *          in="query",
	 *          description="Value which should be added to convert to default unit",
	 *          required=false,
	 *          type="number"
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
	 *          name="limit",
	 *          in="query",
	 *          description="The LIMIT is used to limit the number of results returned. So if you have 1000 results,
	 *     but only want to the first 10, you would set this to 10 and offset to 0. The maximum limit is 200 records.",
	 *     required=false, type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="offset",
	 *          in="query",
	 *          description="OFFSET says to skip that many rows before beginning to return rows to the client. OFFSET 0
	 *     is the same as omitting the OFFSET clause. If both OFFSET and LIMIT appear, then OFFSET rows are skipped
	 *     before starting to count the LIMIT rows that are returned.", required=false, type="integer"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="sort",
	 *          in="query",
	 *          description="Sort by given field. If the field is prefixed with '-', it will sort in descending
	 *     order.",
	 *          required=false,
	 *          type="string"
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
	 *                  @SWG\Items(ref="#/definitions/Unit")
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
	public function index(Request $request, UnitService $unitService){
		$units = $unitService->all($this->getRequest()->all());
		$unitsArr = [];
		foreach($units as $unit){
			$a = $unit->toArray();
			$a['category'] = $a['category']['name'] ?? null;
			$a['default_unit'] = $a['default_unit']['name'] ?? null;
			if(!empty($a['conversion_steps'])){
				foreach($a['conversion_steps'] as &$conversionStep){
					unset($conversionStep['unit_id']);
				}
			} else{
				$a['conversion_steps'] = [];
			}
			$unitsArr[] = $a;
		}
		return new JsonResponse([
			'success' => true,
			'data' => $unitsArr,
		]);
	}
	/**
	 * @param CreateUnitRequest $request
	 * @param UnitService $unitService
	 * @return JsonResponse
	 * @SWG\Post(
	 *      path="/units",
	 *      summary="Store Unit",
	 *      tags={"Unit"},
	 *      description="Store Unit",
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
	 *          description="Unit that should be stored",
	 *          required=false,
	 *          @SWG\Schema(ref="#/definitions/Unit")
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
	 *                  ref="#/definitions/Unit"
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
	public function store(CreateUnitRequest $request, UnitService $unitService){
		$unit = $unitService->create($this->getRequest()->all());
		return new JsonResponse([
			'success' => true,
			'data' => $unit->toArray(),
		]);
	}
	/**
	 * @param UnitService $unitService
	 * @param int $id
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Get(
	 *      path="/units/{id}",
	 *      summary="Get Unit",
	 *      tags={"Unit"},
	 *      description="Get Unit",
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
	 *          description="id of Unit",
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
	 *                  ref="#/definitions/Unit"
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
	public function show($id, UnitService $unitService){
		$unit = $unitService->getWithRelations($id);
		if(empty($unit)){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => $unit->toArray(),
		]);
	}
	/**
	 * @param int $id
	 * @param UpdateUnitRequest $request
	 * @param UnitService $unitService
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Put(
	 *      path="/units/{id}",
	 *      summary="Update Unit",
	 *      tags={"Unit"},
	 *      description="Update Unit",
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
	 *          description="id of Unit",
	 *          type="integer",
	 *          required=true,
	 *          in="path"
	 *      ),
	 *      @SWG\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="Unit that should be updated",
	 *          required=false,
	 *          @SWG\Schema(ref="#/definitions/Unit")
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
	public function update($id, UpdateUnitRequest $request, UnitService $unitService){
		$result = $unitService->updateRich($this->getRequest()->all(), $id);
		if($result === false){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => "Unit updated successfully",
		]);
	}
	/**
	 * @param int $id
	 * @param UnitService $unitService
	 * @return JsonResponse
	 * @throws BadRequestException
	 * @SWG\Delete(
	 *      path="/units/{id}",
	 *      summary="Delete Unit",
	 *      tags={"Unit"},
	 *      description="Delete Unit",
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
	 *          description="id of Unit",
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
	public function destroy($id, UnitService $unitService){
		$result = $unitService->delete($id);
		if($result === false){
			throw new BadRequestException("Record not found");
		}
		return new JsonResponse([
			'success' => true,
			'data' => "Unit deleted successfully",
		]);
	}
}
