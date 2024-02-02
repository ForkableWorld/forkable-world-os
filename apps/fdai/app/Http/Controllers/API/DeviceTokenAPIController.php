<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\API;
use App\Http\Controllers\BaseAPIController;
use App\Slim\Middleware\QMAuth;
use Illuminate\Http\JsonResponse;
class DeviceTokenAPIController extends BaseAPIController {
	/**
	 * @param \Illuminate\Http\Request $request
	 * @return JsonResponse
	 * @OA\Get(
	 *      path="/deviceTokens",
	 *      summary="Get a listing of the DeviceTokens.",
	 *      tags={"DeviceToken"},
	 *      description="Get all DeviceTokens",
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
	 *                  @OA\Items(ref="#/components/schemas/DeviceToken")
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
	 *      path="/deviceTokens",
	 *      summary="Store a newly created DeviceToken in storage",
	 *      tags={"DeviceToken"},
	 *      description="Store DeviceToken",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="DeviceToken that should be stored",
	 *          required=false,
	 *          @OA\Schema(ref="#/components/schemas/DeviceToken")
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
	 *                  ref="#/components/schemas/DeviceToken"
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
		if(!QMAuth::getQMUser()){
			le('!QMAuth::getUser()');
		}
		return parent::store($request);
	}
	/**
	 * @param int $id
	 * @return JsonResponse
	 * @throws \Exception
	 * @OA\Get(
	 *      path="/deviceTokens/{id}",
	 *      summary="Display the specified DeviceToken",
	 *      tags={"DeviceToken"},
	 *      description="Get DeviceToken",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of DeviceToken",
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
	 *                  ref="#/components/schemas/DeviceToken"
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
	 *      path="/deviceTokens/{id}",
	 *      summary="Update the specified DeviceToken in storage",
	 *      tags={"DeviceToken"},
	 *      description="Update DeviceToken",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of DeviceToken",
	 *          type="integer",
	 *          required=true,
	 *          in="path"
	 *      ),
	 *      @OA\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="DeviceToken that should be updated",
	 *          required=false,
	 *          @OA\Schema(ref="#/components/schemas/DeviceToken")
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
	 *                  ref="#/components/schemas/DeviceToken"
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
	 *      path="/deviceTokens/{id}",
	 *      summary="Remove the specified DeviceToken from storage",
	 *      tags={"DeviceToken"},
	 *      description="Delete DeviceToken",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of DeviceToken",
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
