<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties;
use App\CodeGenerators\TVarDumper;
use App\Exceptions\BadRequestException;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidStringAttributeException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\RedundantVariableParameterException;
use App\Exceptions\UnauthorizedException;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Files\PHP\PhpClassFile;
use App\Http\Controllers\Admin\FixInvalidRecordsController;
use App\Http\Parameters\SortParam;
use App\Logging\QMLog;
use App\Logging\QMLogLevel;
use App\Models\BaseModel;
use App\Models\TrackingReminder;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserVariable;
use App\Models\VariableCategory;
use App\Astral\Lenses\InvalidRecordsLens;
use App\Astral\BaseAstralAstralResource;
use App\Properties\User\UserStatusProperty;
use App\Providers\DBQueryLogServiceProvider;
use App\Slim\Model\DBModel;
use App\Slim\QMSlim;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\DBColumn;
use App\Storage\DB\QMDB;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Storage\QueryBuilderHelper;
use App\Traits\HasClassName;
use App\Traits\HasName;
use App\Traits\LoggerTrait;
use App\Traits\PropertyTraits\IsTemporal;
use App\Traits\QMHasAttributes;
use App\Types\MySQLTypes;
use App\Types\PhpTypes;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use App\Utils\AppMode;
use App\Utils\Compare;
use App\Utils\UrlHelper;
use BadMethodCallException;
use Carbon\CarbonInterface;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Form;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Jupitern\Table\Table;
use Jupitern\Table\TableColumn;
use Krlove\CodeGenerator\Model\PropertyModel;
use Krlove\CodeGenerator\Model\Traits\AccessModifierTrait;
use Krlove\CodeGenerator\Model\Traits\DocBlockTrait;
use App\Fields\Boolean;
use App\Fields\Field;
use App\Fields\ID;
use App\Fields\Number;
use App\Fields\Status;
use App\Fields\Text;
use App\Http\Requests\AstralRequest;
use OpenApi\Annotations\ExternalDocumentation;
use OpenApi\Annotations\Items;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use OpenApi\Annotations\Xml;
use Throwable;
abstract class BaseProperty extends PropertyModel
{
    use HasClassName, LoggerTrait, AccessModifierTrait, DocBlockTrait;
    public const FORMAT_DOUBLE = 'double';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_NUMBER = 'number';
    public const TYPE_ENUM = 'enum';
    public const TYPE_DATETIME = 'datetime';
    public const SYNONYMS = [];
    public const NAME_SYNONYMS = [];
    public const NAME = null;
    protected $isPublic = false;
    protected $isUrl = false;
    protected $isUnixTime = false;
    public $showOnCreating = false;
    public $showOnDetail = true;
    public $showOnIndex = false;
    public $showOnUpdating = false;
    public $creationRules = "";
    public $updateRules = "";
    protected $isImageUrl = false;
    protected $isHtml = false;
    /**
     * @var int
     * The smaller the number, the higher the placement in a form
     * Make sure it's 2 digits i.e. "01" as opposed to "1"
     */
    public $order = "99";
    protected $shouldNotContain = []; // Add to model as ['field_to_check' => ['string to exclude']; See \App\Models\WpPost for example
    protected $requiredStrings = [];  // Add to model as ['field_to_check' => ['string to exclude']; See \App\Models\WpPost for example
    public $parentClass;
    protected static $validationDisabledFor = [];
    /**
     * $ref See http://json-schema.org/latest/json-schema-core.html#rfc.section.7
     * @var string
     */
    public $ref;
    /**
     * Can be used to decorate a user interface with information about the data produced by this user interface. preferably be short.
     * @var string
     */
    public $title;
    /**
     * A description will provide explanation about the purpose of the instance described by this schema.
     * @var string
     */
    public $description;
    /**
     * An object instance is valid against "maxProperties" if its number of properties is less than, or equal to, the value of this property.
     * @var integer
     */
    public $maxProperties;
    /**
     * An object instance is valid against "minProperties" if its number of properties is greater than, or equal to, the value of this property.
     * @var integer
     */
    public $minProperties;
    /**
     * If required is true, a DB record cannot be created unless this value is not null
     * @var bool
     */
    public $required;
    /**
     * If nullable is false, it's DB value cannot be changed to null
     * @var bool
     */
    public $canBeChangedToNull = false;
    /**
     * @var Property[]
     */
    public $properties;
    /**
     * The type of the schema/property. The value MUST be one of "string", "number", "integer", "boolean", "array" or "object".
     * @var string
     */
    public $type;
    /**
     * The extending format for the previously mentioned type. See Data Type Formats for further details.
     * @var string
     */
    public $format;
    /**
     * Required if type is "array". Describes the type of items in the array.
     * @var Items
     */
    public $items;
    /**
     * @var string Determines the format of the array if type array is used. Possible values are: csv - comma separated values foo,bar. ssv - space separated values foo bar. tsv - tab separated values foo\tbar. pipes - pipe separated values foo|bar. multi - corresponds to multiple parameter instances instead of multiple values for a single instance foo=bar&foo=baz. This is valid only for parameters in "query" or "formData". Default value is csv.
     * @noinspection SpellCheckingInspection
     */
    public $collectionFormat;
    /**
     * Sets a default value to the parameter. The type of the value depends on the defined type. See http://json-schema.org/latest/json-schema-validation.html#anchor101.
     * @var mixed
     */
    public $default = \OpenApi\Generator::UNDEFINED;
    /**
     * this keyword validates only if the instance is less than or exactly equal to "maximum".
     * See http://json-schema.org/latest/json-schema-validation.html#anchor17.
     * @var float
     */
    public $maximum;
    /**
     * If the instance is a number, then the instance is valid only if it has a value strictly less than (not equal to) "exclusiveMaximum".
     * See http://json-schema.org/latest/json-schema-validation.html#anchor17.
     * @var boolean
     */
    public $exclusiveMaximum;
    /**
     * If the instance is a number, then this keyword validates only if the instance is greater than or exactly equal to "minimum".
     * See http://json-schema.org/latest/json-schema-validation.html#anchor21.
     * @var float
     */
    public $minimum;
    /**
     * If the instance is a number, then the instance is valid only if it has a value strictly greater than (not equal to) "exclusiveMinimum".
     * See http://json-schema.org/latest/json-schema-validation.html#anchor21.
     * @var boolean
     */
    public $exclusiveMinimum;
    /**
     * A string instance is valid against this keyword if its length is less than, or equal to, the value of this keyword.
     * @var integer
     */
    public $maxLength;
    /**
     * A string instance is valid against this keyword if its length is greater than, or equal to, the value of this keyword.
     * @var integer
     */
    public $minLength;
    /**
     * A string instance is considered valid if the regular expression matches the instance successfully.
     * @var string
     */
    public $pattern;
    /**
     * An array instance is valid against "maxItems" if its size is less than, or equal to, the value of this keyword.
     * @var integer
     */
    public $maxItems;
    /**
     * An array instance is valid against "minItems" if its size is greater than, or equal to, the value of this keyword.
     * @var integer
     */
    public $minItems;
    /**
     * If it has boolean value true, the instance validates successfully if all of its elements are unique.
     * @var boolean
     */
    public $uniqueItems;
    /**
     * An instance validates successfully if its value is equal to one of the elements in this keyword's array value.
            Elements in the array might be of any type, including null.
     * @var array
     */
    public $enum;
    /**
     * A numeric instance is valid against "multipleOf" if the result of the division of the instance by this property's value is an integer.
     * @var number
     */
    public $multipleOf;
    /**
     * Adds support for polymorphism. The discriminator is the schema property name that is used to differentiate between other schemas that inherit this schema. The property name used MUST be defined at this schema and it MUST be in the required property list. When used, the value MUST be the name of this schema or any schema that inherits it.
     * @var string
     */
    public $discriminator;
    /**
     * Relevant only for Schema "properties" definitions. Declares the property as "read only". This means that it MAY be sent as part of a response but MUST NOT be sent as part of the request. Properties marked as readOnly being true SHOULD NOT be in the required list of the defined schema. Default value is false.
     * @var boolean
     */
    public $readOnly;
    /**
     * This MAY be used only on properties schemas. It has no effect on root schemas. Adds Additional metadata to describe the XML representation format of this property.
     * @var Xml
     */
    public $xml;
    /**
     * Additional external documentation for this schema.
     * @var ExternalDocumentation
     */
    public $externalDocs;
    /**
     * A free-form property to include a an example of an instance for this schema.
     * @var mixed
     */
    public $example;
    /**
     * An instance validates successfully against this property if it validates successfully against all schemas defined by this property's value.
     * @var Schema[]
     */
    public $allOf;
    /**
     * http://json-schema.org/latest/json-schema-validation.html#anchor64
     * @var bool|object
     */
    public $additionalProperties;
    /** @var string */
    protected $parentModel;
    public $dbInput;
    public $dbType;
    public $fieldType;
    public $fontAwesome;
    public $foreignKeyText;
    public $htmlInput;
    public $htmlType;
    public $htmlValues;
    public $image;
    public $inForm;
    public $inIndex;
    public $inView;
    public $isFillable;
    public $isOrderable;
    public $isPrimary;
    public $isSearchable;
    protected $migrationText;
    public $name;
    public $rules;
    public $table;
    public $validations;
    public $phpType;
    public bool $deprecated = false;
    public $autoIncrement = false;
    public $importance = 0;
    protected $originalValue;
	protected $unsigned;
	protected $blackListedValues = ["null"];
	public $shouldNotEqual = [];
	/** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(BaseModel $parentModel = null){
        if($parentModel){$this->parentModel = $parentModel;}
    }
    public static $_types = [
        'description' => 'string',
        'required' => '[string]',
        'format' => 'string',
        'collectionFormat' => ['csv', 'ssv', 'tsv', 'pipes', 'multi'],
        'maximum' => 'number',
        'exclusiveMaximum' => 'boolean',
        'minimum' => 'number',
        'exclusiveMinimum' => 'boolean',
        'maxLength' => 'integer',
        'minLength' => 'integer',
        'pattern' => 'string',
        'maxItems' => 'integer',
        'minItems' => 'integer',
        'uniqueItems' => 'boolean',
        'multipleOf' => 'integer',
    ];
    public static $_nested = [
        'OpenApi\Annotations\Items' => 'items',
        'OpenApi\Annotations\Property' => ['properties', 'property'],
        'OpenApi\Annotations\ExternalDocumentation' => 'externalDocs',
        'OpenApi\Annotations\Xml' => 'xml'
    ];
    /**
     * @return QMQB
     * Faster than Eloquent Builder because it doesn't fire model events and cast and stuff
     * Good for plucking id's
     */
    public static function qmqb(): QMQB {
        $property = new static();
        $model = $property->getParentModel();
        $qb = ReadonlyDB::getBuilderByTable($model->getTable()); // Don't use Eloquent because it's too slow
        QueryBuilderHelper::restrictQueryBasedOnPermissions($qb);
        return $qb;
    }
    public static function camelizedName():string{
        return QMStr::camelize(static::NAME);
    }
    /**
     * @return QMQB
     */
    public static function whereNullQMQB(): QMQB{
        $qb = static::qmqb();
        $qb->whereNull(static::NAME);
        return $qb;
    }
    /**
     * @param array $with
     * @return BaseModel|Builder
     */
    public static function whereNull(array $with = []): Builder{
        $qb = static::query($with);
        $qb->whereNull(static::NAME);
        return $qb;
    }
    /**
     * @param $value
     * @return bool|int
     */
    public static function updateWhereNull($value){
        $name = static::NAME;
        $updated = static::whereNull()
            ->update([$name => $value]);
        if($updated){
            $class = static::getClassNameTitle();
            QMLog::infoWithoutContext("Set $name to $value where it was null for $updated $class. ");
        }
        return $updated;
    }
    public static function query(array $with = []):Builder{
        $property = new static();
        $parent = $property->getParentModel();
        $qb = $parent->query();
        if($with){
            $qb->with($with);
        }
        return $qb;
    }
    /**
     * @param string|null $title
     * @return Collection|array
     */
    public static function logNulls(string $title = null){
        if(!$title){$title = "Have Null ".static::NAME;}
        $before = static::whereNull()->get();
        QMLog::info($before->count()." records $title");
        QMLog::table($before, $title);
        return $before;
    }
    /**
     * @return QMQB
     */
    public static function whereInvalidQMQB(): QMQB{
        throw new \LogicException("Please implement ".static::class."::".__FUNCTION__);
    }
    /**
     * @return Builder
     * @noinspection PhpInconsistentReturnPointsInspection
     */
    public static function whereInvalid(): Builder {
        le("Please implement ".static::class."::".__FUNCTION__);
    }
    public static function getSynonyms(): array {
	    $synonyms = static::SYNONYMS;
	    array_unshift($synonyms, static::NAME); // Make sure NAME's first
	    return array_unique($synonyms);
    }
    /**
     * @param array $validationDisabledFor
     */
    public static function setValidationDisabledFor(array $validationDisabledFor): void{
        self::$validationDisabledFor = $validationDisabledFor;
    }
    public static function replaceColumnStringsWithConstants(){
        BaseModel::replaceColumnStringsWithConstants(self::getBasePropertiesFolder());
    }
    public static function addIsTemporalTraits(){
        self::addTraitWhereContains(IsTemporal::class, "dbInput = 'datetime");
    }
    /**
     * @param string $traitClass
     * @param string $needle
     */
    public static function addTraitWhereContains(string $traitClass, string $needle){
        $path = self::getBasePropertiesFolder();
        //$withoutSlash = StringHelper::removeFirstCharacter($traitClass);
        $files = FileFinder::getFilesContaining($path, $needle, true);
        \App\Logging\ConsoleLog::info(count($files)." files containing $needle in $path...");
        foreach($files as $file){
            FileHelper::addTrait($traitClass, $file);
        }
    }
	/**
	 * @param string $interfaceClass
	 * @param string $needle
	 */
	public static function addImplementsWhereContains(string $interfaceClass, string $needle){
		$path = self::getFolder();
		//$withoutSlash = StringHelper::removeFirstCharacter($traitClass);
		$files = FileFinder::getFilesContaining($path, $needle, true);
		\App\Logging\ConsoleLog::info(count($files)." files containing $needle in $path...");
		foreach($files as $file){
			$file = new PhpClassFile($file);
			$file->addImplement($interfaceClass);
		}
	}
	/**
	 * @param string $traitClass
	 * @param string $needle
	 */
	public static function removeTrait(string $traitClass, string $needle){
		$path = self::getBasePropertiesFolder();
		//$withoutSlash = StringHelper::removeFirstCharacter($traitClass);
		$files = FileFinder::getFilesContaining($path, $needle, true);
		\App\Logging\ConsoleLog::info(count($files)." files containing $needle in $path...");
		foreach($files as $file){
			$file = new PhpClassFile($file);
			$file->replace("use \$traitClass;", "");
			$short = QMStr::toShortClassName($traitClass);
			$file->replace("use \$short;", "");
		}
	}
    public static function getAll(): array{
        $classes = static::getBaseClasses();
        $all = [];
        foreach($classes as $class){
            $prop = new $class();
            $all[] = $prop;
        }
        return $all;
    }
    private static function getBaseClasses(): array{
        $folder = self::getBasePropertiesFolder();
        return FileHelper::getClassesInFolder($folder);
    }
    /**
     * @return string
     */
    private static function getBasePropertiesFolder(): string{
        $folder = static::getFolder()."/Base";
        return $folder;
    }
	/**
     * @return BaseAstralAstralResource
     */
    public function getAstralResource(): BaseAstralAstralResource{
        return $this->getParentModel()->getAstralResource();
    }

