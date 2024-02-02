<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Astral\BaseAstralAstralResource;
use App\Astral\Filters\IdFilter;
use App\Astral\Filters\NumericMultiColumnFilter;
use App\Astral\Filters\TextFilter;
use App\Astral\UserBaseAstralResource;
use App\Buttons\Admin\MetabaseButton;
use App\Buttons\Admin\PHPStormButton;
use App\Buttons\AstralCreateButton;
use App\Buttons\AstralDetailsButton;
use App\Buttons\AstralIndexButton;
use App\Buttons\AstralUpdateButton;
use App\Buttons\Model\ModelButton;
use App\Buttons\QMButton;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Buttons\RelationshipButtons\HasOneRelationshipButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Buttons\Sharing\SharingButton;
use App\Cards\QMCard;
use App\Charts\QMHighcharts\BaseHighstock;
use App\CodeGenerators\Swagger\SwaggerDefinitionProperty;
use App\Computers\ThisComputer;
use App\Console\Kernel;
use App\DataTableServices\BaseDataTableService;
use App\DataTableServices\BaseEloquentDataTable;
use App\DevOps\XDebug;
use App\Exceptions\AccessTokenExpiredException;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidFilePathException;
use App\Exceptions\InvalidUrlException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoChangesException;
use App\Exceptions\NoIdException;
use App\Exceptions\NotFillableException;
use App\Exceptions\NotFoundException;
use App\Exceptions\QMFileNotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Fields\Avatar;
use App\Fields\Field;
use App\Fields\HasMany as HasManyAlias;
use App\Fields\ID;
use App\Fields\Image;
use App\Fields\Text;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Files\JavaScript\ShowJavaScriptFile;
use App\Files\PHP\BaseModelFile;
use App\Files\PHP\PhpClassFile;
use App\Filters\Filter;
use App\Http\Controllers\BaseDataLabController;
use App\Http\Controllers\WrongParameterException;
use App\Http\Parameters\LimitParam;
use App\Http\Parameters\OffsetParam;
use App\Http\Requests\AstralRequest;
use App\Http\Resources\BaseJsonResource;
use App\Lenses\Lens;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Menus\Astral\AstralRelationshipMenu;
use App\Menus\Astral\SingleModelAstralMenu;
use App\Menus\DataLab\DataLabRelationshipMenu;
use App\Menus\FooterMenu;
use App\Menus\JournalMenu;
use App\Menus\QMMenu;
use App\Menus\RelationshipsMenu;
use App\PhpUnitJobs\Code\ControllersJob;
use App\PhpUnitJobs\Code\ScaffoldJob;
use App\Policies\BasePolicy;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseCreatedAtProperty;
use App\Properties\Base\BaseIdProperty;
use App\Properties\Base\BaseIsPublicProperty;
use App\Properties\Base\BaseUpdatedAtProperty;
use App\Properties\BaseProperty;
use App\Properties\OAClient\OAClientClientSecretProperty;
use App\Properties\User\UserIdProperty;
use App\Providers\DBQueryLogServiceProvider;
use App\ShellCommands\CommandFailureException;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Slim\Model\QMUnit;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\DBTable;
use App\Storage\DB\QMDB;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\TestDB;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Storage\QueryBuilderHelper;
use App\Traits\DataLabTrait;
use App\Traits\HasButton;
use App\Traits\HasCalculatedAttributes;
use App\Traits\HasClassName;
use App\Traits\HasColor;
use App\Traits\HasColumns;
use App\Traits\HasMemory;
use App\Traits\HasModel\HasClient;
use App\Traits\HasProperty\HasCreatedUpdatedDeletedAts;
use App\Traits\HasSeed;
use App\Traits\HasTimestampColumns;
use App\Traits\LoggerTrait;
use App\Traits\ModelTraitGenerator;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\Traits\QMValidatingTrait;
use App\Types\BoolHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\Alerter;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use App\UI\ImageUrls;
use App\UI\MetaHtml;
use App\UI\QMColor;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\EnvOverride;
use App\Utils\Stats;
use App\Utils\UrlHelper;
use Auth;
use BadMethodCallException;
use DateTimeInterface;
use Eloquent;
use Exception;
use Form;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InfyOm\Generator\Generators\SwaggerGenerator;
use jc21\CliTable;
use Laravel\Scout\Searchable;
use LogicException;
use MetaTag;
use OpenApi\Annotations\Schema;
use phpDocumentor\Reflection\Types\Integer;
use ReflectionClass;
use ReflectionException;
use Spatie\QueryBuilder\QueryBuilder;
use stdClass;
use Tests\QMBaseTestCase;
use Watson\Validating\ValidationException;
use Yajra\DataTables\Html\Column;
/** App\Models\BaseModel
 * @method static Builder|BaseModel newModelQuery()
 * @method static Builder|BaseModel newQuery()
 * @method static Builder|BaseModel query()
 * @method static Builder|static withTrashed()
 * @mixin Eloquent
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static exclude(array $columns)
 * @method static |Builder applyRequestParams(\Illuminate\Http\Request $request)
 * @method static Builder|BaseModel excludeLargeColumns()
 */
