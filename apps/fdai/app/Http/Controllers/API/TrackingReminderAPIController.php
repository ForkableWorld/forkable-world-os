<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\API;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Http\JsonResponse;
class TrackingReminderAPIController extends BaseAPIController {
	/**
	 * @param \Illuminate\Http\Request $request
	 * @return JsonResponse
	 * @OA\Get(
	 *      path="/trackingReminders",
	 *      summary="Get a listing of the TrackingReminders.",
	 *      tags={"TrackingReminder"},
	 *      description="Get all TrackingReminders",
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
	 *                  @OA\Items(ref="#/components/schemas/TrackingReminder")
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
	 *      path="/trackingReminders",
	 *      summary="Store a newly created TrackingReminder in storage",
	 *      tags={"TrackingReminder"},
	 *      description="Store TrackingReminder",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="TrackingReminder that should be stored",
	 *          required=false,
	 *          @OA\Schema(ref="#/components/schemas/TrackingReminder")
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
	 *                  ref="#/components/schemas/TrackingReminder"
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
	 *      path="/trackingReminders/{id}",
	 *      summary="Display the specified TrackingReminder",
	 *      tags={"TrackingReminder"},
	 *      description="Get TrackingReminder",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of TrackingReminder",
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
	 *                  ref="#/components/schemas/TrackingReminder"
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
	 *      path="/trackingReminders/{id}",
	 *      summary="Update the specified TrackingReminder in storage",
	 *      tags={"TrackingReminder"},
	 *      description="Update TrackingReminder",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of TrackingReminder",
	 *          type="integer",
	 *          required=true,
	 *          in="path"
	 *      ),
	 *      @OA\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="TrackingReminder that should be updated",
	 *          required=false,
	 *          @OA\Schema(ref="#/components/schemas/TrackingReminder")
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
	 *                  ref="#/components/schemas/TrackingReminder"
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
	 *      path="/trackingReminders/{id}",
	 *      summary="Remove the specified TrackingReminder from storage",
	 *      tags={"TrackingReminder"},
	 *      description="Delete TrackingReminder",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of TrackingReminder",
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
