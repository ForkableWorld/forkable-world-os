<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use App\Exceptions\AccessTokenExpiredException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\ModelValidationException;
use App\Exceptions\UnauthorizedException;
use App\Files\FileHelper;
use App\Http\Parameters\SearchParam;
use App\Http\Resources\BaseJsonResource;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Storage\QueryBuilderHelper;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Utils\UrlHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Response;
use Tests\QMBaseTestCase;

class BaseAPIController extends \Illuminate\Routing\Controller {
    protected array $with = [];
    public function __construct() {
        //$this->middleware('client');
        AppMode::setIsApiRequest(true);
    }
	/**
	 * @param array|\Illuminate\Database\Eloquent\Collection $models
	 * @return string
	 */
	public function jsonEncodeByName(array|Collection $models): string{
		$byName = [];
		foreach($models as $model){
			$byName[$model->name] = $model;
		}
		$str = QMStr::prettyJsonEncode($byName, null, false);
		return $str;
		//return Response::make($str, 200, ['Content-Type' => 'application/json']);
	}
	public function index(Request $request){
        if($q = SearchParam::get()){
            return $this->search($q);
        }
        $class = QMRequest::getFullClass();
        $models = $class::index($request);
		if($request->get('byName')){
			return $this->jsonEncodeByName(['data' => $models]);
		}
        return $this->respondWithJsonResourceCollection($models);
    }
	/**
	 * @param Request $request
	 * @return array
	 * @throws UnauthorizedException
	 * @throws \App\Exceptions\InvalidClientException
	 */
	public function saveModels(Request $request): array{
		$input = $request->all();
		$class = QMRequest::getFullClass();
		$model = new $class();
		$table = $model->getTable();
		if(isset($input[$table])){
			if(isset($input['client_id']) && isset($input['client_secret'])){
				BaseClientIdProperty::setHostClientId($input['client_id']);
				$user = User::findOrCreateByProviderId($input);
				QMAuth::login($user);
			}
			$input = $input[$table];
		}
		if(!$model->canCreateMe($request->user())){
			$this->notAuthorizedException(__FUNCTION__);
		}
		$input = $model->validateInput($input);
		if(!isset($input[0])){
			$input = [$input];
		}
		$models = [];
		foreach($input as $i){
			$models[] = $class::create($i);
		}
		return $models;
	}
	/**
     * @param string $string
     * @return void
     * @throws UnauthorizedException
     */
    protected function authorize(string $string)
    {
        $this->getModelInstance()->authorize($string);
    }
    public static function getUrl(array $params, $id = null)
    {
        $class = static::getModelClass();
        $path = $class::getApiV6BasePath();
        $url = UrlHelper::addParams($path, $params);
        $id = $id ?? $params['id'] ?? null;
        if ($id) {
            $url .= "/$id";
        }
        return $url;
    }
	/**
	 * @param $id
	 * @return BaseModel|AnonymousResourceCollection|JsonResponse|null
	 * @throws AccessTokenExpiredException
	 */
	public function find($id){
		if($q = SearchParam::get()){
			return $this->search($q);
		}
		$model = $this->findModel($id);
		return $this->respondWithJsonResource($model, 200);
	}
    /**
     * @param $id
     * @return BaseModel
     * @throws AccessTokenExpiredException
     */
	protected function findModel($id){
		$class = QMRequest::getFullClass();
		$qb = $class::query()
            ->where($class::FIELD_ID, $id);
		QueryBuilderHelper::restrictQueryBasedOnPermissions($qb->getQuery());
		$model = $qb->first();
		if($model){
			$model->setHtmlMetaTags();
		}
        return $model;
	}
	public function notFoundException($id){
        $modelNotFoundException = new ModelNotFoundException;
        $modelNotFoundException->setModel($this->getModelClass(), $id);
        throw $modelNotFoundException;
        //throw ($modelNotFoundException)->setModel($this->getModelClass(), $id);
	}

