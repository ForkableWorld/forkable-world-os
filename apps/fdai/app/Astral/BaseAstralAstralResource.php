<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnusedParameterInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection PhpMissingReturnTypeInspection */
namespace App\Astral;
use App\Buttons\Model\ModelButton;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Providers\DBQueryLogServiceProvider;
use App\Storage\QueryBuilderHelper;
use App\Types\QMStr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Fields\BelongsTo;
use App\Fields\Field;
use App\Fields\HasMany;
use App\Fields\ID;
use App\Fields\Text;
use App\Http\Requests\AstralRequest;
use App\Http\Requests\ResourceDetailRequest;
use App\Http\Requests\ResourceIndexRequest;
use App\AstralResource as AstralResource;
abstract class BaseAstralAstralResource extends AstralResource {
	public static $model = null;
	/**
	 * Indicates if the resource should be globally searchable.
	 * @var bool
	 */
	public static $globallySearchable = false;
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [10, 25, 50, 100];
	public static $searchRelations = [];
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request){
		$class = static::getModelClass();
		return $class::availableForNavigation($request);
	}
	/**
	 * Build an "index" query for the given resource.
	 * @param AstralRequest $request
	 * @param Builder $query
	 * @return Builder
	 */
	public static function indexQuery(AstralRequest $request, $query){
		// Override for resource specific stuff
		// Universal stuff should be done in \App\PerformsQueries::buildIndexQuery
		//QueryBuilderHelper::restrictQueryBasedOnPermissions($query);
		//QueryBuilderHelper::addParams($query->getQuery(), QMRequest::getReferrerParams());
		return $query;
	}
	/**
	 * @param Builder $query
	 * @param array $orderings
	 * @return Builder
	 */
	protected static function applyOrderings($query, array $orderings){
		if(empty($orderings)){
			/** @var BaseModel $model */
			$model = $query->getModel();
			$orderings = $model->getDefaultOrderings();
		}
		$query = parent::applyOrderings($query, $orderings);
		if($debug = false){
			$sql = DBQueryLogServiceProvider::toSQL($query);
		}
		return $query;
	}
	/**
	 * Get the filters available for the resource.
	 * @param Request $request
	 * @return array
	 */
	public function filters(Request $request){
		$m = $this->getModel();
		return $m->getFilters();
	}
	/**
	 * @return BaseModel|Model
	 */
	public function getModel(): BaseModel{
		return $this->resource;
	}
	/**
	 * Build a Scout search query for the given resource.
	 * @param AstralRequest $request
	 * @param \Laravel\Scout\Builder $query
	 * @return \Laravel\Scout\Builder
	 */
	public static function scoutQuery(AstralRequest $request, $query){
		return $query;
	}
	/**
	 * Build a "detail" query for the given resource.
	 * @param AstralRequest $request
	 * @param Builder $query
	 * @return Builder
	 */
	public static function detailQuery(AstralRequest $request, $query){
		return parent::detailQuery($request, $query);
	}
	/**
	 * Build a "relatable" query for the given resource.
	 * This query determines which instances of the model may be attached to other resources.
	 * @param AstralRequest $request
	 * @param Builder $query
	 * @return Builder
	 */
	public static function relatableQuery(AstralRequest $request, $query){
		return parent::relatableQuery($request, $query);
	}
	/**
	 * Get the displayable label of the resource.
	 * @return string
	 */
	public static function label(){
		$class = static::getModelClass();
		return $class::label();
	}
	/**
	 * Get the URI key for the resource.
	 * @return string
	 */
	public static function uriKey(){
		$class = static::getModelClass();
		return $class::uriKey();
		//return Str::plural(Str::kebab(static::getClassWithoutSuffix()));
	}
	public static function getModelInstance(): BaseModel{
		return new static::$model();
	}
	public static function getFields(Request $request): array{
		$model = static::getModelInstance();
		return $model->getFields();
	}
	/**
	 * @param AstralRequest $request
	 * @param $query
	 * @return Builder
	 */
	public static function relatableUsers(AstralRequest $request, $query){
		$resource = $request->route('resource'); // Returns the resource type.
		$resourceId = $request->route('resourceId'); // Returns the resource id.
		/** @var Builder $query */
		$qb = $query->getQuery();
		$qb->orders = []; // Need to unset default id ordering
		User::applyDefaultOrderings($qb);
		QueryBuilderHelper::restrictQueryBasedOnPermissions($qb);
		$qb->limit(User::DEFAULT_LIMIT);
		return $query;
	}
	/**
	 * @param AstralRequest $request
	 * @param $query
	 * @return Builder
	 */
	public static function relatableUnits(AstralRequest $request, $query){
		$resource = $request->route('resource'); // Returns the resource type.
		$resourceId = $request->route('resourceId'); // Returns the resource id.
		/** @var Builder $query */
		$qb = $query->getQuery();
		$qb->orders = []; // Need to unset default id ordering
		Unit::applyDefaultOrderings($qb);
		QueryBuilderHelper::restrictQueryBasedOnPermissions($qb);
		return $query;
	}
	public static function belongsTo(string $title = null, string $relationshipMethod = null): BelongsTo{
		$model = static::getModelInstance();
		if(!$title){
			$title = $model->getClassNameTitle();
		}
		if(!$relationshipMethod){
			$relationshipMethod = Str::camel($title);
		}
		$field = BelongsTo::make($title, $relationshipMethod, static::class);
		$field->displayUsing(function($value, $resource = null, $attribute = null){
			/** @var BaseModel $baseModel */
			$baseModel = $value->getModel();
			return $baseModel->getNameAttribute();
			$button = $baseModel->getAstralButton();
			return $button->getTailwindLink();
		})->detailLink();
		return $field;
		// Can't do it ->sortable(true);
	}
	/**
	 * @param string|null $title
	 * @param string|null $relationshipMethod
	 * @return HasMany
	 */
	public static function hasMany(string $title = null, string $relationshipMethod = null){
		$model = static::getModelInstance();
		if(!$title){
			$title = $model->getClassTitlePlural();
		}
		if(!$relationshipMethod){
			$relationshipMethod = $model->getTable();
		}
		return HasMany::make($title, $relationshipMethod, static::class)->onlyOnDetail();
	}
	/**
	 * @return BaseModel|string
	 */
	public static function getModelClass(): string{
		return static::$model;
	}
	public function getNameField(): Text{
		return Text::make('Name', function(){
			/** @var BaseModel $this */
			return $this->getNameAttribute();
		})->detailLink();
	}
	/**
	 * @return ID|Field
	 */
	public function idField(): ID{
		$m = $this->getModel();
		return ID::forModel($m);
	}
	public static function getAstralIndexUrl(array $params = []): string{
		$class = static::getModelClass();
		return $class::getAstralIndexUrl($params);
	}
	/**
	 * Get the fields displayed by the resource.
	 * @param Request $request
	 * @return array
	 */
	public function fields(Request $request){
		$m = $this->getModel();
		$fields = $m->getFields();
		$id = collect($fields)->filter(function($one){
			return $one instanceof ID;
		})->first();
		if(!$id){
			//le("Please provide id column so it's searchable");
			$fields[] = $this->idField();
		}
		return $fields;
	}
	/**
	 * Get the actions available for the resource.
	 * @param Request $request
	 * @return array
	 */
	public function actions(Request $request): array{
		return $this->getModel()->getActions($request);
	}
	public static function toResourceClassName(string $baseClass): string{
		$short = QMStr::toShortClassName($baseClass);
		$resource = str_replace("Resource", $short . "Resource", BaseAstralAstralResource::class);
		return $resource;
	}
	public function subtitle(): string{
		$m = $this->getModel();
		return $m->subtitle();
	}
	/**
	 * @param Request $request
	 * @return false
	 */
	public static function authorizedToCreate(Request $request){
		return parent::authorizedToCreate($request);
		$class = static::getModelClass();
		return $class::authorizedToCreate($request);
	}
	public static function getRequest(): AstralRequest{
		$request = resolve(AstralRequest::class);
		return $request;
	}
	public static function getAstralRequest(): AstralRequest{
		return self::getRequest();
	}
	/**
	 * @return string
	 * returns 'App\Http\Controllers\SearchController@index'
	 */
	public static function getRoutControllerMethod(): string{
		return request()->route()->action['uses'];
	}
	/**
	 * @param null $request
	 * @return bool
	 */
	public static function isIndex($request = null){
		if(!$request){
			$request = static::getRequest();
		}
		return $request instanceof ResourceIndexRequest;
	}
	/**
	 * @param null $request
	 * @return bool
	 */
	public static function isDetail($request = null){
		if(!$request){
			$request = static::getRequest();
		}
		return $request instanceof ResourceDetailRequest;
	}
	/**
	 * @param null $request
	 * @return bool
	 */
	public static function isCreate($request = null){
		if(!$request){
			$request = static::getRequest();
		}
		return $request instanceof AstralRequest && $request->editMode === 'create';
	}
	/**
	 * @param null $request
	 * @return bool
	 */
	public static function isUpdate($request = null){
		if(!$request){
			$request = static::getRequest();
		}
		return $request instanceof AstralRequest && $request->editMode === 'update';
	}
	public static function getAstralIndexPath(string $key = null): string{
		$class = static::getModelClass();
		return $class::getAstralIndexPath();
	}
	/**
	 * Get the cards available for the request.
	 * @param Request $request
	 * @return array
	 */
	public function cards(Request $request): array{
		return $this->getModel()->getCards($request);
	}
	public static function button(): QMButton{
		$b = new ModelButton(static::newModel());
		$b->setTextAndTitle(static::label());
		$b->setUrl(static::getAstralIndexUrl());
		return $b;
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getIndexUrl(array $params){
		return static::getAstralIndexUrl($params);
	}
	/** @noinspection PhpUnused */
	/**
	 * @param AstralRequest $request
	 * @param $query
	 * @return Builder
	 */
	public static function relatableVariables(AstralRequest $request, $query): Builder{
		$resource = $request->route('resource'); // Returns the resource type.
		$resourceId = $request->route('resourceId'); // Returns the resource id.
		/** @var Builder $query */
		$qb = $query->getQuery();
		$qb->orders = []; // Need to unset default id ordering
		Variable::applyDefaultOrderings($qb);
		QueryBuilderHelper::restrictQueryBasedOnPermissions($qb);
		if(AstralRequest::isCreateOrAssociatableSearch()){
			$qb->where(Variable::FIELD_IS_PUBLIC, 1);
		}
		$qb->limit(Variable::DEFAULT_LIMIT);
		//$qb->columns = Variable::getImportantColumns();
		return $query;
	}
	/** @noinspection PhpUnused */
	/**
	 * @param AstralRequest $request
	 * @param $query
	 * @return Builder
	 */
	public static function relatableUserVariables(AstralRequest $request, $query): Builder{
		$resource = $request->route('resource'); // Returns the resource type.
		$resourceId = $request->route('resourceId'); // Returns the resource id.
		/** @var Builder $query */
		$qb = $query->getQuery();
		$qb->orders = []; // Need to unset default id ordering
		UserVariable::applyDefaultOrderings($qb);
		QueryBuilderHelper::restrictQueryBasedOnPermissions($qb);
		$qb->limit(UserVariable::DEFAULT_LIMIT);
		//$qb->columns = Variable::getImportantColumns();
		return $query;
	}
	/**
	 * @return string
	 */
	public function title(){
		$m = static::getModelClass();
		return $m::$title;
	}
	/**
	 * Get the logical group associated with the resource.
	 *
	 * @return string
	 */
	public static function group(){
		$m = static::getModelClass();
		return $m::group();
	}
	/**
	 * @return array
	 */
	public static function searchableColumns(){
		$m = static::getModelClass();
		return $m::searchableColumns();
	}
	/**
	 * Determine if this resource is searchable.
	 *
	 * @return bool
	 */
	public static function searchable(){
		$m = static::getModelClass();
		return $m::searchable();
	}
	/**
	 * @return bool
	 */
	public static function authorizable(){
		return parent::authorizable();
	}
}