    public function getPhpClassFile(): PhpClassFile
    {
        return PhpClassFile::find(static::class);
    }
	public function hideOnIndex(){
		return !$this->showOnIndex();
	}
	public function shouldValidate():bool{
        if($this->deprecated){return false;}
        if(in_array(static::class, static::$validationDisabledFor)){return false;}
        return true;
    }
    /**
     * @throws InvalidAttributeException
     * @throws RedundantVariableParameterException
     * @noinspection PhpDocRedundantThrowsInspection
     */
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        $this->globalValidation();
    }
    /**
     * @throws InvalidAttributeException
     */
    protected function globalValidation(){
        $this->validateNotNull();
		$this->validateBlackListedValues();
        // Uncomment for debugging infinite loops $this->logInfo("Validating ".(new \ReflectionClass(static::class))->getShortName()."...");
        $this->validateType();
    }
    public static function getParentShortClassName():string {
        return QMStr::toShortClassName(static::getParentClass());
    }
    public function getParentModelOrFirstExample(): BaseModel {
        $model = $this->parentModel;
        if(!$model){
            /** @var BaseModel $class */
            $class = $this->parentClass;
            $model = $this->parentModel = $class::query()->first();
            if(!$model){
                $model = $this->parentModel = $class::firstOrFakeNew();
            }
        }
        return $model;
    }
    /**
     * @return BaseModel
     */
    public function getParentModel(): BaseModel {
        $model = $this->parentModel;
        if(!$model){
            /** @var BaseModel $class */
            $class = $this->parentClass;
            $model = $this->parentModel = new $class();
        }
        return $model;
    }
    public function getParentModelSchema(): ?array{
        $model_schema_path = FileHelper::absPath('resources/model_schemas/');
        $short = QMStr::toShortClassName($this->parentClass);
        $model_schema_filename = $short.".json";
        try {
            return FileHelper::getDecodedJsonFile($model_schema_path.$model_schema_filename);
        } catch (Throwable $e){
            QMLog::info(__METHOD__.": ".$e->getMessage());
            return null;
        }
    }
	/**
	 * @return mixed|null
	 */
	public function get_model_schema_field() {
        $model_schema = $this->getParentModelSchema();
        if(!$model_schema){return null;}
        return collect($model_schema)->where('name', $this->name)->first();
    }
	/**
	 * @param $key
	 * @return mixed|null
	 */
	public function getAttribute($key){
        if(property_exists($this, $key)){
            return $this->$key;
        }
        $m = $this->getParentModel();
        return $m->getAttribute($key);
    }
	/**
	 * @param $key
	 * @return mixed|null
	 */
	public function getParentAttribute($key){
        $parent = $this->getParentModel();
        return $parent->getAttribute($key);
    }
    /**
     * @return static
     */
    public static function make(): BaseProperty {
        return new static();
    }
    /**
     * @return mixed
     */
    public static function fixInvalidRecords() {
        try {
			if(method_exists(static::class , 'fixTooLong')){
				$arr = static::fixTooLong();
			} else{
				$arr = [];
			}
            static::fixTooEarly();
            return array_merge($arr,
                static::fixTooBig(),
                static::fixTooSmall(),
                static::fixTooLate(),
                static::fixNulls());
        } catch (ModelValidationException $e) {
            le($e);
        }
    }
    public function isNumeric(): bool {return $this->isInt() || $this->isFloat();}
    public function isId():bool{return (bool)$this->isPrimary;}
    public function getLatestAt(): ?string {
        return null;
    }
    public function getEarliestAt(): ?string {
        return null;
    }
    /**
     * @return BaseModel[]|Collection
     */
    public static function fixTooLate(): void {
        $property = new static();
        $latestAt = $property->getLatestAt();
        if($latestAt === null){return;}
        $result = $property->getParentModel()
            ->where($property->name, '>', $latestAt)
            ->update([$property->name => null]);
        QMLog::info("Fixed $result records with too late $property->name");
    }

    /**
     * @return BaseModel|Collection
     */
    public static function fixTooEarly(): void {
        $property = new static();
        $earliestAt = $property->getEarliestAt();
        if($earliestAt === null){return;}
        $result = $property->getParentModel()
            ->where($property->name, '<', $earliestAt)
            ->update([$property->name => null]);
        QMLog::info("Fixed $result records with too early $property->name");
    }
    /**
     * @param QMQB $qb
     * @return \Illuminate\Support\Collection
     */
    public static function pluckIds(QMQB $qb): \Illuminate\Support\Collection {
        $property = new static();
        $ids = $qb->pluck($property->getParentModel()->getPrimaryKey());
        $count = $ids->count();
        $where = QMQB::toHumanizedWhereClause($qb);
        \App\Logging\ConsoleLog::info("Got $count records where $where");
        return $ids;
    }
    /**
     * @return mixed
     */
    public static function fixNulls(){
        $property = new static();
        $required = $property->cannotBeChangedToNull();
        if(!$required){return [];}
        return static::handleNulls();
    }
    /**
     * @param int $id
     * @return BaseModel
     */
    protected static function handleNull(int $id): BaseModel {
        le("Please implement ".__METHOD__);throw new \LogicException();
    }
    /**
     * @return mixed
     * @noinspection PhpReturnDocTypeMismatchInspection
     */
    protected static function handleNulls(){
        $models = [];
        $ids = static::getIdsWhereNull();
        foreach($ids as $id){
            $models[] = static::handleNull($id);
        }
        return $models;
    }
    public function getMaximum(): ?float {
        return $this->maximum;
    }
    public function getMinimum(): ?float {
        return $this->minimum;
    }
    /**
     * @param string $operator
     * @param $value
     * @param string $boolean
     * @return QMQB
     */
    public static function whereQMQB(string $operator, $value, string $boolean = 'and'): QMQB {
        $property = new static();
        $parent = $property->getParentModel();
        $table = $parent->getTable();
        \App\Logging\ConsoleLog::info("Getting $table where $property->name $operator $value...");
        $qb = static::qmqb()->where($property->name, $operator, $value, $boolean);
        QueryBuilderHelper::restrictQueryBasedOnPermissions($qb);
        return $qb;
    }
    /**
     * @param string $operator
     * @param $value
     * @param string $boolean
     * @return Builder
     */
    public static function where(string $operator, $value, string $boolean = 'and'): Builder {
        $property = new static();
        $qb = static::query()->where($property->name, $operator, $value, $boolean);
        $table = static::getTable();
        \App\Logging\ConsoleLog::info("Getting $table where $property->name $operator $value...");
        QueryBuilderHelper::restrictQueryBasedOnPermissions($qb);
        return $qb;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public static function getIdsWhereNull(): \Illuminate\Support\Collection {
        $qb = self::whereNullQMQB();
        $ids = static::pluckIds($qb);
        return $ids;
    }
    /**
     * @param string $operator
     * @param null $value
     * @param string $boolean
     * @return \Illuminate\Support\Collection
     * @noinspection PhpUnused
     */
    public static function getIdsWhere(string $operator, $value, string $boolean = 'and'): \Illuminate\Support\Collection {
        $qb = static::qmqb();
        $property = new static();
        $qb->where($property->name, $operator, $value, $boolean);
        $ids = static::pluckIds($qb);
        return $ids;
    }
    /**
     * @param string $sql
     * @param array $bindings
     * @param string $boolean
     * @return QMQB
     */
    public static function whereRaw(string $sql, array $bindings = [], string $boolean = 'and'): QMQB {
        \App\Logging\ConsoleLog::info("Getting records where $sql...");
        $qb = static::qmqb()->whereRaw($sql, $bindings, $boolean);
        QueryBuilderHelper::restrictQueryBasedOnPermissions($qb);
        return $qb;
    }
    /**
     * @param $sql
     * @param array $bindings
     * @param string $boolean
     * @return BaseModel[]|Collection
     */
    public static function getIdsWhereRaw($sql, array $bindings = [], string $boolean = 'and'): array {
        $property = new static();
        $qb = static::whereRaw($sql, $bindings, $boolean);
        $ids = $qb->pluck($property->getParentModel()->getPrimaryKey())->all();
        $count = count($ids);
        \App\Logging\ConsoleLog::info("Got $count records where $property->name $sql");
        return $ids;
    }
    /**
     * @param string $sql
     * @param string $reason
     * @return BaseModel[]|Collection
     * @throws ModelValidationException
     */
    public static function setNullWhereRaw(string $sql, string $reason): array {
        $property = new static();
        $parentModel = $property->getParentModel();
        $qb = static::whereRaw($sql, $bindings, $boolean);
        $ids = $qb->pluck($property->getParentModel()->getPrimaryKey())->all();
        $models = [];
        foreach($ids as $id){
            $models[] = $model = $parentModel->find($id);
            /** @var BaseModel $id */
            $model->setAttributeNullAndLogError($property->name, $reason);
        }
        return $models;
    }
    /**
     * @param QMQB $qb
     * @param string $reason
     * @return BaseModel[]|Collection
     * @throws ModelValidationException
     */
    public static function setNullWhere(QMQB $qb, string $reason): array {
        $property = new static();
        $ids = static::pluckIds($qb);
        $parentModel = $property->getParentModel();
        $models = [];
        foreach($ids as $id){
            $models[] = $parentModel->find($id);
            /** @var BaseModel $id */
            $id->setAttributeNullAndLogError($property->name, $reason);
        }
        return $models;
    }
    /**
     * @return void
     * @throws InvalidAttributeException
     */
    protected function validateNotNull(): void{
        $val = $this->getDBValue();
        if($val !== null){return;}
        if($this->isGeneratedByDB()){return;}
        if($this->canBeChangedToNull()){return;}
        if($this->originalValueIsNullOrEmptyString() && !$this->isRequired()){return;}
        $this->throwException("must be not be null");
    }
	/**
	 * @throws \App\Exceptions\InvalidAttributeException
	 */
	private function validateBlackListedValues(){
		$val = $this->getDBValue();
		if(in_array($val, $this->blackListedValues, true)){
			$this->throwException("Value should not be one of ".implode("\n\t-", $this->blackListedValues));
		}
	}
    protected function originalValueIsNullOrEmptyString():bool{
        $original = $this->getRawOriginalValue();
        return $original === null || $original === "";
    }
    /**
     * @return bool
     */
    public function cannotBeChangedToNull(): bool {
        return !$this->canBeChangedToNull;
    }
    /**
     * @return string
     * @noinspection PhpInconsistentReturnPointsInspection
     */
    public function getInput():string {
        $value = $this->getAccessorValue();
        $title = $this->getTitleAttribute();
        $name = $this->name;
        $label  = Form::label($name, $title) ;
        if($this->isNumeric()){
            return $label.Form::number($this->name, $this->getAccessorValue());
        }
        if(strpos($name, "unit_id") !== false){
            return Unit::getSelector($value, $name, $this->getTitleAttribute());
        }
        if(strpos($name, "variable_category_id") !== false){
            return VariableCategory::getSelector($value, $name, $this->getTitleAttribute());
        }
        if($this->isDateTime()){
            return $label.Form::date($name, $value, [
                'class' => 'form-control',
                'id' => $name
            ]);
        }
        if($this->isBoolean()){
            return $label.
                '<label class="checkbox-inline">'.
                    Form::hidden($name, 0).
                    Form::checkbox($name, '1', $value).
                '</label>';
        }
        le("Please define ".__METHOD__);
    }
	/**
	 * @return mixed|null
	 */
	public function getAccessorValue(){
        $p = $this->getParentModel();
        $val = $p->getAttribute($this->name);
        return $val;
    }
    /**
     * @return mixed|string|int|float
     */
    public function getHardCodedValue(){
        $value = $this->getAccessorValue();
        if($value === null || $value === ""){
            return "null";
        }
        if($const = static::getConstantEqualTo($value)){
            return "\\".static::class."::".$const;
        }
        if($this->isString()){
            return "'$value'";
        }
        if($value instanceof CarbonInterface){
            $value = db_date($value);
            return "'$value'";
        }
        if(is_array($value)){
            return TVarDumper::dump($value);
        }
        return $value;
    }
	/**
	 * @return mixed|null
	 */
	public function getDBValue(){
        $p = $this->getParentModel();
        $val = $p->getRawAttribute($this->name);
		// bypassAccessor bypasses mutation
        return $val;
    }
    public function isDateTime(): bool {
        if($this->phpType === PhpTypes::DATE){
            return true;
        }
        if($this->dbType === MySQLTypes::DATETIME){
            return true;
        }
        if($this->dbType === MySQLTypes::TIMESTAMP){
            return true;
        }
        if($this->dbType === Types::DATETIME_MUTABLE){
            return true;
        }
        return false;
        return $this->getDBColumnType()->getName() === Types::DATETIME_MUTABLE;
    }
    public function isString(): bool {
        return $this->getPHPType() === PhpTypes::STRING;
    }
    public function isFloat(): bool {
        return $this->phpType === PhpTypes::FLOAT;
    }
    /**
     * @return array
     * @noinspection PhpUnused
     */
    public function getPossibleMySQLTypes():array{
        return QMDB::phpTypeToPossibleMySQLTypes($this->getPHPType());
    }
	/**
	 * @return Type
	 */
    public function getDBType():Type{
        return $this->getDBColumnType();
		if(!$this->dbType){
			$type = MySQLTypes::phpTypeToMostLikelyMySQLType($this->getPHPType());
		} else{
			try {
				$type = Type::getType($this->dbType);
			} catch (Exception $e) {
			} catch (DBALException $e) {
			}
		}
        return $type;
    }
    public function getTitleAttribute(): string {
        if($this->title){
            return $this->title;
        }
        return QMStr::snakeToTitle($this->name);
    }
    /**
     * @return string
     */
    public function getOrder(): string {
        if(strlen($this->order) !== 2){le("strlen(\$this->order) !== ");}
        return $this->order;
    }
    public function getDisplayNameAttribute(): string {
        return $this->getTitleAttribute();
    }
    public function isBoolean(): bool {return $this->getDBColumnType()->getName() === Types::BOOLEAN;}
    public function isEnum(): bool {
        return !empty($this->enum);
    }
    /**
     * @return string
     */
    public static function generateFixInvalidRecordsUrl():string{
        return FixInvalidRecordsController::generateFixInvalidRecordsUrl(static::class);
    }
    /**
     * @param BaseProperty|null $p
     * @return BaseModel
     */
    public static function getParentClass(BaseProperty $p = null): string {
        if(!$p){$p = new static();}
        return $p->parentClass;
    }
    /**
     * @param BaseProperty|null $p
     * @return DBModel
     */
    public static function getParentDBModelClass(BaseProperty $p = null): string {
        if(!$p){
            $p = new static();
        }
        $lClass = $p->parentClass;
        return $lClass::SLIM_CLASS;
    }
    /**
     * @param int|string $id
     * @return BaseModel
     */
    protected static function findParent($id): ?BaseModel {
        $class = static::getParentClass();
        /** @var BaseModel $model */
        $model = $class::findInMemoryOrDB($id);
		if(!$model){le( "Could not find $class with id $id");}
        return $model;
    }
    /**
     * @param int $id
     * @return DBModel
     */
    protected static function findParentDBModel(int $id): ?DBModel{
        $class = static::getParentDBModelClass();
        return $class::find($id);
    }
    /**
     * @param int $id
     * @return DBModel
     * @noinspection PhpUnused
     */
    public static function findParentDBModelInMemory(int $id): ?DBModel{
        $class = static::getParentDBModelClass();
        return $class::findInMemory($id);
    }
    /**
     * @param int $id
     * @return BaseModel
     * @noinspection PhpUnused
     */
    public static function findParentInMemory(int $id): ?BaseModel{
        $class = static::getParentClass();
        return $class::findInMemory($id);
    }
    public static function updateAll() {
        le("Please implement ".__FUNCTION__." for ".static::class);
    }
	public function isValid(): bool{
		try {
			$this->validate();
			return true;
		} catch (InvalidAttributeException|RedundantVariableParameterException $e) {
			$this->logInfo(__METHOD__.": ".$e->getMessage());
			return false;
		}
	}
    public static function getTable(): string {
        return (new static())->table;
    }
    protected function getShouldNotContain(): array {
        return $this->shouldNotContain;
    }
	protected function getShouldNotEqual(): array {
		return $this->shouldNotEqual;
	}
    public static function deleteRecordsContainingBlackListedStrings(){
        $me = (new static());
        $table = static::getTable();
        foreach($me->getShouldNotContain() as $str){
            $queryParams = [$me->name => "%'.$str.'%"];
            $count = QMDB::count($table, [$me->name => "%'.$str.'%"]);
            \App\Logging\ConsoleLog::info("$count $table where ".
                QMDB::paramsToHumanizedWhereClauseString($queryParams, $table));
            if($count){
                Writable::deleteStatic($table, $queryParams);
            }
        }
    }
    /**
     * @param $value
     * @param BaseModel|DBModel|null $model
     * @throws InvalidAttributeException
     */
    public static function validateByValue($value, $model = null){
        $attr = static::NAME;
        $model->setAttribute($attr, $value);
        $model->validateAttribute($attr);
    }
    public function l(): BaseModel {return $this->getParentModel();}
    /**
     * @return mixed
     */
    public function getRawOriginalValue(){
        $model = $this->getParentModel();
        $original = $model->getRawOriginal($this->name);
        return $original;
    }
    public function hasChanged():bool{
        $current = $this->getDBValue();
        $original = $this->getRawOriginalValue();
        return $original !== $current;
    }
    public function beforeChange(bool $log = true): void {
        // Implement in child property class where necessary but don't forget to call parent::onChange();
	    $old = $this->getRawOriginalValue();
        $new = $this->getDBValue();
        if($old === $new){le("didn't change $this->name");}
		if($log){$this->logChange($old, $new, __FUNCTION__.": ".$this->__toString());}
    }
    public function onCreation(){
        if(QMLogLevel::isDebug()){
            $new = $this->getDBValue();
            $this->logDebug(__FUNCTION__.": ".QMStr::efficientPrint($new));
        }
    }
	/**
	 * @param $old
	 * @param bool $log
	 */
	public function onChange($old, bool $log = null){
		if($log === null){$log = QMLogLevel::isDebug();}
		if(!$log){return;}
        $new = $this->getDBValue();
        Compare::assertDifferent($old, $new,
            "Called ".__METHOD__." but the new $this->name value is the same as the old value");
		$this->logChange($old, $new, __FUNCTION__ . ': ' .$this->__toString());
    }
    /**
     * @param $expected
     * @param string $type
     * @throws InvalidAttributeException
     */
    public function assertNotEquals($expected, string $type){
        $val = $this->getDBValue();
        if($val === $expected){
            $this->throwException("value $val should not equal $type value $expected");
        }
    }
    /**
     * @param $expected
     * @param string $type
     * @param null $actual
     * @throws InvalidAttributeException
     */
    public function assertEquals($expected, string $type, $actual = null){
        if($actual === null){
            $actual = $this->getDBValue();
        }
        if($actual !== $expected){
            $this->throwException("value $actual should equal $type value $expected");
        }
    }
    /**
     * @param $expected
     * @param string $type
     * @throws InvalidAttributeException
     */
    public function assertNotEqualsUnlessNull($expected, string $type){
        if($expected === null){
            return;
        }
        $this->assertNotEquals($expected, $type);
    }
    /**
     * @param string $otherAttribute
     * @throws InvalidAttributeException
     */
    public function assertNotEqualsAnotherAttributeUnlessNull(string $otherAttribute){
        $val = $this->getDBValue();
        if($val === null){return;}
        $otherValue = $this->getParentModel()->getAttribute($otherAttribute);
        $this->assertNotEqualsUnlessNull($otherValue, $otherAttribute);
    }
    /**
     * @throws InvalidAttributeException
     */
    protected function validateType(){
        $rawValue = $this->getDBValue();
        if($rawValue === null){return;}
        if($this->isString()){
            $rawValue = $this->getDBValue();
            if(is_array($rawValue) || is_object($rawValue)){
                $this->throwException("should be a string but got ".QMStr::prettyJsonEncode($this->getDBValue()));
            }
        }
        $fromAccessor = $this->getAccessorValue();
        if($this->isObject() && !is_object($fromAccessor)){
            $this->throwException("should be an object but got ".TVarDumper::dump($rawValue));
        }
        if($this->isArray() && !is_array($fromAccessor)){
            $this->getAccessorValue();
            $this->throwException("should be an array but got ".TVarDumper::dump($rawValue));
        }
    }
    /**
     * @param string $message
     * @throws InvalidAttributeException
     */
    public function throwException(string $message){
        $message .= "\n\t(".$this->description.")
        Original Value was: ".$this->getRawOriginalValue();
        throw new InvalidAttributeException($this->getParentModel(),
            $this->name, $this->getDBValue(), $message);
    }
	/**
	 * @throws UnauthorizedException
	 */
	public function authorizeUpdate(): void {
        if(!AppMode::isApiRequest()){return;}
        /** @var User $user */
        $user = \Auth::user();
        if(!$user){return;} // If a user were required, they would have been stopped by middleware
        $parent = $this->getParentModel();
        if(!$user->can('update', $parent)){
            throw new UnauthorizedException();
        }
    }
    /**
     * @return array
     */
    public function getRequiredStrings(): array{
        return $this->requiredStrings;
    }
    /**
     * @param $value
     * @return mixed
     */
    public function decodeIfNecessary($value){
        if(!$value){
            /** @noinspection PhpExpressionAlwaysNullInspection */
            return $value;
        }
        if($this->isJson() && is_string($value)){
            if($this->isObject()){
                $decoded = json_decode($value, false);
            } else {
                $decoded = json_decode($value, true);
            }
            if($decoded === false || $decoded === null){
                le(json_last_error_msg(), $value);
            }
            return $decoded;
        }
        return $value;
    }
    /**
     * @return string
     */
    public function getSubtitleAttribute(): ?string{
		if($this->description === $this->name){
			return null;
		}
        return $this->description;
    }
    protected function exists(): bool {
        return $this->getParentModel()->exists;
    }
    protected function isObject(): bool {
        return $this->getPHPType() === PhpTypes::OBJECT;
    }
	/**
	 * @return mixed
	 */
	abstract public function getExample();
    /**
     * @param DBModel|BaseModel|array|object $data
     * @return mixed|null
     */
    public static function pluck($data){
		if(!$data){le("No Data provided to ".__METHOD__);}
        $synonyms = static::getSynonyms();
        if(is_object($data) && method_exists($data, 'getAttribute')){
            foreach($synonyms as $s){
                $val = $data->getAttribute($s);
                if($val !== null){return $val;}
            }
            return $data->getAttribute(static::NAME);
        }
        return QMArr::getValue($data, $synonyms);
    }
	/**
	 * @param $data
	 * @return bool
	 */
	public static function keyExists($data): bool{
        $synonyms = static::getSynonyms();
        $exists = QMArr::keyExists($data, $synonyms);
        return $exists;
    }
    public static function inSynonyms(string $sort): bool {
        $sort = str_replace("-", "", $sort);
        return QMArr::inArraySnakeCamelInsensitive($sort, static::getSynonyms());
    }
	/**
	 * @param $data
	 * @return mixed|null
	 */
	public static function pluckOrDefault($data){
		if(!$data){le("No Data provided to ".__METHOD__);}
        $val = static::pluck($data);
        if($val === null){
            $val = static::getDefault($data);
        }
        return $val;
    }
    /**
     * @param DBModel|BaseModel $model
     * @param DBModel|BaseModel|array|object $sourceData
     * @return mixed|null
     */
    public static function setAttributeBySynonyms($model, $sourceData){
        $val = static::pluck($sourceData);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
    /**
     * @param bool $throwException
     * @return mixed|null
     */
    public static function fromRequest(bool $throwException = false){
        $input = request()->input();
		$query = request()->query();
	    if(!$query && isset($_GET)){$query = $_GET;}
		$all = array_merge($input, $query);
		if($i = QMSlim::getInstance()){
			$request = $i->request;
			$all = array_merge($all, $request->get());
			$body = $request->getBody();
			if(is_string($body)){$body = json_decode($body, true);}
			if(is_array($body)){$all = array_merge($all, $body);}
		}
		$val = null;
		if($all){$val = static::pluckOrDefault($all);}
        if($val === null){
			if(method_exists(static::class, 'fromHeader')){
				/** @noinspection PhpUndefinedMethodInspection */
				$val = static::fromHeader();
			} else {
				$headers = request()->headers;
				if($headers){
					$val = static::pluckOrDefault($headers->all());
				}
			}
        }
        if($val === null && $throwException){static::throwMissingParameterException();}
        if($val === null){return null;} // Leave this here so we can break when there is a value
        //if($data){$val = static::pluckOrDefault($data);}
        return $val;
    }
    /**
     * @param Builder|\Illuminate\Database\Query\Builder $qb
     */
    public static function applyRequestParamsToQuery(\Illuminate\Database\Query\Builder $qb): void {
        static::applyRequestFiltersToQuery($qb);
        static::applyRequestSortToQuery($qb);
    }
    /**
     * @param Builder|\Illuminate\Database\Query\Builder $qb
     */
    protected static function applyRequestFiltersToQuery(\Illuminate\Database\Query\Builder $qb): void{
        $val = static::fromRequest();
        if($val === null){return;}
        $val = static::fromRequest();
        QueryBuilderHelper::applyFilters($qb, [static::NAME => $val]);
    }
    /**
     * @param Builder|\Illuminate\Database\Query\Builder $qb
     */
    public static function applyRequestSortToQuery(\Illuminate\Database\Query\Builder $qb): void {
        $withoutDirection = SortParam::getSortWithoutDirection();
        if(!$withoutDirection){return;}
        if(!static::inSynonyms($withoutDirection)){return;}
        /** @noinspection UnknownColumnInspection */
        /** @noinspection UnknownColumnInspection */
        $qb->orderBy(static::getTable().'.'.static::NAME, SortParam::getSortDirection());
    }
    /**
     * @param bool $throwException
     * @return mixed|null
     */
    public static function fromRequestDirectly(bool $throwException = false){
		if(!AppMode::isApiRequest() && !$throwException){return null;}
		$query = request()->query();
	    $val = null;
		if($query){
			$val = static::pluck($query);
			if($val){return $val;}
		}
        $data = request()->input();
		if(!$data){
			if($i = QMSlim::getInstance()){
				$data = $i->request->getBody();
				$data = QMStr::jsonDecodeIfNecessary($data, true);
			}
		}
		if($data){$val = static::pluck($data);}
        if($val === null && $throwException){static::throwMissingParameterException();}
        return $val;
    }
	/**
	 * @param null $data
	 * @return null
	 */
	public static function getDefault($data = null){return null;}
    /**
     * @param $data
     * @param UserVariable|TrackingReminder|BaseModel $uv
     * @return BaseModel|TrackingReminder|UserVariable
     * @throws ModelValidationException
     */
    public static function updateFromData($data, BaseModel $uv){
        $attribute = static::NAME;
        $new = static::pluckOrDefault($data);
        if($new === null){ // Have to return if null or we'll delete all values not provided with request
            return $uv;
        }
        /** @var BaseModel $uv */
        $existing = $uv->getAttribute($attribute); // Don't use getRawAttribute so that we don't set user variable values when they're the same as common variable values
        if($existing === $new){
            return $uv;
        }
        $uv->setAttribute($attribute, $new);
        $uv->save();
        return $uv;
    }
    public function isGeneratedByDB(): bool {
        $auto = $this->autoIncrement ||
            $this->name === BaseModel::CREATED_AT ||
            $this->name === BaseModel::FIELD_DELETED_AT ||
            $this->name === BaseModel::UPDATED_AT;
        return $auto;
    }
    /**
     * @param int|string $id
     * @return mixed|null
     */
    public static function fromId($id){
        $model = static::findParent($id);
        return $model->getAttribute(static::NAME);
    }
    /**
     * @return int
     */
    public function getMinLength(): ?int{
        return $this->minLength;
    }
    /**
     * @return int
     */
    public function getMaxLength(): ?int{
        return $this->maxLength;
    }
    public function isArray(): bool {
        return $this->getPHPType() === PhpTypes::ARRAY;
    }
    /**
     * @param $raw
     * @return mixed
     */
    public static function sanitize($raw) {
        le("Please implement ".__METHOD__);
        return $raw;
    }
    public function isUnixtime(): bool {
        return $this->isUnixTime ?? false;
    }
    public function getInvalidRecordsLens(): InvalidRecordsLens {
        return new InvalidRecordsLens($this);
    }
    /**
     * @param $value
     * @return mixed
     */
    public function setOriginalValueAndConvertToDBValue($value){
        $this->originalValue = $value;
        return $this->toDBValue($value);
    }
	/**
	 * @param $value
	 * @return mixed
	 */
	public function toDBValue($value){return $value;}
    /**
     * @param $value
     * @return mixed
     */
    public function processAndSetDBValue($value){
        $DBValue = $this->setOriginalValueAndConvertToDBValue($value);
        $this->setRawAttribute($DBValue);
        return $DBValue;
    }
    /**
     * @param $data
     * @return mixed
     */
    public function pluckAndSetDBValue($data){
        if($this->cannotBeChangedToNull()){
            $val = static::pluckOrDefault($data);
        } else {
            $val = static::pluck($data);
        }
        if($val !== null){
            $previous = $this->getAccessorValue();
            if(is_float($previous)){$val = (float)$val;}
            if($val !== $previous){
                return $this->processAndSetDBValue($val);
            }
        }
        return $val;
    }
    /**
     * @param $processed
     */
    public function setRawAttribute($processed): void{
        $parent = $this->getParentModel();
        $parent->setRawAttribute($this->name, $processed);
    }
    public function isTemporal():bool{
        return $this->isUnixtime() || $this->isDateTime();
    }
    public static function throwMissingParameterException(): void{
        throw new BadRequestException(static::getMissingParameterErrorMessage());
    }
    public static function getMissingParameterErrorMessage():string{
        return "Please provide ".static::NAME." in request. "; // Override in children if necessary
    }
    public static function indexAscending(array $arr): array{
        return QMArr::indexAscending($arr, static::NAME);
    }
    /**
     * @param array $arr
     * @return array
     * @noinspection PhpUnused
     */
    public static function indexDescending(array $arr): array{
        return QMArr::indexDescending($arr, static::NAME);
    }
    /**
     * @param null $default
     * @return mixed
     */
    public static function fromMemory($default = null){
        return Memory::get(static::NAME, Memory::PROPERTIES, $default);
    }
	/**
	 * @param $value
	 * @return mixed
	 */
	public static function setInMemory($value) {
        return Memory::set(static::NAME,$value, Memory::PROPERTIES);
    }
	/**
	 * @param $data
	 * @param bool $throwException
	 * @return mixed|null
	 */
	public static function fromDataOrRequest($data, bool $throwException = false){
        $val = static::pluck($data);
        if($val === null){$val = static::fromRequest($throwException);}
        return $val;
    }
    public function authorizeIfLoggedIn(): void{
        if(!AppMode::isApiRequest()){
            return;
        }
        if(!\Auth::user()){
            throw new UnauthorizedException();
        }
    }
    /**
     * @param $strings
     * @throws InvalidAttributeException
     */
    protected function assertContains($strings){
        if(!is_array($strings)){
            $strings = [$strings];
        }
        $haystack = $this->getDBValue();
        foreach($strings as $needle){
            $needle = (string)$needle;
            if(stripos($haystack, $needle) === false){
                throw new InvalidStringAttributeException("should contain $needle",
                    $haystack, $this->name, $this->getParentModel());
            }
        }
    }
    /**
     * @param string $haystack
     * @throws InvalidAttributeException
     */
    public function assertIsIn(string $haystack): void{
        $needle = $this->getDBValue();
        if(stripos($haystack, (string)$needle) === false){
            $this->throwException("should be present in $haystack");
        }
    }
    /**
     * @return int|string
     */
    public function getId(){
        $model = $this->getParentModel();
        $id = $model->getId();
        return $id;
    }
    public function isJson(): bool {
        $type = $this->getPHPType();
        $obj = $type === PhpTypes::OBJECT;
        $arr = $type === PhpTypes::ARRAY;
        return $obj || $arr || strpos($type, "\\") !== false;
    }
    public function isHyperParameter():bool{
        return property_exists($this, 'isHyperParameter') && $this->isHyperParameter;
    }
	/**
	 * @param mixed $value
	 * @return BaseProperty|void
	 */
	public function setValue($value){
        $m = $this->getParentModel();
        $m->setAttribute(static::NAME, $value);
		return $this;
    }
    /**
     * @param array $target
     * @param array|object $source
     * @return array
     */
    public static function addToArrayIfPresent(array $target, $source): array {
        $val = static::pluckOrDefault($source);
        if($val !== null){$target[static::NAME] = $val;}
        return $target;
    }
    // True if a value is always required or a value exists already and the att
    public function isRequired(): bool {
        if($this->required){
            return true;
        }
        if($this->canBeChangedToNull){
            return false;
        }
        if($this->originalValueIsNullOrEmptyString()){
            return false;
        }
        return true;
    }
    /**
     * @return mixed
     */
    public function getRawOriginal(){
        return $this->getParentModel()->getRawOriginal($this->name);
    }
    public static function getTables(): array {
        return BaseModel::getAllTablesWithColumn(static::NAME);
    }
    public static function setNullInAllTables(): array{
        $tables = static::getTables();
        $results = [];
        $name = static::NAME;
		if($name === "id"){le( "This function should be used for foreign keys");}
        foreach($tables as $table){
            $results[$table] = Writable::getBuilderByTable($table)
                ->whereNotNull($name)
                ->update([$name => null]);
        }
        return $results;
    }
    public function tableField(): string {
        return $this->getTable().'.'.$this->name;
    }
    /**
     * Add a basic where clause to the query.
     * @param Builder $query
     * @param  mixed   $operator
     * @param  mixed   $value
     * @param  string  $boolean
     * @return void
     */
    public function applyWhere(Builder $query, $operator = null, $value = null,
                               string  $boolean = 'and'):void {
        $query->where($this->tableField(), $operator, $value, $boolean);
    }
    /**
     * Add a basic where clause to the query.
     * @param Builder $query
     * @param string $boolean
     * @param bool $not
     * @return void
     */
    public function applyWhereNull(Builder $query, string $boolean = 'and',
                                   bool    $not = false):void {
        $query->whereNull($this->tableField(), $boolean, $not);
    }
	/**
	 * @param Table $table
	 * @param $valueCallback
	 * @param $orderCallback
	 * @return TableColumn
	 * @throws \Exception
	 */
	public function getHtmlColumn(Table $table, $valueCallback, $orderCallback): TableColumn{
        $column = new TableColumn($table);
        /** @noinspection PhpUnhandledExceptionInspection */
        $column
            ->title($this->getTitleAttribute().' '.FontAwesome::html(FontAwesome::QUESTION_CIRCLE))
            ->attr('th', 'title', $this->getSubtitleAttribute())
            ->value($valueCallback)
            ->attr('td', 'data-order', $orderCallback)
            ->add();
        return $column;
    }
    /**
     * @return int
     * @noinspection PhpUnused
     */
    public static function countWhereNull(): int{
        $val = static::whereNull()->withTrashed()->count();
        QMLog::info($val." where ".static::NAME." is null");
        return $val;
    }
	/**
	 * @param $value
	 */
	public function setAttributeIfDifferentFromAccessor($value){
        $l = $this->getParentModel();
        $l->setAttributeIfDifferentFromAccessor($this->name, $value);
    }
    public function getImage():string{
        return $this->image;
    }
    public function getGetterCode():string{
        $camel = $this->getCamelizedName();
        $screaming = $this->getScreamingSnakeName();
        $parentShortClass = $this->getParentModel()->getShortClassName();
        $phpType = $this->getPHPType();
        $dbModelClass = $this->getDBModelClassName();
        if(strpos($phpType, "\\") !== false){
            $phpType = "\\".$phpType;
        }
        $funcName = $this->getGetterFunctionName();
        if(!$dbModelClass){
            return "
    public function $funcName(): ?$phpType {
         return \$this->attributes[$parentShortClass::FIELD_$screaming];
    }";
        }
        return "
    public function $funcName(): ?$phpType {
        if(property_exists(\$this, 'attributes')){
            return \$this->attributes[$parentShortClass::FIELD_$screaming];
        } else {
            /** @var \\$dbModelClass \$this */
            return \$this->$camel;
        }
    }";
    }
    public function getSetterCode(bool $typeHint = true):string{
        $camel = $this->getCamelizedName();
        $screaming = $this->getScreamingSnakeName();
        $parentShortClass = $this->getParentModel()->getShortClassName();
        $phpType = $this->getPHPType();
        if(strpos($phpType, "\\") !== false){
            $phpType = "\\".$phpType;
        }
        $funcName = $this->getSetterFunctionName();
        if($typeHint){
            return "
    public function $funcName($phpType $$camel): void {
        \$this->setAttribute($parentShortClass::FIELD_$screaming, $$camel);
    }";
        }
        return "
    public function $funcName($$camel): void {
        \$this->setAttribute($parentShortClass::FIELD_$screaming, $$camel);
    }";
    }
    public function getCamelizedName(): string{
        return QMStr::camelize($this->name);
    }
    public function getScreamingSnakeName(): string{
        return QMStr::toScreamingSnakeCase($this->name);
    }
    public function getPHPType(): string {
        if(!$this->phpType){
            le("Pleas set phpType for ".static::class);
        }
        return $this->phpType;
    }
    public function getDBModelClassName(): ?string {
        $lClass = $this->getParentClass();
        if(!method_exists($lClass, 'getSlimClass')){
            return null;
        }
        return $lClass::getSlimClass();
    }
    public static function getGetterFunctionName():string{
        return "get".ucfirst(QMStr::camelize(static::NAME));
    }
    public static function getSetterFunctionName():string{
        return "set".ucfirst(QMStr::camelize(static::NAME));
    }
    /**
     * @return bool
     */
    public function canBeChangedToNull(): bool{
        return $this->cannotBeChangedToNull() !== true;
    }
	/**
	 * @param $new
	 */
	public function setIfLessThanExisting($new){
        $existing = $this->getDBValue();
        if($existing === null || $this->lessThanExisting($new)){
            $this->setValue($new);
        }
    }
	/**
	 * @param $new
	 */
	public function setIfGreaterThanExisting($new){
        $existing = $this->getDBValue();
        if($existing === null || $this->greaterThanExisting($new)){
            $this->setValue($new);
        }
    }
    public function lessThanExisting(float $min): bool {
        $existing = $this->getDBValue();
        return (float)$existing > $min;
    }
    public function greaterThanExisting(float $new): bool {
        $existing = $this->getDBValue();
        return (float)$existing < $new;
    }
    public function isCalculated():bool{
        return property_exists($this, 'isCalculated') && $this->isCalculated;
    }
    public function isReadOnly():bool{return $this->isCalculated() || $this->isGeneratedByDB();}
    public function showOnIndex(): bool{return false;}
    public function showOnUpdate(): bool{return !$this->isReadOnly();}
    public function showOnCreate(): bool{return !$this->isReadOnly();}
    public function showOnDetail(): bool{return true;}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	abstract public function getUpdateField($resolveCallback = null, string $name = null): Field;
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	abstract public function getCreateField($resolveCallback = null, string $name = null): Field;
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	abstract public function getDetailsField($resolveCallback = null, string $name = null): Field;
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	abstract public function getIndexField($resolveCallback = null, string $name = null): Field;
    /**
     * @param null $resolveCallback
     * @param string|null $name
     * @return Field
     */
    public static function field($resolveCallback = null, string $name = null): Field {
        $prop = new static();
        return $prop->getField($resolveCallback, $name);
    }
	/**
	 * @param string|null $title
	 * @param null $callback
	 * @return Text
	 */
	protected function getDetailLinkTextField(string $title = null, $callback = null): Text{
        return Text::make($title ?? $this->getTitleAttribute(), $this->name, $callback)
            ->sortable()
            ->readonly()
            ->detailLink();
    }
    protected function getLinkField(string $url, string $text = "Open"): Text{
        return Text::make($this->getTitleAttribute(), function () use ($url, $text) {
            return HtmlHelper::getTailwindLink($url, $text);
        })->asHtml();
    }
	/**
	 * @param $displayCallback
	 * @param string|null $title
	 * @param null $callback
	 * @return Text
	 */
	protected function getHtmlField($displayCallback, string $title = null, $callback = null): Text{
        $t = Text::make($title ?? $this->getTitleAttribute(), $this->name, $callback);
        if($displayCallback){
            $t->displayUsing($displayCallback);
        }
        return $t->asHtml();
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getField($resolveCallback = null, string $name = null): Field {
        if(AstralRequest::isStandardIndex()){
            $f = $this->getIndexField($resolveCallback, $name);
        } else if (AstralRequest::isUpdate()){
            $f = $this->getUpdateField($resolveCallback, $name);
        } else if (AstralRequest::isDetail()){
            $f = $this->getDetailsField($resolveCallback, $name);
        } else if (AstralRequest::isCreateOrAssociatableSearch()){
            $f = $this->getCreateField($resolveCallback, $name);
        } else {
            $f = $this->getIndexField($resolveCallback, $name);
        }
        $f->rules(explode("|",$this->rules));
        $f->creationRules($this->creationRules);
        $f->updateRules($this->updateRules);
        $default = $this->getDefaultValue();
        $this->addHelpToField($f);
        return $f;
    }
    /**
     * @return mixed|string|null
     */
    public function getDefaultValue(){
        return ($this->default === UNDEFINED || $this->default === 'undefined') ? null : $this->default;
    }
    /**
     * @param string|null $name
     * @param $resolveCallback
     * @return Text
     * @noinspection PhpUnusedParameterInspection
     */
    protected function getTextField(?string $name, $resolveCallback): Text{
        $f = new Text($name ?? $this->getTitleAttribute(), $this->name, $resolveCallback ??
            function($value, $resource, $attribute){
                return $value;
            });
        return $f;
    }
    /**
     * @param Field $f
     */
    protected function addHelpToField(Field $f): void{
        if($this->description && $this->description !== $this->name){
            $f->help($this->description);
        }
    }
    /**
     * @param string|null $name
     * @param $resolveCallback
     * @return Status
     */
    protected function getStatusField(?string $name, $resolveCallback): Status{
        return Status::make($name ?? $this->getTitleAttribute(), $this->name, $resolveCallback)
            ->loadingWhen([
                UserStatusProperty::STATUS_WAITING,
                UserStatusProperty::STATUS_ANALYZING,
                null
            ])->failedWhen([
                UserStatusProperty::STATUS_ERROR
            ]);
    }
    /**
     * @param string|null $name
     * @param $resolveCallback
     * @return ID
     */
    protected function getIdField(string $name = null, $resolveCallback = null): ID{
        return ID::make($name ?? $this->getTitleAttribute(), $this->name, $resolveCallback)
            ->hideFromIndex()
            ->hideWhenCreating()
            ->hideWhenUpdating()
            //->detailLink()
            ->readonly()
            ->sortable();
    }
    public function castDBValue(){
        $value = $this->getDBValue();
        $this->setRawAttribute($value);
    }
    public static function getLaravelShortClassName():string{
        return QMStr::toShortClassName(static::getParentClass());
    }
    public static function assertNoInvalidRecords(): void {
        $qb = static::whereInvalid();
        $models = $qb->get();
        $count = $models->count();
        $class = static::getLaravelShortClassName();
        $query = $qb->getQuery();
        $where = DBQueryLogServiceProvider::toWhereString($query, true);
        $message = "$count invalid ".$class."s where $where";
        QMLog::debug($message);
        if($count){
            HasName::logNames($models);
            le($message);
        }
    }
    public function isNonIdNumeric():bool{
        if($this->isId()){return false;}
        return $this->isNumeric();
    }
    /**
     * @param \ArrayAccess $arr
     * @return static[]
     */
    public static function indexBy(\ArrayAccess $arr): array{
        $byName = [];
        $byArrays = false;
        foreach($arr as $item){
            $val = static::pluckOrDefault($item);
            if(isset($byName[$val])){
                $byName = [];
                $byArrays = true;
                break;
            }
            $byName[$val] = $item;
        }
        if($byArrays){
            foreach($arr as $item){$byName[static::pluckOrDefault($item)][] = $item;}
        }
        return $byName;
    }
    /**
     * @param $arr
     * @return array
     */
    public static function pluckColumn($arr): array{
        $byName = [];
        foreach($arr as $item){
            $byName[] = static::pluckOrDefault($item);
        }
        return $byName;
    }
	/**
	 * @return mixed|null
	 */
	public static function fromReferrer(){
		$d = QMRequest::getReferrerParams();
		if(!$d){return null;}
        return static::pluck($d);
    }
    /**
     * @param Arrayable|array $arr
     * @return array
     */
    public static function pluckArray($arr): array {
        $plucked = [];
        foreach($arr as $object){$plucked[] = static::pluck($object);}
        return $plucked;
    }
    protected function parentIsPopulated(): bool{
        return !empty($this->getParentModel()->attributesToArray());
    }
	/**
	 * @param $max
	 * @return mixed
	 */
	public static function deleteWhereLessThan($max){
        $name = static::NAME;
        $class = static::getClassNameTitlePlural();
        $deleted = static::where('<', $max)->delete();
        QMLog::infoWithoutContext(__FUNCTION__." Deleted $deleted $class where $name < $max...");
        return $deleted;
    }
    /**
     * @param string $needle
     * @param \Illuminate\Support\Collection|QMHasAttributes[] $arr
     * @return \Illuminate\Support\Collection
     */
    public static function filterWhereLike(string $needle, $arr): \Illuminate\Support\Collection{
        $arr = QMArr::collect($arr);
        if(!$arr->count()){return $arr;}
        $synonym = static::findMatchingSynonym($arr->first());
        return QMArr::filterWhereLike($synonym, $needle, $arr);
    }
	/**
	 * @param string $needle
	 * @param \Illuminate\Support\Collection|QMHasAttributes[]|array $arr
	 * @return \Illuminate\Support\Collection
	 */
	public static function filterWhereStartsWith(string $needle, $arr): \Illuminate\Support\Collection{
		$arr = QMArr::collect($arr);
		if(!$arr->count()){return $arr;}
		$synonym = static::findMatchingSynonym($arr->first());
		return QMArr::filterWhereStartsWith($synonym, $needle, $arr);
	}
    /**
     * @param QMHasAttributes $exampleObj
     * @return string|null
     * @noinspection PhpMissingParamTypeInspection
     */
    public static function findMatchingSynonym($exampleObj):string{
        $all = $synonyms = static::getSynonyms();
        foreach($synonyms as $synonym){
            if(method_exists($exampleObj, 'attributeExists')){
	            if($exampleObj->attributeExists($synonym)){return $synonym;}
            } else {
				if(property_exists($exampleObj, $synonym)){return $synonym;}
            }
        }
        foreach($synonyms as $synonym){
            $camel = QMStr::camelize($synonym);
            $all[] = $camel;
            if(!in_array($camel, $synonyms)){
	            if(method_exists($exampleObj, 'attributeExists')){
		            if($exampleObj->attributeExists($synonym)){return $synonym;}
	            } else {
		            if(property_exists($exampleObj, $synonym)){return $synonym;}
	            }
            }
        }
        le(__METHOD__.": Provided object does not have an attribute called any of:\n\t - ".
            implode("\n\t - ", $all), $exampleObj);throw new \LogicException();
    }
    /**
     * @param string|float|int $needle
     * @param QMHasAttributes[]|\Illuminate\Support\Collection|array $objects
     * @return mixed
     * @noinspection PhpMissingParamTypeInspection
     */
    public static function findInArray($needle, $objects){
        $collection = QMArr::collect($objects);
        if(!$collection->count()){return null;}
		$example = $collection->first();
        $synonym = static::findMatchingSynonym($example);
        $filtered = $collection->filter(function($one) use ($needle, $synonym){
			if(method_exists($one, 'getAttribute')){
				/** @var QMHasAttributes $one */
				$val = $one->getAttribute($synonym);
			} else {
				$val = $one->$synonym;
			}
			if($val === null){
				le("could not get $synonym for $one");
			}
	        return $val === $needle;
		 });
		return $filtered->first();
    }
	/**
	 * @return string
	 */
	public function __toString() {
        $str = $this->name;
        if($this->parentModel){$str .= " on $this->parentModel";}
        return $str;
    }
	/**
	 * @param string $message
	 * @param array $meta
	 */
	public function logError(string $name, $meta = [], bool $obfuscate = true, string $message = null){
		QMLog::error("$this->name: $name", $meta, $obfuscate, $message);
	}
	/**
	 * @return DBColumn
	 */
	public function getMySqlColumn(): DBColumn{
		$col = DBColumn::find($this->table, static::NAME);
		$col->setNotnull($this->cannotBeChangedToNull());
		$col->setComment($this->getSubtitleAttribute());
		//$col->setType($this->getMySQLType());
		return $col;
	}
	/**
	 * @param string $url
	 * @param string $value
	 * @return string
	 */
	public static function addToUrl(string $url, string $value): string{
		return UrlHelper::addParam($url, static::NAME, $value);
	}
	public function getSqlAddStatement(string $table): string{
		$c = $this->getDBColumn();
		return $c->getAddStatement($table);
	}
	/**
	 * @return DBColumn
	 */
	public function getDBColumn(): DBColumn {
        $columnName = $this->getName();
        if(!$columnName){le("No name for $this");}
        $c = DBColumn::find($this->getTableName(), $columnName);
		if($c){
			if(!$c->getComment()){
				$c->setComment($this->getSubtitleAttribute());
			}
			return $c;
		}
		$all = DBColumn::fromAllTables($columnName);
		if($c = $all->first()){
			foreach($all as $one){
				$c = $one;
				if($c->getComment()){break;}
			}
			$c = new DBColumn($c, $columnName, null, $this->getParentModelOrFirstExample()->getDBTable());
		} else{
            $typesMap = Type::getTypesMap();
            $type = $typesMap[$this->dbType];
            $type = MySQLTypes::getType($this->dbType);
			$c = new DBColumn(null, $columnName, $type);
			$inputsArr = explode(':', $this->dbInput);
			$fieldTypeParams = explode(',', array_shift($inputsArr));
			$fieldType = array_shift($fieldTypeParams);
			$c->setComment($this->getSubtitleAttribute());
			$c->setAutoincrement($this->autoIncrement);
			$c->setUnsigned($this->unsigned);
			$c->setNotnull(!$this->canBeChangedToNull());
			if($this->maxLength){$c->setLength($this->maxLength);}
		}
		if(strlen($this->getSubtitleAttribute()) > strlen($c->getComment())){
			$c->setComment($this->getSubtitleAttribute());
		}
		return $c;
	}
	public function getSqlDeclaration(): string {
		return $this->getDBColumn()->getSQLDeclaration();
	}
	public function getMigrationText(): string
	{
		$inputsArr = explode(':', $this->dbInput);
		$this->migrationText = '$table->';

		$fieldTypeParams = explode(',', array_shift($inputsArr));
		$this->fieldType = array_shift($fieldTypeParams);
		$this->migrationText .= $this->fieldType."('".$this->name."'";

		if ($this->fieldType == 'enum') {
			$this->migrationText .= ', [';
			foreach ($fieldTypeParams as $param) {
				$this->migrationText .= "'".$param."',";
			}
			$this->migrationText = substr($this->migrationText, 0, strlen($this->migrationText) - 1);
			$this->migrationText .= ']';
		} else {
			foreach ($fieldTypeParams as $param) {
				$this->migrationText .= ', '.$param;
			}
		}

		$this->migrationText .= ')';

		foreach ($inputsArr as $input) {
			$inputParams = explode(',', $input);
			$functionName = array_shift($inputParams);
			if ($functionName == 'foreign') {
				$foreignTable = array_shift($inputParams);
				$foreignField = array_shift($inputParams);
				$this->foreignKeyText .= "\$table->foreign('".$this->name."')->references('".$foreignField."')->on('".$foreignTable."');";
			} else {
				$this->migrationText .= '->'.$functionName;
				$this->migrationText .= '(';
				$this->migrationText .= implode(', ', $inputParams);
				$this->migrationText .= ')';
			}
		}

		$this->migrationText .= ';';
		return $this->migrationText;
	}
	public function isInt(): bool {
		return $this->getDBColumnType()->getName() === Types::INTEGER;
	}
	public function getDBColumnType(): Type {
		return $this->getDBColumn()->getType();
	}
    public function getDBColumnTypeFromPhpType(): Type {
        return $this->convertToDatabaseValue();
    }
	/**
	 * Dynamically proxy static method calls.
	 * @param  string  $method
	 * @param array $parameters
	 * @return void
	 */
	public static function __callStatic(string $method, array $parameters){
		if (! property_exists(get_called_class(), $method)) {
			throw new BadMethodCallException("Method {$method} does not exist.");
		}
		return (new static())->$method(...$parameters);
	}
	/**
	 * @param array $arr
	 * @return string
	 */
	public static function printValues(array $arr): string{
		$values = static::pluckArray($arr);
		return \App\Logging\QMLog::print_r($values, true);
	}
	/**
	 * @param array $arr
	 * @return string
	 */
	public static function listValues(array $arr): string{
		$values = static::pluckArray($arr);
		return QMStr::list($values);
	}

    private function db(): \Illuminate\Database\Connection
    {
        return $this->getParentModel()->getConnection();
    }

    /**
     * @return mixed
     */
    public function convertToDatabaseValue()
    {
        return $this->getDoctrineConnection()->convertToDatabaseValue($this->value, $this->getPHPType());
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getDoctrineConnection(): \Doctrine\DBAL\Connection
    {
        return $this->db()->getDoctrineConnection();
    }

    public function getTableName(): string
    {
        if(!$this->table){
            $this->table = $this->getParentModel()->getTable();
        }
        return $this->table;
    }
}
