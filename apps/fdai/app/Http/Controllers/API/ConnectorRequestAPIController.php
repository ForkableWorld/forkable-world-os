<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\API;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
class ConnectorRequestAPIController extends BaseAPIController {
	/**
	 * @param \Illuminate\Http\Request $request
	 * @return JsonResponse|Response
	 * @OA\Get(
	 *      path="/connectorRequests",
	 *      summary="Get a listing of the ConnectorRequests.",
	 *      tags={"ConnectorRequest"},
	 *      description="Get all ConnectorRequests",
	 *      produces={"application/json"},
	 *      @OA\Response(
	 *          response=200,
	 *          description="successful operation",
	 *          @OA\Schema(
	 *              type="object",
	 *              @OA\Property(
	 *                  property="success",
	 *                  type="boolean"
	 *              ),
	 *              @OA\Property(
	 *                  property="data",
	 *                  type="array",
	 *                  @OA\Items(ref="#/components/schemas/ConnectorRequest")
	 *              ),
	 *              @OA\Property(
	 *                  property="message",
	 *                  type="string"
	 *              )
	 *          )
	 *      )
	 * )
	 */
	public function index(\Illuminate\Http\Request $request){
		return parent::index($request);
	}
	/**
	 * @param \Illuminate\Http\Request $request
	 * @return JsonResponse
	 * @OA\Post(
	 *      path="/connectorRequests",
	 *      summary="Store a newly created ConnectorRequest in storage",
	 *      tags={"ConnectorRequest"},
	 *      description="Store ConnectorRequest",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="ConnectorRequest that should be stored",
	 *          required=false,
	 *          @OA\Schema(ref="#/components/schemas/ConnectorRequest")
	 *      ),
	 *      @OA\Response(
	 *          response=200,
	 *          description="successful operation",
	 *          @OA\Schema(
	 *              type="object",
	 *              @OA\Property(
	 *                  property="success",
	 *                  type="boolean"
	 *              ),
	 *              @OA\Property(
	 *                  property="data",
	 *                  ref="#/components/schemas/ConnectorRequest"
	 *              ),
	 *              @OA\Property(
	 *                  property="message",
	 *                  type="string"
	 *              )
	 *          )
	 *      )
	 * )
	 */
	public function store(\Illuminate\Http\Request $request){
		return parent::store($request);
	}
	/**
	 * @param int $id
	 * @return JsonResponse
	 * @throws \Exception
	 * @OA\Get(
	 *      path="/connectorRequests/{id}",
	 *      summary="Display the specified ConnectorRequest",
	 *      tags={"ConnectorRequest"},
	 *      description="Get ConnectorRequest",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of ConnectorRequest",
	 *          type="integer",
	 *          required=true,
	 *          in="path"
	 *      ),
	 *      @OA\Response(
	 *          response=200,
	 *          description="successful operation",
	 *          @OA\Schema(
	 *              type="object",
	 *              @OA\Property(
	 *                  property="success",
	 *                  type="boolean"
	 *              ),
	 *              @OA\Property(
	 *                  property="data",
	 *                  ref="#/components/schemas/ConnectorRequest"
	 *              ),
	 *              @OA\Property(
	 *                  property="message",
	 *                  type="string"
	 *              )
	 *          )
	 *      )
	 * )
	 */
	public function show($id){
		return parent::show($id);
	}
	/**
	 * @param int $id
	 * @param \Illuminate\Http\Request $request
	 * @return JsonResponse
	 * @OA\Put(
	 *      path="/connectorRequests/{id}",
	 *      summary="Update the specified ConnectorRequest in storage",
	 *      tags={"ConnectorRequest"},
	 *      description="Update ConnectorRequest",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of ConnectorRequest",
	 *          type="integer",
	 *          required=true,
	 *          in="path"
	 *      ),
	 *      @OA\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="ConnectorRequest that should be updated",
	 *          required=false,
	 *          @OA\Schema(ref="#/components/schemas/ConnectorRequest")
	 *      ),
	 *      @OA\Response(
	 *          response=200,
	 *          description="successful operation",
	 *          @OA\Schema(
	 *              type="object",
	 *              @OA\Property(
	 *                  property="success",
	 *                  type="boolean"
	 *              ),
	 *              @OA\Property(
	 *                  property="data",
	 *                  ref="#/components/schemas/ConnectorRequest"
	 *              ),
	 *              @OA\Property(
	 *                  property="message",
	 *                  type="string"
	 *              )
	 *          )
	 *      )
	 * )
	 */
	public function update($id, \Illuminate\Http\Request $request){
		return parent::update($id, $request);
	}
	/**
	 * @param int $id
	 * @return JsonResponse
	 * @throws \Exception
	 * @OA\Delete(
	 *      path="/connectorRequests/{id}",
	 *      summary="Remove the specified ConnectorRequest from storage",
	 *      tags={"ConnectorRequest"},
	 *      description="Delete ConnectorRequest",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of ConnectorRequest",
	 *          type="integer",
	 *          required=true,
	 *          in="path"
	 *      ),
	 *      @OA\Response(
	 *          response=200,
	 *          description="successful operation",
	 *          @OA\Schema(
	 *              type="object",
	 *              @OA\Property(
	 *                  property="success",
	 *                  type="boolean"
	 *              ),
	 *              @OA\Property(
	 *                  property="data",
	 *                  type="string"
	 *              ),
	 *              @OA\Property(
	 *                  property="message",
	 *                  type="string"
	 *              )
	 *          )
	 *      )
	 * )
	 */
	public function destroy($id){
		return parent::destroy($id);
	}
}
