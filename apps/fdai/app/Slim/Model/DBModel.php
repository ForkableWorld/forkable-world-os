<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model;
use App\Buttons\QMButton;
use App\Cards\BasicCardWithLinkedButtons;
use App\Cards\QMListCard;
use App\DataSources\QMClient;
use App\Exceptions\BadRequestException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NotFoundException;
use App\Fields\HasMany;
use App\Http\Parameters\LimitParam;
use App\Http\Parameters\OffsetParam;
use App\Http\Parameters\SortParam;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\Variable;
use App\Models\WpPost;
use App\Properties\BaseProperty;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\QMDB;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\Writable;
use App\Traits\CanBeCalledStatically;
use App\Traits\DataLabTrait;
use App\Traits\HasCalculatedAttributes;
use App\Traits\HasClassName;
use App\Traits\HasColumns;
use App\Traits\HasDBModel;
use App\Traits\HasMemory;
use App\Traits\HasProperty\HasCreatedUpdatedDeletedAts;
use App\Traits\HasTimestampColumns;
use App\Types\BoolHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Utils\AppMode;
use App\Utils\Compare;
use App\Utils\IonicHelper;
use App\Utils\UrlHelper;
use BadMethodCallException;
use Closure;
use Dialogflow\RichMessage\Suggestion;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Tests\QMAssert;
use TypeError;
use Watson\Validating\ValidationException;
abstract class DBModel extends StaticModel {
	use HasTimestampColumns, CanBeCalledStatically, ForwardsCalls, DataLabTrait, HasClassName, HasColumns, //QMHasAttributes,
		HasCreatedUpdatedDeletedAts, HasCalculatedAttributes, HasMemory;
	// Don't use private properties because it has unintended consequences
	protected $buttons;
	protected $card;
	protected $clientId;
	protected $createdAt;
	protected $dbRow;
	protected $deletedAt;
	protected $laravelModel;
	protected $lastQB;
	protected $modifiedFields;
	protected $updatedAt;
	public const DB_FIELD_NAME_TO_PROPERTY_NAME_MAP = [];
	protected static array $dbFieldNameToPropertyNameMap = [];
	public const DEFAULT_LIMIT = BaseModel::DEFAULT_LIMIT;
	public const MAX_LIMIT     = 200;
	protected static $fields = [];
	protected static $propertyNameToDbFieldNameMap = [];
	protected static $requiredFields = [];
	public const CACHE_LIFETIME     = null;
	public const COLLECTION_NAME    = null;
	public const DEFAULT_SORT_FIELD = null;
	public const FIELD_CREATED_AT   = 'created_at';
	public const FIELD_DELETED_AT   = 'deleted_at';
	public const FIELD_NAME         = null;
	public const FIELD_UPDATED_AT   = 'updated_at';
	public const TABLE              = null;
	public const LARAVEL_CLASS      = null;
	public const MYSQL_COLUMN_TYPES = [];
	/**
	 * @param string|null $sort
	 */
	public static function validateSort(string $sort): void{
		if(empty($sort)){
			le('empty($sort)');
		}
		$availableFields = static::getColumns();
		if(!in_array($sort, $availableFields, true)){
			throw new BadRequestException("Sort field named: $sort does not exist. Available fields are:\n\t-" .
				implode("\n\t-", $availableFields));
		}
	}
	/**
	 * @return QMQB
	 */
	public static function readonly(): QMQB{
		$qmqb = ReadonlyDB::getBuilderByTable(static::TABLE);
		$qmqb->class = static::class;
		return $qmqb;
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
	 * @param int|string $id
	 * @return QMQB
	 */
	public static function whereId($id): QMQB{
		$qb = static::writable();
		$qb->where(static::FIELD_ID, $id);
		return $qb;
	}
	/**
	 * @return array
	 */
	protected static function getNotNullColumns(): array{
		return [];
	}
	/**
	 * @return array
	 */
	public static function getColumns(): array{
		if(isset(static::$fields[static::connectionName()][static::TABLE])){
			return static::$fields[static::connectionName()][static::TABLE];
		}
		$laravelClass = static::getLaravelClassName();
		/** @var BaseModel $laravelClass */
		$fields = $laravelClass::getColumns();
		return static::$fields[static::connectionName()][static::TABLE] = $fields;
	}
	/**
	 * @return array
	 */
	public static function getImportantColumnsForRelation(): array{
		$laravelClass = static::getLaravelClassName();
		/** @var BaseModel $laravelClass */
		$str = $laravelClass::getImportantColumnsForRelation();
		return explode(',', $str);
	}
	/**
	 * @param QMQB $qb
	 * @param string|null $sortTableAlias
	 * @param string|null $rawSort
	 * @return QMQB
	 */
	public static function addSortToQb(QMQB|Builder|\Illuminate\Database\Eloquent\Builder $qb, 
	                                   string $sortTableAlias = null, string $rawSort = null): 
	QMQB|Builder|\Illuminate\Database\Eloquent\Builder{
		if($rawSort){
			$withoutOrder = str_replace('-', '', $rawSort);
			$sort = static::getDbFieldNameForProperty($withoutOrder);
			$order = SortParam::rawSortToOrder($rawSort);
		} elseif($rawSort = SortParam::getSort()){
			$withoutOrder = str_replace('-', '', $rawSort);
			$sort = static::getDbFieldNameForProperty($withoutOrder);
			$order = SortParam::getOrder();
		} else{
			$rawSort = static::DEFAULT_SORT_FIELD;
			$order = SortParam::rawSortToOrder(static::DEFAULT_SORT_FIELD);
			$sort = QMRequest::formatSort($rawSort);
		}
		static::validateSort($sort);
		if($sortTableAlias){
			$sort = $sortTableAlias . '.' . $sort;
		}
		$qb->orderBy($sort, $order);
		return $qb;
	}
	/**
	 * @param QMQB $qb
	 */
	private static function addLimitToQb(QMQB $qb){
		$limit = static::getLimitFromRequestOrModelDefault();
		$qb->limit($limit);
	}
	/**
	 * @return int
	 */
	public static function getLimitFromRequestOrModelDefault(): int{
		return LimitParam::getLimit(static::DEFAULT_LIMIT, static::MAX_LIMIT);
	}
	/**
	 * @param QMQB $qb
	 */
	private static function addOffsetToQb(QMQB $qb){
		$offset = OffsetParam::getOffset();
		if($offset !== null){
			$qb->offset($offset);
		}
	}
	/**
	 * @param QMQB $qb
	 * @param string|null $sortTableAlias
	 * @param string|null $sortField
	 */
	public static function addLimitOffsetSort(QMQB $qb, string $sortTableAlias = null, string $sortField = null){
		static::addSortToQb($qb, $sortTableAlias, $sortField);
		static::addOffsetToQb($qb);
		static::addLimitToQb($qb);
	}
	/**
	 * Add a basic where clause to the query.
	 * @param string|array|Closure $column
	 * @param mixed $operator
	 * @param mixed $value
	 * @param string $boolean
	 * @return QMQB
	 */
	public static function where($column, $operator = null, $value = null, string $boolean = 'and'): QMQB{
		$qb = static::qb();
		if(!$qb->columns){
			$qb->columns = static::getSelectColumns();
		}
		if(!str_contains($column, '.')){
			$column = static::TABLE . '.' . $column;
		}
		$qb->where($column, $operator, $value, $boolean);
		$qb->whereNull(static::TABLE . '.' . self::FIELD_DELETED_AT);
		return $qb;
	}
	/**
	 * Add a basic where clause to the query.
	 * @param string $sql
	 * @param array $bindings
	 * @param string $boolean
	 * @return QMQB
	 */
	public static function whereRaw(string $sql, array $bindings = [], string $boolean = 'and'): QMQB{
		$qb = static::qb();
		if(!$qb->columns){
			$qb->columns = static::getSelectColumns();
		}
		$qb->whereRaw($sql, $bindings, $boolean);
		$qb->whereNull(static::TABLE . '.' . self::FIELD_DELETED_AT);
		return $qb;
	}
	/**
	 * @param $column
	 * @return QMQB
	 */
	public static function whereNull($column): QMQB{
		$qb = static::qb();
		if(!$qb->columns){
			$qb->columns = static::getSelectColumns();
		}
		if(!str_contains($column, '.')){
			$column = static::TABLE . '.' . $column;
		}
		$qb->whereNull($column);
		return $qb;
	}
	/**
	 * @param string|null $tableAlias
	 * @return array
	 */
	protected static function getSelectColumns(string $tableAlias = null): array{
		$fields = static::addSelectFields([], $tableAlias);
		return $fields;
	}
	/**
	 * @param string|array $fieldName
	 * @param mixed $value
	 * @param int|null $limit
	 * @param int|null $offset
	 * @param QMQB|null $qb
	 * @return static[]
	 */
	public static function qmWhere($fieldName, $value = null, int $limit = null, int $offset = null,
		QMQB $qb = null): array{
		if(!$qb){
			$qb = static::writable();
		}
		if($limit === null){
			$limit = static::getLimitFromRequestOrModelDefault();
		}
		if($limit !== 0){
			$qb->limit($limit);
		}
		if(!$offset){
			$offset = OffsetParam::getOffset();
		}
		if($offset){
			$qb->offset($offset);
		}
		if(is_array($fieldName)){
			foreach($fieldName as $field => $fieldValue){
				$qb->where($field, $fieldValue);
			}
		} else{
			$qb->where($fieldName, $value);
		}
		$rows = $qb->getArray();
		if(!$rows){
			return [];
		}
		return static::convertRowsToModels($rows, true);
	}
	/**
	 * @param array $rows
	 * @param bool $setDbRow
	 * @return null|static[]
	 */
	protected static function convertRowsToModels(array $rows, bool $setDbRow): ?array{
		if(!$rows){
			return null;
		}
		$models = [];
		foreach($rows as $row){
			$model = static::convertRowToModel($row, $setDbRow);
			$models[] = $model;
		}
		return $models;
	}
	/**
	 * @param object|array|BaseModel $l
	 * @param bool $setDbRow
	 * @return static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	protected static function convertRowToModel($l, bool $setDbRow = true){
		$model = new static();
		if($l instanceof BaseModel){
			$model->setLaravelModel($l);
		}
		$model->populateFieldsByArrayOrObject($l);
		$model->populateDefaultFields();
		if($setDbRow){
			$model->setDbRow($l);
		}
		return $model;
	}
	/**
	 * @throws ValidationException
	 */
	public function validate(): void {
		$this->validateRequiredProperties();
		$this->validateIdIfExists();
		$l = $this->l();
		$this->populateLaravelModel($l);
		try {
			$l->isValidOrFail();
		} catch (\Throwable $e) {
			$this->populateLaravelModel($l);
			$l->isValidOrFail();
		}
	}
	/**
	 * @param array $arr
	 * @return array
	 */
	protected function validateValuesInUpdateArray(array $arr): array{
		$l = $this->l(); // Set first so they're available during property validation
		foreach($arr as $key => $value){
			$l->setAttribute($key, $value);
		}
		//foreach($arr as $key => $value){$l->validateAttribute($key, $value);}
		return $arr;
	}
	/**
	 * @param array $arr
	 * @param string|null $reason
	 * @return int
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function updateDbRow(array $arr, string $reason = null){
		$this->validateValuesInUpdateArray($arr);
		$unique = $this->getUniqueIndex();
		if(!$unique){
			le('!$unique');
		}
		if(count($unique) > 1){
			unset($arr['id']);
		} // Can't update global variable relationships otherwise
		$arr = self::shrinkUpdateArr($arr);
		$l = $this->l();
		foreach($arr as $key => $value){
			$l->setAttribute($key, $value);
		}
		// Don't use saveOrFail, so we are able to update rows with pre-existing invalid values
		$result = $l->forceSave();  // forceSave skips validation since we already validated
		$this->populateByDbFieldNames($arr, true);
		$this->setDbRow($arr);
		return $result;
	}
	/**
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	public function setAttribute($key, $value){
		$this->setDBModelAttribute($key, $value);
		$this->setLaravelAttribute($key, $value);
		return $value;
	}
	/**
	 * @param string $key
	 * @param $value
	 */
	public function setAndValidateAttribute(string $key, $value){
		$this->setAttribute($key, $value);
		try {
			$this->validateAttribute($key);
		} catch (InvalidAttributeException $e) {
			le($e);
		}
	}
	/**
	 * Get an attribute from the model.
	 * @param string|string[] $column
	 * @return mixed
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public function getAttribute($column){
		if(is_array($column)){
			return parent::getAttribute($column);
		}
		$property = static::getPropertyNameForDbField($column);
		$sVal = $lVal = null;
		if(property_exists($this, $property)){
			$sVal = $this->$property ??
				null; // Sometimes we get Undefined property: QMUserVariable::$numberOfUserTags if the property was unset
		}
		/** @var BaseModel $l */
		if($l = $this->laravelModel){
			if($db = static::getDbFieldNameForProperty($column)){
				$lVal = $l->getAttribute($db);
				if($sVal !== null && $lVal !== null){
					if(!is_array($sVal) && !is_object($sVal) && !is_array($lVal) && !is_object($lVal)){
						$message = "$property Slim: $sVal Laravel: $lVal";
                        try {
                            QMAssert::assertSimilar($lVal, $sVal, $message);
                        } catch (\Throwable $e) {
                            if(AppMode::isUnitTest()){
                                le($e);
                            } else {
                                ExceptionHandler::dumpOrNotify($e);
                                return $lVal;
                            }
                        }
					}
				}
			}
		}
		return $lVal ?? $sVal;
	}
	/**
	 * @param string $string
	 * @param $value
	 */
	public function setAttributeIfNotSet(string $string, $value){
		$camel = self::getPropertyNameForDbField($string);
		if(!$camel){
			$camel = QMStr::camelize($string);
		}
		if(property_exists($this, $camel) && !isset($this->$camel)){
			$this->$camel = $value;
		}
	}
	/**
	 * @param QMQB|Builder $qb
	 */
	protected function addUniqueWhereClauses($qb){
		if(isset($this->id) && is_int($this->id) && $this->id > 0){
			$qb->where(static::FIELD_ID, $this->id);
			return;
		}
		$unique = $this->getUniqueIndex();
		if(!$unique){
			le('!$unique');
		}
		foreach($unique as $fieldName => $value){
			if(is_int($fieldName)){
				$fieldName = $value;
				$camel = QMStr::camelize($fieldName);
				if($camel === 'iD'){
					$camel = 'id';
				}
				$value = $this->$camel;
			}
			$qb->where($fieldName, $value);
		}
	}
	/**
	 * @param array $arr
	 * @return array
	 */
	protected static function shrinkUpdateArr(array $arr): array{
		foreach($arr as $key => $value){
			if($value instanceof DBModel &&
				isset($value->id)){  // It's already stored in DB so compress.  instanceof DBModel avoids compressing charts
				if(method_exists($value, 'getWithoutArrayOrObjectProperties')){
					$arr[$key] = $value->getWithoutArrayOrObjectProperties();
				}
			}
		}
		return $arr;
	}
	/**
	 * @return \App\Models\BaseModel|null
	 */
	public function getDbRow(){
		$row = $this->dbRow;
		if($row === null){
			$row = $this->findLaravelModel();
		}
		return $row;
	}
	/**
	 * @param null $row
	 * @return bool|null|object
	 */
	protected function setDbRow($row = null){
		if($row instanceof BaseModel){
			$this->setLaravelModel($row);
		}
		return $this->dbRow = $row;
	}
	/**
	 * @return int
	 */
	public function getMinutesSinceUpdatedAt(): ?int{
		if(!$this->updatedAt){
			return null;
		}
		return round(TimeHelper::minutesAgo($this->updatedAt));
	}
	/**
	 * @return int
	 */
	public static function getNumberUpdatedInLastDay(): int{
		return QMDB::addUpdatedInLastDayWhereClause(static::readonly())->count();
	}
	/**
	 * @return int
	 */
	public static function logNumberUpdatedInLastDay(): int{
		$numberUpdated = static::getNumberUpdatedInLastDay();
		QMLog::info($numberUpdated . " " . (new \ReflectionClass(static::class))->getShortName() .
			"s updated in last 24 hours");
		return $numberUpdated;
	}
	/**
	 * @return string|null
	 */
	public function getClientId(): ?string{
		if(empty($this->clientId)){
			return null;
		}
		return $this->clientId;
	}
	/**
	 * @param mixed $clientId
	 */
	public function setClientId(string $clientId){
		$this->clientId = $clientId;
	}
	/**
	 * @return string
	 */
	public function getUpdatedAt(): ?string{
		return $this->updatedAt ?: $this->setUpdatedAt();
	}
	/**
	 * @param string|null $value
	 * @return string
	 */
	public function setUpdatedAt(string $value = null): ?string{
		if(!$value){
			$value = now_at();
		}
		return $this->updatedAt = $value;
	}
	/**
	 * @return string
	 */
	public function getCreatedAt(): ?string{
		return $this->createdAt ?: $this->setCreatedAt();
	}
	/**
	 * @param string|null $value
	 * @return string|null
	 */
	public function setCreatedAt(string $value = null): ?string{
		if(!$value){
			$value = now_at();
		}
		return $this->createdAt = $value;
	}
	public function populateFromSnakeCaseArray(array $arr){
		$log = \App\Utils\Env::get('OUTPUT_CONSTRUCTOR');
		foreach($this as $key => $value){
			$snake = QMStr::snakize($key);
			if(isset($arr[$snake])){
				if($log && property_exists($this, $key)){
					\App\Logging\ConsoleLog::info("\t\$this->$key = \$l->$snake");
				}
				$this->$key = $arr[$snake];
			}
		}
	}
	public function outputConstructor(){
		$arr = [];
		foreach($this as $key => $value){
			$arr[$key] = $value;
		}
		ksort($arr);
		foreach($arr as $key => $value){
			$snake = QMStr::snakize($key);
			\App\Logging\ConsoleLog::info("\t\$this->$key = \$l->$snake");
		}
	}
	/**
	 * @param object|array $object
	 * @param bool $overwrite
	 */
	public function populateByDbFieldNames($object, bool $overwrite): void{
		/** @var BaseModel $l */
		$l = $this->laravelModel;
		foreach($this as $propertyName => $currentValue){
			if(!$overwrite && isset($currentValue)){
				continue;
			}
			$dbFieldName = static::getDbFieldNameForProperty($propertyName);
			if(!$dbFieldName){
				continue;
			}
			if(is_array($object)){
				if(!array_key_exists($dbFieldName, $object)){
					continue;
				}
				$newValue = $object[$dbFieldName];
			} elseif(is_object($object)){
				if(!property_exists($object, $dbFieldName)){
					continue;
				}
				$newValue = $object->$dbFieldName;
			} else{
				continue;
			}
			$this->$propertyName = $newValue;
			if($l){ // Don't do this because it tries to save mutated values to DB
				$l->setAttribute($dbFieldName, $newValue);
			}
			if($dbFieldName === self::FIELD_UPDATED_AT && is_object($newValue)){
				le("updated should not be an object!");
			}
		}
	}
	/**
	 * @param BaseModel $laravelModel
	 * @return BaseModel
	 */
	public function setLaravelModel(BaseModel $laravelModel): BaseModel{
		return $this->laravelModel = $laravelModel;
	}
	/**
	 * @return BaseModel
	 */
	public function findLaravelModel(): ?BaseModel{
		/** @var BaseModel $laravelClass */
		$laravelClass = static::getLaravelClassName();
		if($this->hasId()){ // We can have 0 IDs sometimes
			$id = $this->getId();
			$m = $laravelClass::findInMemoryOrDB($id);
		} else{
			$unique = $this->getUniqueIndex();
			if(!$unique){
				return null;
			}
			$qb =
				$laravelClass::withTrashed();  // If we already have the DBModel we may as well get the trashed laravel
			//model, or it causes exception: Could not get laravel model for QMUserVariableRelationship because it's deleted!
			foreach($unique as $key => $value){
				if($value === null){
					le("$key is null in unique index!");
				}
				$qb->where($key, $value);
			}
			$m = $qb->first();
			if($m && $m->getAttribute(BaseModel::FIELD_DELETED_AT)){
				$m->logError("Got laravel model but it's been soft-deleted!");
			}
		}
		if($m && !$m->exists){
			le('$m && !$m->exists');
		}
		return $m;
	}
	/**
	 * @return QMQB
	 */
	public function getWritable(): QMQB{
		$qb = static::writable();
		$unique = $this->getUniqueIndex();
		if(!$unique){
			le('!$unique');
		}
		foreach($unique as $fieldName => $value){
			if(!$value){
				le("$fieldName is $value");
			}
			$qb->where($fieldName, $value);
		}
		return $qb;
	}
	/**
	 * @param array $data
	 * @param string|null $reason
	 * @return int
	 */
	protected function softDelete(array $data = [], string $reason = null): int{
		if($reason){
			$this->logError("Soft deleting because $reason");
		}
		$data[static::FIELD_DELETED_AT] = now_at();
		return $this->getWritable()->update($data);
	}
	/**
	 * @param string $reason
	 * @param bool $countFirst
	 * @return int
	 */
	public function hardDelete(string $reason, bool $countFirst = true): int{
		$db = $this->getWritable();
		$result = $db->hardDelete($reason, $countFirst);
		if(!$result){
			$this->logError("Could not hard delete!");
		}
		return $result;
	}
	/**
	 * @return \Dialogflow\RichMessage\Dialogflow\Response\Suggestion
	 * @noinspection PhpUndefinedClassInspection
	 * @noinspection PhpUndefinedNamespaceInspection
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getSuggestions(){
		$arr = [];
		foreach($this->getButtons() as $button){
			$arr[] = $button->text;
		}
		return Suggestion::create($arr);
	}
	/**
	 * @return QMButton[]
	 */
	public function getButtons(): array{
		$buttons = $this->buttons;
		if($buttons === null){
			$buttons = $this->setDefaultButtons();
		}
		return $this->buttons = $buttons;
	}
	/**
	 * @return QMButton[]
	 */
	public function setDefaultButtons(): array{
		$buttons = IonicHelper::getIonicButtons();
		return $this->buttons = $buttons;
	}
	/**
	 * @return QMListCard
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getOptionsListCard(){
		$listCard = new QMListCard($this);
		return $this->card = $listCard;
	}
	/**
	 * @return BasicCardWithLinkedButtons
	 */
	public function getBasicCardWithLinkedButtons(): BasicCardWithLinkedButtons{
		return new BasicCardWithLinkedButtons($this);
	}
	/**
	 * @return string
	 */
	public function getListCardTitle(): string{
		return "Stuff You Can Do";
	}
	/**
	 * @param array $uniqueParams
	 * @return static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function getOrCreate(array $uniqueParams){
		$models = static::qmWhere($uniqueParams);
		if($models){
			return $models[0];
		}
		$new = static::convertRowToModel($uniqueParams, false);
		return $new;
	}
	/**
	 * @param array|string $fieldName
	 * @param mixed|null $value
	 * @return static
	 */
	public static function findByArray(array|string $fieldName, mixed $value = null): ?DBModel{
		$models = static::qmWhere($fieldName, $value, 1, 0);
		if($models){
			return $models[0];
		}
		return null;
	}
	/**
	 * @param string $dbField
	 * @return string
	 */
	protected static function getPropertyNameForDbField(string $dbField): string{
		$const = static::DB_FIELD_NAME_TO_PROPERTY_NAME_MAP;
		if(isset($const[$dbField])){
			return $const[$dbField];
		}
		$camelPropertyName =
			static::$dbFieldNameToPropertyNameMap[static::TABLE][$dbField] ?? QMStr::camelize($dbField);
		if($camelPropertyName === 'iD'){
			return 'id';
		}
		return $camelPropertyName;
	}
	/**
	 * @param string $providedPropertyName
	 * @return string
	 */
	protected static function getDbFieldNameForProperty(string $providedPropertyName){
		$const = static::DB_FIELD_NAME_TO_PROPERTY_NAME_MAP;
		foreach($const as $field => $property){
			if($providedPropertyName === $property){
				return $field;
			}
		}
		$byField = static::$dbFieldNameToPropertyNameMap[static::TABLE] ?? [];
		$byProperty = static::$propertyNameToDbFieldNameMap[static::TABLE] ?? [];
		if(isset($byProperty[$providedPropertyName])){
			return $byProperty[$providedPropertyName];
		}
		foreach($byField as $dbFieldName => $propertyName){
			if($providedPropertyName === $propertyName){
				return static::$propertyNameToDbFieldNameMap[static::TABLE][$providedPropertyName] = $dbFieldName;
			}
		}
		if($fields = static::getColumns()){
			if(in_array($providedPropertyName, $fields, true)){
				return static::$propertyNameToDbFieldNameMap[static::TABLE][$providedPropertyName] =
					$providedPropertyName;
			}
			$snake = QMStr::snakize($providedPropertyName);
			if(in_array($snake, $fields, true)){
				return static::$propertyNameToDbFieldNameMap[static::TABLE][$providedPropertyName] = $snake;
			}
		}
		return static::$propertyNameToDbFieldNameMap[static::TABLE][$providedPropertyName] = false;
	}
	/**
	 * @param array|object $arrayOrObject
	 * @return void
	 */
	public function populateFieldsByArrayOrObject(array|object $arrayOrObject): void {
		if($arrayOrObject instanceof BaseModel){
			$this->laravelModel = $arrayOrObject;
			$arrayOrObject = $arrayOrObject->toArray();
		}
		if(!$arrayOrObject){
			return;
		}
		$class = static::getLaravelClassName();
		if($arrayOrObject instanceof $class){
			$this->populateByLaravelModel($arrayOrObject);
		} else{
			parent::populateFieldsByArrayOrObject($arrayOrObject);
			$this->populateByDbFieldNames($arrayOrObject, false);
		}
		$this->populateDefaultFields();
	}
	/**
	 * @param array|object $arrayOrObject
	 * @return object
	 */
	public function populateFieldsByArrayOrObjectAndMemory($arrayOrObject): object{
		$this->populateFieldsByArrayOrObject($arrayOrObject);
		$fromMemory = $this->getMeFromMemory();
		if($fromMemory && $fromMemory !== $this){
			$fromMemory->verifyJsonEncodableAndNonRecursive();
			$this->logInfo("Already have a copy in globals! Copying values from DB and using that version " .
				"instead because it might already have data on it. One case where this happens is when getting tags with conversion factors from DB. ");
			$fromMemory->populateFieldsByArrayOrObject($arrayOrObject);
			$fromMemory->verifyJsonEncodableAndNonRecursive();
			return $fromMemory;
		} else{
			$this->addToMemory();
			return $this;
		}
	}
	/**
	 * @param bool $instantiate
	 * @return QMQB
	 */
	public static function qb(bool $instantiate = true): QMQB{
		$qb = static::writable();
		if($instantiate){
			$qb->class = static::class;
		}
		return $qb;
	}
	/**
	 * @return BaseModel|\Illuminate\Database\Eloquent\Builder
	 */
	public static function query(){
		$lClass = static::getLaravelClassName();
		return $lClass::query();
	}
	/**
	 * @return static[]
	 */
	public static function getAll(): array{
		$qb = static::readonly();
		$qb->setDisableWhereChecks(true);
		//$qb->limit(100);
		$rows = $qb->get();
		$users = static::instantiateDBRows($rows);
		return $users;
	}
	/**
	 * @param Collection|array $rows
	 * @return static[]
	 * Much faster than instantiateNonDBRows but less versatile
	 */
	public static function instantiateDBRows($rows): array{
		$models = [];
		foreach($rows as $row){
			if($row instanceof static){
				$model = $row;
			} else {
				$model = new static();
				$model->populateByDbFieldNames($row, false);
			}
			$models[] = $model;
		}
		return $models;
	}
	/**
	 * @param QMQB $qb
	 * @param string $foreignTable
	 * @param string $foreignField
	 * @return QMQB
	 */
	public static function addJoinOnId(QMQB $qb, string $foreignTable, string $foreignField): QMQB {
		return $qb->join(static::TABLE, static::TABLE . '.' . static::FIELD_ID, '=',
			$foreignTable . '.' . $foreignField);
	}
	/**
	 * @param array $params
	 * @return static[]
	 */
	public static function get(array $params = []): array{
		$qb = static::readonly();
		foreach($params as $key => $value){
			if(!$key){
				le("key for $value is $key");
			}
			$field = static::getDbFieldNameForProperty($key);
			if(empty($field)){
				static::getDbFieldNameForProperty($key);
				le("Could not getDbFieldNameForProperty $key");
			}
			QMDB::addWhereClauses($qb, $params, static::TABLE);
		}
		if(!$qb->columns){ // We can pre-define columns in \App\Slim\Model\DBModel::readonly like done in Vote model
			$columns = static::addSelectFields([]);
			$qb->select($columns); // Faster to add camelCase to select than convert later
		}
		$rows = $qb->getArray();
		if(!$rows){
			return [];
		}
		return static::convertRowsToModels($rows, true);
	}
	/**
	 * @param $id
	 * @return bool|static
	 */
	public static function find($id): ?DBModel{
		if(!$id){
			throw new BadRequestHttpException("Please provide id!");
		}
		if($id instanceof static){
			return $id;
		}
		$lClass = static::getLaravelClassName();
		/** @var HasDBModel $l */
		$l = $lClass::findInMemoryOrDB($id);
		if(!$l){
			QMLog::error("Could not get by id $id");
			return null;
		}
		$model = $l->getDBModel();
		if(is_numeric($id)){
			$id = (int)$id;
		}
		if($model->getId() !== $id){
			$model->logError("gotten id " . $model->getId() . " does not equal requested id $id!  model: " .
				json_encode($model));
		}
		return $model;
	}
	/**
	 * @param string $field
	 * @return mixed
	 */
	public function getPropertyValueByDbFieldName(string $field){
		$name = QMStr::camelize($field);
		return $this->$name;
	}
	/**
	 * @throws \App\Exceptions\ProtectedDatabaseException
	 */
	public static function truncate(){
		static::writable()->truncate();
	}
	/**
	 * @return string
	 */
	protected static function connectionName(): string{ return Writable::getConnectionName(); }
	/**
	 * @param string|null $reason
	 * @return string
	 */
	public function getHardDeletionUrl(string $reason = null): string{
		$id = $this->getId();
		if(!$id){
			le("No ID for SQL");
		}
		$url = UrlHelper::getApiUrlForPath('sql', [
			'sql' => 'DELETE ' . static::TABLE . ' from ' . static::TABLE . ' where id =' . $this->getId() . " /*
                $reason
            */  ",
		]);
		return $url;
	}
	/**
	 * @param string $clientId
	 * @param string $reason
	 * @return int
	 */
	public static function softDeleteAllForClientId(string $clientId, string $reason): int{
		$qb = static::writable()->where(QMClient::FIELD_CLIENT_ID, $clientId);
		return $qb->softDelete([], $reason);
	}
	/**
	 * @param QMQB $qb
	 * @param string|null $prefix
	 */
	public static function addImportantFieldsCamelized(QMQB $qb, string $prefix = null): void{
		$fields = static::getImportantColumnsForRelation();
		foreach($fields as $field){
			$propertyName = $prefix ? static::getPropertyNameForDbField($prefix . '_' .
				$field) : static::getPropertyNameForDbField($field);
			$qb->columns[] = static::TABLE . '.' . $field . " as " . $propertyName;
		}
	}
	/**
	 * @return static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function instance(){
		return new static();
	}
	/**
	 * @param string[]|Expression[] $arr
	 * @param string|null $tableAlias
	 * @param string|null $propertyPrefix
	 * @return array
	 */
	public static function addSelectFields(array $arr, string $tableAlias = null, string $propertyPrefix = null): array{
		$arr = QMDB::addCamelCaseAliases($arr);
		$fields = static::getColumns();
		if(!$tableAlias){
			$tableAlias = static::TABLE;
		}
		foreach($fields as $field){
			$tableField = $tableAlias . '.' . $field;
			foreach($arr as $expression){
				if(!is_string($expression)){
					/** @var Expression $expression */
					$expression = $expression->getValue();
				}
				if(str_contains($expression, $tableField)){
					continue 2;
				}
			}
			$propertyName = static::getPropertyNameForDbField($field);
			$class = static::class;
			if(!property_exists($class, $propertyName)){
				QMLog::debug("$propertyName does not exist");
				\App\Logging\ConsoleLog::info("protected $$propertyName; // Not found on $class");
				continue;
			}
			foreach($arr as $expression){
				if(!is_string($expression)){
					/** @var Expression $expression */
					$expression = $expression->getValue();
				}
				if($propertyPrefix){
					$propertyNameWithPrefix = $propertyPrefix . $propertyName;
				} else{
					$propertyNameWithPrefix = $propertyName;
				}
				if(stripos($expression, " as $propertyNameWithPrefix") !== false){
					continue 2;
				}
			}
			$arr[] = "$tableField as $propertyName";
		}
		return $arr;
	}
	/**
	 * @return BaseModel
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function firstOrNewLaravelModel(){
        try {
            $m = $this->firstLaravelModel();
            if($m){
                return $m;
            }
            $m = $this->newLaravelModel();
        } catch (NotFoundException $e) {
            ExceptionHandler::dumpOrNotify($e);
            $m = $this->newLaravelModel();
	        try {
		        $m->save();
	        } catch (ModelValidationException $e) {
				le($e);
	        }
        }
		return $m;
	}
	/**
	 * @return BaseModel
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function l(){
		return $this->firstOrNewLaravelModel();
	}
	/**
	 * @return BaseModel
	 * Avoids database queries just for model buttons
	 */
	public function attachedOrNewLaravelModel(): BaseModel{
		$m = $this->laravelModel;
		if($m){
			return $m;
		}
		return $this->newLaravelModel();
	}
	/**
	 * @return int|null
	 */
	public function getDBGeneratedAutoIncrementId(): ?int{
		if(!$this->hasValidId()){
			return null;
		}
		$id = $this->getId();
		if(is_int($id)){
			return $id;
		}
		return null;
	}
	/**
	 * @return BaseModel
	 */
	public function firstLaravelModel(): ?BaseModel{
		if($m = $this->laravelModel){
			return $m;
		}
		$m = $this->findLaravelModel();
		if(!$m){
			$id = $this->getDBGeneratedAutoIncrementId(); // Don't use strings like device_tokens for this check
			if($id){
				$class = $this->getShortClassName();
				if($this->deletedAt){
					$this->throwLogicException("Could not get laravel model for $class because it's deleted!");
				} else{
					throw new NotFoundException("Could not get laravel model for $class even though we have id ($id) and it's not deleted! " .
						\App\Logging\QMLog::print_r(static::getDataLabUrls(), true));
				}
			}
			return null;
		}
		if(!$m->exists){ // This should not happen!
			$this->logError("Model should exist!");
			//$m = $this->firstLaravelModel();
			//$m->exists = true;
		}
		return $this->setLaravelModel($m);
	}
	/**
	 * @return BaseModel
	 */
	public function newLaravelModel(): BaseModel{
		$laravelClass = static::getLaravelClassName();
		/** @var BaseModel $model */
		$model = new $laravelClass();
		$attributes = $this->toDbInsertionArray();
		$model->forceFill($attributes);
		$id = $attributes['id'] ?? null;
		if($id && is_int($id)){
			$model->exists = true;
		}
		return $this->setLaravelModel($model);
	}
	/**
	 * @return array
	 */
	public function getHardCodedParametersArray(): array{
		$staticFields = static::getStaticFields();
		$arr = [];
		foreach($staticFields as $field){
			$camelPropertyName = static::getPropertyNameForDbField($field);
			if(property_exists($this, $camelPropertyName) && isset($this->$camelPropertyName)){
				$value = $this->$camelPropertyName;
				$arr[$field] = $value;
			} else{
				$camel = QMStr::camelize($field);
				if(property_exists($this, $camel) && isset($this->$camel)){
					$value = $this->$camel;
					$arr[$field] = $value;
				} else {
					$this->logInfoWithoutContext("No $camelPropertyName property for field $field on ".static::class);
				}
			}
		}
		return $arr;
	}
	/**
	 * @return array
	 */
	public static function getDynamicCalculatedFields(): array{
		$dbFields = static::getColumns();
		$dynamic = [];
		$globalDynamicFields = [
			self::FIELD_DELETED_AT,
			self::FIELD_UPDATED_AT,
			self::FIELD_CREATED_AT,
			Variable::FIELD_STATUS,
			Variable::FIELD_REASON_FOR_ANALYSIS,
			Variable::FIELD_BEST_EFFECT_VARIABLE_ID,
			Variable::FIELD_BEST_CAUSE_VARIABLE_ID,
			Variable::FIELD_STATUS,
			Variable::FIELD_INTERNAL_ERROR_MESSAGE,
			Variable::FIELD_USER_ERROR_MESSAGE,
			Variable::FIELD_CLIENT_ID,
			Variable::FIELD_OPTIMAL_VALUE_MESSAGE,
			Variable::FIELD_BEST_AGGREGATE_CORRELATION_ID,
			Variable::FIELD_ADDITIONAL_META_DATA,
			Variable::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS,
			Variable::FIELD_CLIENT_ID,
			Variable::FIELD_OPTIMAL_VALUE_MESSAGE,
			Variable::FIELD_STANDARD_DEVIATION,
			Variable::FIELD_VARIANCE,
		];
		foreach($dbFields as $field){
			if(in_array($field, $globalDynamicFields)){
				$dynamic[] = $field;
				continue;
			}
			if(QMStr::endsWith('_at', $field)){
				$dynamic[] = $field;
				continue;
			}
			if(QMStr::endsWith('_time', $field)){
				$dynamic[] = $field;
				continue;
			}
			if(str_contains($field, HasMany::$number_of_)){
				$dynamic[] = $field;
				continue;
			}
			if(str_contains($field, '_recorded_value')){
				$dynamic[] = $field;
				continue;
			}
			if(str_contains($field, 'most_common')){
				$dynamic[] = $field;
			}
		}
		return $dynamic;
	}
	/**
	 * @return array
	 */
	public static function getStaticFields(): array{
		$static = [];
		$dynamic = static::getDynamicCalculatedFields();
		$all = static::getColumns();
		foreach($all as $field){
			if(!in_array($field, $dynamic)){
				$static[] = $field;
			}
		}
		return $static;
	}
	/**
	 * @param string $field
	 * @return static|null
	 */
	public static function getMostRecently(string $field): ?DBModel{
		$row = QMAccessToken::readonly()->orderBy($field, 'desc')->first();
		if(!$row){
			return null;
		}
		return static::instantiateIfNecessary($row);
	}
	/**
	 * @return static|null
	 */
	public static function getMostRecentlyCreated(): ?DBModel{
		return static::getMostRecently(self::FIELD_CREATED_AT);
	}
	/**
	 * @param string $reason
	 * @return int
	 */
	public function hardDeleteWithRelations(string $reason): int{
		$this->hardDeleteRelated($reason);
		return $this->hardDelete($reason);
	}
	/**
	 * @param string $reason
	 */
	protected function hardDeleteRelated(string $reason){
		if(method_exists($this, 'firstWpPost')){
			/** @var WpPost $post */
			$post = $this->firstWpPost();
			if($post){
				$post->hardDeleteWithRelations($reason);
			}
		}
        QMLog::exceptionIfTesting(__METHOD__." is too slow because it has to introspect the database! ");

		$this->logError("Hard deleting related records because $reason");
		$table = static::TABLE;
		$referencedColumn = static::FIELD_ID;
		$idReferenced = $this->getId();
		if(!$idReferenced){
			le('!$idReferenced,"No id on $this ".get_class($this)');
		}
		Writable::deleteRelatedForeignRecords($reason, $table, $referencedColumn, $idReferenced);
	}
	/**
	 * @return array
	 */
	public function toDbInsertionArray(): array{
		$dbFields = static::getColumns();
		$arr = [];
		foreach($dbFields as $field){
			$camel = static::getPropertyNameForDbField($field);
			if(!property_exists($this, $camel)){
				QMLog::debug("public $$camel; // Need property to create DbInsertionArray on " . get_class($this));
				continue;
			}
			if(!isset($this->$camel)){
				continue;
			}
			$arr[$field] = $this->$camel;
		}
		return $arr;
	}
	/**
	 * @param $ids
	 * @return BaseModel[]|Collection
	 */
	public static function findLaravelModels($ids): Collection{
		$lClass = static::getLaravelClassName();
		$models = [];
		foreach($ids as $id){
			$models[$id] = $lClass::findInMemoryOrDB($id);
		}
		return collect($models);
	}
	/**
	 * @return bool
	 * @throws ModelValidationException
	 */
	public function save(): bool{
		$l = $this->firstOrNewLaravelModel();
		$this->populateLaravelModel($l);
		$result = $l->save();
		if(!$l->hasId()){
			try {
				$l->saveOrFail();
			} catch (InvalidAttributeException|ModelValidationException $e) {
				le($e);
			}
		}
		if(!$l->hasId()){
			$l->save();
		}
		$id = $l->getId();
		if($id !== null){
			$this->id = $id;
		}
		$this->addToMemory();
		return $result;
	}
	/**
	 * @param string $column
	 * @param string $boolean
	 * @return QMQB
	 */
	public static function whereNotNull(string $column, string $boolean = 'and'): QMQB{
		$qb = static::qb();
		$qb->whereNotNull($column, $boolean);
		return $qb;
	}
	/** @noinspection PhpUnused */
	public function serializationDebug(): void{
		// NOTE:  This must be a dynamic function on the object to check private properties so don't move to ObjectHelper
		foreach($this as $key => $value){
			\App\Logging\ConsoleLog::info("serializing $key. If this crashes run " . '$this->' . $key .
				'->serializationDebug(); to continue to narrow down');
			serialize($value);
		}
	}
	/**
	 * @return array
	 */
	public function __sleep(): array{
		$serializable = [];
		foreach($this as $paramName => $paramValue){
			if(!is_string($paramValue) && !is_array($paramValue) && is_callable($paramValue)){
				continue;
			}
			if(in_array($paramName, ['lastQb', 'dbRow'])){
				continue;
			}
			$serializable[] = $paramName;
		}
		return $serializable;
	}
	public function makeSerializable(){
		foreach($this as $paramName => $paramValue){
			if(!is_string($paramValue) && !is_array($paramValue) && is_callable($paramValue)){
				$this->$paramName = null;
			}
		}
	}
	public function shrink(){
		parent::shrink();
		$this->dbRow = $this->lastQB = null;
	}
	/**
	 * Get an attribute from the model.
	 * @return mixed
	 */
	public static function getTableName(): string{
		return static::TABLE;
	}
	/** @noinspection PhpUnused */
	public function getDBModel(): DBModel{
		return $this;
	}
	/**
	 * @param BaseModel $l
	 */
	protected function populateLaravelModel(BaseModel $l): void{
		$db = $this->toDbInsertionArray();
		foreach($db as $key => $value){
			$l->setAttributeIfDifferentFromAccessor($key, $value);
		}
	}
	/**
	 * @return static
	 */
	public static function first(): ?DBModel{
		$lClass = static::getLaravelClassName();
		/** @var HasDBModel $l */
		$l = $lClass::first();
		if(!$l){
			return null;
		}
		return $l->getDBModel();
	}
	public static function deleteAll(){
		$class = static::getPluralizedClassName();
		$message = "Deleting all $class...";
		static::writable()->delete();
		if(AppMode::isUnitOrStagingUnitTest()){
			\App\Logging\ConsoleLog::info($message);
		} else{
			QMLog::error($message);
		}
	}
	/**
	 * @param string $attribute
	 * @return BaseProperty
	 */
	public function getPropertyModel(string $attribute): BaseProperty{
		$p = $this->l()->getPropertyModel($attribute);
		if(!$p){
			le("No $attribute property for " . static::class);
		}
		return $p;
	}
	/**
	 * @return BaseProperty[]
	 */
	public function getPropertyModels(): array{
		$p = $this->l()->getPropertyModels();
		return $p;
	}
	/**
	 * @param int|null $userId
	 */
	public function populateFromBaseModel(int $userId = null){
		// Implement in child models
	}
	public function populateByLaravelModel(BaseModel $l){
		$this->setLaravelModel($l);
		$arr = $l->attributesToArray();
		foreach($this as $propertyName => $currentValue){
			$dbFieldName = static::getDbFieldNameForProperty($propertyName);
			if(!$dbFieldName){
				continue;
			}
			if(!array_key_exists($dbFieldName, $arr)){
				continue;
			}
			$newValue = $arr[$dbFieldName];
			$this->setDBModelAttribute($propertyName, $newValue);
		}
	}
	public function validateId(){
		if(!$this->id){
			le('!$this->id');
		}
	}
	public function validateIdIfExists(){
		if(!$this->id){
			return;
		}
		$this->validateId();
	}
	/**
	 * Get a specified relationship.
	 * @param string $relation
	 * @return mixed
	 */
	public function getRelation(string $relation){
		$camel = QMStr::camelize($relation);
		if(property_exists($this, $camel) && isset($this->$camel)){
			$dbm = $this->$camel;
			return $dbm->l();
		}
		return $this->l()->getRelation($relation);
	}
	/**
	 * Get a specified relationship.
	 * @param string $relation
	 * @return mixed
	 */
	public function getRelationIfLoaded(string $relation){
        $loaded = $this->relationLoaded($relation);
		if(!$loaded){
			return null;
		}
        $l = $this->l();
        try {
            if(!$l->relationLoaded($relation)){
                return null;
            }
            return $l->getRelation($relation);
        } catch (\Throwable $e) {
            debugger($e);
            $loaded = $this->relationLoaded($relation);
            $l = $this->l();
            le($e);
        }
	}
	/**
	 * Determine if the given relation is loaded.
	 * @param string $key
	 * @return bool
	 */
	public function relationLoaded(string $key): bool{
		$l = $this->laravelModel;
		if(!$l){
			$camel = QMStr::camelize($key);
			if(property_exists($this, $camel) && isset($this->$camel)){
				return true;
			}
			return false;
		}
        $l = $this->l();
		return $l->relationLoaded($key);
	}
	public static function getRequiredPropertyNames(): array{
		return [];
	}
	public function validateRequiredProperties(){
		if($this->id){
			$this->validateId();
		}
		$req = static::getRequiredPropertyNames();
		foreach($req as $prop){
			if(!isset($this->$prop)){
				le("$prop not found");
			}
		}
	}
	/**
	 * @throws InvalidAttributeException
	 */
	public function validateAttributes(){
		$cols = $this->getColumns();
		foreach($cols as $col){
			$this->validateAttribute($col);
		}
	}
	/**
	 * @param string $key
	 * @param $value
	 * @throws InvalidAttributeException
	 */
	public function validateAndSet(string $key, $value){
		$this->validateAttribute($key, $value);
		$this->setAttribute($key, $value);
	}
	/**
	 * @param string $key
	 * @param null $value
	 * @throws InvalidAttributeException
	 */
	public function validateAttribute(string $key, $value = null){
		$l = $this->laravelModel;
		if(!$l){
			if($this->hasId()){
				$l = $this->firstLaravelModel();
			} else{
				$l = $this->newLaravelModel();
			}
		}
		if($value !== null){
			$l->setAttribute($key, $value);
		}
		$l->validateAttribute($key);
	}
	/**
	 * @param string $attr
	 * @return bool
	 */
	public function hasAttribute(string $attr): bool{
		$attr = QMStr::camelize($attr);
		$fields = static::getColumns();
		return in_array($attr, $fields);
	}
	/**
	 * @param BaseModel[]|\Illuminate\Database\Eloquent\Collection $baseModels
	 * @return static[]
	 */
	public static function toDBModels($baseModels): array{
		$dbms = [];
		/** @var HasDBModel $l */
		foreach($baseModels as $l){
			try {
				$dbms[] = $l->getDBModel();
			} catch (\Throwable $e) {
				QMLog::info(__METHOD__.": ".$e->getMessage());
				$dbms[] = $l->getDBModel();
			}
		}
		return $dbms;
	}
	/**
	 * @return BaseProperty[]
	 */
	public function getTemporalProperties(): array{
		return $this->l()->getTemporalProperties();
	}
	/**
	 * @throws \App\Exceptions\InvalidAttributeException
	 * @throws \App\Exceptions\RedundantVariableParameterException
	 */
	public function validateTimes(){
		$props = $this->getTemporalProperties();
		foreach($props as $prop){
			$prop->validate();
		}
	}
	/**
	 * @param $mixed
	 * @return static
	 */
	public static function pluck($mixed): ?DBModel{
		$class = str_replace("QM", "", (new \ReflectionClass(static::class))->getShortName());
		$camel = QMStr::camelize($class);
		if($model = QMArr::pluckValue($mixed, $camel)){
			return static::instantiateIfNecessary($model);
		}
		$id = QMArr::pluckValue($mixed, $camel . 'Id');
		if(!$id){
			return null;
		}
		return static::find($id);
	}
	/**
	 * Dynamically retrieve attributes on the model.
	 * @param string $key
	 * @return mixed
	 */
	public function __get(string $key){
		if($key === "attributes"){
			return $this->l()->attributesToArray();
		}
		return $this->getAttribute($key);
	}
	/**
	 * Dynamically set attributes on the model.
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function __set(string $key, $value){
		$this->setAttribute($key, $value);
	}
	/**
	 * Handle dynamic method calls into the model.
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public function __call(string $method, array $parameters){
		$l = $this->l();
		if(method_exists($l, $method)){
			return $this->forwardCallTo($l, $method, $parameters);
		} else {
			throw new BadMethodCallException(sprintf(
				'Call to undefined method %s::%s()', static::class, $method
			));
		}
	}
	/**
	 * @param $key
	 * @return mixed|null
	 */
	public function getRawAttribute($key){
		$l = $this->l();
		return $l->getRawAttribute($key);
	}
	/**
	 * @param array|Collection $arr
	 * @return static
	 */
	public static function last($arr): DBModel{
		return QMArr::last($arr);
	}
	/**
	 * @throws InvalidAttributeException
	 */
	protected function checkNotNullAttributes(){
		$l = $this->l();
		$props = $this->getRequiredProperties();
		foreach($props as $p){
			$snake = $p->name;
			$camel = static::getPropertyNameForDbField($p->name);
			if($this->$camel === null){
				$p->throwException("$camel should not be null");
			}
			if($l->getAttribute($snake) === null){
				$p->throwException("$snake should not be null");
			}
		}
	}
	/**
	 * @return BaseProperty[]
	 */
	protected function getRequiredProperties(): array{
		$props = $this->getPropertyModels();
		$required = [];
		foreach($props as $p){
			if($p->isRequired()){
				$required[] = $p;
			}
		}
		return $required;
	}
	/**
	 * @return static[]
	 */
	public static function getByRequest(): array{
		$lClass = static::getLaravelClassName();
		$c = $lClass::getByRequest();
		return static::toDBModels($c);
	}
	public function getTable(): string{
		return static::TABLE;
	}
	/**
	 * @param string $key
	 * @param $value
	 */
	private function setLaravelAttribute(string $key, $value): void{
		/** @var BaseModel $l */
		if($l = $this->laravelModel){
			if($dbName = static::getDbFieldNameForProperty($key)){
				$l->setAttribute($dbName, $value);
			} else{
				$this->logDebug("No DB field for $key. ");
			}
		}
	}
	/**
	 * @param string $key
	 * @param $value
	 */
	private function setDBModelAttribute(string $key, $value): void{
		$prop = static::getPropertyNameForDbField($key);
		if(!isset($this->$prop)){
			$previous = null;
		} else {
			$previous = $this->$prop;
		}
		if($previous === $value){return;}
		if($previous !== null){
			if(BoolHelper::equalAccordingToMySQL($previous, $value)){
				return;
			}
			if(!Compare::isSimilar($previous, $value)){
				// TODO
				//                if(static::hasColumn($key)){
				//                    $prop = $this->getPropertyModel($key);
				//                    $prop->setValue($value);
				//                    $prop->onChange($previous);
				//                }
				$message =
					__FUNCTION__ . ": Changed $key from " .
					QMStr::efficientPrint($previous, QMLog::MAX_NAME_LENGTH) . " on DBModel to " .
					QMStr::efficientPrint($value, QMLog::MAX_NAME_LENGTH);
				if($previous && $value === null){
					$this->logInfo($message);
				} else{
					$this->logDebug($message);
				}
			}
		}
		$this->$prop = $value;
	}
	/**
	 * @return bool
	 */
	public function hasValidId(): bool{
		try {
			$id = $this->getId();
		} catch (TypeError $e){
		    return false;
		}
		return $id > 0;
	}
	public function getFontAwesome(): string{
		return static::getClassFontAwesome();
	}
	public static function getClassFontAwesome(): string{
		$class = static::getLaravelClassName();
		return $class::getClassFontAwesome();
	}
	public function getColor(): string{
		if(property_exists($this, 'color') && $this->color){
			return $this->color;
		}
		$class = static::getLaravelClassName();
		return $class::COLOR;
	}
	public static function getClassDescription(): string{
		$lClass = static::getLaravelClassName();
		return $lClass::getClassDescription();
	}
	/**
	 * @return array
	 */
	public static function getUniqueIndexColumns(): array{
		$lClass = static::getLaravelClassName();
		return $lClass::getUniqueIndexColumns();
	}
	public function getDisplayNameAttribute(): string {
		return $this->getTitleAttribute();
	}
	public function getNameAttribute(): string {
		return $this->getTitleAttribute();
	}
	public function getUrl(array $params = []): string{
		if($this->laravelModel){
			return $this->l()->getUrl($params);
		}
		$lClass = self::getLaravelClassName();
		$id = $this->getId();
		if($id){return $lClass::generateAstralShowUrl($id, $params);}
		return $lClass::generateAstralIndexUrl($params);
	}
	public function getKeyWordString(): string{
		$keywords = $this->l()->getKeyWords();
		return QMStr::generateKeyWordString($keywords);
	}
	/**
	 * @return bool
	 */
	public function uniqueFieldsAreSet(): bool{
		$uniqueFields = static::getUniqueIndexColumns();  // Don't use self::
		foreach($uniqueFields as $uniqueField){
			$property = static::getPropertyNameForDbField($uniqueField);
			if(!isset($this->$property)){
				return false;
			}
		}
		return true;
	}
}
