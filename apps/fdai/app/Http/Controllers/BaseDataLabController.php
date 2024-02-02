<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoIdException;
use App\Models\BaseModel;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Types\QMStr;
use App\Utils\QMRoute;
use Flash;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Request;
use Response;
use View;
/** @OA\OpenApi(
 *   basePath="/api/v1",
 *   @OA\Info(
 *     title="Laravel Generator APIs",
 *     version="1.0.0",
 *   )
 * )
 * This class should be parent class for other API controllers
 * Class AppBaseController
 */
class BaseDataLabController extends Controller {
	const ACTION_CREATE = "create";
	const ACTION_DESTROY = "destroy";
	const ACTION_EDIT = "edit";
	const ACTION_INDEX = "index";
	const ACTION_SHOW = "show";
	const ACTION_STORE = "store";
	const ACTION_UPDATE = "update";
	/**
	 * @param $id
	 * @return BaseModel
	 */
	public function find($id): ?BaseModel{
		$class = QMRequest::getFullClass();
		/** @var BaseModel $model */
		$model = $class::findByNameLikeOrId($id);
		if($model){
			$model->setHtmlMetaTags();
		}
		return $model;
	}
	/**
	 * @return Route[]
	 */
	public static function getRoutes(): array{
		return QMRoute::getRoutesLike('datalab', "GET");
	}
	/**
	 * @return QMRoute[]
	 */
	public static function getIndexRoutes(): array{
		return QMRoute::getIndexRoutesLike('datalab');
	}
	/**
	 * Show the form for editing the specified UserTag.
	 * @param int $id
	 * @return Factory|RedirectResponse|Redirector|\Illuminate\View\View|Response
	 */
	public function edit($id){
		$model = $this->find($id);
		if(empty($model)){
			return $this->notFoundRedirectToIndex($id);
		}
		if(!$model->canWriteMe()){
			return $this->unauthorizedRedirectToIndex($id, "modify");
		}
		return view('model-edit')->with('model', $model);
	}
	/**
	 * @param $id
	 * @return RedirectResponse|Redirector
	 */
	public function notFoundRedirectToIndex($id){
		$singularTitle = QMRequest::getSingularClassTitle();
		Flash::error("$singularTitle $id not found");
		return $this->redirectToIndex();
	}
	/**
	 * @param $id
	 * @param string $viewOrModify
	 * @return RedirectResponse|Redirector
	 */
	public function unauthorizedRedirectToIndex($id, string $viewOrModify){
		Flash::error(QMRequest::getNotAuthorizedMessage($id, $viewOrModify));
		return $this->redirectToIndex();
	}
	/**
	 * Update the specified Variable in storage.
	 * @param int|string $id
	 * @return RedirectResponse
	 * @throws ModelValidationException
	 * @throws InvalidAttributeException
	 */
	public function update($id){
		$model = $this->find($id);
		if(empty($model)){
			return $this->notFoundRedirectToIndex($id);
		}
		if(!$model->canWriteMe()){
			return $this->unauthorizedRedirectToIndex($id, "modify");
		}
		$data = Request::all();
		$singularTitle = QMRequest::getSingularClassTitle();
		foreach($data as $key => $value){
			if(str_starts_with($key, '_')){
				continue;
			}
			$model->setAttribute($key, $value);
		}
		$model->saveOrFail();
		Flash::success("$singularTitle updated successfully.");
		try {
			return redirect($model->getDataLabShowUrl());
		} catch (NoIdException $e) {
			/** @var \LogicException $e */
			throw $e;
		}
	}
	/**
	 * Show the form for creating a new BaseModel.
	 * @return Response|Factory|RedirectResponse|Redirector|View
	 */
	public function create(){
		return view(qm_request()->getViewPathByType('create'));
	}
	/**
	 * Store a newly created BaseModel in storage.
	 * @return Response|Factory|RedirectResponse|Redirector|View
	 */
	public function store(){
		$input = Request::all();
		$class = QMRequest::getFullClass();
		$model = $class::create($input);
		$title = QMRequest::getSingularClassTitle();
		Flash::success("$title saved successfully.");
		return redirect($model->getDataLabShowUrl());
	}
	/**
	 * Display the specified BaseModel.
	 * @param int $id
	 * @return Factory|RedirectResponse|Redirector|Response|string|View
	 */
	public function show($id){
		$model = $this->find($id);
		if(empty($model)){
			return $this->notFoundRedirectToIndex($id);
		}
		$user = QMAuth::getQMUser()->l();
		if(!$user->can('view', $model)){
			return $this->unauthorizedRedirectToIndex($id, "view");
		}
		if(QMRequest::getParam('email')){
			return $model->getEmailContent();
		}
		$view = view(qm_request()->getViewPathByType('show'))
			->with(QMRequest::getSingularCamelClassTitle(get_class($model)), $model)
			->with('model', $model);
		return self::getShowView($model);
	}
	/**
	 * @param \App\Models\BaseModel $model
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\View|Factory
	 */
	public static function getShowView(BaseModel $model): \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View{
		$view = view(qm_request()->getViewPathByType('show'))
			->with(QMRequest::getSingularCamelClassTitle(get_class($model)), $model)
			->with('model', $model);
		return $view;
	}
	/**
	 * Remove the specified BaseModel from storage.
	 * @param int $id
	 * @return Response|Factory|RedirectResponse|Redirector|View
	 * @throws \Exception
	 */
	public function destroy($id){
		$model = $this->find($id);
		if(empty($model)){
			return $this->notFoundRedirectToIndex($id);
		}
		if(!$model->canWriteMe()){
			return $this->unauthorizedRedirectToIndex($id, "modify");
		}
		$model->delete();
		$name = QMRequest::getSingularClassTitle();
		Flash::error("$name deleted successfully.");
		return $this->redirectToIndex();
	}
	/**
	 * @return RedirectResponse|Redirector
	 */
	protected function redirectToIndex(){
		$name = $this->getRouteNameByType('index');
		return redirect(route($name));
	}
	/**
	 * @param string $function
	 * @return string
	 */
	protected function getRouteNameByType(string $function): string{
		$route = QMStr::between(url()->current(), 'datalab/', '/');
		return "datalab.$route." . $function;
	}
	/**
	 * Display a listing of the GlobalVariableRelationshipController.
	 * @return Factory|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\View
     */
	public function dashboard(){
		return view('datalab');
	}
	public static function getURL(array $params = []): string{
		$base = \App\Utils\Env::getAppUrl() . "/datalab";
		if(static::class === BaseDataLabController::class){
			return $base;
		}
		$class = (new \ReflectionClass(static::class))->getShortName();
		$class = str_replace("Controller", "", $class);
		$class = QMStr::pluralize($class);
		$class = lcfirst($class);
		return $base . "/" . $class;
	}
}
