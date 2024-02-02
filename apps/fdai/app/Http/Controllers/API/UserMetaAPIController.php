<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\API;
use App\Exceptions\AccessTokenExpiredException;
use App\Exceptions\BadRequestException;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\BaseAPIController;
use App\Models\User;
use App\Models\WpUsermetum;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/** Class UserController
 * @package App\Http\Controllers\API
 */
class UserMetaAPIController extends BaseAPIController {
	/**
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|JsonResponse
	 * @throws \App\Exceptions\UnauthorizedException
	 */
	public function index(\Illuminate\Http\Request $request){
	    $user = $this->getUser();
		$key = QMRequest::getInput('key');
		if($key){
			/** @var WpUsermetum $meta */
			$meta = $user->user_meta()->where('meta_key', $key)->first();
			if(!$meta){
				throw new ModelNotFoundException("No meta data found for key $key");
			}
			return response()->json($meta->meta_value, 200);
		}
	    return $user->getUserIndexedByKeyMeta(BaseClientIdProperty::fromRequest(true));
    }
    /**
     * @param null $id
     * @return User
     * @throws UnauthorizedException
     * @throws AccessTokenExpiredException
     */
    public function find($id = null): WpUsermetum {
		$user = User::fromRequest();
	    /** @var WpUsermetum $model */
	    $model = $user->user_meta()->find($id);
        $model->validateCanRead();
        return $model;
    }
	/**
	 * @param \Illuminate\Http\Request $request
	 * @return JsonResponse|object
	 * @throws \App\Exceptions\UnauthorizedException
	 */
    public function store(\Illuminate\Http\Request $request){
	    WpUsermetum::authorizedToCreate($request);
		$user = $this->getUser();
	    $data = $request->all();
		$saved = [];
		foreach($data as $key => $value){
			if($key === 'user_id'){
				continue;
			}
			$user->setUserMetaValue($key, $value);
			$saved[$key] = $value;
		}
		if(!$saved){
			throw new BadRequestException('No data to save');
		}
        return response()->json($saved, 201);
    }
	/**
	 * @return \App\Models\BaseModel|\App\Models\User|null
	 * @throws \App\Exceptions\UnauthorizedException
	 */
	public function getUser(): \App\Models\BaseModel|null|User{
		$user = User::fromRequest();
		$user->validateCanRead(QMAuth::getUser());
		return $user;
	}
}
