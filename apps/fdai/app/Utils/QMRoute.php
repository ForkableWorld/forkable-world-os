<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App\Buttons\QMButton;
use App\Buttons\RouteButton;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileHelper;
use App\Http\Controllers\BaseDataLabController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\QMAuthenticate;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\WpLink;
use App\Parameters\IdPathParameter;
use App\Parameters\LimitParameter;
use App\Parameters\OffsetParameter;
use App\Parameters\QMParameter;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Storage\Memory;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\QMColor;
use Illuminate\Http\Request;
use Route;
use Spatie\Menu\Link;
use Spatie\Menu\Menu;
class QMRoute extends \Illuminate\Routing\Route {
	/**
	 * @var array
	 */
	private static $routes;
	private static $paths;
	public $route;
	public $wpLink;
	public $fontAwesomeHtml;
	public $tooltip;
	public $image;
	public $title;
	public $link;
	/**
	 * @var string
	 */
	public $titleFromUrl;
	/**
	 * @var string
	 */
	public $titleFromName;
	public $fontAwesome;
	public $backgroundColor;
	/**
	 * @param $route
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct(\Illuminate\Routing\Route $route){
		$this->route = $route;
		//parent::__construct($route->methods(), $route->uri(), $route->action); Doesn't set correct uri
		foreach($route as $key => $value){
			$this->$key = $value;
		}
	}
	/**
	 * @return static[]
	 */
	public static function getAdminMiddlewareRoutes(): array{
		return static::getRoutesWithMiddleware(AdminMiddleware::NAME);
	}
	/**
	 * @return static[]
	 */
	public static function getAdminPathRoutes(): array{
		return self::getRoutesLike('admin/');
	}
	/**
	 * @param QMRoute[] $routes
	 * @param array $excludeLike
	 * @return QMRoute[]
	 */
	public static function filterRoutesLike(array $routes, array $excludeLike): array{
		$toKeep = [];
		foreach($routes as $route){
			$url = $route->uri();
			foreach($excludeLike as $str){
				if(stripos($url, $str) !== false){
					continue 2;
				}
			}
			$toKeep[$url] = $route;
		}
		return $toKeep;
	}
	/**
	 * @param QMRoute[] $routes
	 * @return QMRoute[]
	 */
	public static function filterRoutesBasedOnAuthentication(array $routes): array{
		$user = QMAuth::getQMUser();
		if(!$user){
			$routes = collect($routes)->filter(function(QMRoute $route){
				return !$route->requiresAuth() && !$route->isAdmin();
			})->all();
		}
		if(!$user || !$user->isAdmin()){
			$routes = collect($routes)->filter(function($route){
				/** @var QMRoute $route */
				$middleware = $route->getMiddleware();
				return !$route->isAdmin() &&
					!empty($middleware); // Some packages like Pragmarx Health Check don't specify any middleware
			})->all();
		}
		return $routes;
	}
	public static function findByUri(string $uri, string $method): QMRoute{
		$routes = self::getRoutes();
		foreach($routes as $route){
			$methods = $route->methods();
			if($route->uri === $uri && in_array($method, $methods)){
				return $route;
			}
		}
		throw new \LogicException("No route for $uri");
	}

    public static function findByControllerAndAction(string $class): QMRoute
    {
        $routes = self::getRoutes();
        foreach($routes as $route){
            $action = $route->action['uses'];
            if(is_object($action)){
                continue;
            }
            if(str_contains($action, $class)){
                return $route;
            }
        }
        le("No route for $class");
    }