abstract class BaseModel extends Model {
	use DataLabTrait, HasClassName, HasCalculatedAttributes, HasClient, HasMemory, HasTimestampColumns, HasClassName,
		LoggerTrait, QMValidatingTrait, HasColumns, HasCreatedUpdatedDeletedAts, HasButton, HasFactory;
	use HasColor;
    use HasSeed;
	const METABASE_PATH = '';
	protected $hidden = [
        //BaseClientIdProperty::NAME, //
    ];
    const UNIQUE_INDEX_COLUMNS = [self::FIELD_ID];
	public const ANALYZABLE              = false;  // Faster
	public const CAST_BOOL               = 'bool';
	public const CAST_FLOAT              = 'float';
	public const CAST_INT                = QMDB::TYPE_INT;
	public const CAST_STRING             = 'string';
	public const CAST_TIMESTAMP          = 'timestamp';
	public const CLASS_CATEGORY          = "Miscellaneous";
	public const CLASS_DESCRIPTION       = null;
	public const CLASS_DISPLAY_NAME      = null;
	public const COLOR                   = QMColor::HEX_BLUE;
	public const DEFAULT_IMAGE           = ImageUrls::PUZZLED_ROBOT;
	public const DEFAULT_LIMIT           = 20;
	public const DEFAULT_ORDERINGS       = [self::UPDATED_AT => self::ORDER_DIRECTION_DESC];
	public const DEFAULT_ORDER_DIRECTION = self::ORDER_DIRECTION_DESC;
	public const DEFAULT_SEARCH_FIELD    = 'name';
	public const FIELD_CREATED_AT        = 'created_at';
	public const FIELD_DELETED_AT        = 'deleted_at';
	public const FIELD_UPDATED_AT        = 'updated_at';
	public const FONT_AWESOME            = 'fas fa-database';
	public const IMPORTANT_FIELDS        = '';
	public const LARGE_FIELDS            = [];
	public const MAX_LIMIT               = 40;
	public const ORDER_DIRECTION_ASC     = 'asc';
	public const ORDER_DIRECTION_DESC    = 'desc';
	public const TABLE                   = null;
    protected $fillable = ['*'];
	/**
	 * Indicates if the resource should be displayed in the sidebar.
	 * @var bool
	 */
	public static $displayInNavigation = true;
	/**
	 * Where should the global search link to?
	 * @var string
	 */
	public static $globalSearchLink = 'detail';
	/**
	 * The number of results to display in the global search.
	 * @var int
	 */
	public static $globalSearchResults = 5;
	/**
	 * Indicates if the resource should be globally searchable.
	 * @var bool
	 */
	public static $globallySearchable = true;
	/**
	 * The logical group associated with the resource.
	 * @var string
	 */
	public static $group = 'Other';
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [25, 50, 100];
	/**
	 * The number of resources to show per page via relationships.
	 * @var int
	 */
	public static $perPageViaRelationship = 5;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [];
	public static $searchRelations = [];
    protected static array $deprecatedAttributes = [];
    protected static array $propertyModelsCache = [];
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = 'id';
	/**
	 * @var mixed
	 */
	protected static $columns;
	protected static array $mergedCasts = [];
    protected static array $mergedDates = [];
	protected bool $throwValidationExceptions = true;
	protected $hints = [];
	protected array $openApiSchema = [];
	protected $guarded = [
		self::FIELD_CREATED_AT,
		self::FIELD_UPDATED_AT,
		self::FIELD_DELETED_AT,
		self::FIELD_ID,
        OAClient::FIELD_CLIENT_SECRET,
	];
	public const FIELD_ID = 'id';
	protected bool $logChanges = false;
    public function __construct(array $attributes = []) {
        //$this->getFillable();
        parent::__construct($attributes);
    }
    protected static array $autoGeneratedColumns = [];
	/**
	 * @param string $searchTerm
	 * @return Builder
	 */
	public static function searchQB(string $searchTerm): Builder{
		$qb = static::minimalBuilderWithEagerLoads();
		$searchQB = $qb->where(static::DEFAULT_SEARCH_FIELD, \App\Storage\DB\ReadonlyDB::like(), "%$searchTerm%");
		return $searchQB;
	}
	/**
	 * @param $q
	 * @return static
	 */
	public static function findByNameIdSynonymOrSlug($q): self {
		if(QMStr::isInt($q)){
			return static::find((int)$q);
		}
		$before = $q;
		$nameFromSlug = static::formatQuery($q);
		/** @var static $m */
		$m = static::findByNameIdOrSynonym($nameFromSlug);
		/** @noinspection UnknownTableOrViewInspection */
		if(!$m && static::hasColumn('slug')){
			$slug = QMStr::slugify($before);
			$m = static::whereSlug($slug)->first();
		}
		if(!$m && $before !== $nameFromSlug){
			$m = static::findByNameIdOrSynonym($before);
		}
		if(!$m){
			throw new NotFoundException("$q not found");
		}
		return $m;
	}
	public function getAutoGeneratedColumns(): array{
        if(isset(static::$autoGeneratedColumns[static::TABLE])){
            return static::$autoGeneratedColumns[static::TABLE];
        }
        $all = static::getColumns();
        foreach ($all as $column) {
            if($this->attributeIsAutoGenerated($column)) {
                static::$autoGeneratedColumns[static::TABLE][] = $column;
            }
        }
        if(!static::$autoGeneratedColumns[static::TABLE]){
            QMLog::error(static::class . ' has no auto generated columns');
            return [];
        }
        return static::$autoGeneratedColumns[static::TABLE];
    }
	/**
	 * @param Request $request
	 * @return bool
	 */
	public static function authorizedToCreate(Request $request): bool{
		$policy = self::getGatePolicy();
		return $policy->create($request->user());
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
     */
	public static function availableForNavigation(Request $request): bool{
		$policy = self::getGatePolicy();
		return $policy->viewAny($request->user());
	}
	/**
	 * @param array $data
	 * @param int $batchSize
	 * @param bool $insertIgnore
	 * @return array
	 */
	public static function batchInsert(array $data, int $batchSize = 500, bool $insertIgnore = false): array{
		foreach($data as $m){
			$values[] = array_values($m);
		}
		$keys = array_keys($data[0]);
		if(empty($values)){
			le("empty data");
		}
		try {
			/** @noinspection PhpParamsInspection */
			return Batch::insert(new static(), $keys, $values, $batchSize, $insertIgnore);
		} catch (\Throwable $e) {
			/** @var LogicException $e */
			throw $e;
		}
	}
	public static function button(): QMButton{
		$b = new ModelButton();
		$b->setTextAndTitle(static::label());
		$b->setUrl(static::getDataLabIndexUrl());
		return $b;
	}
	public static function createdInLastXHours(int $hours): int{
		$created = static::whereCreatedInLastXHours($hours)->count();
		QMLog::info($created . " " . static::getClassNameTitle() . " CREATED in last $hours hours...");
		return $created;
	}
	/**
	 * @param int $hours
	 * @return static|Builder
	 */
	public static function whereCreatedInLastXHours(int $hours){
		return static::where(static::CREATED_AT, ">", db_date(time() - 3600 * $hours));
	}
	public static function deleteAll(){
		$class = static::getPluralizedClassName();
		$message = "Deleting all $class...";
		if(AppMode::isUnitOrStagingUnitTest()){
			\App\Logging\ConsoleLog::info($message);
			static::query()->forceDelete();
		} else{
			le("Why are we " . $message . " outside of testing environment?");
		}
	}
	/**
	 * @return string
	 */
	public static function getPluralizedClassName(): string{
		$path = explode('\\', static::class);
		return Str::plural(array_pop($path));
	}
	/**
	 * @return bool|null
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function forceDelete(){
		$this->deleteFromMemory();
		$res = parent::forceDelete();
		$found = static::find($this->getId());
		if($found){
			le("Why wasn't this deleted?");
		}
		return $res;
	}
	/**
	 * @return int|string|null
	 */
	public function getId(){
		$id = $this->attributes[User::FIELD_ID] ?? $this->attributes['id'] ?? $this->getPrimaryKeyValue();
		//if($id === 0 && static::class !== User::class){le("ID should not be 0 for $this");}
		if($id === null){
            QMLog::info("No id or primary key value for " . get_class($this), $this->attributes);
            return null;
		}
		if(!is_int($id) && !is_string($id)){
			le("ID should be int or string but is: " . \App\Logging\QMLog::print_r($id, true), $this->attributes);
		}
		return $id;
	}
	/**
	 * @return int|string|null
	 */
	public function getPrimaryKeyValue(): int|string|null {
		return $this->getAttribute($this->primaryKey);
	}
    /**
     * @return BaseModel[]
     */
    public static function getNonAbstractModels(): array{
        $models = [];
        $nonAbstractClasses = static::getNonAbstractClasses();
        foreach ($nonAbstractClasses as $class) {
            $models[] = new $class;
        }
        return $models;
    }
    /**
     * @return BaseModel[]
     */
    public static function getNonAbstractModelsWithTables(): array{
        $models = [];
        $nonAbstractClasses = static::getNonAbstractClasses();
        foreach ($nonAbstractClasses as $class) {
	        /** @var BaseModel $model */
	        $model = new $class;
	        $table = $model->getTable();
			if($table === 'sources'){
				le($model);
			}
	        if(!$table){continue;}
	        $models[] = $model;
        }
        return $models;
    }
    /**
     * @return string[]
     */
    public static function getNonAbstractClasses(): array
    {
        $classes = [];
        foreach (glob(app_path() . '/Models/*.php') as $filename) {
            $class = 'App\\Models\\' . basename($filename, '.php');
            try {
                $reflection = new ReflectionClass($class);
            } catch (ReflectionException $e) {
                le($e);
            }
            if (!$reflection->isAbstract()) {
                $classes[] = $class;
            }
        }
        return $classes;
    }
    public function getNonNullAttributes(): array {
        $attributes = $this->attributesToArray();
        foreach ($attributes as $key => $value) {
            if ($value === null) {
                unset($attributes[$key]);
            }
        }
        return $attributes;
    }
	public static function findModelByTable(string $table): self {
		$class = static::getClassByTable($table);
		if(!$class){
			le("No class found for table: $table");
		}
		if($class === BaseModel::class){
			le("Why is this BaseModel class?");
		}
		return new $class;
	}
	/**
	 * @param $key
	 * @return mixed
	 */
	private function profileGetAttribute($key){
		$start = microtime(true);
		$res = parent::getAttribute($key);
		$existing = Memory::get($key, static::TABLE . '_duration') ?? 0;
		$duration = $existing + microtime(true) - $start;
		Memory::set($key, $duration, static::TABLE . '_duration');
		return $res;
	}
    /**
     * @param string $searchTerm
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected static function minimalTrailingWildcardSearchWithEagerLoads(string $searchTerm):
\Illuminate\Database\Eloquent\Builder
    {
        $qb = static::minimalBuilderWithEagerLoads();
        $field = static::DEFAULT_SEARCH_FIELD;
        $qb = $qb->where($field, ReadonlyDB::like(), "$searchTerm%");
        return $qb;
    }
    /**
     * @return int|null
     */
    private static function getLimit(): ?int
    {
        return LimitParam::getLimit(static::DEFAULT_LIMIT, static::MAX_LIMIT);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected static function minimalBuilderWithEagerLoads(): \Illuminate\Database\Eloquent\Builder
    {
        /** @var BaseModel $class */
        $class = static::class;
        $limit = static::getLimit();
        $qb = $class::setEagerLoads([])
            ->select(static::getMinimalFields())
            ->limit($limit);
        (new static)->restrictQueryBasedOnPermissions($qb);
        static::applyDefaultOrderings($qb);
        return $qb;
    }
    /**
     * @param $models
     * @return static[]|Collection
     */
    protected static function sortByDefaultOrdering($models)
    {
        foreach (static::DEFAULT_ORDERINGS as $column => $direction) {
            $models = $models->sortBy($column, SORT_REGULAR, $direction === BaseModel::ORDER_DIRECTION_DESC);
            if ($direction === BaseModel::ORDER_DIRECTION_DESC) {
                $nulls = $models->filter(function (BaseModel $model) use ($column) {
                    return $model->$column === null;
                });
                $notNulls = $models->filter(function (BaseModel $model) use ($column) {
                    return !$model->$column !== null;
                });
                $models = $notNulls->merge($nulls);
            }
        }
        return $models;
    }

    public function getQueueableRelations(): array{
		if(self::setUserRelationOnCollections()){
			/**
			 * This introduced seg faults during serialize function call
			 * where 1 of the relations had a relationship defined back to
			 * the initial model, thus creating an infinite loop.
			 * @see Model::getQueueableRelations()
			 * @see https://github.com/laravel/framework/issues/23505
			 */
			$relations = [];
			foreach($this->getRelations() as $key => $relation){
				if(!method_exists($this, $key)){
					continue;
				}
				$relations[] = $key;
			}
			return array_unique($relations);
		}
		// if($this->relations){ConsoleLog::info(static::class."->".__FUNCTION__);} // Uncomment for segfault debugging
		return parent::getQueueableRelations();
	}
	/**
	 * @return bool
	 */
	public static function setUserRelationOnCollections(): bool{
		return AppMode::isAstral() && static::hasColumn('user_id');
	}
	/**
	 * @param BaseModel[] $models
	 * @return \Illuminate\Database\Eloquent\Collection
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function newCollection(array $models = []){
		// ConsoleLog::info(static::class." ".__FUNCTION__." "); // Uncomment for segfault debugging
		$coll = parent::newCollection($models);
		$astralAndUserId = self::setUserRelationOnCollections();
		$users = [];
		foreach($models as $m){
			$m->addToMemory();
			// Astral makes a million duplicate user queries if relation isn't set
			if($astralAndUserId && method_exists($m, 'getUserId')){
				if(!$m->relationLoaded('user')){
					if($userId = $m->getUserId()){ // Not set when plucking values
						if($u = $users[$userId] ?? null){
							$m->setRelation('user', $u);
						} else{
							if($u = User::findInMemory($userId)){
								$users[$userId] = $u;
								$m->setRelationAndAddToMemory('user', $u);
							}
						}
					}
				}
			}
		}
		return $coll;
	}
	/**
	 * @param string $relation
	 * @param BaseModel|array $value
	 * @return static
	 */
	public function setRelationAndAddToMemory(string $relation, $value): BaseModel{
		if($value){
			$this->addRelationToMemory($value);
		}
		return parent::setRelation($relation, $value);
	}
	/**
	 * @param BaseModel $value
	 */
	private function addRelationToMemory(BaseModel $value): void{
		$value->addToMemory();
	}
	public static function deleteForDeletedUsers(){
		$deletedUserIds = User::onlyTrashed()->pluck(User::FIELD_ID);
		$qb = static::whereIn(static::FIELD_USER_ID, $deletedUserIds);
		$count = $qb->count();
		if($count){
			$qb->delete();
			$class = static::getClassNameTitlePlural();
			QMLog::info("Deleted $count $class for deleted users: " . \App\Logging\QMLog::print_r($deletedUserIds, true));
		}
	}

    /**
     * @return static
     */
    public static function fakeSaveFromPropertyModels(): BaseModel{
        $model = static::fakeFromPropertyModels();
        try {
            $model->save();
        } catch (ModelValidationException $e) {
            le($e);
        }
        return $model;
    }
	/**
	 * @return static
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function fakeFromPropertyModels(int $userId = UserIdProperty::USER_ID_TEST_USER): self{
		$m = static::newFake($userId);
		$properties = $m->getPropertyModels();
        if(!$properties){
            return static::factory()->make();
        }
		foreach($properties as $property){
			if($property->deprecated){
				continue;
			}
			if(!isset($m->attributes[$property->name])){
				$ex = $property->getExample();
				if($ex === null){
					continue;
				}
				if($property->name == 'id'){
					le("why are we generating fake id when it should be auto-generated?", $property);
				}
				$m->setAttribute($property->name, $ex);
				try {
					$m->validateAttribute($property->name);
				} catch (InvalidAttributeException $e) {
					$property->example = null;
					$ex = $property->getExample();
					$m->setAttribute($property->name, $ex);
					/** @noinspection PhpUnhandledExceptionInspection */
					$m->validateAttribute($property->name);
				}
			}
		}
		return $m;
	}
	/**
	 * @return static
	 * Override in child classes with manually set attributes like cause_variable_id needed to generate example values
	 */
	protected static function newFake(int $userId = UserIdProperty::USER_ID_TEST_USER): self{
		$m =  new static();
        if(static::hasColumn('user_id')){
            $m->setAttribute('user_id', $userId);
        }
        return $m;
	}
	/**
	 * @param int|string $nameOrId
	 * @return static|null
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function findByNameLikeOrId($nameOrId){
		if(Stats::is_int($nameOrId) || !in_array('name', static::getColumns())){
			$qb = static::query()->where(static::FIELD_ID, $nameOrId);
		} else{
			$qb = static::query()->where('name', $nameOrId);
		}
		QueryBuilderHelper::restrictQueryBasedOnPermissions($qb->getQuery());
		/** @var static $model */
		$model = $qb->first();
		return $model;
	}
	/** @noinspection PhpMissingReturnTypeInspection */
	/**
	 * @return array
	 */
	public static function getColumns(): array{
		if(isset(static::$columns[static::TABLE])){
			return static::$columns[static::TABLE];
		}
		$constants = static::getConstants();
		$fields = [];
		foreach($constants as $name => $value){
			if(is_string($value) && str_starts_with($name, 'FIELD_')){
				$fields[] = $value;
			}
		}
		$unique = array_unique($fields);
		sort($fields);
		//if(count($unique) !== count($fields)){le("duplicate field");}
		return static::$columns[static::TABLE] = $unique;
	}
	/** @noinspection PhpMissingReturnTypeInspection */
	/**
	 * @param BaseModel|DBModel|array $relatedObj
	 * @return static
	 */
	public static function findByRelated($relatedObj): ?BaseModel{
		if(is_array($relatedObj)){
			$data = $relatedObj;
		} else{
			$data = $relatedObj->toNonNullArray();
			$foreignKey = QMStr::classToForeignKey(get_class($relatedObj));
			$data[$foreignKey] = $relatedObj->getId();
		}
		unset($data['id']);
		return static::findByData($data);
	}
	/**
	 * @return array
	 */
	public function toNonNullArray(): array{
		$arr = $this->toArray();
		$arr = QMArr::removeNulls($arr);
		return $arr;
	}
	/**
	 * @return array|void
	 */
	public function toArray(): array{
		// ConsoleLog::info(static::class." ".__FUNCTION__." "); // Uncomment for segfault debugging
		try {
			$arr = parent::toArray();
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			$arr = parent::toArray();
		}
		ksort($arr);
		return $arr;
	}
	/**
	 * @param $data
	 * @return static
	 */
	public static function findByData($data): ?BaseModel{
		$me = new static();
		$primary = $me->getPrimaryProperty();
		if($id = $primary->pluck($data)){
			if($found = static::findInMemoryOrDB($id)){return $found;}
		}
		$unique = static::getUniqueIndexColumns();
		if(count($unique) === 1 && $unique[0] === $primary->name){return null;}
		$qb = static::query();
		$me->populateForeignKeys($data); // Needed for pluckProcessAndSet
		foreach($unique as $attribute){
			$p = $me->getPropertyModel($attribute);
			$processed = $p->pluckAndSetDBValue($data); // Need to process so we get rounded start_time for unique key
			if($processed === null){return null;}
			if($attribute === BaseClientIdProperty::NAME){continue;}
			// Need to use LIKE for SQLite to be case-insensitive
			$qb->where($attribute, Writable::like(), $processed);
		}
		return $qb->first();
	}
	public function getPrimaryProperty(): BaseProperty{
		$properties = $this->getPropertyModels();
		$primary = [];
		foreach($properties as $property){
			if($property->isPrimary){
				$primary[] = $property;
			}
		}
		if(!$primary){
			le("Please define primary for " . static::class);
		}
		if(count($primary) > 1){
			le("Got 2 primary properties!  Set isPrimary as false on one. ");
		}
		return $primary[0];
	}
	/**
	 * @param array $ids
	 * @return Collection|static[]
	 */
	public static function findManyInMemoryOrDB(array $ids): Collection {
		$fromMemory = $idsNotInMemory = [];
		foreach($ids as $id){
			$m = static::findInMemory($id);
			if($m){
				$fromMemory[$id] = $m;
			} else{
				$idsNotInMemory[] = $id;
			}
		}
		if($idsNotInMemory){
			$notFromMemory = static::find($idsNotInMemory);
			return collect(array_merge($fromMemory, $notFromMemory->all()));
		} else {
			return collect($fromMemory);
		}
	}
	public static function getUniqueIndexColumns(): array{
		return [static::FIELD_ID];
	}
	/**
	 * @param $data
	 * @return void
	 */
	public function populateForeignKeys($data){
		$foreign = $this->getForeignKeyPropertyModels();
		foreach($foreign as $p){
			if($p->isGeneratedByDB()){
				continue;
			}
			$val = $p->getDBValue();
			if($val !== null){
				continue;
			}
			$p->pluckAndSetDBValue($data);
		}
	}
	/**
	 * @return static
	 */
	public static function findByRequest(): ?BaseModel{
		$data = qm_request()->input() + qm_request()->query();
		return static::findByData($data);
	}
	/**
	 * @param int|string $id
	 * @return static|null
	 * @deprecated TODO Figure out how to make sure Cache is purged on changes to model or causes many nightmares
	 * @noinspection PhpUnused
	 */
	public static function findInMemoryCacheOrDB($id): ?BaseModel{
		if($m = static::findInMemory($id)){
			return $m;
		}
		if(!$m){
			$m = static::find($id);
		}
		if($m){
			$m->addToMemory();
		}
		return $m;
	}
	public function getPrimaryKey(): string{
		return $this->primaryKey;
	}
	/**
	 * @return bool
	 */
	public function hasId(): bool{
		$id = $this->attributes['ID'] ?? $this->attributes['id'] ?? $this->getPrimaryKeyValue();
		return $id !== null; // We have a 0 id user for some reason so make sure to check against null
	}
	/**
	 * @param array $attributes
	 * @return static
	 */
	public static function findInMemoryDBOrCreate(array $attributes = []){
		$c = static::findInMemoryOrDBWhere($attributes);
		if($c){
			return $c;
		}
		return static::create($attributes);
	}
	/**
	 * Save a new model and return the instance.
	 * @param array $attributes
	 * @return Model|$this
	 * @static
	 */
	public static function create(array $attributes = []){
		$attributes = QMArr::removeNullsAndEmptyStrings($attributes);
		/** @var static $model */
        $builder = static::query();
        $keys = array_keys($attributes);
        $cols = static::getColumns();
        $diff = collect($cols)
            ->diff($keys);
        try {
			$guarded = $builder->getModel()->getGuarded();
            $model = $builder->create($attributes);
        } catch (\Throwable $e) {
            $sql = QMQB::addBindingsToSql($builder->getQuery());
            $str = DBQueryLogServiceProvider::toSQL($builder->getQuery());
	        $model = $builder->create($attributes);
            le($e);
        }
		//$model = static::find($model->getId()); // Make sure all DB-populated attributes are set
		$model->addToMemory();
		return $model;
	}
	/**
	 * @param array $params
	 * @return static
	 */
	public static function findInMemoryOrDBWhere(array $params): ?BaseModel{
		if($u = static::findInMemoryWhere($params)){
			return $u;
		}
		$m = static::where($params)->first();
		/** @var static $m */
		return $m;
	}
	/**
	 * @return static
	 */
	public static function findOrNewByRequest(): BaseModel{
		$data = qm_request()->input() + qm_request()->query();
		return static::firstOrNewByData($data);
	}
	/**
	 * @param $id
	 * @return static
	 */
	public static function findWithTrashed($id): BaseModel{
		$res = static::withTrashed()->where(static::FIELD_ID, $id)->first();
		/** @var static $res */
		return $res;
	}
	/**
	 * Get the first record matching the attributes or create it.
	 * @param array $attributes
	 * @param array $values
	 * @return static
	 * @static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function firstOrCreate(array $attributes, array $values = []){
		if($mem = static::findInMemoryWhere($attributes)){
			return $mem;
		}
        $builder = static::query();
        try {
            $model = $builder->firstOrCreate($attributes, $values);
        } catch (\Illuminate\Database\QueryException $e) {
            $builder->where($attributes)->update(['deleted_at' => null]);
            $model = $builder->where($attributes)->first();
            if(!$model){
                le($e);
            }
        }
		//if($model->wasRecentlyCreated && count($model->attributes) < 10){
		//$model = static::find($model->getId()); // Don't save partially populated models to memory
		//}
		$model->addToMemory();
		return $model;
	}
	/**
	 * @param $data
	 * @return static
	 */
	public static function firstOrCreateByForeignData($data): BaseModel{
		$data = QMArr::toArray($data);
		unset($data['id']);
		return static::firstOrCreateByData($data);
	}
	/**
	 * @param $data
	 * @return static
	 */
	public static function firstOrCreateByData($data): BaseModel{
		$model = static::firstOrNewByData($data);
		if(!$model->exists){
			try {
				$model->save();
			} catch (ModelValidationException $e) {
				le($e);
			}
		}
		return $model;
	}
	/**
	 * @param $data
	 * @return static
	 */
	public static function firstOrNewByData($data): BaseModel{
		$model = static::findByData($data);
		if($model){
			/** @noinspection PhpPossiblePolymorphicInvocationInspection */
			if($model->deleted_at){
				/** @noinspection PhpPossiblePolymorphicInvocationInspection */
				$model->deleted_at = null;
			}
			return $model;
		}
		$model = new static();
		$model->populate($data);
		return $model;
	}
	/**
	 * @param array|object $data
	 */
	public function populate($data): void{
		$this->populateForeignKeys($data);
		$nonForeign = $this->getNonForeignKeyPropertyModels();
		if(!$nonForeign){
			$this->fill($data);
		}
		foreach($nonForeign as $p){
			$name = $p->name;
			if($p->isGeneratedByDB()){
				continue;
			}
			if(TimeHelper::isAtAttribute($name)){
				continue;
			}
			$val = $p->pluck($data);
			if($val !== null){
				$this->setAttribute($name, $val);
			}
		}
		$this->setClientIdFromRequest();
	}
	private function setClientIdFromRequest(): void{
		if(AppMode::isApiRequest() && static::hasColumn('client_id') && !$this->getAttribute('client_id')){
            if($value = BaseClientIdProperty::fromRequest(false)){
                $this->setAttribute('client_id', $value);
            }
		}
	}
	/**
	 * @param string $field
	 * @return bool
	 */
	public static function hasColumn(string $field): bool{
		$fields = static::getColumns();
		return in_array($field, $fields);
	}
	/**
	 * @param array $options
	 * @return bool
	 * @throws ModelValidationException
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function save(array $options = []){
		// ConsoleLog::info(static::class." ".__FUNCTION__." "); // Uncomment for segfault debugging
		$changes = $this->getChangeList();
		$alreadyExisted = $this->exists;
		if(!$changes && $alreadyExisted){
			$this->logDebug("No Changes so skip saving and validation");
			return false;
		}
		$this->addOrDeleteFromMemory();
		$this->assertIdNotZero();
		$this->beforeChange($changes);
        try {
            $res = parent::save($options);
        } catch (\Throwable $e) {
            if(str_contains($e, "no such column")){
                le("No such column: ".$e->getMessage().
                "\n\tTable: ".static::getTableName()).
                "\n\tColumns: \n\t".implode("\n\t", static::getColumns());
            }
            if(EnvOverride::isLocal()){
//                if(stripos($e->getMessage(), '" violates not-null constraint') !== false){
//                    $col = QMStr::between($e->getMessage(), ' "', '" of relation');
//                    $mig = $this->createMigrationForColumn($col);
//                    le("Column $col is not nullable. Run this migration: $mig", $e);
//                }
                // TODO $this->createSaveTest();
            }
            le($e);
        }
		if($alreadyExisted){
			$this->onChange($changes);
		} else{
			$this->onCreation($changes);
			if(XDebug::active()){
				try {
					QMLog::info("Created: ".static::getShortClassName().". View at: ".static::getDataLabIndexUrl());
				} catch (\Throwable $e) {
					QMLog::info(__METHOD__.": ".$e->getMessage());
				}
			}
		}
		$this->assertIdNotZero();
		if(method_exists($this, 'updateDBModel')){
			$this->updateDBModel();
		}
		return $res;
	}
    public function createUnitTest(string $name, string $contents): string {
        $file = $this->getPhpClassFile();
        $file = $file->getUnitTestFile();
        try {
            $file->setPath(str_replace('tests/', 'tests/Generated/', $file->getPath()));
        } catch (InvalidFilePathException $e) {
            le($e);
        }
        $file->addMethod($name)->setBody($contents);
        return $file->save();
    }
    public function getUnitTestFile(): \App\Files\PHP\UnitTestFile
    {
        $file = $this->getPhpClassFile();
        return $file->getUnitTestFile();
    }
    public function getPhpClassFile(): PhpClassFile
    {
        return new PhpClassFile($this);
    }
	public function getChangeList(): array{
		$arr = [];
		$this->castDBValues();
		$dirty = $this->getDirty();
        $appends = $this->appends;
		foreach($dirty as $key => $value){
            if(in_array($key, $appends)){
                continue;
            }
			$original = $this->getRawOriginal($key);
			if($original === 1 && $value === true){
				le(__FUNCTION__ . ": $key original is 1 && new is true!  This should have been fixed in castDBValues");
			}
			if($original === 0 && $value === false){
				le(__FUNCTION__ . ": $key original is 0 && new is false!  This should have been fixed in castDBValues");
			}
			if($original === "" && $value === null){
				$this->logInfo(__FUNCTION__ .
					": getDirty says $key changed but original is empty string and new value is null");
				continue;
			}
			if($original === null && $value === null && !array_key_exists($key, $this->original)){
				$this->logDebug(__FUNCTION__ . ": value is null and the original key doesn't exist. " .
					"Maybe we should be returning this but it causes problems with my property model validator? ");
				continue;
			}
			if($original === $value){
				//$dirty = $this->getDirty();
				$this->logInfo(__FUNCTION__ . ": getDirty says $key changed but original ===  new value");
				continue;
			}
			if(Stats::areEqualFloats($original, $value)){
				//$dirty = $this->getDirty();
				$this->logInfo(__FUNCTION__ .
					": getDirty says $key changed but areEqualFloats returns true: original is $original and new value is $value");
				continue;
			}
			$arr[$key] = ['before' => $original, 'after' => $value];
		}
		return $arr;
	}
	protected function castDBValues(): void{
		$columns = static::getColumns();
        $ignore = [];
		foreach($columns as $column){
			if(isset($this->attributes[$column])){
				$prop = $this->getPropertyModel($column);
				if(!$prop){
					if(!in_array($column, $ignore)){
						QMLog::once("No property model for $column on ".static::class);
					}
					continue;
				}
				$prop->castDBValue();
			}
		}
	}
	protected function addOrDeleteFromMemory(): void{
		if($this->hasValidId() && !$this->wasRecentlyCreated){
			//$this->authorizeUpdate();
			if(static::class !== User::class){ //Causes problems with user for some reason
				$this->addToMemory();
			}
			if($this->attributes['deleted_at'] ?? false){
				$this->deleteFromMemory();
			}
		}
	}
	/**
	 * @return bool
	 */
	public function hasValidId(): bool{
		$id = $this->attributes['ID'] ?? $this->attributes['id'] ?? $this->getPrimaryKeyValue();
		return $id > 0;
	}
	protected function assertIdNotZero(): void{
		if(static::class !== User::class){
			if(isset($this->attributes['id']) && $this->attributes['id'] === 0){
				le(static::class . " id is 0");
			}
		}
	}
	public function beforeChange(array $changeList): void{
		foreach($changeList as $column => $data){
			$before = $data['before'];
			if($before === null){
				return;
			}// You can remove this line if necessary, but I thought I'd save some CPU cycles
			if($prop = $this->getPropertyModel($column)){
				$prop->beforeChange($this->logChanges);
			}
		}
	}
	public function onChange(array $changeList): void{
		foreach($changeList as $column => $data){
			if($prop = $this->getPropertyModel($column)){
				try {
					$prop->onChange($data['before'], $this->logChanges);
				} catch (\Throwable $e){
				    QMLog::info(__METHOD__.": ".$e->getMessage());
					$prop->onChange($data['before'], $this->logChanges);
				}
			}
		}
	}
	public function onCreation(array $changeList): void{
		foreach($changeList as $column => $data){
			if($prop = $this->getPropertyModel($column)){
				$prop->onCreation();
			}
		}
	}
	public function logAstralUrl(string $prefix = "View"): void{
		QMLog::linkButton("$prefix " . $this->getTitleAttribute(), $this->getDataLabShowUrl());
	}
	public function getTitleAttribute(): string{
		$a = $this->attributes;
		$name = $a['title'] ?? $a['display_name'] ?? $a['name'] ?? static::getClassNameTitle();
		return $name;
	}
	public function getNameAttribute(): string{
		$a = $this->attributes;
		$name = $a['name'] ?? $a['display_name'] ?? $a['title'] ?? static::getClassNameTitle();
		return $name;
	}
	public function getAstralShowUrl(array $params = null): string{
		$index = static::getDataLabIndexUrl();
		if(!$this->hasId()){
			return UrlHelper::addParams($index, $params);
		}
		return UrlHelper::addParams($index . "/" . $this->getId(), $params ?? []);
	}
	public static function getAstralIndexUrl(array $params = []): string{
		$path = static::getAstralIndexPath();
		return UrlHelper::addParams(\App\Utils\Env::getAppUrl() . $path, $params);
	}
	public static function getAstralIndexPath(): string{
		return "/astral/resources/" . static::uriKey();
	}
	/**
	 * Get the URI key for the resource.
	 * @return string
	 */
	public static function uriKey(): string{
		$class = class_basename(get_called_class());
		return Str::plural(Str::kebab($class));
	}
	public function getFontAwesome(): string{
		return static::FONT_AWESOME;
	}
	/**
	 * @return static
	 */
	public static function firstOrFakeNew(): BaseModel{
		return QMBaseTestCase::firstOrFakeNew(static::class);
	}
	/**
	 * @return static
	 */
	public static function firstOrFakeSave(): BaseModel{
		return QMBaseTestCase::firstOrFakeSave(static::class);
	}
	/**
	 * @param string $column
	 * @param string $pattern
	 * @return int
	 */
	public static function forceDeleteWhereLike(string $column, string $pattern): int{
		$qb = static::whereLike($column, $pattern);
		$count = $qb->count();
		\App\Logging\ConsoleLog::info("Deleting $count " . static::TABLE . " with $column like $pattern");
		return $qb->forceDelete();
	}
	/**
	 * @param string $column
	 * @param string $pattern
	 * @return Builder
	 */
	public static function whereLike(string $column, string $pattern): Builder{
		return static::where($column, \App\Storage\DB\ReadonlyDB::like(), $pattern);
	}
	public static function forceDeleteWhereUserId(int $userId): ?bool{
		return static::query()->where('user_id', $userId)->forceDelete();
	}
	/**
	 * @param $data
	 * @return static
	 */
	public static function fromData($data): BaseModel{
		return static::upsertOne($data);
	}
	/**
	 * @param $data
	 * @return static
	 */
	public static function upsertOne($data): BaseModel{
        $model = static::findByData($data);
        if(!$model){
            $pk = (new static)->getPrimaryKey();
            $prop = (new static)->getPropertyModel($pk);
            $val = $prop->pluck($data);
            $builder = static::query()->where($pk, $val)->withTrashed();
            //$result = $builder->update([self::FIELD_DELETED_AT => null]);
            $model = $builder->first();
            if($model){
                $model->deleted_at = null;
            }
        }
		if($model){
			$model->populate($data);
		} else{
			$model = static::new($data);
		}
		try {
			$model->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		return $model;
	}
	/** @noinspection PhpUnused */
	/**
	 * @param $data
	 * @return static
	 */
	public static function new($data): self {
		// ConsoleLog::info(static::class." ".__FUNCTION__." "); // Uncomment for segfault debugging
		$me = new static();
		$me->populate($data);
		return $me;
	}
	/**
	 * @param $data
	 * @return static
	 */
	public static function fromForeignData($data): BaseModel{
		if($data instanceof DBModel){
			$data = $data->toNonNullArray();
		}
		if($data instanceof BaseModel){
			$data = $data->toNonNullArray();
		}
		if(!is_array($data)){
			/** @noinspection PhpUnhandledExceptionInspection */
			$data = json_decode(json_encode($data), true, 512);
		}
		unset($data['id']);
		if($dbModel = QMArr::pluckValue($data, (new \ReflectionClass(static::class))->getShortName())){
			return $dbModel->l();
		}
		return static::upsertOne($data);
	}
	/**
	 * @return BaseModel|static
	 */
	public static function fromRequest(): ?self{
		return static::findOrCreateByRequest();
	}
	/**
	 * @return static
	 */
	public static function findOrCreateByRequest(): BaseModel{
		$data = qm_request()->input() + qm_request()->query();
		return static::findOrCreate($data);
	}
	/**
	 * @param $data
	 * @return static
	 */
	public static function findOrCreate($data): BaseModel{
		if($existing = static::findByData($data)){
			return $existing;
		}
		return static::upsertOne($data);
	}
	public static function generateAPIControllers(){
		ControllersJob::infyomGenerateAPIFromTable((new \ReflectionClass(static::class))->getShortName(),
			static::TABLE);
	}
	public static function generateAllAstralResources(){
		$classes = BaseModel::getClasses();
		foreach($classes as $class){
			$class::generateAstralResourceClass();
		}
	}
	public static function generateAstralResourceClass(): void{
		try {
			$content = FileHelper::getContents('astral/src/Console/stubs/resource.stub');
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
		$content = str_replace("{{full_class}}", static::class, $content);
		$content = str_replace("{{short_class}}", QMStr::toShortClassName(static::class), $content);
		$resourceClass = static::getAstralResourceClass();
		$path = FileHelper::classToPath($resourceClass);
		FileHelper::write($path, $content);
	}
	public static function generateBaseModel(): void{
		$me = new static;
		$connection = $me->getConnection();
		BaseModelFile::generateByTable(static::TABLE, $connection->getName());
	}
	public static function generateAstralTrashedUrl(): string{
		return static::generateAstralIndexUrl([QMRequest::PARAM_TRASHED => 1]);
	}
	public static function generateAstralIndexUrl(array $params = []): string{
		return static::getDataLabIndexUrl($params);
	}
	public static function generateScaffold(){
		//$url = static::generateBaseModel();
		ScaffoldJob::infyomGenerateScaffoldFromTable((new \ReflectionClass(static::class))->getShortName(),
			static::TABLE);
		//return $url;
	}

    /**
     * @return string[]
     */
    public static function getAllTables(): array{
        $classes = BaseModel::getClasses();
        foreach($classes as $class){
            $reflectionClass = new ReflectionClass($class);
            if($reflectionClass->isAbstract()){
                continue;
            }
            if($reflectionClass->getName() === BillingPlan::class){
                continue;
            }

            if($reflectionClass->getName() === ConnectorDevice::class){
                continue;
            }
            if(!$reflectionClass->hasConstant('TABLE')){
                continue;
            }
            $table = $class::TABLE;
            if(!$table){
               continue;
            }
            $tables[] = $table;
        }
        sort($tables);
        return $tables;
    }
	/**
	 * @param $needleCols
	 * @param bool $like
	 * @return string[]
	 */
	public static function getAllTablesWithColumn($needleCols, bool $like = false): array{
		if(is_string($needleCols)){
			$needleCols = [$needleCols];
		}
		$classes = static::getClasses();
		$matchedTables = [];
		foreach($classes as $class){
			if($like){
				if(!$class::hasColumnsLike($needleCols)){
					continue;
				}
			} else{
				if(!$class::hasColumns($needleCols)){
					continue;
				}
			}
			$matchedTables[] = $class::TABLE;
		}
		$matchedTables = array_values(array_unique($matchedTables));
		return $matchedTables;
	}
	/**
	 * @return BaseModel[]
	 */
	public static function getClasses(): array{
		return self::getClassNames();
	}
	/**
	 * @return BaseModel[]
	 */
	public static function getClassNames(): array{
		$files = FileFinder::listFiles('app/Models', false, '.php');
		$names = [];
		foreach($files as $file){
			if(strpos($file, 'BaseModel.php')){
				continue;
			}
			$names[] = '\App\Models\\' . QMStr::filePathToShortClassName($file);
		}
		return $names;
	}
	/**
	 * @param array $columns
	 * @return bool
	 */
	public static function hasColumnsLike(array $columns): bool{
		if(empty($columns)){
			le("no columns");
		}
		foreach($columns as $column){
			if(!static::hasColumnLike($column)){
				return false;
			}
		}
		return true;
	}
	/**
	 * @param string $needle
	 * @return bool
	 */
	public static function hasColumnLike(string $needle): bool{
		if(empty($needle)){
			le("no needle");
		}
		$columns = static::getColumns();
		foreach($columns as $column){
			if(strpos($column, $needle) !== false){
				return true;
			}
		}
		return false;
	}
	/**
	 * @param array $columns
	 * @return bool
	 */
	public static function hasColumns(array $columns): bool{
		if(empty($columns)){
			le("no columns");
		}
		foreach($columns as $column){
			if(!static::hasColumn($column)){
				return false;
			}
		}
		return true;
	}
	/**
	 * @return Collection|static[]
	 */
	public static function getByRequest(): Collection{
		$eb = static::queryFromRequest();
		return $eb->get();
	}
	/**
	 * @return Builder|static
	 */
	public static function queryFromRequest(): Builder{
		$eb = static::query();
		$db = $eb->getQuery();
		$props = (new static())->getPropertyModels();
		foreach($props as $prop){
			if(in_array($prop->name, [
				Measurement::FIELD_CLIENT_ID,
				Measurement::FIELD_SOURCE_NAME,
			])){
				continue;
			}
			$prop->applyRequestParamsToQuery($db);
		}
		$db->take(static::getLimit());
		$db->offset(OffsetParam::getOffset());
		QueryBuilderHelper::restrictQueryBasedOnPermissions($eb);
		return $eb;
	}
	public static function getClassDescription(): string{
		$d = static::CLASS_DESCRIPTION;
		if(!$d){
			//\App\Logging\ConsoleLog::info("Please add CLASS_DESCRIPTION to ".static::class);
			//return '';
			le("Please add CLASS_DESCRIPTION to " . static::class);
		}
		return static::CLASS_DESCRIPTION;
	}
	public static function getClassFontAwesome(): string{
		return static::FONT_AWESOME;
	}
	public static function getClassImage(): string{
		return static::DEFAULT_IMAGE;
	}
	/**
	 * @param string $needle
	 * @return BaseModel[]
	 */
	public static function getClassesLike(string $needle): array{
		return collect(self::getClasses())->filter(function($class) use ($needle){
			return strpos($class, $needle) !== false;
		})->all();
	}
	/**
	 * @param string $primaryModelTable
	 * @param bool $implode
	 * @return array|string
	 */
	public static function getColumnsForSmallFieldsNotIn(string $primaryModelTable, bool $implode){
		$primaryModelFields = BaseModel::getColumnsForTable($primaryModelTable);
		$relationFields = static::getColumns();
		$relationFieldsNotInPrimary = array_diff($relationFields, $primaryModelFields);
		$columns = [];
		foreach($relationFieldsNotInPrimary as $item){
			if(in_array($item, static::LARGE_FIELDS)){
				continue;
			}
			$columns[] = $item;
		}
		$columns[] = static::FIELD_ID;
		if($implode){
			return implode(',', $columns);
		}
		return $columns;
	}
	public static function getColumnsForTable(string $table): array{
		$class = QMStr::tableToShortClassName($table);
		$class = str_replace("BaseModel", $class, BaseModel::class);
		$constants = static::getConstants($class);
		$fields = [];
		foreach($constants as $name => $value){
			if(strpos($name, 'FIELD_') === 0){
				$fields[] = $value;
			}
		}
		return $fields;
	}
	/**
	 * @param $value
	 * @param bool $fallbackToExport
	 * @return string
	 * @throws ExceptionInterface
	 */
	public static function getConstantStringForValue($value, bool $fallbackToExport): ?string{
		$const = static::getConstants();
		$shortModelClass = (new \ReflectionClass(static::class))->getShortName();
		if(strpos($value, static::TABLE . '.') !== false){
			$tableStr = $shortModelClass . "::TABLE.'.'.";
			$value = str_replace(static::TABLE . '.', "", $value);
		} else{
			$tableStr = "";
		}
		foreach($const as $constName => $constValue){
			if($constValue === $value){
				return $tableStr . $shortModelClass . "::$constName";
			}
		}
		if($fallbackToExport){
			return VarExporter::export($value);
		}
		return null;
	}
	/** @noinspection PhpUnused */
	public static function getCountPercentOfAllRecordsBox(string $where, string $title = null,
		string $faIcon = null): string{
		$count = static::whereRaw($where)->count();
		$all = static::count();
		if($all){
			$percent = round($count / $all * 100);
		} else{
			$percent = 0;
			$title = "No " . static::getPluralizedClassName();
		}
		if(!$title){
			$title = QMStr::camelToTitle(static::TABLE);
		}
		if(!$faIcon){
			$faIcon = static::FONT_AWESOME;
		}
		return "
            <!-- Apply any bg-* class to to the info-box to color it -->
                <div class=\"info-box bg-red\">
                    <span class=\"info-box-icon\"><i class=\"fa fa-$faIcon\"></i></span>
                    <div class=\"info-box-content\">
                        <span class=\"info-box-text\">$title</span>
                        <span class=\"info-box-number\">$count</span>
                        <!-- The progress section is optional -->
                        <div class=\"progress\">
                            <div class=\"progress-bar\" style=\"width: $percent%\"></div>
                        </div>
                        <span class=\"progress-description\">
                          $percent% where $where
                        </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
        ";
	}
	public static function getCreatedOverTimeChart(array $wheres = []): string{
		return self::getChartOverTime(static::FIELD_CREATED_AT, $wheres);
	}
	public static function getChartOverTime(string $field, array $wheres = [], string $color = 'blue'): string{
		return BaseHighstock::getHtmlForField(static::TABLE, $field, $wheres, $color);
	}
	public static function getDeletedOverTimeChart(array $wheres = []): string{
		return self::getChartOverTime(static::FIELD_DELETED_AT, $wheres);
	}
	public static function getExampleValues(): array{
		$m = new static();
		$properties = $m->getPropertyModels();
		$examples = [];
		foreach($properties as $property){
			$examples[$property->name] = $property->getExample();
		}
		return $examples;
	}
	public static function getFilePathByTable(string $table): string{
		return FileHelper::absPath('app/Models/') . QMStr::tableToShortClassName($table) . '.php';
	}
	public static function getImportantProperties(): array{
		$attributes = static::getImportantColumns();
		$me = new static();
		$properties = [];
		foreach($attributes as $attribute){
			$p = $me->getPropertyModel($attribute);
			if(!$p){
				continue;
			}
			$properties[] = $p;
		}
		return $properties;
	}
	/**
	 * @return array|string
	 */
	public static function getImportantColumns(): array{
		if(empty(static::IMPORTANT_FIELDS)){
			return static::getColumns();
		}
		return explode(',', static::getImportantColumnsForRelation());
	}
	/**
	 * @return array|string
	 */
	public static function getImportantColumnsForRelation(): string{
		return static::IMPORTANT_FIELDS;
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public static function getIndexUrl(array $params = []): string{
		return static::getDataLabIndexUrl($params);
	}
	/**
	 * @param string $table
	 * @return BaseModel
	 */
	public static function getInstanceByTable(string $table): BaseModel{
		if(empty($table)){
			le("No table provided to " . __FUNCTION__);
		}
		if(class_exists($table)){
			$class = $table;
		} else{
			$class = static::getClassByTable($table);
		}
		return new $class();
	}
	/**
	 * @param string $table
	 * @return BaseModel
	 */
	public static function getClassByTable(string $table): string{
		$shortClass = QMStr::tableToShortClassName($table);
		return static::generateFullClassName($shortClass);
	}
	/**
	 * @param string $shortClass
	 * @return string|BaseModel
	 */
	public static function generateFullClassName(string $shortClass): string{
		return str_replace("BaseModel", $shortClass, BaseModel::class);
	}
	/** @noinspection PhpUnused */
	public static function getInstanceOfAllClasses(): array{
		$classes = static::getClasses();
		$models = [];
		foreach($classes as $class){
            $testClass     = new ReflectionClass($class);
            if($testClass->isAbstract()){
                continue;
            }
			$models[] = new $class();
		}
		return $models;
	}
	/**
	 * @return BaseModel[]
	 */
	public static function getInterestingModelClasses(): array{
		$filePaths = FileFinder::listFilesAndFoldersNonRecursively('app/Models', false);
		$interestingTables = QMDB::getInterestingTables();
		$interestingClasses = [];
		foreach($filePaths as $filePath){
			$className = FileHelper::filePathToClassName($filePath);
			$table = QMStr::classToTableName($className);
			if(!in_array($table, $interestingTables)){
				continue;
			}
			$interestingClasses[] = $className;
		}
		return $interestingClasses;
	}
	public function toCliTable(): string{
		$data = [];
		$table = new CliTable;
		$table->addField("Name", 0);
		$table->addField("Value", 1);
		foreach($this->attributes as $key => $value){
			$data[] = [QMStr::truncate($key, 50), ($value) ? QMStr::truncate($value, 50) : null];
		}
		ksort($data);
		$table->injectData($data);
		return $table->get();
	}
	/**
	 * @return static[]
	 */
	public static function getInvalidRecords(): array{
		$rules = (new static())->getPreparedRules();
		$all = [];
		/** @var string[] $ruleStrings */
		foreach($rules as $field => $ruleStrings){
			$all = array_merge($all, self::getInvalidRecordForAttribute($field));
		}
		return $all;
	}
	/**
	 * @param string $field
	 * @return array
	 */
	public static function getInvalidRecordForAttribute(string $field): array{
		$m = new static();
		$rules = $m->getPreparedRules();
		$all = [];
		$ruleStrings = $rules[$field] ?? [];
		foreach($ruleStrings as $ruleString){
			if(in_array($ruleString, ["nullable", "integer"])){
				continue;
			}
			/** @var string $ruleString */
			[$name, $value] = explode(":", $ruleString);
			$models = [];
			if($name === "min"){
				if(!$m->attributeIsNumeric($field)){
					$models = static::whereRaw("length($field) < $value")->get();
				} else{
					$models = static::whereRaw("$field < $value")->get();
				}
			} elseif($name === "max"){
				if(!$m->attributeIsNumeric($field)){
					$models = static::whereRaw("length($field) > $value")->get();
				} else{
					$models = static::whereRaw("$field>$value")->get();
				}
			} elseif($name === "required"){
				$models = static::whereNull($field)->get();
			} else{
				le("Please implement getter for rule $name");
			}
			/** @var BaseModel $model */
			foreach($models as $model){
				$all[$model->getId()] = $model;
			}
		}
		if($all){
			QMLog::error(count($all) . " users with invalid timezones");
		}
		return $all;
	}
	public function attributeIsNumeric(string $attribute): bool{
		return $this->attributeIsFloat($attribute) || $this->attributeIsInt($attribute);
	}
	public function attributeIsFloat(string $attribute): bool{
		$cast = $this->getCast($attribute);
		return $cast === self::CAST_FLOAT;
	}
	public function getCast(string $attribute): string{
		if(in_array($attribute, $this->dates)){
			return "timestamp";
		}
		$casts = $this->casts;
		if(isset($casts[$attribute])){
			return $casts[$attribute];
		}
		if($attribute === Variable::FIELD_ID){
			return "int";
		}
		if($attribute === OAClient::FIELD_CLIENT_ID){
			return "string";
		}
		return $casts[$attribute] ?? "string";
	}
	public function attributeIsInt(string $attribute): bool{
		$cast = $this->getCast($attribute);
		return $cast === self::CAST_INT;
	}
	/**
	 * @param string $string
	 * @return static[]|Collection
	 */
	public static function getLike(string $string): Collection{
		return static::search($string);
	}
	/**
	 * @param string $searchTerm
	 * @param null $callback
	 * @return Collection|\Illuminate\Database\Eloquent\Collection|static[]
	 */
	public static function search(string $searchTerm = '', $callback = null){
        $qb = static::minimalTrailingWildcardSearchWithEagerLoads($searchTerm);
        $results = $qb->get(); // No wildcard in front is like 50X faster (3s vs 0.05s for variables)
		if($results->count() === static::getLimit()){
			return $results;
		}
		$searchQB = self::searchQB($searchTerm);
		$results = $searchQB
            ->get();
		return $results;
	}
	public static function getMinimalFields(): array{
		$minimal = static::getColumns();
		$model = new static();
		$minimal = collect($minimal)->diff($model->hidden)->all();
		$minimal = collect($minimal)->diff(static::getJsonFields())->all();
		$minimal = collect($minimal)->diff(Variable::LARGE_FIELDS)->all();
		$minimal = collect($minimal)->diff(WpPost::LARGE_FIELDS)->all();
		return $minimal;
	}
	public static function getJsonFields(): array{
		$model = new static();
		$casts = $model->casts;
		$json = [];
		foreach($casts as $name => $type){
			if($type === 'json'){
				$json[] = $name;
			}
		}
		return $json;
	}
	/**
	 * @param QMQB|Builder|\Illuminate\Database\Query\Builder|HasMany $qb
	 */
	public static function applyDefaultOrderings($qb){
		$orderings = static::getDefaultOrderings();
		if($qb instanceof Builder){$qb = $qb->getQuery();}
		foreach($orderings as $field => $dir){
			$qb->orderBy($field, $dir);
		}
	}
	/**
	 * @return array
	 */
	public static function getDefaultOrderings(): array{
		return static::DEFAULT_ORDERINGS;
	}
	/**
	 * @param string|null $label
	 * @return AstralResource|AbstractResource
	 */
	public static function getAstralCreateMenuItem(string $label = null): AstralResource{
		return static::getAstralCreateButton($label)->getAstralMenuItem();
	}
	/**
	 * @param string|null $label
	 * @return AstralCreateButton
	 */
	public static function getAstralCreateButton(string $label = null): AstralCreateButton{
		return (new AstralCreateButton(static::class, $label));
	}
	public static function getAstralCreateUrl(array $params = []): string{
		$resourceClass = self::getAstralResourceClass();
		return $resourceClass::getDataLabIndexUrl($params) . "/new";
	}
	/**
	 * @return BaseAstralAstralResource|string
	 */
	public static function getAstralResourceClass(): string{
		return \App\Astral::class . self::getResourceShortClass();
	}
    /**
     * @return BaseJsonResource|string
     */
    public static function getJsonResourceClass(): string{
        return '\App\Http\Resources\\' . self::getResourceShortClass();
    }
	/**
	 * @return string
	 */
	protected static function getResourceShortClass(): string{
		$shortClass = QMStr::toShortClassName(self::getLaravelClassName());
		return $shortClass . "Resource";
	}
	/**
	 * @return BaseModel
	 */
	public static function getLaravelClassName(): string{
		return static::getShortClassName(false);
	}
	/**
	 * @param string $needle
	 * @return BaseProperty[]
	 */
	public static function getPropertiesLike(string $needle): array{
		return collect((new static)->getPropertyModels())->filter(function($prop) use ($needle){
			/** @var BaseProperty $prop */
			return strpos($prop->getShortClassName(), $needle) !== false;
		})->all();
	}
	/**
	 * @return string
	 * @noinspection PhpUnused
	 */
	public static function getRouteName(): string{
		$t = QMStr::stripPrefixes(static::TABLE);
		return QMStr::camelize($t);
	}
	/**
	 * @param int|string|bool|null $selectedOption
	 * @param string|null $name
	 * @param string|null $title
	 * @return string
	 */
	public static function getSelector($selectedOption, string $name, string $title = null): string{
		$list = self::getSelectorOptions();
		if(!$title){
			$title = QMStr::snakeToTitle(str_replace("_id", "", $name));
		}
		return Form::label($name, $title) .
			Form::select($name, $list, $selectedOption, ['placeholder' => "Pick a $title..."]);
	}
	/**
	 * @param array|null $models
	 * @return array
	 */
	public static function getSelectorOptions(array $models = null): array{
		if(!$models){
			$models = static::all();
		}
		$list = [];
		foreach($models as $one){
			$list[$one->getId()] = $one->getTitleAttribute();
		}
		return $list;
	}
	public static function getUpdatedOverTimeChart(array $wheres = []): string{
		return self::getChartOverTime(static::FIELD_UPDATED_AT, $wheres);
	}
	/**
	 * @return string
	 * @noinspection PhpUnused
	 */
	public static function getViewName(): string{
		$singular = (new \ReflectionClass(static::class))->getShortName();
		$snake = QMStr::snakize($singular);
		return QMStr::pluralize($snake);
	}
	/**
	 * Get the logical group associated with the resource.
	 * @return string
	 */
	public static function group(): string{
		return static::$group;
	}
	/**
	 * @param stdClass $row
	 * @return static
	 */
	public static function hydrateOne(stdClass $row): BaseModel{
		return (new static())->newFromBuilder($row);
	}
	/**
	 * @param Request $request
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public static function index(Request $request): \Illuminate\Database\Eloquent\Collection{
		$eb = static::getRequestEloquentBuilder($request);
		$models = $eb->get();
		if(QMRequest::getParam('cards')){
			$models = $models->map(function($m){
				/** @var BaseModel $m */
				return $m->getCard();
			});
		}
        $models = static::sortByDefaultOrdering($models);
        if(static::hasColumn('name')){
            $models = $models->keyBy('name');
        }
		return $models;
	}
	/**
	 * @param Request $request
	 * @return QueryBuilder
	 */
	public static function getRequestEloquentBuilder(Request $request): QueryBuilder{
		return (new static())->scopeApplyRequestParams(static::query(), $request);
	}
	/**
	 * Exclude an array of elements from the result.
	 * @param Builder|QueryBuilder $query
	 * @param Request $request
	 * @return QueryBuilder
	 * USAGE: $medicines = \App\Medicine::exclude('description')->get();
	 */
	public function scopeApplyRequestParams($query, $request): QueryBuilder{
		$this->applyRequestLimit($query, $request);
		$this->applyRequestSort($query, $request);
		$this->applyRequestOffset($query, $request);
		$this->applyRequestPermissions($query);
		$this->applySearchParamToQuery($query);
		$query->whereNull(self::FIELD_DELETED_AT);
		$this->excludeLargeColumnsIfNecessary($query);
		$allowed = $this->getAllowedFilterFields();
		/** @noinspection CallableParameterUseCaseInTypeContextInspection */
		$query = QueryBuilder::for($query, $request)
			->allowedFields($allowed)
			->allowedSorts($allowed)
			->allowedFilters($allowed) // TODO: Maybe try commenting to see if that breaks anything because it's not
			// compatible with current Symfony version
		;
		$query->allowedIncludes($this->getRelationshipNames());
		//$sql = QMQB::addBindingsToSql($query->getQuery());
		return $query;
	}
	/**
	 * Exclude an array of elements from the result.
	 * @param Builder|QueryBuilder $query
	 * @param \Illuminate\Http\Request $request
	 * @return mixed
	 */
	private function applyRequestLimit($query, \Illuminate\Http\Request $request): Builder{
		$limit = $request->get('limit');
		if(!$limit){
			$limit = $this->getDefaultLimit();
		}
		$query->limit($limit);
		return $query;
	}
	public function getDefaultLimit(): int{
		return static::DEFAULT_LIMIT;
	}
	/**
	 * Exclude an array of elements from the result.
	 * @param Builder|QueryBuilder $query
	 * @param \Illuminate\Http\Request $request
	 * @return mixed
	 */
	private function applyRequestSort($query, \Illuminate\Http\Request $request): Builder{
		$sort = $request->get('sort');
		if(empty($sort)){
			static::applyDefaultOrderings($query);
			return $query;
		}
		$direction = self::ORDER_DIRECTION_ASC;
		if(strpos($sort, '-') === 0){
			$sort = substr($sort, 1);
			$direction = self::ORDER_DIRECTION_DESC;
		}
		$query->orderBy($sort, $direction);
		return $query;
	}
	/**
	 * Exclude an array of elements from the result.
	 * @param Builder|QueryBuilder $query
	 * @param \Illuminate\Http\Request $request
	 * @return mixed
	 */
	private function applyRequestOffset($query, \Illuminate\Http\Request $request): Builder{
		$offset = $request->get('skip') || $request->get('offset');
		if($offset){
			/** @noinspection PhpExpressionAlwaysConstantInspection */
			$query->skip($offset);
		}
		return $query;
	}
	/**
	 * Exclude an array of elements from the result.
	 * @param Builder|QueryBuilder $query
	 * @return mixed
	 */
	protected function applyRequestPermissions($query): Builder{
		$this->restrictQueryBasedOnPermissions($query->getQuery());
		return $query;
	}
	/**
	 * @param \Illuminate\Database\Query\Builder|Builder $qb
	 * @param User|QMUser $user
	 * @return \Illuminate\Database\Query\Builder
	 */
	public function restrictQueryBasedOnPermissions($qb, $user = null): \Illuminate\Database\Query\Builder{
		if($qb instanceof Builder){
			$qb = $qb->getQuery();
		}
		if(!$user){
			$user = Auth::user();
		}
		if(!$user && !AppMode::isApiRequest()){
			return $qb;
		}
		if($user && $user->isAdmin()){
			return $qb;
		}
		$table = $this->getTable();
		if($user && $this->hasColumn('user_id')){
			$qb->whereIn($table . '.user_id', $user->getAccessibleUserIds());
		} elseif($this->hasColumn(BaseIsPublicProperty::NAME)){
			$qb->where($table . '.' . BaseIsPublicProperty::NAME, true);
		}
		return $qb;
	}
	/**
	 * @param Builder|QueryBuilder $query
	 */
	protected function applySearchParamToQuery($query){
		$searchValue = QMRequest::getSearchPhrase();
		if($searchValue && $searchValue !== "null" && $searchValue !== "undefined"){
			$defaultSearchField = $this->getDefaultSearchField();
			$searchValue = str_replace("%", "", $searchValue);
			$query->where($defaultSearchField, \App\Storage\DB\ReadonlyDB::like(), "%$searchValue%");
		}
	}
	public function getDefaultSearchField(): string{
		return static::DEFAULT_SEARCH_FIELD;
	}
	/**
	 * @param Builder $query
	 */
	protected function excludeLargeColumnsIfNecessary(Builder $query): void{
		$limit = $query->getQuery()->limit;
		if(QMRequest::getParam('cards') || !$limit || $limit > 1){
			$this->scopeExcludeLargeColumns($query);
		}
	}
	/**
	 * Exclude an array of elements from the result.
	 * @param Builder|QueryBuilder $query
	 * @return Builder
	 * USAGE: $medicines = \App\Medicine::exclude('description')->get();
	 */
	public function scopeExcludeLargeColumns($query): Builder{
		$large = $this->getLargeFields();
		$all = $this->getColumns();
		$diff = array_diff($all, $large);
		$diff = array_diff($diff, $this->hidden);
		return $query->select($diff);
	}
	public function getLargeFields(): array{
		return static::LARGE_FIELDS;
	}
	public function getAllowedFilterFields(): array{
		$all = static::getColumns();
		if(QMAuth::isAdmin()){
			return $all;
		}
		$except = ['client_id', 'user_id'];
		$except = array_merge($except, $this->hidden);
		$allowed = collect($all)->except($except)->all();
		$allowed = array_unique($allowed);
		return $allowed;
	}
	/**
	 * @return QMCard
	 */
	public function getCard(): QMCard{
		return $this->getAstralCard();
	}
	public function getAstralCard(): QMCard{
		$c = new QMCard($this->getUniqueIndexIdsSlug());
		$c->setBackgroundColor($this->getColor());
		$c->setImage($this->getImage());
		$c->setSubTitle($this->getSubtitleAttribute());
		$c->setContent($this->getSubtitleAttribute());
		$c->setTitle($this->getTitleAttribute());
		$c->setUrl($this->getDataLabShowUrl());
		$buttons = $this->getDataLabModelButtons();
		$c->setActionSheetButtons($buttons);
		$c->setHtmlContent($this->getDataLabButtonsHtml());
		return $c;
	}
	public function getColor(): string{
		return static::COLOR;
	}
	public function getImage(): string{
		$img = $this->attributes[Variable::FIELD_IMAGE_URL] ??
			$this->attributes[User::FIELD_AVATAR_IMAGE] ?? $this->attributes[Connector::FIELD_IMAGE] ?? null;
		if(!$img && $this->hasId() && method_exists($this, 'getVariableImage')){
			return $this->getVariableImage();
		}
		if(!$img){
			$img = static::DEFAULT_IMAGE;
		}
		return $img;
	}
	public function getSubtitleAttribute(): string{
		$description = $this->getAttribute('description');
		if(!$description){
			$description = $this->getAttribute('long_description');
		}
		if(!$description){
			$description = $this->getAttribute('short_description');
		}
		if(!$description){
			$description = $this->getCategoryDescription();
		}
		return $description;
	}
	public function getCategoryDescription(): string{
		$des = static::CLASS_DESCRIPTION;
		if(!$des){
			$this->logError("Please set " . static::class . "::CLASS_DESCRIPTION");
			$des = "";
		}
		return $des;
	}
	/**
	 * @return string
	 */
	public static function getTableName(): ?string{
		return static::TABLE;
	}

    /**
     * Build an "index" query for the given resource.
     * @return Builder
     * @throws AccessTokenExpiredException
     */
	public static function indexQuery(): Builder{
		$query = static::query();
		static::applyOrderings($query);
		QueryBuilderHelper::restrictQueryBasedOnPermissions($query);
		QueryBuilderHelper::addParams($query->getQuery(), QMRequest::getReferrerParams());
		return $query;
	}
	/**
	 * @param Builder $query
	 * @param array $orderings
	 * @return Builder
	 * @noinspection OnlyWritesOnParameterInspection
	 */
	public static function applyOrderings(Builder $query, array $orderings = []): Builder{
		/** @var BaseModel $me */
		$me = $query->getModel();
		if(empty($orderings)){
			$orderings = $me::getDefaultOrderings();
		}
		$orderings = array_filter($orderings);
		if(empty($orderings)){
			return empty($query->getQuery()->orders) ? $query->latest($me->getQualifiedKeyName()) : $query;
		}
		foreach($orderings as $column => $direction){
			$query->orderBy($column, $direction);
		}
		/** @noinspection PhpUnusedLocalVariableInspection */
		if($debug = false){
			/** @noinspection PhpUnusedLocalVariableInspection */
			$sql = DBQueryLogServiceProvider::toSQL($query);
		}
		return $query;
	}
	/**
	 * @return string
	 */
	public static function logAll(): string{
		$meta = "";
		$all = static::all();
		foreach($all as $item){
			$item->logInfo("");
			$meta .= $item->__toString() . "\n";
		}
		return $meta;
	}
	/**
	 * @return string
	 */
	public function __toString(){
		// ConsoleLog::info(static::class." ".__FUNCTION__." "); // Uncomment for segfault debugging
		return $this->getTitleAttribute() . " " . static::getClassNameTitle();
	}
	public static function logNew(){
		QMLog::logLink(static::generateDataLabIndexUrl(['sort' => '-' . static::CREATED_AT]),
			"=== Newest " . static::getPluralizedClassName() . " ===");
	}
	public static function logAstralIndexUrl(array $params = []){
		QMLog::info(self::getDataLabIndexUrl($params));
	}
	/**
	 * @return QMQB
	 */
	public static function readonly(): QMQB{
		return ReadonlyDB::getBuilderByTable(static::TABLE);
	}
	public static function replaceColumnStringsWithConstants(string $path): void{
		$classes = static::getClasses();
		foreach($classes as $class){
			$withoutSlash = QMStr::removeFirstCharacter($class);
			$shortClass = QMStr::toShortClassName($class);
			\App\Logging\ConsoleLog::info("Checking fields for " . $class . "...");
			$fields = $class::getColumns();
			foreach($fields as $field){
				$needle = "'$field'";
				$files = FileFinder::getFilesContaining($path, $needle, true);
				\App\Logging\ConsoleLog::info(count($files) . " files containing $needle in $path...");
				foreach($files as $file){
					\App\Logging\ConsoleLog::info($file);
					try {
						$contents = FileHelper::getContents($file);
					} catch (QMFileNotFoundException $e) {
						/** @var LogicException $e */
						throw $e;
					}
					$contents = str_replace($needle, $shortClass . '::FIELD_' . strtoupper($field), $contents);
					if(strpos($contents, 'use ' . $withoutSlash) === false){
						$contents = str_replace(';
class ', ";
use $withoutSlash;
class ", $contents);
						FileHelper::writeByFilePath($file, $contents);
					}
				}
			}
		}
	}
	/**
	 * Determine if this resource is searchable.
	 * @return bool
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function searchable(){
		return !empty(static::searchableColumns()) || static::usesScout();
	}
	/**
	 * @return array
	 */
	public static function searchableColumns(): array{
		if(static::$searchRelations){
			return [];
		}
		return empty(static::$search) ? [(new static)->getKeyName()] : static::$search;
	}
	/**
	 * Get a fresh instance of the model represented by the resource.
	 * @return static
	 */
	public static function newModel(): self{
		return new static;
	}
	/**
	 * Determine if this resource uses Laravel Scout.
	 * @return bool
	 */
	public static function usesScout(): bool{
		return in_array(Searchable::class, class_uses_recursive(static::newModel()));
	}
	/**
	 * Get the displayable singular label of the resource.
	 * @return string
	 */
	public static function singularLabel(): string{
		return Str::singular(static::label());
	}
	/**
	 * Get the displayable label of the resource.
	 * @return string
	 */
	public static function label(): string{
		return Str::plural(Str::title(Str::snake(class_basename(static::class), ' ')));
	}
	public static function tableExists(string $name): bool{
		$tables = self::getTableNames();
		return in_array($name, $tables);
	}
	public static function getTableNames(): array{
		$classes = static::getClasses();
		$tables = [];
		foreach($classes as $class){
			$table = $class::TABLE;
			if($table){
				$tables[] = $class::TABLE;
			}
		}
		return $tables;
	}
	public static function updateModel(){
		$m = new static;
		Kernel::artisan("code:models", [
			"--table" => static::TABLE,
			"--schema" => $m->getConnection()->getDatabaseName(),
			"--connection" => $m->getConnection()->getName(),
		]);
		$class = static::class;
		try {
			ThisComputer::exec("php artisan ide-helper:models $class --write");
		} catch (CommandFailureException $e) {
			le($e);
		}
		static::generateProperties($m->getConnection()->getName());
		static::generateTrait();
	}
	public static function generateTrait(){
		ModelTraitGenerator::generateByClass(static::class);
	}
	/**
	 * @param array $arr
	 * @return array
	 */
	public static function upsert(array $arr): array{
		$models = [];
		foreach($arr as $item){
			$models[] = static::upsertOne($item);
		}
		return $models;
	}
	/**
	 * @param BaseModel|DBModel|array $relatedObj
	 * @return static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function upsertByRelated($relatedObj){
		if(is_array($relatedObj)){
			$data = $relatedObj;
		} else{
			$data = $relatedObj->toNonNullArray();
			$foreignKey = QMStr::classToForeignKey(get_class($relatedObj));
			$data[$foreignKey] = $relatedObj->getId();
		}
		unset($data['id']);
		return static::upsertOne($data);
	}
	/**
	 * @param int $limit
	 * @return Builder
	 */
	public static function whereNew(int $limit = 100): Builder{
		return static::query()->orderBy(static::TABLE . '.' . static::CREATED_AT, self::ORDER_DIRECTION_DESC)
			->limit($limit);
	}
	/**
	 * @param null $userId
	 * @return QMQB
	 */
	public static function writable($userId = null): QMQB{
		$qb = Writable::getBuilderByTable(static::TABLE);
		if(!$userId){
			return $qb;
		}
		return $qb->where(static::FIELD_USER_ID, $userId);
	}
	/**
	 * @return string
	 * @noinspection PhpUnused
	 */
	protected static function getPluralParentClassName(): string{
		$parentClassName = (new \ReflectionClass(static::class))->getShortName();
		$parentWithoutQM = str_replace('QM', '', $parentClassName);
		$pluralParentClass = QMStr::pluralize($parentWithoutQM);
		return $pluralParentClass;
	}
	/**
	 * @param string $method
	 * @param array $parameters
	 * @return BaseModel|mixed
	 */
	public function __call($method, $parameters){
		try {
			return parent::__call($method, $parameters);
		} catch (BadMethodCallException $badMethodCallException) {
			if(method_exists($this, 'getDBModel')){
				try {
					$dbm = $this->getDBModel();
				} catch (\Throwable $dbmException){
				    le($dbmException);
				}
				if(method_exists($dbm, $method)){
					return $this->forwardCallTo($dbm, $method, $parameters);
				}
			}
			throw $badMethodCallException;
		}
	}
	/**
	 * Handle dynamic static method calls into the method.
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return mixed
	 * @noinspection SenselessMethodDuplicationInspection*/
	public static function __callStatic($method, $parameters)
	{
		return (new static)->$method(...$parameters);
	}
    public static function updateOrCreate(array $attributes, array $values = []): Builder|BaseModel|static{
        $model = static::where($attributes)->first();
        if($model){
            $model->update($values);
            return $model;
        }
        return static::create(array_merge($attributes, $values));
    }
	public static function truncate() {
		$table = static::TABLE;
		$c = static::query()->getConnection();
		$databaseName = $c->getDatabaseName();
		$testDBName = TestDB::getDbName();
		if($databaseName !== $testDBName && $databaseName !== ':memory:'){
			$schema = $c->getConfig('schema');
			if(!str_contains($schema, "test") && !str_contains($databaseName, "test")){
				le(__METHOD__.": Not using test db: $databaseName. Actual connection setting is: $databaseName");
			}
        }
        Writable::disableForeignKeyConstraints($c);
        $c->table($table)->truncate();
        Writable::enableForeignKeyConstraints($c);
	}
	protected static function boot(){ // this is a recommended way to declare event handlers
		parent::boot();
		static::saving(function($model){
			/** @var BaseModel $model */
			$model->beforeSave();
		});
		// Should we do this?
		//        static::addGlobalScope('order', function (Builder $builder) {
		//            $builder->orderBy(static::DEFAULT_SORT_FIELD, static::DEFAULT_ORDER_DIRECTION);
		//        });
	}
	protected function beforeSave(){
		// Observer already does this, I think? It's really slow! $this->isValidOrFail(true);
	}
	/**
	 * @return bool|null
	 */
	public function delete(): ?bool{
		$this->deleteFromMemory();
		try {
			return parent::delete();
		} catch (Exception $e) {
			le($e, $this);
		}
	}
	/**
	 * @param array $fields
	 * @return array
	 */
	public function addUserFieldIfNecessary(array $fields): array{
		$u = QMAuth::getQMUser();
		if($u->hasPatients()){
			$hasUserId = $this->hasUserIdAttribute();
			if($hasUserId){
				$fields[] = UserBaseAstralResource::belongsTo();
			}
		}
		return $fields;
	}
	public function hasUserIdAttribute(): bool{
		return $this->hasColumn('user_id');
	}
	public function attributeIsAutoGenerated(string $key): bool{
		$arr = [
			BaseCreatedAtProperty::NAME,
			BaseUpdatedAtProperty::NAME
		];
		if($primary = $this->getPrimaryKey()){
			$arr[] = $primary;
		}
		return in_array($key, $arr);
	}
	/**
	 * A description will provide explanation about the purpose of the instance described by this schema.
	 * @return string
	 */
	public function attributeIsRequired(string $attribute): ?string{
		$rules = $this->getRulesForAttribute($attribute);
		return in_array("required", $rules);
	}
	public function getRulesForAttribute(string $attribute): array{
		$rules = $this->getPreparedRules();
		$forAttr = $rules[$attribute] ?? [];
		return $forAttr;
	}
	/**
	 * @param string $attr
	 * @return bool
	 */
	public function attributeIsSet(string $attr): bool{
		return isset($this->attributes[$attr]);
	}
	/**
	 * @param string $key
	 * @return bool
	 */
	public function attributePresent(string $key): bool{
		return array_key_exists($key, $this->attributes);
	}

    /**
     * @throws UnauthorizedException
     */
    public function authorizePropertyUpdates(array $input = null){
		$changes = $input ?? $this->getDirty();
		foreach($changes as $attr => $value){
			/** @var BaseProperty $prop */
			$prop = $this->getPropertyModel($attr);
            if(!$prop){
                le("No property model for $attr");
            }
			$prop->authorizeUpdate();
		}
		$this->authorize('update');
	}

    /**
     * @param string $ability
     * @return void
     * @throws UnauthorizedException
     */
    public function authorize(string $ability){
		if(!AppMode::isApiRequest()){
			return;
		}
		/** @var User $user */
		$user = \Auth::user();
		if(!$user){
			throw new UnauthorizedException();
		}
		if(!$user->can($ability, $this)){
			throw new UnauthorizedException();
		}
	}

    /**
     * @throws UnauthorizedException
     */
    public function authorizeView(){
		if(!AppMode::isApiRequest()){
			return;
		}
		/** @var User $user */
		$user = \Auth::user();
		if(!$user){
			return;
		} // If a user were required, they would have been stopped by middleware
		$this->authorize('view');
	}
	/**
	 * @return Avatar
	 */
	public function avatarField(): Avatar{
		return Avatar::make('', function(){
			return $this->getImage();
		})->disk('public')->maxWidth(50)->disableDownload()->thumbnail(function(){
			return $this->getImage();
		})->preview(function(){
			return $this->getImage();
		});
	}
    /**
     * @param null $writer
     * @return bool
     */
    public function canCreateMe($writer = null): bool{
        if(!$writer){
            $writer = QMAuth::getQMUser();
        }
        if(!$writer){
            return false;
        }
        return $writer->can('create', $this);
    }
	/**
	 * @param null $writer
	 * @return bool
	 */
	public function canWriteMe($writer = null): bool{
		if(!$this->attributes){
			return true; // This happens when we try to show profile update link in astral
		}
		if($this->readerIsOwnerOrAdmin($writer)){
			return true;
		}
		if(method_exists($this, 'patientGrantedAccess')){
			return $this->patientGrantedAccess('write', $writer);
		} else{
			return false;
		}
	}
	/**
	 * @param null $reader
	 * @return bool
	 */
	public function readerIsOwnerOrAdmin($reader = null): bool{
		if(is_int($reader)){
			$reader = QMUser::find($reader);
		}
		if(!$reader){
			$reader = QMAuth::getQMUser();
		}
		if(!$reader){
			return false;
		}
		if($reader->isAdmin()){
			return true;
		}
		if(method_exists($this, 'getUserId')){
			$ownerUserId = $this->getUserId();
			if($ownerUserId === $reader->getId()){
				return true;
			}
            $accessibleUserIds = $reader->getAccessibleUserIds();
            if(in_array($ownerUserId, $accessibleUserIds)){
                return true;
            }
		}
		return false;
	}
	public function clone(array $new = []): self{
		$attr = $this->attributesToArray();
		unset($attr[$this->getPrimaryKey()]);
		$attr = array_merge($attr, $new);
		if(!isset($attr[BaseClientIdProperty::NAME])){
			$attr[BaseClientIdProperty::NAME] = BaseClientIdProperty::fromRequestJobOrSystem();
		}
		$newV = static::new($attr);
		return $newV;
	}
	/**
	 * @param $data
	 * @throws NoChangesException
	 */
	public function exceptionIfNoChanges($data){
		$changed = $this->getDirty();
		if(!$changed){
			if(!$this->hasId()){
				le('!$this->hasId()');
			}
			$this->populate($data);
			/** @noinspection PhpUnusedLocalVariableInspection */
			$changed = $this->getDirty();
			throw new NoChangesException($data, $this);
		}
	}
	/**
	 * @return QMButton[]
	 */
	public function getActionButtons(): array{
		return [];
	}
	/**
	 * Get the actions available for the resource.
	 * @param Request $request
	 * @return array
	 */
	public function getActions(Request $request): array{
		return [// i.e. new PHPUnitAction($request),
		];
	}
	/**
	 * A description will provide explanation about the purpose of the instance described by this schema.
	 * @return string
	 * @var string
	 */
	public function getAttributeDescription(string $attribute): ?string{
		$val = $this->hints[$attribute] ?? $attribute;
		if(empty($val)){
			$val = $attribute;
		}
		return $val;
	}
	/**
	 * The type of the schema/property. The value MUST be one of "string", "number", "integer", "boolean", "array" or
	 * "object".
	 * @param string $attribute
	 * @return  string
	 */
	public function getAttributeFormat(string $attribute): ?string{
		$type = $this->getCast($attribute);
		if($type === "date"){
			return "date";
		}
		if($type === self::CAST_TIMESTAMP){
			return "date-time";
		}
		return null;
	}
	/**
	 * A string instance is valid against this keyword if its length is less than, or equal to, the value of this
	 * keyword.
	 * @return float|null
	 * @var integer
	 */
	public function getAttributeMaxLength(string $attribute): ?float{
		if(!$this->attributeIsString($attribute)){
			return null;
		}
		$val = $this->getRuleForAttribute($attribute, "max");
		if($val !== null){
			return intval($val);
		}
		return null;
	}
	public function attributeIsString(string $attribute): bool{
		$cast = $this->getCast($attribute);
		return $cast === self::CAST_STRING;
	}
	/**
	 * @param string $attribute
	 * @param string $ruleName
	 * @return array|mixed|string|string[]|null
	 */
	public function getRuleForAttribute(string $attribute, string $ruleName){
		$forAttr = $this->getRulesForAttribute($attribute);
		foreach($forAttr as $ruleStr){
			if(strpos($ruleStr, "$ruleName:") === 0){
				$val = str_replace("$ruleName:", '', $ruleStr);
				return $val;
			}
		}
		return null;
	}
	/**
	 * this keyword validates only if the instance is less than or exactly equal to "maximum".
	 * See http://json-schema.org/latest/json-schema-validation.html#anchor17.
	 * @param string $attribute
	 * @return float|null
	 */
	public function getAttributeMaximum(string $attribute): ?float{
		if(!$this->attributeIsNumeric($attribute)){
			return null;
		}
		$val = $this->getRuleForAttribute($attribute, "max");
		if($val !== null){
			return floatval($val);
		}
		return null;
	}
	/**
	 * A string instance is valid against this keyword if its length is greater than, or equal to, the value of this
	 * keyword.
	 * @return float|null
	 * @var integer
	 */
	public function getAttributeMinLength(string $attribute): ?float{
		if(!$this->attributeIsString($attribute)){
			return null;
		}
		$val = $this->getRuleForAttribute($attribute, "min");
		if($val !== null){
			return intval($val);
		}
		return null;
	}
	/**
	 * If the instance is a number, then this keyword validates only if the instance is greater than or exactly equal
	 * to "minimum". See http://json-schema.org/latest/json-schema-validation.html#anchor21.
	 * @return float|null
	 * @var number
	 */
	public function getAttributeMinimum(string $attribute): ?float{
		if(!$this->attributeIsNumeric($attribute)){
			return null;
		}
		$val = $this->getRuleForAttribute($attribute, "min");
		if($val !== null){
			return floatval($val);
		}
		return null;
	}
	/**
	 * Can be used to decorate a user interface with information about the data produced by this user interface.
	 * preferably be short.
	 * @return string
	 * @var string
	 */
	public function getAttributeTitle(string $attribute): string{
		return self::attributeToTitle($attribute);
	}
	public static function attributeToTitle(string $attribute): string{
		$title = str_replace(HasManyAlias::$number_of_, '', $attribute);
		$title = str_replace('_link', '', $title);
		$title = str_replace('_button', '', $title);
		$title = str_replace('_at', '', $title);
		if(strpos($attribute, '_as_effect') !== false){
			$title = "Causes";
		}
		if(strpos($attribute, '_as_cause') !== false){
			$title = "Effects";
		}
		if($title === "id"){
			$title = "ID";
		}
		$title = QMStr::snakeToTitle($title);
		$title = str_replace(" Id", " ID", $title);
		return $title;
	}
	/**
	 * The type of the schema/property. The value MUST be one of "string", "number", "integer", "boolean", "array" or
	 * "object".
	 * @param string $attribute
	 * @return  string
	 */
	public function getAttributeType(string $attribute): string{
		$type = $this->getCast($attribute);
		if($type === self::CAST_INT){
			return SwaggerDefinitionProperty::TYPE_integer;
		}
		if($type === self::CAST_FLOAT){
			return SwaggerDefinitionProperty::TYPE_number;
		}
		if($type === self::CAST_BOOL){
			return SwaggerDefinitionProperty::TYPE_boolean;
		}
		if($type === self::CAST_TIMESTAMP){
			return SwaggerDefinitionProperty::TYPE_string;
		}
		if(!in_array($type, [
			SwaggerDefinitionProperty::TYPE_string,
			SwaggerDefinitionProperty::TYPE_number,
			SwaggerDefinitionProperty::TYPE_integer,
			SwaggerDefinitionProperty::TYPE_boolean,
			SwaggerDefinitionProperty::TYPE_array,
			SwaggerDefinitionProperty::TYPE_object,
		])){
			le("Invalid type: $type");
		}
		return $type;
	}
	public function getButtons(): array{
		return [
			$this->getButton(),
		];
	}
	public function getCardColumns(): array{
		$large = $this->getLargeFields();
		$all = $this->getColumns();
		return collect($all)->diff($large)->all();
	}
	/**
	 * Get the cards available for the request.
	 * @param Request $request
	 * @return \App\Card[]
	 */
	public function getCards(Request $request): array{
		return [];
	}
	public function getCasts(): array{
        //return parent::getCasts();
		$merged = static::$mergedCasts[static::TABLE] ?? null;
		if($merged){  // Much faster than calling array_merge a million times
			return $merged;
		}
        $casts = parent::getCasts();
        foreach ($this->rules as $column => $rule){
            if(strpos($rule, 'datetime') !== false){
                $this->dates[] = $column;
                $casts[$column] = 'datetime';
            }
        }
        $this->dates = array_unique($this->dates);
        return static::$mergedCasts[static::TABLE] = $casts;
	}
    public function getDates(){
        $merged = static::$mergedDates[static::TABLE] ?? [];
        if($merged){  // Much faster than calling array_merge a million times
            return $merged;
        }
        $merged = parent::getDates();
        foreach (static::getColumns() as $column){
            if(str_ends_with($column, '_at')){
                $merged[] = $column;
            }
        }
        foreach ($this->rules as $column => $rule){
            if(strpos($rule, 'datetime') !== false){
                $merged[] = $column;
            }
        }
        return static::$mergedDates[static::TABLE] = $this->dates = array_unique($merged);
    }

    public function getConstantNameForValue(string $needleValue): ?string{
		$constants = self::getConstants(static::class);
		foreach($constants as $name => $value){
			if($needleValue === $value){
				return $name;
			}
		}
		return null;
	}
	/**
	 * @param string $constantName
	 * @return mixed|null
	 */
	public function getConstantValue(string $constantName){
		$constants = self::getConstants(static::class);
		return $constants[$constantName] ?? null;
	}
	public function getDebugUrl(): string{
		if(method_exists($this, "getAnalyzeUrl")){
			$url = $this->getAnalyzeUrl();
		} else{
			$url = $this->getUrl();
		}
		return UrlHelper::toLocalUrl($url);
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		if($this->hasValidId()){
			$url = $this->getDataLabShowUrl();
		} else{
			$url = static::getDataLabIndexUrl();
		}
		return UrlHelper::addParams($url, $params);
	}
	public function getDefaultSortOrderDirection(): string{
		$orderings = static::getDefaultOrderings();
		$key = $this->getDefaultSortField();
		return $orderings[$key];
	}
	public function getDefaultSortField(): string{
		$orderings = static::getDefaultOrderings();
		$key = array_key_first($orderings);
		return $key;
	}
	public function getDescriptionHtml(): string{
		return "<div>" . $this->getSubtitleAttribute() . "</div>";
	}
	/**
	 * Build DataTable class.
	 * @param mixed $query Results from query() method.
	 * @return BaseEloquentDataTable
	 */
	public function getEloquentDataTable($query = null): BaseEloquentDataTable{
		$svc = $this->getDataTableService();
		return $svc->getEloquentDataTable($query);
	}
	public function getDataTableService(): BaseDataTableService{
		$class = BaseDataTableService::class;
		$class = str_replace("Base", $this->getShortClassName(), $class);
		return new $class($this);
	}
	/**
	 * Get the fields displayed by the resource.
	 * @return array
	 */
	public function getFields(): array{
		$fields = [];
		if(!AstralRequest::isCreateOrAssociatableSearch()){ // Otherwise we're probably creating
			$fields[] = $this->imageField();
			$fields[] = $this->getNameDetailsLink();
		}
		$fields = array_merge($fields, $this->getShowablePropertyFields());
		return $fields;
	}
	/**
	 * @return Image
	 */
	public function imageField(): Image{
		return Image::make('', function(){
			return $this->getImage();
		})->disk('public')->disableDownload()->thumbnail(function(){
			return $this->getImage();
		})->preview(function(){
			return $this->getImage();
		});
	}
	public function getNameDetailsLink(): Text{
		$f = $this->nameField();
		return $f->detailLink();
	}
	/**
	 * @return Field[]
	 */
	protected function getShowablePropertyFields(): array{
		$fields = [];
		$props = $this->getPropertyModels();
		foreach($props as $property){
			$field = $property->getField();
			if(!$property->showOnIndex()){
				$field->hideFromIndex();
			}
			if(!$property->showOnCreate()){
				$field->hideWhenCreating();
			}
			if(!$property->showOnUpdate()){
				$field->hideWhenUpdating();
			}
			if(!$property->showOnDetail()){
				$field->hideFromDetail();
			}
			$fields[$property->getOrder() ."-". $property->getTitleAttribute()] = $field;
		}
		ksort($fields);
		return $fields;
	}
	/**
	 * @return Filter[]
	 */
	public function getFilters(): array{
		$gotten = $haveFilters = $shouldShow = [];
		$props = $this->getPropertyModels();
		foreach($props as $p){
			if(method_exists($p, 'getFilter')){
				$haveFilters[] = $p;
			}
		}
		foreach($haveFilters as $p){
			if($p->shouldShowFilter()){
				$shouldShow[$p->name] = $p;
			}
		}
		foreach($shouldShow as $p){
			if($f = $p->getFilter()){
				$gotten[] = $f;
			}
		}
		$combined = [];
		//$combined[] = new NumericFilter($this);
		if($f = $this->getTextFilter($gotten)){
			$combined[] = $f;
		}
		if($f = $this->getNumericFilter($gotten)){
			$combined[] = $f;
		}
		$combined = array_merge($combined, $gotten);
		if($f = $this->getIdFilter($gotten)){
			$combined[] = $f;
		}
		return $combined;
	}
	public function getTextFilter(array $fromPropertyModels = []): ?TextFilter{
		$multiColumns = [];
		$props = $this->getStringPropertyModels();
		foreach($props as $prop){
			if(!isset($fromPropertyModels[$prop->name])){
				$multiColumns[$prop->name] = [
					'type' => 'text',
					'label' => $prop->getTitleAttribute(),
					'defaultOperator' => '=',
					'operators' => [
						'=' => '=',
						'LIKE' => 'Like',
						//'IS NULL' => 'Is Null',
					],
				];
			}
		}
		if(!$multiColumns){
			return null;
		}
		return new TextFilter($multiColumns, false, // Apply filter with the button
			'text', // Default input type
			'Text Filter' // Filter name
		);
	}
	public function getNumericFilter(array $fromPropertyModels = []): ?NumericMultiColumnFilter{
		$multiColumns = [];
		$props = $this->getNonIdNumericPropertyModels();
		foreach($props as $prop){
			if(!isset($fromPropertyModels[$prop->name])){
				$multiColumns[$prop->name] = [
					'type' => 'number',
					'label' => $prop->getTitleAttribute(),
					'defaultOperator' => '=',
				];
			}
		}
		if(!$multiColumns){
			return null;
		}
		return new NumericMultiColumnFilter($multiColumns, false, // Apply filter with the button
			'number', // Default input type
			'Numeric Filter' // Filter name
		);
	}
	public function getIdFilter(array $fromPropertyModels = []): ?IdFilter{
		$multiColumns = [];
		$props = $this->getIdPropertyModels();
		foreach($props as $prop){
			if(!isset($fromPropertyModels[$prop->name])){
				// https://github.com/dddeeemmmooonnn/astral-multicolumn-filter
				$multiColumns[$prop->name] = [
					'type' => ($prop->isNumeric()) ? 'number' : 'text',
					'label' => $prop->getTitleAttribute(),
					'defaultOperator' => '=',
					//TODO: Use defaultValue for filtering instead of query in IndexController
					// 'defaultValue' => $prop->fromReferrer(),
					//'preset' => false,
					//'placeholder' => $prop->getSubtitleAttribute(),
					// 'apply' => 'customApply', custom apply method, that will filter the column
				];
			}
		}
		if(!$multiColumns){
			return null;
		}
		return new IdFilter($multiColumns, false, // Apply filter with the button
			'number', // Default input type
			'ID Filter' // Filter name
		);
	}
	/**
	 * @return mixed
	 */
	public static function getGatePolicy(): BasePolicy{
		$p = \Gate::getPolicyFor(static::class);
		if(!$p){
			return new BasePolicy();
		}
		return $p;
	}
	/**
	 * @param string $attribute
	 * @return string
	 */
	public function getHint(string $attribute): ?string{
		return $this->hints[$attribute] ?? null;
	}
	/**
	 * @return array
	 */
	public function getHints(): array{
		return $this->hints;
	}
	public function getIcon(): string{
		return $this->getImage();
	}
	/**
	 * @return int|string
	 */
	public function getIdIfExists(){
		return $this->attributes[User::FIELD_ID] ?? $this->attributes['id'] ?? $this->getPrimaryKeyValue();
	}
	public function getInterestingRelationshipsMenu(): AstralRelationshipMenu{
		return $this->getAstralRelationshipMenu();
	}
	public function getAstralRelationshipMenu(): AstralRelationshipMenu{
		return new AstralRelationshipMenu($this);
	}
	/**
	 * Get the lenses available for the resource.
	 * @param Request $request
	 * @return Lens[]
	 */
	public function getLenses(Request $request): array{
		return [];
	}
	public function getNameField(): Text{
		return Text::make('Name', function(){
			return $this->getNameAttribute();
		})->detailLink();
	}
    /**
     * @return string|null
     */
    public function getHumanizedIdentifierAttributeName(): ?string
    {
        $keyBy = null;
        if ($this->hasAttribute('display_name')) {
            $keyBy = 'display_name';
        }
        if ($this->hasAttribute('app_display_name')) {
            $keyBy = 'app_display_name';
        }
        if ($this->hasAttribute('name')) {
            $keyBy = 'name';
        }
        return $keyBy;
    }
	public function getNameUpdateLink(): Text{
		return $this->nameField()->updateLink();
	}
	public function nameField(): Text{
		return Text::make('Name', function(){
			return $this->getNameAttribute();
		});
	}
	/**
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function getAstralButtonsHtml(): string{
		$html = '';
		$buttons = $this->getAstralModelButtons();
		foreach($buttons as $button){
			$html .= $button->getRoundOutlineWithIcon();
		}
		return $html;
	}
	/**
	 * @return QMButton[]
	 */
	public function getAstralModelButtons(): array{
		try {
			return $this->getAstralSingleModelMenu()->getButtons();
		} catch (NoIdException $e) {
			return [static::getAstralIndexButton()];
		}
	}
	/** @noinspection PhpUnused */
	public function getAstralDeleteUrl(array $params = []): ?string{
		$id = $this->getId();
		if($id === null){
			le("No id to generate delete url on $this " . get_class($this));
		}
		return static::generateAstralShowUrl($this->getId(), $params);
	}
	/**
	 * @param $id
	 * @param array $params
	 * @return string
	 */
	public static function generateAstralShowUrl($id, array $params = []): string{
		$urlEncoded = urlencode($id);
		return static::generateAstralUrl($urlEncoded, $params);
	}
	public static function generateAstralUrl(string $path = null, array $params = []): string{
		$url = static::getDataLabIndexUrl();
		if($path){
			$url .= "/$path";
		}
		return UrlHelper::addParams($url, $params);
	}
	/**
	 * @param string|null $label
	 * @return AstralDetailsButton
	 */
	public function getAstralDetailsButton(string $label = null): AstralDetailsButton{
		return (new AstralDetailsButton($this, $label));
	}
	public function getAstralEditButton(array $params = []): QMButton{
		$b = $this->getAstralUpdateButton();
		if($params){
			$b->setParameters($params);
		}
		return $b;
	}
	/**
	 * @param string|null $label
	 * @return AstralUpdateButton
	 */
	public function getAstralUpdateButton(string $label = null): AstralUpdateButton{
		return (new AstralUpdateButton($this, $label));
	}
	/**
	 * @param array $params
	 * @return QMButton
	 */
	public function getAstralButton(array $params = []): QMButton{
		try {
			$b = $this->getAstralDetailsButton($this->getNameAttribute());
		} catch (NoIdException $e) {
			$b = static::getAstralIndexButton($params);
		}
		$name = QMStr::truncate($this->getTitleAttribute(), 20);
		$b->setTextAndTitle($name);
		return $b;
	}
	/**
	 * @param array $params
	 * @param null $badgeText
	 * @param string|null $name
	 * @param string|null $fontAwesome
	 * @param string|null $tooltip
	 * @param string|null $color
	 * @param string|null $url
	 * @return AstralIndexButton
	 */
	public static function getAstralIndexButton(array $params = [], $badgeText = null, string $name = null,
		string $fontAwesome = null, string $tooltip = null, string $color = null, string $url = null): AstralIndexButton{
		return (new AstralIndexButton(static::class, $name, $badgeText, $fontAwesome, $tooltip, $color, $url, $params));
	}
	/** @noinspection PhpUnused */
	public function getAstralNameDropDownButton(string $title = null): string{
		return $this->getAstralSingleModelMenu($title)->getDropDownMenu();
	}
	public function getAstralSingleModelMenu(string $title = null): SingleModelAstralMenu{
		return new SingleModelAstralMenu($this, $title);
	}
	public function getAstralProfileButton(array $params = []): QMButton{
		$params[QMRequest::PARAM_PROFILE] = 1;
		$b = new QMButton();
		$b->setFontAwesome(FontAwesome::HOURGLASS);
		$b->setTextAndTitle("Profile View");
		$b->setTooltip("Profile View");
		$b->setUrl($this->getDataLabShowUrl($params));
		$b->setImage(ImageUrls::EDUCATION_HOURGLASS);
		$b->setBackgroundColor($this->getColor());
		return $b;
	}
	public function getAstralUpdateUrl(array $params = []): string{
		return UrlHelper::addParams($this->getDataLabShowUrl() . "/edit", $params);
	}
	public function getOpenApiAttributeSchema(string $attribute): array{
		$schema = $this->openApiSchema[$attribute] ?? null;
		if(!$schema){
			$cast = $this->getCast($attribute);
			if($cast === "int"){
				$cast = "integer";
			}
			if($cast === "bool"){
				$cast = "boolean";
			}
			$fieldData = SwaggerGenerator::getFieldType($cast);
			$schema = ['type' => $fieldData['fieldType']];
			if(isset($fieldData['fieldFormat'])){
				$schema['format'] = $fieldData['fieldFormat'];
			}
			if(!isset($schema['type']) && $cast === "json"){
				$schema['type'] = "object";
			}
			if(!isset($schema['type']) && $cast === "array"){
				$schema['type'] = "array";
			}
			if(!isset($schema['type']) && $cast === "object"){
				$schema['type'] = "object";
			}
		}
		if(!isset($schema['type']) || $schema['type'] === "array" && !isset($schema['items'])){
			le("Please set openApiSchema[$attribute] on " . __CLASS__);
		}
		return $schema;
	}
	public function getOpenApiSchema(): Schema{
		$definition = new Schema([]);
		$definition->title = $this->getShortClassName();
		$definition->schema = $this->getShortClassName();
		$definition->required = $this->getRequiredFields();
		return $definition;
	}
	public function getRequiredFields(): array{
		$required = [];
		if($props = $this->getPropertyModels()){
			foreach($props as $p){
				if($p->required){
					$required[] = $p->name;
				}
			}
		} else {
			foreach($this->rules as $field => $str){
				if(stripos($str, 'required') !== false){
					$required[] = $field;
				}
			}
		}
		return $required;
	}
	/**
	 * @return string
	 */
	public function getPHPStormUrl(): string{
		return PHPStormButton::redirectUrl($this->getModelFilePath(), 0);
	}
	public function getPluralTitle(): string{
		return static::getClassNameTitlePlural();
	}
	/**
	 * @param string $attribute
	 * @return mixed|null
	 */
	public function getRawAttribute(string $attribute){
		return $this->attributes[$attribute] ?? null;
	}
	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function getRelationIfLoaded(string $key){
		if(isset($this->relations[$key]) && $this->relations[$key]){
			return $this->relations[$key];
		}
		return null;
	}
	/**
	 * @return BaseAstralAstralResource
	 */
	public function getAstralResource(): \App\AstralResource{
		$resourceClass = self::getAstralResourceClass();
		return new $resourceClass($this);
	}
    /**
     * @return BaseJsonResource
     */
    public function getJsonResource(): BaseJsonResource {
        $resourceClass = self::getJsonResourceClass();
        return new $resourceClass($this);
    }
	/**
	 * @return string
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function getEmailHtml(): string{
		$path = qm_request()->getViewPathByType("email", static::TABLE);
		$singular = (new \ReflectionClass(static::class))->getShortName();
		$snake = QMStr::snakize($singular);
		/** @noinspection PhpUnhandledExceptionInspection */
		$view = view($path, [
			'model' => $this,
			$snake => $this,
		]);
		return HtmlHelper::renderView($view);
	}
	/**
	 * @return string
	 * Much faster than getUniqueNamesSlug
	 */
	public function getSlugFromMemory(): string{
		return $this->getUniqueNamesSlug();
	}
	public function getUniqueNamesSlug(): string{
		return $this->getId();
	}
	public function getSortingScore(): float{ return strtotime($this->getUpdatedAt()); }
	public function getUpdatedAt(): ?string{ return $this->getAttribute(static::UPDATED_AT); }
	public function getStaticContent(): string{
		return $this->getEmailContent();
	}
	public function getEmailContent(): string{
		return $this->getEmailHtml(); // Keep this wrapper so I don't forget what it's called and re-implement
	}
	public function getTemporalProperties(): array{
		$properties = $this->getPropertyModels();
		$temporal = [];
		foreach($properties as $property){
			if($property->isTemporal()){
				$temporal[] = $property;
			}
		}
		return $temporal;
	}
	/**
	 * Force a hard delete on a soft deleted model.
	 * This method protects developers from running forceDelete when trait is missing.
	 * @param string $reason
	 * @return bool|null
	 */
	public function hardDelete(string $reason): ?bool{
		$this->logError("Hard deleting because $reason");
		return $this->forceDelete();
	}
	/**
	 * @param string $reason
	 * @return bool|void|null
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function hardDeleteWithRelations(string $reason){
		le("Please implement hardDeleteWithRelations for $this " . get_class($this));
		//return $this->getDBModel()->hardDeleteWithRelations($reason);
	}
	public function isAnalyzable(): bool{
		return static::ANALYZABLE;
	}
	/**
	 * @return ID
	 */
	public function isField(): ID{
		return ID::forModel($this);
	}
	public function isOrderable(string $attribute): bool{
		if(!$this->hasColumn($attribute)){
			return false;
		}
		$type = $this->casts[$attribute] ?? null;
		if(in_array($type, ["float", "int", "date"])){
			return true;
		}
		if(static::isCountAttribute($attribute)){
			return true;
		}
		if(static::isDateTime($attribute)){
			return true;
		}
		return strpos($attribute, 'name') !== false;
	}
	public function isCountAttribute(string $attribute): bool{
		return strpos($attribute, HasManyAlias::$number_of_) === 0;
	}
	public function isDateTime(string $attribute): bool{
		return in_array($attribute, $this->dates);
	}
	public function isSearchable(string $attribute): bool{
		if(!$this->hasColumn($attribute)){
			return false;
		}
		if(static::isCountAttribute($attribute)){
			return false;
		}
		if(static::isDateTime($attribute)){
			return false;
		}
		if($this->attributeIsNumeric($attribute)){
			return false;
		}
		return true;
	}
	public function l(): BaseModel{ return $this; }
	public function logAdminerUrl(): string{
		$qb = Writable::db()->table(static::TABLE)->where($this->getPrimaryKey(), $this->getId());
		return $qb->getAdminerUrl();
	}
	public function nameLinkToShowField(): Text{
		return Text::make('Name', function(BaseModel $resource){
			//if(!$this->attributes){return null;}
			return '<a href="' . $resource->getUrl() . '" 
                class="no-underline font-bold dim text-primary"
                title="Go to Study" 
                target="_blank">
                ' . $resource->getNameAttribute() . '
            </a>';
		})->asHtml();
	}
	public function print(): string{
		return QMLog::print_r($this->toArray(), true);
	}
	/**
	 * @param string $key
	 * @param $value
	 */
	public function processAndSetAttribute(string $key, $value): void{
		$p = $this->getPropertyModel($key);
		$p->processAndSetDBValue($value);
	}
	/**
	 * @return array
	 */
	public function revertChanges(): array{
		if($this->exists && $this->original){
			$this->attributes = $this->original;
		}
		return $this->attributes;
	}
	/**
	 * Exclude an array of elements from the result.
	 * @param Builder|QueryBuilder $query
	 * @param $columns
	 * @return Builder
	 * USAGE: $medicines = \App\Medicine::exclude('description')->get();
	 */
	public function scopeExclude($query, $columns): Builder{
		return $query->select(array_diff($this->getColumns(), (array)$columns));
	}
	/**
	 * query scope nPerGroup
	 * @param Builder|QueryBuilder $query
	 * @param $group
	 * @param int $n
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function scopeNPerGroup($query, $group, int $n = 10){
		// queried table
		$table = ($this->getTable());
		// initialize MySQL variables inline
		$query->from(DB::raw("(SELECT @rank:=0, @group:=0) as vars, {$table}"));
		// if no columns already selected, let's select *
		if(!$query->getQuery()->columns){
			$query->select("{$table}.*");
		}
		// make sure column aliases are unique
		$groupAlias = 'group_' . md5(time());
		$rankAlias = 'rank_' . md5(time());
		// apply mysql variables
		$query->addSelect(DB::raw("@rank := IF(@group = {$group}, @rank+1, 1) as {$rankAlias}, @group := {$group} as {$groupAlias}"));
		// make sure first order clause is the group order
		array_unshift($query->getQuery()->orders, ['column' => $group, 'direction' => self::ORDER_DIRECTION_ASC]);
		$subQuery = $query->toSql(); // prepare subquery
		$newBase = $this->newQuery() // prepare new main base Query\Builder
		->from(DB::raw("({$subQuery}) as {$table}"))->mergeBindings($query->getQuery())->where($rankAlias, '<=', $n)
			->getQuery();
		// replace underlying builder to get rid of previous clauses
		$query->setQuery($newBase);
	}
	/**
	 * @param string $key
	 * @param $new
	 */
	public function setAttributeIfExistsAndDifferent(string $key, $new){
		if(!$this->hasColumn($key)){
			return;
		}
		$this->setAttributeIfDifferentFromAccessor($key, $new);
	}
	/**
	 * @param string $key
	 * @param $new
	 */
	public function setAttributeIfDifferentFromAccessor(string $key, $new){
		$existing = $this->getAttribute($key);
		if($existing === "" && $new === null){
			return;
		}
		if($existing === null && $new === ""){
			return;
		}
		if($existing === $new){
			return;
		}
		if($this->attributeIsBool($key)){
			if(BoolHelper::isEqual($existing, $new)){
				return;
			}
		}
		$prop = $this->getPropertyModel($key);
		if(!$prop){
			$this->logInfo("No property model for $key on ".static::class);
		} else {
			if($prop->isGeneratedByDB()){
				return;
			}
		}
		$this->setAttribute($key, $new);
	}
	/** @noinspection PhpUnused */
	public function attributeIsBool(string $attribute): bool{
		$cast = $this->getCast($attribute);
		return $cast === self::CAST_BOOL;
	}
	/**
	 * @param string $key
	 * @param $newValue
	 */
	public function setAttributeIfNull(string $key, $newValue){
		if($newValue === null){
			return;
		}
		$existing = $this->getAttribute($key);
		if($existing === null){
			$this->setAttribute($key, $newValue);
		}
	}
	/**
	 * @param string $attribute
	 * @param string $reason
	 * @throws ModelValidationException
	 */
	public function setAttributeNullAndLogError(string $attribute, string $reason){
		$this->logError("Changing $attribute from $this->$attribute to null because $reason");
		/** @var BaseModel $item */
		$this->$attribute = null;
		try {
			$this->isValidOrFail();
		} catch (ValidationException $e) {
			/** @var ModelValidationException $e */
			throw $e;
		}
		$this->save();
	}
	/**
	 * @param string $key
	 * @param $new
	 */
	public function setIfGreaterThanExisting(string $key, $new){
		if($new === null){
			return;
		}
		if($prop = $this->getPropertyModel($key)){
			$prop->setIfGreaterThanExisting($new);
		} else{
			$existing = $this->getAttribute($key);
			if(is_int($existing) && TimeHelper::isCarbon($new)){
				$new = $new->timestamp;
			}
			if($existing === null || (float)$existing < (float)$new){
				$this->setAttribute($key, $new);
			}
		}
	}
	/**
	 * @param string $key
	 * @param $new
	 */
	public function setIfLessThanExisting(string $key, $new){
		if($new === null){
			return;
		}
		if($prop = $this->getPropertyModel($key)){
			$prop->setIfLessThanExisting($new);
		} else{
			$existing = $this->getAttribute($key);
			$isCarbon = TimeHelper::isCarbon($new);
			if(is_int($existing) && $isCarbon){
				$new = $new->timestamp;
			}
			if($existing === null || (float)$existing > (float)$new){
				$this->setAttribute($key, $new);
			}
		}
	}
	/**
	 * @param string $key
	 * @param $value
	 * @return $this
	 */
	public function setRawAttribute(string $key, $value): BaseModel{
		// If an attribute is listed as a "date", we'll convert it from a DateTime instance into a form proper for
		// storage on the database tables using the connection grammar's date format. We will auto set the values.
		if($value && $this->isDateAttribute($key)){
			$value = $this->fromDateTime($value);
		}
		if($this->isJsonCastable($key) && !is_null($value) && !is_string($value)){
			$value = $this->castAttributeAsJson($key, $value);
		}
		// If this attribute contains a JSON ->, we'll set the proper value in the attribute's underlying array.
		// This takes care of properly nesting an attribute in the array's value in the case of deeply nested items.
		if(Str::contains($key, '->')){
			return $this->fillJsonAttribute($key, $value);
		}
		$this->attributes[$key] = $value;
		return $this;
	}
	/**
	 * Get the search result subtitle for the resource.
	 * @return string|null
	 */
	public function subtitle(): ?string{
		return $this->getSubtitleAttribute();
	}
	public function toDBArray(): array{
		// ConsoleLog::info(static::class." ".__FUNCTION__." "); // Uncomment for segfault debugging
		$arr = $this->toArray();
		foreach($arr as $key => $val){
			if(is_object($val) || is_array($val)){
				$arr[$key] = json_encode($val);
			}
		}
		return $arr;
	}
	/**
	 * @param BaseModel|string $class
	 * @param string|null $foreignKey
	 * @return array
	 */
	public function toNewRelationArray(string $class, string $foreignKey = null): array{
		$fields = $class::getColumns();
		$model = new $class;
		$arr = [];
		foreach($fields as $field){
			if($field === $model->getPrimaryKey()){
				continue;
			}
			if($field === $class::CREATED_AT){
				continue;
			}
			if($field === $class::UPDATED_AT){
				continue;
			}
			if($field === $class::FIELD_DELETED_AT){
				continue;
			}
			$val = $this->getAttribute($field);
			if($val !== null){
				$arr[$field] = $val;
			}
		}
		if(!$foreignKey){
			$foreignKey = $this->getForeignKey();
		}
		$arr[$foreignKey] = $this->getPrimaryKeyValue();
		return $arr;
	}
	/**
	 * Get the indexable data array for the model.
	 * @return array
	 */
	public function toSearchableArray(): array{
		$arr = [];
		$arr[$this->getKeyName()] = $this->getKey();
		$arr['name'] = $this->getNameAttribute();
		foreach($this->getCountFields() as $field){
			$arr[$field] = $this->getAttribute($field);
		}
		if(method_exists($this, 'getUserId')){
			$arr['user_id'] =
				($this->attributes) ? $this->getUserId() : null; // Prevent php artisan scout:status when model is empty
		}
		return $arr;
	}
	/**
	 * @return string[]
	 */
	public function getCountFields(): array{
		return static::getColumnsLike("number_of_");
	}
	/**
	 * @param string $string
	 * @return string[]
	 */
	public static function getColumnsLike(string $string): array{
		$matches = [];
		foreach(static::getColumns() as $field){
			if(stripos($field, $string) !== false){
				$matches[] = $field;
			}
		}
		return $matches;
	}
	/**
	 * @return int[]
	 */
	public function updateInterestingRelationshipCountFields(): array{
		$relationships = $this->getInterestingRelationshipButtons();
		$counts = [];
		foreach($relationships as $button){
			if(!property_exists($button, 'methodName')){
				QMLog::debug("Skipping " . $button->title);
				continue; // Variable category button
			}
			$relationName = $button->methodName;
			/** @var HasMany $relation */
			$relation = $button->getButtonRelation();
			$relatedModel = $relation->getRelated();
			$numberOfField = "number_of_" . $relationName;
			if($this->hasColumn($numberOfField)){
				$this->logInfo("Calculating $numberOfField...");
				$foreignKeys = $relation->getQualifiedForeignKeyName();
				$qb = $relatedModel->newQuery();
				if(is_array($foreignKeys)){
					foreach($foreignKeys as $foreignKey){
						$withoutTable = QMStr::after('.', $foreignKey);
						$qb->where($foreignKey, $this->getAttribute($withoutTable));
					}
				} else{
					$qb->where($foreignKeys, $this->getId());
				}
				$value = $qb->count();
				$counts[$numberOfField] = $this->$numberOfField = $value;
			}
		}
		//$this->save();
		return $counts;
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		return [];
	}
	public function validFilterField(string $name): ?string{
		$all = $this->getAllowedFilterFields();
		if(in_array($name, $all)){
			return $name;
		}
		$name = str_replace($this->table . '_', '', $name);
		if(!in_array($name, $all)){
			return null;
		}
		return $name;
	}
	/**
	 * @param string $key
	 * @param $value
	 * @throws InvalidAttributeException
	 */
	public function validateAndSetAttribute(string $key, $value){
		$prop = $this->getPropertyModel($key);
		$prop->setRawAttribute($value);
		$prop->validate();
	}
	/**
	 * @param User|int|null $reader
	 * @throws UnauthorizedException
	 */
	public function validateCanRead($reader = null){
		if(!$this->canReadMe($reader)){
			$this->canReadMe($reader);
			throw new UnauthorizedException();
		}
	}
	/**
	 * @param User|int|null $reader
	 * @return bool
	 */
	public function canReadMe($reader = null): bool{
		if($this->readerIsOwnerOrAdmin($reader)){
			return true;
		}
		if($this->patientGrantedAccess('read', $reader)){
			return true;
		}
        if(method_exists($this, 'getUserId')){
            $ownerUserId = $this->getUserId();
            try {
                $data = \request()->input();
                if($data && OAClientClientSecretProperty::pluck($data)){
                    $client = OAClient::authorizeBySecret($data);
                    $t = $client->oa_access_tokens()
                        ->where('user_id', $ownerUserId)
                        ->where(OAAccessToken::FIELD_EXPIRES, '>', Carbon::now())
                        ->first();
                    if($t){
                        return true;
                    }
                }

            } catch (\Throwable $e) {}
        }
		return false;
	}
	public function patientGrantedAccess(string $accessType, User $accessor = null): bool{return false;}
    public function getDBTable(): DBTable{
		return DBTable::find($this->getTable());
    }
    public function validateInput(array $input): array
    {
		if(isset($input[0])){
			foreach($input as $i => $row){
				$input[$i] = $this->validateInput($row);
			}
			return $input;
		}
        $input = $this->convertObjectsOrArraysToIds($input);
        foreach ($input as $key => $value) {
            if(in_array($key, $this->appends)){
                unset($input[$key]);
            }
        }
        $this->validateFillable($input);
        $this->validateColumnExistence($input);
        return $input;
    }
    private function validateColumnExistence(array $input){
        $columns = $this->getColumns();
        $inputKeys = array_keys($input);
        $doNotExist = array_diff($inputKeys, $columns);
        if($doNotExist){
            throw new WrongParameterException($this->getShortClassName(), $doNotExist, $columns);
        }
    }

    private function validateFillable(array $input)
    {
        $fillable = $this->getFillable();
        $notFillable = QMArr::array_diff_recursive($input, $fillable);
        if(!$notFillable){
            throw new NotFillableException($this->getShortClassName(), $notFillable, $fillable);
        }
    }
    public static function getApiV6BasePath(): string{
        return '/api/v6/' . static::TABLE;
    }
    public function getApiV6IdPath(): string{
        return static::getApiV6BasePath()."/".$this->getId();
    }
    /**
	 * @return Field[]
	 */
	protected function getFloatFields(): array{
		$fields = [];
		$props = $this->getPropertyModels();
		foreach($props as $property){
			if($property->isFloat()){
				$fields[] = $property->getField();
			}
		}
		return $fields;
	}
	/**
	 * @return Column[]
	 */
	protected function getValueTableColumns(): array{
		$columns = [];
		$fields = $this->getColumns();
		foreach($fields as $field){
			$data = $name = $field;
			//$type = $this->getCastType($field);
			//$searchable = $type === "string";
			$searchable = false;
			$orderable = true;
			if(stripos($field, '_id') !== false){
				$name = str_replace('_id', '.name', $field);
				$data = str_replace('_id', '_link', $field);
				$orderable = false;
			}
			$columns[$field] = new Column([
				'title' => QMStr::humanizeFieldName($field),
				'data' => $data,
				'name' => $name,
				'searchable' => $searchable,
				'orderable' => $orderable,
			]);
		}
		return $columns;
	}
	public function popupLinkToIndex(string $title = null){
		Alerter::popupWithButton($title ?? $this->getTitleAttribute() . " Not found", $this->getIndexUrl(),
			"See available " . $this->getPluralTitle(), ImageUrls::ESSENTIAL_COLLECTION_LIST, static::DEFAULT_IMAGE);
	}
	/**
	 * @param array $arr
	 * @param string|null $reason
	 * @return int
	 */
	public function updateDbRow(array $arr, string $reason = null): int{
		$this->unguard(true);
		$this->fill($arr);
		$this->logInfo("Updating because $reason");
		try {
			$result = $this->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		return $result;
	}
	/**
	 * Encode the given value as JSON.
	 *
	 * @param  mixed  $value
	 * @return string
	 */
	protected function asJson($value): string{
		if(is_string($value) && QMStr::isJson($value)){
			if($value === "[]"){return $value;}
			if(QMStr::isJson($value)){
				return $value; // Don't double cast
			} // Don't double cast
		}
		return parent::asJson($value);
	}
	/**
	 * Decode the given JSON back into an array or object.
	 *
	 * @param  string  $value
	 * @param  bool  $asObject
	 * @return mixed
	 */
	public function fromJson($value, $asObject = false)
	{
		if(!$value){return null;}
		if(!is_string($value)){
			if(!$asObject && is_array($value)){
				return $value;
			} else {
				$value = json_encode($value);
			}
		}
		return json_decode($value, ! $asObject);
	}
	public function getReportTitleAttribute():string{
		return $this->getTitleAttribute();
	}
	/**
	 * @param static[] $array
	 * @param bool $unsetNulls
	 * @return QMCard[]
	 */
	public static function toCards(array $array, bool $unsetNulls = true): array{
		$cards = [];
		foreach($array as $item){
			$cards[] = $item->getCard();
		}
		if(!$unsetNulls){return $cards;}
		foreach($cards as $key => $card){
			$cards[$key] = $card->unsetNullAndEmptyStringFields();
		}
		return $cards;
	}
	public function getShowContentView(array $params = []): View{
		return BaseDataLabController::getShowView($this);
	}
	protected function getShowPageView(array $params = []): View{
		return BaseDataLabController::getShowView($this);
	}
	public function getAvatar(): string{
		return $this->getImage();
	}
	public function getShowFolderPath(): string{
		return static::getIndexPath() . "/" . $this->getSlug();
	}
	public function getShowButton(): QMButton{
		$b = new QMButton();
		$b->setUrl($this->getUrl());
		$b->setFontAwesome($this->getFontAwesome());
		$b->setImage($this->getIcon());
		$b->setTextAndTitle($this->getTitleAttribute());
		$b->setTooltip($this->getTooltip());
		return $b;
	}
	public function getShowContent(bool $inlineJs = false): string{
		$params = [];
		if($inlineJs){
			$params['inlineJs'] = true;
		}
		$v = $this->getShowContentView($params);
		return HtmlHelper::renderView($v);
	}
	public function getShowUrl(array $params = []): string{
		return qm_url($this->getShowFolderPath(), $params);
	}
	/**
	 * @param $id
	 * @return string
	 */
	public static function generateShowUrl($id): string{
		return static::generateShowUrlBySlug($id);
	}
	protected static function generateShowUrlBySlug(string $slug): string{
		$url = qm_url(static::getIndexPath() . "/$slug");
		return $url;
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getShowPageHtml(array $params = []): string{
		$this->setShowMeta();
		$v = $this->getShowPageView($params);
		return HtmlHelper::renderView($v);
	}
	/**
	 * @return string
	 */
	public function getShowPage(): string{
		return $this->getShowPageHtml();
	}
	public function getShowPublicIndexFilePath(): string{
		$path = static::getShowFolderPath() . "/index.html";
		return FileHelper::addPublicToPathIfNecessary($path);
	}
	public function getShowPublicJsPath(): string{
		$path = static::getShowFolderPath() . "/data.js";
		return FileHelper::addPublicToPathIfNecessary($path);
	}
	public function getShowStaticJsPath(): string{
		$path = static::getShowFolderPath() . "/data.js";
		return FileHelper::addStaticToPathIfNecessary($path);
	}
	/**
	 * @param $q
	 * @return string
	 * @throws NotFoundException
	 * @throws \App\Exceptions\UnauthorizedException
	 */
	public static function generateShowPage($q): string{
		$m = self::findByNameIdSynonymOrSlug($q);
		$m->validateCanRead(Auth::user());
		if(QMRequest::analyze()){
			$m->analyze("analyze param provided");
		}
		return $m->getShowPageHtml();
	}
	/**
	 * @param int|string $nameOrId
	 * @return static|null
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function findByNameIdOrSynonym($nameOrId){
		if(!is_numeric($nameOrId)){
			$nameOrId = static::fromSlug($nameOrId);
		}
		return static::findInMemoryOrDB($nameOrId);
	}
	public static function fromSlug(string $slug): string{
		return $slug; // Override in child
	}
	public static function formatQuery(string $q): string{
		return static::fromSlug($q);
	}
	public function saveShowHtml(){
		AppMode::setCrowdsourcingCures();
		$this->setShowMeta();
		FileHelper::saveToStatic($this->getShowPublicIndexFilePath(), $this->getShowPageHtml());
	}
	protected function setShowMeta(): void{
		MetaTag::set('title', $this->getTitleAttribute());
		MetaTag::set('image', $this->getImage());
		MetaTag::set('description', $this->getSubtitleAttribute());
		MetaTag::set('keywords', $this->getKeyWordString());
	}
	/**
	 * @throws InvalidUrlException
	 * @noinspection PhpUnused
	 */
	public function validateShowUrl(): void{
		$url = $this->getUrl();
		QMStr::validateUrl($url, __FUNCTION__);
	}
	public function getShowJs(): string{
		$data = $this->getShowJsData();
		return ShowJavaScriptFile::toJS($this->getShortClassName(), $data);
	}
	public function getShowJsData(): array{
		$data = $this->toNonNullArray();
		unset($data[BaseUpdatedAtProperty::NAME]);
		return $data;
	}
	/**
	 * @return array
	 */
	public function toNonNullArrayFast(): array{
		$arr = $this->attributes;
		$arr = QMArr::removeNulls($arr);
		if(!isset($arr['image'])){
			$arr['image'] = $this->getImage();
		}
		if(!isset($arr['avatar'])){
			$arr['avatar'] = $this->getAvatar();
		}
		if(!isset($arr['badge_text'])){
			$arr['badge_text'] = $this->getBadgeText();
		}
		if(!isset($arr['url'])){
			$arr['url'] = $this->getUrl();
		}
		if(!isset($arr['title'])){
			$arr['title'] = $this->getTitleAttribute();
		}
		if(!isset($arr['sorting_score'])){
			$arr['sorting_score'] = $this->getSortingScore();
		}
		if(!isset($arr['keywords'])){
			$arr['keywords'] = $this->getKeyWordString();
		}
		if(!isset($arr[Variable::FIELD_DESCRIPTION])){
			$arr[Variable::FIELD_DESCRIPTION] = $this->getSubtitleAttribute();
		}
		if(!isset($arr[VariableCategory::FIELD_FONT_AWESOME])){
			$arr[VariableCategory::FIELD_FONT_AWESOME] = $this->getFontAwesome();
		}
		return $arr;
	}
	public function getShowJsUrl(): string{
		return $this->getUrl() . "/data.js";
	}
	/** @noinspection PhpUnused */
	public function getShowJsTag(bool $inline): string{
		if($inline){
			$js = $this->getShowJs();
			return "
<script>
$js
</script>            
";
		} else{
			$url = $this->getShowJsUrl();
			return "
<script src=\"$url\"></script>
";
		}
	}
	public function getShowParams(array $params = []): array{
		$params['uv'] = $params['model'] = $this;
		if(isset($params['inlineJs'])){
			$params['js'] = $this->getShowJsTag(true);
		}
		return $params;
	}
	public function getKeyWords(): array{
		return [static::getClassNameTitlePlural()];
	}
	public function getKeyWordString(): string{
		try {
			$keywords = $this->getKeyWords();
			return QMStr::generateKeyWordString($keywords);
		} catch (\Throwable $e){
			$keywords = $this->getKeyWords();
			return QMStr::generateKeyWordString($keywords);
		}
	}
	public function getTopMenu(): QMMenu{
		return JournalMenu::instance();
	}
	public function getSideMenus(): array{
		$menus = [$this->getRelationshipsMenu()->setTitle("Related")];
		if($actions = $this->getActionsMenu()){
			$menus[] = $actions;
		}
		return $menus;
	}
	public function getActionsMenu(): ?QMMenu{ return null; }
	public function getFooterMenu(): QMMenu{ return FooterMenu::instance(); }
	public static function generateIndexHtml(): string{ return static::getIndexPageHtml(); }
	public static function getClassButton(): QMButton{
		$b = new QMButton();
		$b->setTextAndTitle(static::getClassNameTitle());
		$b->setTooltip(self::getClassDescription());
		$b->setUrl(self::getIndexUrl());
		$b->setFontAwesome(self::getClassFontAwesome());
		return $b;
	}
	protected static function getIndexPageView(): View{
		return view('chip-search-page', self::getViewParams());
	}
	public static function getIndexContentView(): View{
		return view('chip-search', self::getViewParams());
	}
	/**
	 * @param Collection $unsorted
	 * @return Collection
	 */
	public static function sortBySortingScore(Collection $unsorted): Collection{
		$sorted = $unsorted->sortByDesc(function($model){
			/** @var BaseModel $model */
			return $model->getSortingScore();
		});
		return $sorted;
	}
	/**
	 * @return array
	 */
	protected static function getViewParams(): array{
		return [
			'heading' => self::getClassNameTitle(),
			'searchId' => self::getTableName(),
			'buttons' => static::getIndexModels(),
		];
	}
	/**
	 * @return string
	 */
	public static function getIndexFilePath(): string{
		$path = 'public/' . static::getIndexPath() . "/index.html";
		return $path;
	}
	/**
	 * @return string
	 */
	public static function getJsFilePath(): string{
		return 'public/' . static::getIndexPath() . ".js";
	}
	/**
	 * @return QMButton[]
	 */
	public static function getIndexButtons(): array{
		$models = static::getIndexModels();
		return static::toButtons($models);
	}
	public static function cardSearch(): string{
		return view('alpine-small-card-search', [
			'table' => static::getTableName(),
			'placeholder' => "Search for a " . strtolower(static::getClassNameTitle()) . "...",
		]);
	}
	public static function alpineChipSearchView(): View{
		return view('chip-search-page', [
			'table' => static::getTableName(),
			'placeholder' => "Search for a " . strtolower(static::getClassNameTitle()) . "...",
		]);
	}
	public static function getIndexButton(): QMButton{
		return static::getClassButton();
	}
	public static function getIndexTitle(): string{
		return static::getClassNameTitlePlural();
	}
	/** @noinspection PhpUnused */
	public static function getIndexDescription(): string{
		return static::getClassDescription();
	}
	public static function getIndexImage(): string{
		return static::getClassImage();
	}
	/** @noinspection PhpUnused */
	public static function chipSearchHtml(): string{
		return HtmlHelper::renderView(static::alpineChipSearchView());
	}
	/** @noinspection PhpUnused */
	public static function indexChipsHtml(): string{
		return HtmlHelper::renderView(static::indexChipsView());
	}
	public static function indexChipsView(): View{
		return view('chips', [
			'searchId' => static::getSlugifiedClassName(),
			'buttons' => static::getIndexModels(),
		]);
	}
	public static function indexSelectQB(): Builder {
		$qb = static::query()->select(static::getIndexColumns());
		return $qb;
	}
	public static function getClassKeywords(): array{
		return [static::getClassNameTitle(), static::getIndexTitle()];
	}
	/**
	 * @return string
	 */
	public static function getIndexPageHtml(): string{
		if($mem = static::getFromClassMemory(__FUNCTION__)){
			return $mem;
		}
		self::setIndexMeta();
		$html = HtmlHelper::renderView(static::getIndexPageView());
		//if(\App\Utils\EnvOverride::isLocal()){FileHelper::writeHtmlFile(static::getIndexFilePath(), $html);}
		static::setInClassMemory(__FUNCTION__, $html);
		return $html;
	}
	public static function saveIndexHtml(string $path = null){
		if(!$path){
			$path = self::getIndexFilePath();
		}
		$html = static::getIndexPageHtml();
		FileHelper::saveToStatic($path, $html);
		self::saveIndexJS();
	}
	/** @noinspection PhpUnused */
	public static function saveIndexJSNecessary(){
		if(!FileHelper::fileExists(self::getJsFilePath())){
			self::saveIndexJS();
		}
	}
	private static function saveIndexJS(){
		$js = static::generateJsSearchScript();
		FileHelper::saveToStatic(self::getJsFilePath(), $js);
	}
	public static function generateIndexFiles(){
		//VariableCategory::updateCountFields();
		foreach(VariableCategory::all() as $category){
			$category->saveShowHtml();
		}
		AppMode::setCrowdsourcingCures();
		Variable::saveIndexHtml();
		Variable::saveIndexHtml("public/index.html");
	}
	private static function generateJsSearchScript(): string{
		$tableName = static::getTableName();
		$json = static::getIndexSearchJson();
		return "
    function load_$tableName() {
        return {
            search: \"\",
            myForData: Object.values(qm_$tableName).sort(function(a, b) {
                return parseFloat(b.sorting_score) - parseFloat(a.sorting_score);
            }),
            get filtered_$tableName() {
                if (this.search === \"\") {
                    return this.myForData;
                }
                var results  = this.myForData.filter((item) => {
                    return item.keywords
                        .toLowerCase()
                        .includes(this.search.toLowerCase());
                });
                if(results.length < 10){
                    var box = document.getElementById('not-found-box');
                    if(box){
                        box.style.display = 'block';
                    }
                }
                return results;
            },
        };
    }
var qm_$tableName = $json;
if(typeof window !== \"undefined\"){
    window.qm_$tableName = qm_$tableName
} else {
    global.qm_$tableName = qm_$tableName
}";
	}
	/**
	 * @return array
	 */
	private static function getIndexData(): array{
		$all = [];
		$models = static::getIndexModels();
		foreach($models as $model){
			$name = $model->getNameAttribute();
			$one = $model->toNonNullArray();
			$one['avatar'] = $model->getAvatar();
			$one['title'] = $model->getTitleAttribute();
			$one['subtitle'] = $model->getSubTitle();
			$one['tooltip'] = $model->getTooltip();
			$one['badge_text'] = $model->getBadgeText();
			$one['url'] = $model->getUrl();
			$one['keywords'] = $model->getKeyWordString();
			$one['sorting_score'] = $model->getSortingScore();
			$all[$name] = $one;
		}
		return $all;
	}
	private static function getIndexSearchJson(): string{
		return QMStr::prettyJsonEncode(static::getIndexData());
	}
	public static function getIndexColumns(): array{
		$columns = static::getColumns();
		if(count($columns) < 10){
			return $columns;
		}
		$columns = array_merge(static::getNameColumns(), static::getIdColumns(), static::getImageColumns(),
			static::getCountColumns());
		if(static::hasColumn('is_public')){
			$columns[] = 'is_public';
		}
		return $columns;
	}
	/**
	 * @return string
	 */
	public static function getIdColumn(): string{ return static::FIELD_ID; }
	public static function getNameColumns(): array{
		$all = static::getColumns();
		return collect($all)->filter(function($field){
			return strpos($field, 'name_') !== false || $field === "name";
		})->all();
	}
	public static function getImageColumns(): array{
		$all = static::getColumns();
		return collect($all)->filter(function($field){
			return strpos($field, 'image') !== false || strpos($field, 'icon') !== false ||
				strpos($field, 'avatar') !== false;
		})->all();
	}
	public function getTooltip(): string{
		return $this->getSubtitleAttribute();
	}
	public function getBadgeText(): ?string{
		return $this->getSortingScore();
	}
	public static function indexUrl(): string{
		return qm_url(static::getPluralizedSlugifiedClassName());
	}
	protected static function setIndexMeta(): void{
		MetaTag::set('title', static::getIndexTitle());
		MetaTag::set('image', static::getClassImage());
		MetaTag::set('description', static::getClassDescription());
		MetaTag::set('keywords', implode(",", static::getIndexKeywords()));
	}
	public static function getIndexKeywords(): array{
		return [static::getIndexTitle()];
	}
	/**
	 * @return Collection|\App\Models\BaseModel[]
	 */
	public static function getIndexModels(): Collection{
		if($models = static::getFromClassMemory(__METHOD__)){
			return $models;
		}
		$qb = static::indexSelectQB();
		$unsorted = $qb->get();
		QMRequest::validateCanRead($unsorted);
		$sorted = self::sortBySortingScore($unsorted);
		return static::setInClassMemory(__METHOD__, $sorted);
	}
	/**
	 * @return string
	 */
	public function getUniqueIdentifier(): string{
		return $this->getUniqueIndexIdsSlug();
	}
	/**
	 * @return string
	 * We need to use getUniqueIndexIdsSlug instead of getId so we can set things false that don't exist in DB to
	 * avoid redundant DB requests
	 * @noinspection PhpConditionAlreadyCheckedInspection
	 */
	public function getUniqueIndexIdsSlug(): string{
		$columns = static::getUniqueIndexColumns(); // Don't use self::
		$tableName = $this->getTableName();
		$tableName = str_replace(Writable::TABLE_PREFIXES_TO_STRIP, "", $tableName);
		$tableName = str_replace("_", "-", $tableName);
		if(!$columns || $columns === ['id'] || $columns === ['ID']){
			$id = $this->getId();
			if($id === null || $id === ""){le("No id value for unique index" . get_class($this), $this);}
			return $tableName . "-$id";
		}
		$pieces = [];
		$useDashesInsteadOfQueryString =
			true; // Might want to switch to make this more useful since id's are worthless for SEO purposes anyway
		foreach($columns as $column){
			$value = $this->getAttribute($column);
			if(empty($value)){
				$value = $this->getAttribute($column);
				le("No $column value for unique index! " . QMStr::prettyJsonEncode($this));
			}
			if($useDashesInsteadOfQueryString){
				$str = str_replace('_id', "", $column);
				$str = str_replace('_', "-", $str);
				$pieces[] = $str . "-" . $value;
			} else{
				$pieces[] = $column . "=" . $value;
			}
		}
		if($useDashesInsteadOfQueryString){
			$slug = implode("-", $pieces);
		} else{
			$slug = implode("&", $pieces);
		}
		return $tableName . "-" . $slug;
	}
	public function getSlug(): string{
		return $this->getId();
	}
	public static function nameToSlug(string $name): string{
		return QMStr::slugify($name);
	}
	public static function slugToName(string $name): string{
		return str_replace("_", " ", $name);
	}
	public function getSlugWithNames(): string{
		return static::nameToSlug($this->getNameAttribute());
	}
	/**
	 * @return string
	 * Need getPostNameSlug because we have to override and use study UniqueIndexIdsSlug in QMCorrelations to avoid
	 *     duplicate posts
	 */
	public function getSlugWithPluralClassAndId(): string{
		return static::getPluralizedSlugifiedClassName() . "-" . $this->getId();
	}
	/**
	 * @param string|null $html
	 * @return string
	 */
	public function getOrAddSocialSharingButtons(string $html = ''): string{
		$imagePreview = $this->getImage();
		$url = $this->getUrl();
		$shortTitle = $this->getTitleAttribute();
		$briefDescription = $this->getSubtitleAttribute();
		$html =
			HtmlHelper::getSocialSharingButtonsHtmlNonEmail($url, $shortTitle, $imagePreview, $briefDescription, $html);
		return $html;
	}
	public function getSharingButtons(): array{
		return SharingButton::getSharingButtons($this->getSharingUrl(), $this->getTitleAttribute(), true);
	}
	/**
	 * @return string
	 */
	public function getSocialMetaHtml(): string{
		return MetaHtml::generateSocialMetaHtml($this->getTitleAttribute(), $this->getSubtitleAttribute(), $this->getImage(),
			$this->getSharingUrl());
	}
	public function getSharingUrl(): string{
		return $this->getUrl();
	}
	/**
	 * @return ReflectionClass
	 */
	protected function getReflectionClass(): ReflectionClass{
		$reflector = new ReflectionClass($this);
		return $reflector;
	}
	/**
	 * @return \ReflectionMethod[]
	 */
	protected function getMethods(): array{
		$reflector = $this->getReflectionClass();
		$methods = $reflector->getMethods();
		return $methods;
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getAllRelationshipButtons(): array{
		$folder = base_path(RelationshipButton::BUTTONS_FOLDER . $this->getShortClassName());
		/** @var RelationshipButton[] $classes */
		$classes = FileHelper::getClassesInFolder($folder);
		$relationships = [];
		/** @var RelationshipButton|BelongsToRelationshipButton $class */
		foreach($classes as $class){
			try {
				try {
					/** @var RelationshipButton $relationshipButton */
					$relationshipButton = new $class($this);
				} catch (\Throwable $e) {
					if($e instanceof NoIdException){
						throw $e;
					} // Don't need to debug this
					le($e);
				}
				$url = $relationshipButton->link;
				if(!$url){
					$this->logDebug("Skipping $relationshipButton->title because there's no url");
				}
			} catch (NoIdException $e) {
				QMLog::debug(__METHOD__.": ".$e->getMessage()); // We don't have cause_unit_id sometimes to create the link for the button so we skip
				continue;
			}
			$relationships[] = $relationshipButton;
		}
		return $relationships;
	}
	/** @noinspection PhpUnused */
	public function getDataLabRelationshipCountBoxesHtml(): string{
		return $this->getDataLabRelationshipMenu()->getCountBoxesHtml();
	}
	/** @noinspection PhpUnused */
	public function getDataLabRelationshipCountCardsHtml(): string{
		return $this->getDataLabRelationshipMenu()->getMaterialStatCards();
	}
	public function getDataLabRelationshipMenu(): DataLabRelationshipMenu{
		return new DataLabRelationshipMenu($this);
	}
	/**
	 * @return string[]
	 */
	public function getRelationshipNames(): array{
		return array_keys($this->getRelationshipNamesAndTypes());
	}
	/**
	 * @return string[]
	 */
	public function getRelationshipNamesAndTypes(): array{
		$methods = $this->getMethods();
		$relationships = [];
		foreach($methods as $method){
			$returnType = $method->getReturnType();
			if($returnType){
				$types = ['HasOne', 'HasMany', 'BelongsTo', 'BelongsToMany', 'MorphToMany', 'MorphTo'];
				if(!method_exists($returnType, 'getName')){
					continue;
				}
                try {
                    $relationshipType = class_basename($returnType->getName());
                } catch (\Throwable $e) {
                    ConsoleLog::info("Error getting relationship type for $method->name because ".$e->getMessage()." for returnType ".QMLog::print($returnType));
                    continue;
                }
				if(in_array($relationshipType, $types)){
					$relationships[$method->name] = $relationshipType;
				}
			}
		}
		return $relationships;
	}
	public function getRelationshipLabels(): string{
		return $this->getDataLabRelationshipMenu()->getLabelsHtml();
	}
	/** @noinspection PhpUnused */
	public function getRelationshipMDLChips(): string{
		return $this->getDataLabRelationshipMenu()->getMDLChipsHtml();
	}
	/** @noinspection PhpUnused */
	public function getRelationshipListWithCountBadgesHtml(): string{
		return $this->getDataLabRelationshipMenu()->getListWithCountBadgesHtml();
	}
	public function getAvatarBadgesRelationshipListBoxHtml(): string{
		$m = $this->getDataLabRelationshipMenu();
		return $m->getAvatarBadgesListBoxHtml();
	}
	public function getRelationshipButton(string $class): RelationshipButton{
		$all = $this->getAllRelationshipButtons();
		foreach($all as $button){
			if(get_class($button) === $class){
				return $button;
			}
		}
		le("$class not found");
		throw new \LogicException();
	}
	/**
	 * @return RelationshipButton[]
	 */
	public static function generateRelationshipButtons(): array{
		$model = new static();
		$buttons = [];
		$methods = $model->getRelationshipNames();
		foreach($methods as $method){
			$rel = $model->$method();
			$rel->methodName = $method;
			if($rel instanceof HasMany){
				$b = new HasManyRelationshipButton($method, $rel);
			} elseif($rel instanceof HasOne){
				$b = new HasOneRelationshipButton($method, $rel);
			} elseif($rel instanceof BelongsTo){
				$b = new BelongsToRelationshipButton($method, $rel);
			} else{
				le("please implement for " . get_class($rel));
			}
			$b->saveHardCodedModel();
			$buttons[] = $b;
		}
		return $buttons;
	}
	public function getRelationshipsMenu(): QMMenu{
		$m = new RelationshipsMenu($this);
		return $m;
	}
	public static function getIdColumns(): array{
		$all = static::getColumns();
		return collect($all)->filter(function($field){
			return strpos($field, '_id') !== false || $field === "id" || $field === "ID";
		})->all();
	}
	public static function getCountColumns(): array{
		$all = static::getColumns();
		return collect($all)->filter(function($field){
			return strpos($field, HasManyAlias::$number_of_) === 0;
		})->all();
	}
	public static function updateCountFields(){
		$props = (new static())->getCountProperties();
		foreach($props as $prop){
			$prop->updateAll();
		}
	}
	/**
	 * @return IsNumberOfRelated[]
	 */
	public function getCountProperties(): array{
		$columns = static::getCountColumns();
		$props = [];
		foreach($columns as $column){
			$props[$column] = $this->getPropertyModel($column);
		}
		return $props;
	}
	public function updateNumberOfRelated(): array{
		$props = $this->getNumberOfRelatedProperties();
		$results = [];
		foreach($props as $prop){
			$results[$prop->name] = $prop->calculate($this->l());
		}
		return $results;
	}
	/**
	 * @return IsNumberOfRelated[]|BaseProperty[]
	 */
	public function getNumberOfRelatedProperties(): array{
		return $this->getCountProperties();
	}
	public static function getIndexPath(): string{
		return static::getPluralizedSlugifiedClassName();
	}
	public function getHtmlPage(bool $inlineJs = false): string{
		$body = $this->getShowContent($inlineJs);
		$view = view('html-layout', ['html' => $body, 'title' => $this->getTitleAttribute()]);
		$html = HtmlHelper::renderView($view);
		$html = str_replace("http://localhost", Env::getAppUrl(), $html);
		return $html;
	}
	public function getEmailBody(): string{
		return $this->getHtmlPage();
	}
	/**
	 * @param string $attr
	 * @return bool
	 */
	public function hasAttribute(string $attr): bool{
		$fields = static::getColumns();
		return in_array($attr, $fields);
	}

    /**
     * @param array $array
     * @return Collection|static[]
     */
    public static function instantiateArray(array $array): Collection {
        $models = [];
        foreach ($array as $item){
            $m = new static();
            $m->fill($item);
//            foreach ($item as $key => $value){
//                $snake_key = snake($key);
//                if($m->hasAttribute($snake_key)){
//                    $m->$snake_key = $value;
//                }
//            }
            $models[] = $m;
        }
        return collect($models);
    }
    protected function serializeDate(DateTimeInterface $date): string
    {
        $res = $date->format('Y-m-d H:i:s');
        //$res = Carbon::instance($date)->toISOString(false);
        return $res;
    }


    public function createSaveTest(): string
    {
        return $this->createUnitTest(__FUNCTION__, "
                    \$model = new " . static::class . "();
                    \$model->populate(" . QMLog::var_export($this->toArray(), true) . ");
                    \$model->save();
                ");
    }

    /**
     * @param string|null $col
     * @return string
     */
    private function createMigrationForColumn(?string $col): string
    {
        $prop = $this->getPropertyModel($col);
        $p = $prop->getDBColumn();
        $mig = $p->createMigration();
        return $mig;
    }

    /**
     * @param array $input
     * @return array
     */
    private function convertObjectsOrArraysToIds(array $input): array
    {
		if(isset($input[0])){
			foreach($input as $key => $value){
				$input[$key] = $this->convertObjectsOrArraysToIds($value);
			}
			return $input;
		}
        foreach ($input as $key => $value) {
            if(static::hasColumn($key)){
                continue;
            }
			if($key === 'variable_name'){
				$v = Variable::findOrCreateByName($value, $input);
				unset($input[$key]);
				$input['variable_id'] = $v->id;
				continue;
			}
	        if($key === 'unit_name'){
		        $unit = QMUnit::findByNameOrAbbreviatedName($value);
		        unset($input[$key]);
		        $input['unit_id'] = $unit->id;
		        continue;
	        }
	        if($key === 'variable_category_name'){
		        $cat = VariableCategory::findByNameIdOrSynonym($value);
		        unset($input[$key]);
		        $input['variable_category_id'] = $cat->id;
				continue;    
	        }
            if (is_array($value)) {
                $id = BaseIdProperty::pluck($value);
                $newKey = $key . '_id';
                $input[$newKey] = $id;
                unset($input[$key]);
            }
        }
        return $input;
    }

    /**
     * @return string[]
     */
    public function getFillable(): array{
	    $fillable = $this->fillable;
	    if ($fillable === ['*']) {
			$this->fillable = [];
            $all = static::getColumns();
            $auto = $this->getAutoGeneratedColumns();
            $guarded = $this->getGuarded();
            $guarded = array_merge($guarded, $auto);
            if(!$guarded){
                return $all;
            }
            foreach ($all as $column) {
                if (!in_array($column, $guarded)) {
                    $this->fillable[] = $column;
                }
            }
        }
        return parent::getFillable();
    }
    public function getFillableRules(): array{
        $rules = [];
        $fillable = $this->getFillable();
        foreach ($this->getRules() as $field => $rule){
            if(in_array($field, $fillable)){
                $rules[$field] = $rule;
            }
        }
        return $rules;
    }
    public static function getDeprecatedAttributes(): array{
        if(isset(static::$deprecatedAttributes[static::TABLE])){
            return static::$deprecatedAttributes[static::TABLE];
        }
        $props = static::getPropertyModelsStatic();
        $deprecated = [];
        foreach ($props as $prop){
            if($prop->deprecated){
                $deprecated[] = $prop->name;
            }
        }
        return static::$deprecatedAttributes[static::TABLE] = $deprecated;
    }
    public static function getPropertyModelsStatic(): array{
        if(isset(static::$propertyModelsCache[static::TABLE])){
            return static::$propertyModelsCache[static::TABLE];
        }
        return static::$propertyModelsCache[static::TABLE] = (new static)->getPropertyModels();
    }
    public static function removeDeprecatedAttributesFromArray(array $data): array
    {
        $deprecated = static::getDeprecatedAttributes();
        foreach ($deprecated as $key) {
            unset($data[$key]);
        }
        return $data;
    }

    public function getTitleAttributeWithUser(): string
    {
        if(!method_exists($this, 'getUser') || !isset($this->attributes['user_id'])){
            return $this->getTitleAttribute();
        }
        return $this->getTitleAttribute()." for ".$this->getUser()->getTitleAttribute();
    }
    public static function updatePrimaryKeyAutoIncrementSequence(){
        Writable::updatePrimaryKeySequence(new static());
    }
	/**
	 * @param static[]|Collection $baseModels
	 * @return array
	 */
	public static function toDBModels($baseModels): array{
		// ConsoleLog::info(static::class." ".__FUNCTION__." "); // Uncomment for segfault debugging
		if($baseModels instanceof BaseModel){$baseModels = [$baseModels];}
		$dbms = [];
		foreach($baseModels as $l){
			$l->addToMemory();
			$dbms[] = $l->getDBModel();
		}
		return $dbms;
	}
	public static function logMetabaseLink(){
		$b = new MetabaseButton(static::METABASE_PATH, static::getClassNameTitle()." Metabase Link");
		$b->logLink();
	}
}
