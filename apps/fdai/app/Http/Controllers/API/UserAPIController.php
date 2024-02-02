<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\API;
use App\AppSettings\AppSettings;
use App\Exceptions\AccessTokenExpiredException;
use App\Exceptions\BadRequestException;
use App\Exceptions\InvalidClientException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoChangesException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NotFoundException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\BaseAPIController;
use App\Http\Resources\UserResource;
use App\Logging\QMLog;
use App\Models\Application;
use App\Models\Collaborator;
use App\Models\OAClient;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserEmailProperty;
use App\Properties\User\UserProviderIdProperty;
use App\Properties\User\UserUserLoginProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Storage\QueryBuilderHelper;
use App\Types\QMStr;
use Illuminate\Http\JsonResponse;
/** Class UserController
 * @package App\Http\Controllers\API
 */
class UserAPIController extends BaseAPIController {
	public function me(\Illuminate\Http\Request $request){
		$u = QMAuth::getUser();
		$u = $u->getUser();
		return $this->respondWithJsonResource($u, 200);
	}
	public function metadata(){
		$u = QMAuth::getUser();
		$u = $u->getUser();
		$meta = $u->generateNftMetadata();
		return response()->json($meta);
	}
	/**
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
	 * @throws \App\Exceptions\UnauthorizedException
	 */
	public function index(\Illuminate\Http\Request $request)
    {
		if($this->loggedInUserOwnsApplication()){
			/** @var Application $app */
			$app = Application::fromRequest();
			$users = $app->getUsersWithAccessTokens();
			return UserResource::collection($users);   
		}
	    $users = [];
        try {
            $data = $request->all();
            $client = OAClient::authorizeBySecret($data);
			$providerId = UserProviderIdProperty::pluck($data);
			if($providerId){
				$u = User::findByClientUserId($providerId, $client->client_id);
				if($u){$users[] = $u;}
			} else {
				$users = $client->getUsers();
			}
        } catch (\Throwable $e) {
	        /** @var User $user */
	        $user = auth()->user();
			if(!$user){
				throw new UnauthorizedException();
			}
	        $users = $user->getPatients();
        }
        return UserResource::collection($users);
    }

    /**
     * @param null $id
     * @return \Illuminate\Http\JsonResponse|object
     * @throws UnauthorizedException
     * @throws AccessTokenExpiredException
     */
    public function find($id = null) {
        if(!$id){
            $u = QMAuth::getUser();
            return $u->getUser();
        }
        $class = QMRequest::getFullClass();
        $qb = $class::query()
            ->where($class::FIELD_ID, $id);
        QueryBuilderHelper::restrictQueryBasedOnPermissions($qb->getQuery());
        /** @var User $model */
        $model = $qb->first();
        if($model){
            $model->setHtmlMetaTags();
        } else {
            throw new NotFoundException("User $id not found");
        }
        $model->validateCanRead();
        return $this->respondWithJsonResource($model, 200);
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse|object
     * @throws NoChangesException
     * @throws ModelValidationException
     */
    public function store(\Illuminate\Http\Request $request){
        $input = $request->all();
        try {
            $client = OAClient::authorizeBySecret($input);
        } catch (InvalidClientException $e) {
			foreach($input as $k => $v){
				if(!User::hasColumn($k)){
					unset($input[$k]);
					QMLog::error("UserAPIController::store() - $k is not a valid User column");
				}
			}
            $user = User::create($input);
            return $this->respondWithJsonResourceCollection([$user], 201);
        }
        $providerId = UserProviderIdProperty::pluck($input);
        $login = UserUserLoginProperty::pluck($input);
		if(!$providerId && !$login){
			$email = UserEmailProperty::pluck($input);
			if(!$email){
				throw new BadRequestException("Please provide 'user_email' or your user's id as a 'provider_id' property in your request");
			}
			$login = QMStr::slugify($email);
		}
        if(!$providerId){
            $providerId = $login;
        }
        if(!$providerId){
            throw new BadRequestException("Please provide your user's id as a 'provider_id' property in your request");
        }
        $user = User::whereProviderId($providerId)
            ->where(User::FIELD_REG_PROVIDER, $client->getId())
            ->first();
        $input[User::FIELD_DELETED_AT] = null;
        $input[User::FIELD_PROVIDER_ID] = $providerId;
        $input[User::FIELD_REG_PROVIDER] = $client->getId();
        $input[User::FIELD_USER_LOGIN] = $login ?? $client->getId() ."-".QMStr::slugify($providerId);
        if($user){
            $user->fill($input);
            $result = $user->save();
            $changes = $user->getChanges();
            if(!$changes) {
                throw new NoChangesException($input);
            }
        } else {
            $user = User::createUserFromClient($input);
        }
        return $this->respondWithJsonResourceCollection([$user], 201);
    }
	/**
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 */
	public function analyze(\Illuminate\Http\Request $request){
		$userId = $request->get('user_id');
		if(!$userId){
			$user = QMAuth::getUser();
			$userId = $user->getId();
		}
		$user = User::find($userId);
		if(!$user){
			throw new NotFoundException("User $userId not found");
		}
		$user->analyze("API Request");
		return $this->respondWithJsonResource($user, 200);
	}
	private function loggedInUserOwnsApplication(){
		$clientId = BaseClientIdProperty::fromRequest();
		return Collaborator::userIsCollaboratorOrAdmin($clientId);
	}
}
