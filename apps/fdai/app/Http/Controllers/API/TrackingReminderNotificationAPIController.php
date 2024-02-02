<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\API;
use App\Http\Controllers\BaseAPIController;
use App\Models\User;
use App\PhpUnitJobs\Reminders\ReminderNotificationGeneratorJob;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use Illuminate\Http\JsonResponse;
class TrackingReminderNotificationAPIController extends BaseAPIController {
	/**
	 * @param \Illuminate\Http\Request $request
	 * @return JsonResponse
	 * @OA\Get(
	 *      path="/trackingReminderNotifications",
	 *      summary="Get a listing of the TrackingReminderNotifications.",
	 *      tags={"TrackingReminderNotification"},
	 *      description="Get all TrackingReminderNotifications",
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
	 *                  @OA\Items(ref="#/components/schemas/TrackingReminderNotification")
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
	 *      path="/trackingReminderNotifications",
	 *      summary="Store a newly created TrackingReminderNotification in storage",
	 *      tags={"TrackingReminderNotification"},
	 *      description="Store TrackingReminderNotification",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="TrackingReminderNotification that should be stored",
	 *          required=false,
	 *          @OA\Schema(ref="#/components/schemas/TrackingReminderNotification")
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
	 *                  ref="#/components/schemas/TrackingReminderNotification"
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
	 *      path="/trackingReminderNotifications/{id}",
	 *      summary="Display the specified TrackingReminderNotification",
	 *      tags={"TrackingReminderNotification"},
	 *      description="Get TrackingReminderNotification",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of TrackingReminderNotification",
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
	 *                  ref="#/components/schemas/TrackingReminderNotification"
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
	 *      path="/trackingReminderNotifications/{id}",
	 *      summary="Update the specified TrackingReminderNotification in storage",
	 *      tags={"TrackingReminderNotification"},
	 *      description="Update TrackingReminderNotification",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of TrackingReminderNotification",
	 *          type="integer",
	 *          required=true,
	 *          in="path"
	 *      ),
	 *      @OA\Parameter(
	 *          name="body",
	 *          in="body",
	 *          description="TrackingReminderNotification that should be updated",
	 *          required=false,
	 *          @OA\Schema(ref="#/components/schemas/TrackingReminderNotification")
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
	 *                  ref="#/components/schemas/TrackingReminderNotification"
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
	 *      path="/trackingReminderNotifications/{id}",
	 *      summary="Remove the specified TrackingReminderNotification from storage",
	 *      tags={"TrackingReminderNotification"},
	 *      description="Delete TrackingReminderNotification",
	 *      produces={"application/json"},
	 *      @OA\Parameter(
	 *          name="id",
	 *          description="id of TrackingReminderNotification",
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
	public function generate(\Illuminate\Http\Request $request){
		$userId = QMRequest::getParam('user_id');
		$number = ReminderNotificationGeneratorJob::createTrackingReminderNotifications($userId);
		if(!$number){
			return $this->sendError('No tracking reminders could be generated for this user', 201);
		}
		return $this->sendSuccess("Created $number notifications", 201);
	}
	public function notify(){
		/** @var User $user */
		$user = QMAuth::getUser();
		$number = $user->generateTrackingReminderNotifications();
		if(!$number){
			return $this->sendError('No tracking reminders could be generated for this user', 201);
		}
		return $this->sendSuccess("Created $number notifications for user $user->id", 201);
	}
}
