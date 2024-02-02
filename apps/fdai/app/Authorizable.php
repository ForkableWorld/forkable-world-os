<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnusedParameterInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpMissingReturnTypeInspection */
namespace App;
use App\Models\BaseModel;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use App\Http\Requests\AstralRequest;
trait Authorizable {
	/**
	 * Determine if the current user can create new resources or throw an exception.
	 * @param Request $request
	 * @return void
	 * @throws AuthorizationException
	 */
	public static function authorizeToCreate(Request $request){
		$auth = static::authorizedToCreate($request);
		if(!$auth){
			throw new AuthorizationException();
		}
	}
	/**
	 * Determine if the current user can create new resources.
	 * @param Request $request
	 * @return bool
	 */
	public static function authorizedToCreate(Request $request){
		if(static::authorizable()){
			return static::getPolicy()->create($request->user());
		}
		return true;
	}
	/**
	 * Determine if the resource should be available for the given request.
	 * @param Request $request
	 * @return bool
	 */
	public static function authorizedToViewAny(Request $request){
		return !static::authorizable() || static::getPolicy()->viewAny($request->user());
	}
	protected static function getPolicy():BasePolicy{
		/** @var BaseModel $model */
		$model = static::newModel();
		$policy = $model::getGatePolicy();
		return $policy;
	}
	/**
	 * Determine if the current user has a given ability.
	 * @param Request $request
	 * @param string $ability
	 * @return void
	 * @throws AuthorizationException
	 */
	public function authorizeTo(Request $request, string $ability){
		throw_unless($this->authorizedTo($request, $ability), AuthorizationException::class);
	}
	/**
	 * Determine if the current user can delete the given resource or throw an exception.
	 * @param Request $request
	 * @return void
	 * @throws AuthorizationException
	 */
	public function authorizeToDelete(Request $request){
		$this->authorizeTo($request, 'delete');
	}
	/**
	 * Determine if the current user can update the given resource or throw an exception.
	 * @param Request $request
	 * @return void
	 * @throws AuthorizationException
	 */
	public function authorizeToUpdate(Request $request){
		$this->authorizeTo($request, 'update');
	}
	/**
	 * Determine if the current user can view the given resource or throw an exception.
	 * @param Request $request
	 * @return void
	 * @throws AuthorizationException
	 */
	public function authorizeToView(Request $request){
		$this->authorizeTo($request, 'view') && $this->authorizeToViewAny($request);
	}
	/**
	 * Determine if the resource should be available for the given request.
	 * @param Request $request
	 * @return void
	 */
	public function authorizeToViewAny(Request $request){
		if(!static::authorizable()){
			return;
		}
		$p = static::newModel()->getGatePolicy();
		$p->authorizeTo($request, 'viewAny');
	}
	/**
	 * Determine if the given resource is authorizable.
	 * @return bool
	 */
	public static function authorizable(){
		/** @var BaseModel $m */
		$m = static::newModel();
		if(!method_exists($m, 'getGatePolicy')){
			return false;
		}
		return $m->getGatePolicy();
	}
	/**
	 * Determine if the current user can view the given resource.
	 * @param Request $request
	 * @param string $ability
	 * @return bool
	 */
	public function authorizedTo(Request $request, string $ability){
		return static::getPolicy()->{$ability}($request->user(), $this->model());
		//return !static::authorizable() || Gate::check($ability, $this->resource);
	}
	/**
	 * Determine if the user can add / associate models of the given type to the resource.
	 * @param AstralRequest $request
	 * @param Model|string $model
	 * @return bool
	 */
	public function authorizedToAdd(AstralRequest $request, $model){
		if(!static::authorizable()){
			return true;
		}
		$method = 'add' . class_basename($model);
		return !method_exists($this->model()->getGatePolicy(), $method) || Gate::check($method, $this->model());
	}
	/**
	 * Determine if the user can attach models of the given type to the resource.
	 * @param AstralRequest $request
	 * @param Model|string $model
	 * @return bool
	 */
	public function authorizedToAttach(AstralRequest $request, $model){
		if(!static::authorizable()){
			return true;
		}
		$method = 'attach' . Str::singular(class_basename($model));
		return !method_exists($this->model()->getGatePolicy(), $method) ||
			Gate::check($method, [$this->model(), $model]);
	}
	/**
	 * Determine if the user can attach any models of the given type to the resource.
	 * @param AstralRequest $request
	 * @param Model|string $model
	 * @return bool
	 */
	public function authorizedToAttachAny(AstralRequest $request, $model){
		if(!static::authorizable()){
			return true;
		}
		$method = 'attachAny' . Str::singular(class_basename($model));
		return !method_exists($this->model()->getGatePolicy(), $method) || Gate::check($method, [$this->model()]);
	}
	/**
	 * Determine if the current user can delete the given resource.
	 * @param Request $request
	 * @return bool
	 */
	public function authorizedToDelete(Request $request){
		return $this->authorizedTo($request, 'delete');
	}
	/**
	 * Determine if the user can detach models of the given type to the resource.
	 * @param AstralRequest $request
	 * @param Model|string $model
	 * @param string $relationship
	 * @return bool
	 */
	public function authorizedToDetach(AstralRequest $request, $model, string $relationship){
		if(!static::authorizable()){
			return true;
		}
		$method = 'detach' . Str::singular(class_basename($model));
		return !method_exists($this->model()->getGatePolicy(), $method) ||
			Gate::check($method, [$this->model(), $model]);
	}
	/**
	 * Determine if the current user can force delete the given resource.
	 * @param Request $request
	 * @return bool
	 */
	public function authorizedToForceDelete(Request $request){
		return $this->authorizedTo($request, 'forceDelete');
	}
	/**
	 * Determine if the current user can restore the given resource.
	 * @param Request $request
	 * @return bool
	 */
	public function authorizedToRestore(Request $request){
		return $this->authorizedTo($request, 'restore');
	}
	/**
	 * Determine if the current user can update the given resource.
	 * @param Request $request
	 * @return bool
	 */
	public function authorizedToUpdate(Request $request){
		return $this->authorizedTo($request, 'update');
	}
	/**
	 * Determine if the current user can view the given resource.
	 * @param Request $request
	 * @return bool
	 */
	public function authorizedToView(Request $request){
		return $this->authorizedTo($request, 'view') && $this->authorizedToViewAny($request);
	}
}
