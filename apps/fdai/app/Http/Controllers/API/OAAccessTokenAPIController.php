<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\API;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Http\JsonResponse;
class OAAccessTokenAPIController extends BaseAPIController {
	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @OA\Get(
	 *      path="/bshafferOauthAccessTokens",
	 *      summary="Get a listing of the OAAccessTokens.",
	 *      tags={"OAAccessToken"},
	 *      description="Get all OAAccessTokens",
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
	 *                  @OA\Items(ref="#/components/schemas/OAAccessToken")
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
	 *      path="/bshafferOauthAccessTokens",
	 *      summary="Store a newly created OAAccessToken in storage",
	 *      tags={"OAAccessToken"},
	 *      description="Store OAAccessToken",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="OAAccessToken that should be stored",
	 *          required=false,
	 *          @OA\Schema(ref="#/components/schemas/OAAccessToken")
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
	 *                  ref="#/components/schemas/OAAccessToken"
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
	 *      path="/bshafferOauthAccessTokens/{id}",
	 *      summary="Display the specified OAAccessToken",
	 *      tags={"OAAccessToken"},
	 *      description="Get OAAccessToken",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of OAAccessToken",
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
	 *                  ref="#/components/schemas/OAAccessToken"
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
	 *      path="/bshafferOauthAccessTokens/{id}",
	 *      summary="Update the specified OAAccessToken in storage",
	 *      tags={"OAAccessToken"},
	 *      description="Update OAAccessToken",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of OAAccessToken",
	 *          type="integer",
	 *          required=true,
	 *          in="path"
	 *      ),
	 *      @OA\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="OAAccessToken that should be updated",
	 *          required=false,
	 *          @OA\Schema(ref="#/components/schemas/OAAccessToken")
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
	 *                  ref="#/components/schemas/OAAccessToken"
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
	 *      path="/bshafferOauthAccessTokens/{id}",
	 *      summary="Remove the specified OAAccessToken from storage",
	 *      tags={"OAAccessToken"},
	 *      description="Delete OAAccessToken",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of OAAccessToken",
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