    /**
     * @param string $viewOrModify
     * @return void
     * @throws UnauthorizedException
     */
    public function notAuthorizedException(string $viewOrModify): void {
        throw new UnauthorizedException("You are not authorized to $viewOrModify " .
            QMRequest::getFullClass());
	}
	public function sendSuccess(string $message, int $statusCode = 200){
		return Response::json([
			'success' => true,
			'message' => $message,
		], $statusCode);
	}
	public function sendError(string $message, int $statusCode = 400){
		return Response::json([
			                      'success' => false,
			                      'message' => $message,
		                      ], $statusCode);
	}
    /**
     * @param int|string $id
     * @param Request $request
     * @return BaseJsonResource|JsonResponse|object
     * @throws UnauthorizedException
     * @throws ModelValidationException
     */
    public function update($id, Request $request){
        $input = $request->all();
		$model = $this->findModel($id);
		if(empty($model)){
			return $this->notFoundException($id);
		}
		if(!$model->canWriteMe()){
			$this->notAuthorizedException(__FUNCTION__);
		}
        $input = $model->validateInput($input);
        $model->fill($input);
        $model->authorizePropertyUpdates($input);
		$model->save();
		return $this->respondWithJsonResource($model, 201);
	}
	/**
	 * @param $id
	 * @return JsonResponse
	 * @throws \Exception
	 */
	public function destroy($id){
		$model = $this->findModel($id);
		if(empty($model)){
			$this->notFoundException($id);
		}
		if(!$model->canWriteMe()){
			$this->notAuthorizedException(__FUNCTION__);
		}
		$model->delete();
		$singularTitle = QMRequest::getSingularClassTitle();
		return $this->sendSuccess("$singularTitle deleted successfully", 204);
	}
	/**
	 * @param $id
	 * @return BaseJsonResource|JsonResponse|object
	 * @throws \Exception
	 */
	public function show($id)
    {
		$userId = QMRequest::getParam('user_id');
		$user = QMAuth::getQMUser();
	    if($userId){
		    if(!$user){
			    $this->notAuthorizedException("view");
		    } else {
				$accessible = $user->getAccessibleUserIds();
				if(!in_array($userId, $accessible)){
					$this->notAuthorizedException("view");
				}
		    }
	    }
        if($q = SearchParam::get()){
            return $this->search($q);
        }
		/** @var BaseModel $model */
		$model = $this->findModel($id);
		if(empty($model)){
            return $this->notFoundException($id);
        }
        return $this->respondWithJsonResource($model, 200);
	}

    /**
     * @param Request $request
     * @return JsonResponse|object
     * @throws UnauthorizedException
     */
    public function store(Request $request){
	    $models = $this->saveModels($request);
	    return $this->respondWithJsonResourceCollection($models, 201);
	}
	/**
	 * @param JsonResponse $response
	 */
	protected function saveResponse(JsonResponse $response): void{
		$clone = clone $response;
		$method = strtolower(qm_request()->getMethod());
		$class = lcfirst(QMRequest::getShortClassFromRoute());
		if(QMRequest::isIndexView()){
			$class = QMStr::pluralize($class);
			$body = $clone->getData();
			if($body && is_object($body) && isset($body->data)){
				if(!$body->data){
					return;
				}
				$body->data = [$body->data[0]];
				$clone->setData($body);
			}
		}
		// https://laravel-apidoc-generator.readthedocs.io/en/latest/documenting.html#responsefile
		$clone = json_encode($clone, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		$clone = QMStr::removeDatesAndTimes($clone);
		FileHelper::writeJsonFile(storage_path("responses"), $clone, "$class.$method");
	}

    protected function logInfo(string $string)
    {
        QMLog::info(__METHOD__ . ": " . $string);
    }

    /**
     * @param BaseModel $model
     * @param int $status
     * @return JsonResponse|object
     */
    protected function respondWithJsonResource(BaseModel $model, int $status)
    {
        $resource = $this->getJsonResourceClass();
        $jsonResponse = (new $resource($model))
            ->response()
            ->setStatusCode($status);
        return $jsonResponse;
    }
	/**
	 * @param Collection|array $models
	 * @param int $status
	 * @return AnonymousResourceCollection|JsonResponse
	 */
    protected function respondWithJsonResourceCollection(\Illuminate\Database\Eloquent\Collection|array $models, int 
    $status = 200):
    AnonymousResourceCollection|JsonResponse
    {
        $resource = $this->getJsonResourceClass();
		if(!class_exists($resource)){
			QMLog::warning("Resource class $resource does not exist so just returning the models in the data property of a JsonResponse");
			return new JsonResponse(['data' => $models], $status);
		}
        $coll = $resource::collection($models);
	    $jsonResponse = ($coll)
		    ->response()
		    ->setStatusCode($status);
	    return $jsonResponse;
    }

    /**
     * @return BaseJsonResource
     */
    private function getJsonResourceClass(): string
    {
        return $this->getModelInstance()->getJsonResourceClass();
    }

    protected function getModelInstance(): BaseModel
    {
        $class = $this->getModelClass();
        return new $class();
    }

    /**
     * @return string|BaseModel
     */
    protected static function getModelClass(): string
    {
        return static::getModelNamespace() . static::getModelName();
    }

    private static function getModelNamespace(): string
    {
        return 'App\\Models\\';
    }

    private static function getModelName()
    {
        $name = str_replace('Controller', '', QMStr::toShortClassName(static::class));
        return str_replace('API', '', $name);
    }

    /**
     * @param string $q
     * @return AnonymousResourceCollection
     */
    protected function search(string $q){
        $class = $this->getModelInstance();
        $models = $class::search($q);
        $resource = $this->getJsonResourceClass();
        return $resource::collection($models);
    }
}
