<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\API;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Http\JsonResponse;
class OAClientAPIController extends BaseAPIController {
	/**
	 * @param \Illuminate\Http\Request $request
	 * @return JsonResponse
	 * @OA\Get(
	 *      path="/bshafferOauthClients",
	 *      summary="Get a listing of the OAClients.",
	 *      tags={"OAClient"},
	 *      description="Get all OAClients",
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
	 *                  @OA\Items(ref="#/components/schemas/OAClient")
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
	 *      path="/bshafferOauthClients",
	 *      summary="Store a newly created OAClient in storage",
	 *      tags={"OAClient"},
	 *      description="Store OAClient",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="OAClient that should be stored",
	 *          required=false,
	 *          @OA\Schema(ref="#/components/schemas/OAClient")
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
	 *                  ref="#/components/schemas/OAClient"
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
	 *      path="/bshafferOauthClients/{id}",
	 *      summary="Display the specified OAClient",
	 *      tags={"OAClient"},
	 *      description="Get OAClient",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of OAClient",
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
	 *                  ref="#/components/schemas/OAClient"
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
	 *      path="/bshafferOauthClients/{id}",
	 *      summary="Update the specified OAClient in storage",
	 *      tags={"OAClient"},
	 *      description="Update OAClient",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of OAClient",
	 *          type="integer",
	 *          required=true,
	 *          in="path"
	 *      ),
	 *      @OA\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="OAClient that should be updated",
	 *          required=false,
	 *          @OA\Schema(ref="#/components/schemas/OAClient")
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
	 *                  ref="#/components/schemas/OAClient"
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
	 *      path="/bshafferOauthClients/{id}",
	 *      summary="Remove the specified OAClient from storage",
	 *      tags={"OAClient"},
	 *      description="Delete OAClient",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of OAClient",
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