    public function getLink(): Link{
		if($this->link){
			return $this->link;
		}
		$url = $this->getUrl();
		$link = Link::to("/$url", $this->getTitleWithIcon());
		$link->setAttribute('target', '_blank');
		$link->setAttribute('title', $this->getTooltip() . " @ $url");
		return $this->link = $link;
	}
	public function getTitleWithIcon(): string{
		$titleText = $this->getTitleAttribute();
		$icon = $this->getFontAwesomeHtml();
		return $icon . $titleText;
	}
	public function getFontAwesome(): string{
		if($this->fontAwesome){
			return $this->fontAwesome;
		}
		if($model = $this->getModel()){
			return $this->fontAwesome = $model->getFontAwesome();
		}
		if(!AppMode::isApiRequest()){ // TOO SLOW!!!
			return $this->fontAwesome = FontAwesome::findIconLike($this->getTitleAttribute(), FontAwesome::POPOUT);
		}
		return $this->fontAwesome = FontAwesome::QUESTION_CIRCLE;
	}
	public function getBackgroundColor(): string{
		if($this->backgroundColor){
			return $this->backgroundColor;
		}
		if($model = $this->getModel()){
			return $this->backgroundColor = $model->getColor();
		}
		return $this->backgroundColor = QMColor::HEX_BLUE;
	}
	public function getFontAwesomeHtml(): string{
		return $this->fontAwesomeHtml = FontAwesome::html($this->getFontAwesome());
	}
	public function getImage(): string{
		if($this->image){
			return $this->image;
		}
		if($model = $this->getModel()){
			return $this->image = $model->getImage();
		}
		$image = ImageUrls::QUESTION_MARK;
		if(!AppMode::isApiRequest()){ // TOO SLOW!!!
			$image = ImageUrls::findConstantValueWithNameLike($this->getTitleAttribute(), ImageUrls::QUESTION_MARK);
		}
		return $this->image = $image;
	}
	public function getUrl($params): string{
		$route = $this->getRoute();
		$url = $route->uri();
		foreach($params as $key => $value){
			/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
			$placeholder = "{{$key}}";
			if(str_contains($url, $placeholder)){
				$url = str_replace($placeholder, $value, $url);
				unset($params[$key]);
			}
		}
		return url($url, $params);
	}
	public static function isAnalysisProgress(): bool{
		/** @var BaseModel $fullClassName */
		$fullClassName = QMRequest::getFullClassFromRoute();
		$analyzable = $fullClassName::ANALYZABLE;
		$param = QMRequest::getParam([QMRequest::PARAM_ANALYSIS, QMRequest::PARAM_ANALYZABLE]);
		if($param && !$analyzable){
			throw new \LogicException("$fullClassName is not an analyzable!");
		}
		return (bool)$param;
	}
	public static function isTrash(): bool{
		$param = QMRequest::getParam([QMRequest::PARAM_TRASH, QMRequest::PARAM_TRASHED, QMRequest::PARAM_DELETED]);
		return (bool)$param;
	}
	public static function getCurrent(Request $request = null): QMRoute{
		if($request){
			$route = $request->path();
		} else{
			$route = Route::getCurrentRoute();
		}
		return new QMRoute($route);
	}
	/**
	 * @return BaseModel
	 */
	public function getModel(): ?BaseModel{
		$class = $this->getClass();
		if(!$class){
			return null;
		}
		try {
			return new $class();
		} catch (\Throwable $e) {
			le("Could not instantiate $class because " . $e->getMessage());
			throw new \LogicException();
		}
	}
	/**
	 * @return BaseModel
	 */
	public function getClass(): ?string{
		$class = QMRequest::getFullClassFromRoute($this->uri);
		if(!class_exists($class)){
			QMLog::debug("$class class not found for route $this->uri");
			return null; // Probably Adminer or some library route
		}
		return $class;
	}
	public function getTooltip(): string{
		if($this->tooltip){
			return $this->tooltip;
		}
		$model = $this->getModel();
		if($model){
			return $this->tooltip = $model->getSubtitleAttribute();
		}
		$route = $this->getRoute();
		$url = $route->uri();
		/** @var Menu $menu */
		if(stripos($url, 'admin/api') !== false){
			$url = $route->uri();
		}
		$name = $route->getName();
		$as = $route->getAction('as');
		$tooltip = $name ?? $as ?? $url;
		$titleTooltip = QMStr::titleCaseSlow(str_replace(".", " ", $tooltip));
		return $titleTooltip;
	}
	public function getDescriptionHtml(string $boundUri = null): string{
		$str = "\n<div>" . "\n\t" . $this->getFontAwesomeHtml() . $this->getTooltip() . "\n</div>";
		if($this->isAPIEndpoint()){
			$uri = str_replace('api/v6', 'datalab', $boundUri);
			$str .= "\n<div>" . "\n\t" . "<a href=\"$uri\" target='_blank'>Check out your " . $this->getClassTitle() .
				" in the DataLab!</a>" . "\n</div>";
		}
		return $str;
	}
	public function getSubtitleAttribute(): string{
		return $this->getTooltip();
	}
	public function getClassTitle(): string{
		if($this->isIndex()){
			return $this->getModel()->getClassTitlePlural();
		} else{
			return $this->getModel()->getClassNameTitle();
		}
	}
	/**
	 * @return \Illuminate\Routing\Route
	 */
	public function getRoute(): \Illuminate\Routing\Route{
		return $this->route;
	}
	/**
	 * @return string
	 */
	public function getTitleAttribute(): string{
		if($this->title){
			return $this->title;
		}
		if($this->isAPIEndpoint()){
			/** @var BaseModel $model */
			$model = $this->getModel();
			$title = $this->getActionTitle() . " ";
			if($this->isIndex()){
				$title .= $model->getClassTitlePlural();
			} else{
				$title .= "a " . $model->getClassNameTitle();
			}
			return $this->title = $title//." Endpoint"
				;
		}
		$name = $this->getName();
		if($name){
			$arr = explode('.', $name);
			if(count($arr) === 3){
				$action = $arr[2];
				$pluralTitle = QMStr::routeToTitle($arr[1]);
				if($action === BaseDataLabController::ACTION_CREATE){
					return $this->title = "Create " . QMStr::singularize($pluralTitle);
				}
				if($action === BaseDataLabController::ACTION_DESTROY){
					return $this->title = "Delete " . QMStr::singularize($pluralTitle);
				}
				if($action === BaseDataLabController::ACTION_EDIT){
					return $this->title = "Edit " . QMStr::singularize($pluralTitle);
				}
				if($action === BaseDataLabController::ACTION_INDEX){
					return $this->title = "List $pluralTitle";
				}
				if($action === BaseDataLabController::ACTION_SHOW){
					return $this->title = "View " . QMStr::singularize($pluralTitle);
				}
				if($action === BaseDataLabController::ACTION_STORE){
					return $this->title = "Store " . QMStr::singularize($pluralTitle);
				}
				if($action === BaseDataLabController::ACTION_UPDATE){
					return $this->title = "Update " . QMStr::singularize($pluralTitle);
				}
			}
		}
		$titleFromUrl = $this->getTitleFromUrl();
		$titleFromName = $this->getTitleFromName();
		if($titleFromName && strlen($titleFromName) > strlen($titleFromUrl)){
			$title = $titleFromName;
		} else{
			$title = $titleFromUrl;
		}
		$title = str_replace("Admin ", '', $title);
		$title = QMStr::titleCaseSlow($title);
		return $this->title = $title;
	}
	protected function getTitleFromName(): ?string{
		if($this->titleFromName){
			return $this->titleFromName;
		}
		$route = $this->getRoute();
		$as = $route->getAction("as");
		if(!$as){return null;}
		$text = str_replace("datalab.", "", $as);
		$text = str_replace(".index", "", $text);
		$text = str_replace(".", " ", $text);
		$text = str_replace("-", " ", $text);
		return $this->titleFromName = QMStr::camelToTitle($text);
	}
	protected function getTitleFromUrl(): string{
		if($this->titleFromUrl){
			return $this->titleFromUrl;
		}
		$route = $this->getRoute();
		$uri = $route->uri;
		$text = str_replace("datalab/", "", $uri);
		$text = str_replace("api/v2", "", $text);
		$text = str_replace("/", " ", $text);
		if(strpos($text, 'admin ') === 0){
			$exploded = explode($text, " ");
			if(count($exploded) === 3){
				$text = QMStr::camelToTitle($exploded[1]);
				$text = implode(" ", [$exploded[0], $text, $exploded[2]]);
			}
		}
		return $this->titleFromUrl = QMStr::titleCaseSlow($text);
	}
	/**
	 * @return WpLink[]
	 */
	public static function generateWpLinksForRoutes(): array{
		$routes = self::getDataLabRoutes();
		$wpLinks = [];
		foreach($routes as $route){
			$wpLinks[] = $route->getWpLink();
		}
		return $wpLinks;
	}
	/**
	 * @return WpLink
	 */
	public function getWpLink(): WpLink{
		if($this->wpLink){
			return $this->wpLink;
		}
		$title = $this->getTitleAttribute();
		$params = [
			WpLink::FIELD_LINK_URL => $this->getUrl(),
			WpLink::FIELD_LINK_NAME => $title,
			WpLink::DEFAULT_IMAGE => $this->getImage(),
			WpLink::FIELD_LINK_NOTES => json_encode($this->action),
			WpLink::FIELD_LINK_TARGET => "self",
			WpLink::FIELD_LINK_DESCRIPTION => $this->getTooltip(),
			WpLink::FIELD_LINK_VISIBLE => $this->getVisibility(),
			WpLink::FIELD_LINK_OWNER => UserIdProperty::USER_ID_SYSTEM,
			WpLink::FIELD_CLIENT_ID => BaseClientIdProperty::CLIENT_ID_SYSTEM,
		];
		return $this->wpLink = WpLink::firstOrCreate($params);
	}
	public function getButton(): QMButton{
		if($this->isIndex()){
			$m = $this->getModel();
			if($m){
				return $m->getDataLabIndexButton();
			}
		}
		$b = new RouteButton($this);
		return $b;
	}
	public function getMiddleWare(): array{
		$all = $this->getAction('middleware') ?? [];
		if(is_string($all)){
			$all = [$all];
		}
		return $all;
	}
	/**
	 * @param $type
	 * @return bool
	 */
	public function hasMiddleWare($type): bool{
		return in_array($type, $this->getMiddleWare());
	}
	/**
	 * @return bool
	 */
	public function requiresAdmin(): bool{
		return $this->hasMiddleWare(AdminMiddleware::NAME);
	}
	/**
	 * @return bool
	 */
	public function requiresAuth(): bool{
		return $this->hasMiddleWare(QMAuthenticate::NAME);
	}
	public function getVisibility(): string{
		if($this->requiresAdmin()){
			return "admin";
		}
		if($this->requiresAuth()){
			return "auth";
		}
		return "open";
	}
	/** @noinspection PhpUnused */
	/**
	 * @return QMRoute[]
	 */
	public static function getRoutes(): array{
		if($qmRoutes = self::$routes){
			return $qmRoutes;
		}
		self::$routes = $qmRoutes = Memory::getByPrimaryKey(Memory::ROUTES);
		if($qmRoutes){
			return $qmRoutes;
		}
		$routes = Route::getRoutes();
		$qmRoutes = [];
		foreach($routes as $route){
			if($route->uri === "/"){
				continue;
			}
			$qmRoutes[] = new static($route);
		}
		Memory::setByPrimaryKey(Memory::ROUTES, $qmRoutes);
		return self::$routes = $qmRoutes;
	}
	/**
	 * @param string $pattern
	 * @return QMRoute[]
	 */
	public static function getIndexRoutesLike(string $pattern): array{
		$routes = self::getRoutesLike($pattern, "GET");
		$all = collect($routes)->filter(function(QMRoute $r){
			$name = $r->getName();
			if(!$name){
				return false;
			}
			return str_contains($name, '.index');
		})->all();
		return QMArr::indexBy($all, 'uri');
	}
	/**
	 * @param string $pattern
	 * @param string|null $method
	 * @return QMRoute[]
	 */
	public static function getRoutesLike(string $pattern, string $method = null): array{
		$routes = self::getRoutes();
		$matches = [];
		foreach($routes as $route){
			if(strpos($route->uri, $pattern) !== false){
				if($method){
					$methods = $route->methods();
					if(!in_array($method, $methods)){
						continue;
					}
				}
				$matches[] = $route;
			}
		}
		return $matches;
	}
	/**
	 * @return QMRoute[]
	 */
	public static function getDataLabRoutes(): array{
		return self::getRoutesLike('datalab/');
	}
	/**
	 * @return QMRoute[]
	 */
	public static function getDataLabIndexRoutes(): array{
		return self::getIndexRoutesLike('datalab/');
	}
	/**
	 * @return QMRoute[]
	 */
	public static function getPhysicianIndexRoutes(): array{
		return self::getIndexRoutesLike('physician/');
	}
	/**
	 * @param bool $excludeAPI
	 * @return QMRoute[]
	 */
	public static function getHorizonRoutes(bool $excludeAPI = true): array{
		$routes = self::getRoutesLike('horizon');
		if(!$excludeAPI){
			return $routes;
		}
		$matches = [];
		foreach($routes as $r){
			if(strpos($r->uri, '/api/') === false){
				$matches[] = $r;
			}
		}
		return $matches;
	}
	/**
	 * @return QMRoute[]
	 */
	public static function getHealthPanelRoutes(): array{
		return self::getRoutesLike('health/');
	}
	/**
	 * @return QMRoute[]
	 */
	public static function getDevRoutes(): array{
		$routes = self::getRoutesLike('dev/');
		if(!$routes){
			le("No dev routes!");
		}
		return $routes;
	}
	/**
	 * @return QMRoute[]
	 */
	public static function getAPIRoutes(): array{
		$routes = self::getRoutesLike('api/v6', null);
		return $routes;
		$documented = [];
		foreach($routes as $route){
			if($route->getExampleResponse()){
				$documented[] = $route;
			}
		}
		//return [self::findByUri('api/v6/global_variable_relationships', 'GET')];
		return $documented;
	}
	/**
	 * @param string $middleware
	 * @return QMRoute[]
	 */
	public static function getRoutesWithMiddleware(string $middleware): array{
		$routes = self::getRoutes();
		$matches = [];
		foreach($routes as $route){
			if($route->hasMiddleWare($middleware)){
				$matches[] = $route;
			}
		}
		return $matches;
	}
	/**
	 * @return QMRoute[]
	 */
	public static function getRoutesWithoutMiddleware(): array{
		$routes = self::getRoutes();
		$matches = [];
		/** @var \Illuminate\Routing\Route $route */
		foreach($routes as $route){
			$all = $route->getMiddleWare();
			if(!$all){
				$matches[] = $route;
			}
		}
		return $matches;
	}
	public function isAdmin(): bool{
		return $this->hasMiddleWare(AdminMiddleware::NAME);
	}
	/**
	 * @return BaseModel
	 */
	public function getFullClassName(): string{
		return QMRequest::getFullClassFromRoute($this->uri);
	}
	public function isAPIEndpoint(): bool{
		return stripos($this->uri, 'api/v6/') !== false;
	}
	public function isIndex(): bool{
		$actionAs = $this->getAction('as');
		if(!$actionAs){return false;}
		return str_contains($actionAs, '.index');
	}
	public function getActionTitle(): string{
		$method = $this->getActionMethod();
		if($method === "index"){
			return "List";
		}
		if($method === "destroy"){
			return "Delete";
		}
		if($method === "show" && $this->isAPIEndpoint()){
			return "Get";
		}
		return ucfirst($method);
	}
	public function getMethod(): string{
		return $this->methods[0];
	}
	public function getExampleResponse(){
		try {
			$data = FileHelper::readJsonFile($this->getExampleResponsePath());
		} catch (QMFileNotFoundException $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			return null;
		}
		return $data->original ?? $data;
	}
	public function getExampleResponseJson(): string{
		return json_encode($this->getExampleResponse(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}
	public function getExampleResponsePath(): string{
		$m = $this->getModel();
		$short = $m->getShortClassName();
		if($this->isIndex()){
			$short = QMStr::pluralize($short);
		}
		$filename = lcfirst($short) . '.' . strtolower($this->getMethod()) . '.json';
		return storage_path('responses/' . $filename);
	}
	/**
	 * @return Operation
	 * @noinspection PhpUnused
	 * Keep this for when we switch to OpenAPI v3
	 */
	public function getOpenApiOperation(): Operation{
		$method = $this->getMethod();
		$class = "\OpenApi\Annotations\\" . ucfirst(strtolower($method));
		/** @var Operation $o */
		$o = new $class([]);
		$path = $this->uri;
		$path = QMStr::replace_between($path, "{", "}", "id");
		$o->path = '/' . $path;
		$o->method = strtolower($method);
		$o->description = $this->getSubtitleAttribute();
		$o->summary = $this->getTitleAttribute();
		$o->parameters = $this->getParameters();
		$o->operationId = $this->getOperationId();
		$o->externalDocs = $this->getExternalDocs();
		$o->responses = [$this->getResponse()];
		return $o;
	}
	public function getSwaggerPath(): Path{
		$method = $this->getMethod();
		static::$paths[$this->uri] = $path = static::$paths[$this->uri] ?? new Path([]);
		$uri = '/' . QMStr::replace_between($this->uri, "{", "}", "id");
		$path->path = $uri;
		$operationClass = "\OpenApi\Annotations\\" . ucfirst(strtolower($method));
		/** @var Operation $o */
		$o = new $operationClass([]);
		$o->tags = [];
		$o->summary = $this->getTitleAttribute();
		$o->description = $this->getSubtitleAttribute();
		$o->consumes = ['application/json'];
		$o->produces = ['application/json'];
		$o->parameters = $this->getParameters();
		$o->operationId = $this->getOperationId();
		$o->externalDocs = $this->getExternalDocs();
		$o->responses = [$this->getResponse()];
		$lcMethod = strtolower($method);
		$path->$lcMethod = $o;
		return $path;
	}
	public function getResponse(): Response{
		$codes = [
			QMRequest::METHOD_DELETE => 204,
			QMRequest::METHOD_GET => 200,
			QMRequest::METHOD_POST => 201,
			QMRequest::METHOD_PUT => 201,
		];
		$code = $codes[$this->getMethod()];
		$r = new Response([]);
		$r->response = $code;
		$r->x = [];
		$r->x['repository'] = 'def';
		$r->schema = $this->getSchema();
		$r->description = "Successful operation";
		return $r;
	}
	public function getOperationId(): string{
		$t = $this->getTitleAttribute();
		$t = str_replace(" a ", "", $t);
		$t = str_replace(" ", "", $t);
		return lcfirst($t);
	}
	public function getDataLabUrl(): string{
		$m = $this->getModel();
		return $m->getDataLabIndexUrl([]);
	}
	public function getExternalDocs(): ExternalDocumentation{
		$ed = new ExternalDocumentation([]);
		$ed->description = "DataLab";
		$ed->url = $this->getDataLabUrl();
		$ed->url = str_replace(Env::ENV_LOCAL . '.', 'app.', $ed->url);
		$ed->url = str_replace(Env::ENV_STAGING . '.', 'app.', $ed->url);
		return $ed;
	}
	/**
	 * @return QMParameter[]
	 */
	public function getParameters(): array{
		$params = [];
		$m = $this->getModel();
		$hasPathParam = stripos($this->uri, "{") !== false;
		if($hasPathParam){
			$params[] = new IdPathParameter($m);
		}
		if($this->isIndex()){
			$params[] = new LimitParameter($m);
			$params[] = new OffsetParameter($m);
			$fields = $m->getAllowedFilterFields();
			foreach($fields as $field){
				if($hasPathParam && $field === "id"){
					continue;
				}
				$schema = $m->getOpenApiAttributeSchema($field);
				$p = new QMParameter($field, $schema, $m);
				if(!self::usingOpenApiV3()){
					unset($p->example);
					unset($p->schema);
					unset($p->parameter);
				}
				if(in_array($p->type, ["string", "number", "integer", "boolean"])){
					$params[] = $p;
				}
			}
		}
		return $params;
	}
	public function getShortClassName(): string{
		return $this->getModel()->getShortClassName();
	}
	/**
	 * @return Schema
	 */
	protected function getSchema(): Schema{
		$schema = new Schema([]);
		$schema->ref = '#/definitions/' . $this->getShortClassName();
		return $schema;
	}
	public static function usingOpenApiV3(): bool{
		$version = config('l5-swagger.swagger_version');
		$result1 = version_compare($version, '3.0', '>=');
		$result = (string)$version >= '3.0';
		return $result;
	}
	public function getTestUrl(): string{
		return UrlHelper::getTestUrl($this->uri);
	}
	public function like(string $string): bool{
		return stripos($this->uri, $string) !== false;
	}
	public static function getAppDisplayName(): string{
		if(AppMode::isCrowdsourcingCures()){
			return "Crowdsourcing Cures";
		}
		return config('app.name', 'QuantiModo');
	}
}
