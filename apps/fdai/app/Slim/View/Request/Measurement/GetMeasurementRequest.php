<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection SuspiciousAssignmentsInspection */
// TODO: Rename to GetProcessedMeasurementRequest, create GetRawMeasurementRequest, move non-processy functions to GetRawMeasurementRequest and extend it
namespace App\Slim\View\Request\Measurement;
use App\CodeGenerators\Swagger\SwaggerDefinition;
use App\DataSources\QMDataSource;
use App\Exceptions\BadRequestException;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Logging\QMLog;
use App\Models\Measurement;
use App\Models\Variable;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Measurement\MeasurementValueProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Measurement\DailyMeasurement;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\QMMeasurementExtended;
use App\Slim\Model\Measurement\RawQMMeasurement;
use App\Slim\Model\QMUnit;
use App\Slim\Model\User\QMUser;
use App\Slim\QMSlim;
use App\Slim\View\Request\Request;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\QueryBuilderHelper;
use App\Types\BoolHelper;
use App\Types\ObjectHelper;
use App\Types\TimeHelper;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use App\Utils\QMAPIValidator;
use App\Variables\QMUserVariable;
use App\Variables\QMVariableCategory;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use RuntimeException;
/** Class GetMeasurementRequest
 * @package App\Slim\View\Request\Measurement
 */
class GetMeasurementRequest extends Request {
	private $connectorId;
	private $connectorName;
	private $earliestFillingTime;
	private $earliestRawMeasurementTime;
	private $groupByDate;
	private $latestFillingTime;
	private $latestRawMeasurementTime;
	private $offset;
	private $processedMeasurements;
	private $requestParams;
	private $unitAbbreviatedName;
	private $userId;
	/**
	 * @var \App\Models\UserVariable
	 */
	private $qmUserVariable;
	private $userVariableRequestParams;
	public $doNotConvert;
	public $doNotProcess;
	public $earliestMeasurementTime;
	public $excludeExtendedProperties;
	public $groupingWidth;
	public $latestMeasurementTime;
	public $limit;
	public $minMaxFilter;
	public $sort;
	public $variableCategoryName;
	public $variableId;
	public $variableName;
	/**
	 * GetMeasurementRequest constructor.
	 * @param $requestParams
	 */
	public function __construct($requestParams = null){
		if($requestParams){
			$this->setRequestParams($requestParams);
		}
	}
	/**
	 * @param $userId
	 * @return QMMeasurement[]|Collection
	 */
	public static function getBasicMeasurements(int $userId): Collection{
		$message = 'Getting measurements from database for user ' . $userId;
		QMLog::info($message);
		$measurements = QMMeasurement::readonly()
			->select('measurements.start_time AS startTime', 'measurements.value AS value',
				Variable::TABLE . '.name AS variableName',
				Variable::TABLE . '.variable_category_id as variableCategoryId',
				'units.abbreviated_name as unitAbbreviatedName', 'measurements.note AS additionalMetaData')
			->leftJoin(Variable::TABLE, 'measurements.variable_id', '=', Variable::TABLE . '.id')
			->join('units', 'measurements.unit_id', '=', 'units.id')->where('measurements.user_id', '=', $userId)
			->get();
		if(!$measurements){
			$errorMessage = "No measurements to export for user";
			$metaData = ['user_id' => $userId];
			QMLog::error($errorMessage, $metaData);
			return [];
		}
		foreach($measurements as $m){
			$m->variableCategoryName = "Unknown Variable Category";
			if($m->variableCategoryId){
				$m->variableCategoryName = QMVariableCategory::find($m->variableCategoryId)->name;
			}
		}
		QMLog::info("Got " . count($measurements) . " measurements", ['user id' => $userId]);
		return $measurements;
	}
	/**
	 * @return QMMeasurement[]|string
	 */
	public function handle(){
		if($this->getDeleteId() && QMAuth::isAdmin()){
			return $this->handleDeleteRequest();
		}
		if($this->deleteOutsideAllowedRange()){
			return $this->handleDeleteOutsideRangeRequest();
		}
		return $this->handleGetRequest();
	}
	/**
	 * @return DailyMeasurement[]
	 */
	public function getDailyMeasurements(): array{
		$this->setGroupByDate(true);
		$this->setGroupingWidth(86400);
		$uv = $this->getQMUserVariable();
		$measurements = $uv->getMeasurementsWithTagsJoinsChildrenInUserOrProvidedUnit($this);
		return $measurements;
	}
	public function hasFillingValue(): bool{
		$v = $this->getQMUserVariable();
		return $v->hasFillingValue();
	}
	/**
	 * @return int
	 */
	public function getEarliestMeasurementTime(): ?int{
		if($this->earliestMeasurementTime){
			return TimeHelper::universalConversionToUnixTimestamp($this->earliestMeasurementTime);
		}
		return $this->setEarliestMeasurementTime();
	}
	/**
	 * @param int $earliestMeasurementTime
	 * @return int
	 */
	public function setEarliestMeasurementTime($earliestMeasurementTime = null): ?int{
		if($earliestMeasurementTime !== null){
			$this->earliestMeasurementTime = $earliestMeasurementTime;
		} elseif($this->getParamInArray([
			'earliestTaggedMeasurementTime',
			'startTime',
		])){
			$this->earliestMeasurementTime = $this->getParamInArray([
				'earliestTaggedMeasurementTime',
				'startTime',
			]);
		}
		if($this->earliestMeasurementTime){
			return $this->earliestMeasurementTime =
				TimeHelper::universalConversionToUnixTimestamp($this->earliestMeasurementTime);
		}
		return null;
	}
	/**
	 * @return int
	 */
	public function getLatestMeasurementTime(): ?int{
		if($this->latestMeasurementTime){
			return TimeHelper::universalConversionToUnixTimestamp($this->latestMeasurementTime);
		}
		return $this->setLatestMeasurementTime();
	}
	/**
	 * @param int $latestMeasurementTime
	 * @return int
	 */
	public function setLatestMeasurementTime($latestMeasurementTime = null): ?int{
		if($latestMeasurementTime !== null){
			$this->latestMeasurementTime = $latestMeasurementTime;
		} elseif($this->getParamInArray([
			'latestTaggedMeasurementTime',
			'endTime',
		])){
			$this->latestMeasurementTime = $this->getParamInArray([
				'latestTaggedMeasurementTime',
				'endTime',
			]);
		}
		if($this->latestMeasurementTime){
			return $this->latestMeasurementTime =
				TimeHelper::universalConversionToUnixTimestamp($this->latestMeasurementTime);
		}
		return null;
	}
	private function validateEarliestLatestTimeParams(){
		if($this->getEarliestMeasurementTime() && $this->getLatestMeasurementTime() &&
			$this->getEarliestMeasurementTime() > $this->getLatestMeasurementTime()){
			if(AppMode::isProduction()){
				QMLog::error('earliestTaggedMeasurementTime cannot exceed latestTaggedMeasurementTime!  Unsetting both!',
					['params' => $this]);
			}
			$this->setEarliestMeasurementTime(null);
			$this->setLatestMeasurementTime(null);
		}
	}
	/**
	 * @return bool
	 */
	public function getSumByDate(): bool{
		if($this->qmUserVariable && $this->getGroupByDate() &&
			$this->getQMUserVariable()->combinationOperation === BaseCombinationOperationProperty::COMBINATION_SUM){
			return true;
		}
		return false;
	}
	/**
	 * @return bool
	 */
	public function getAverageByDate(): bool{
		$uv = $this->qmUserVariable;
		return $uv && $this->getGroupByDate() &&
			$uv->combinationOperation === BaseCombinationOperationProperty::COMBINATION_MEAN;
	}
	/**
	 * Populate this request's properties from an Application instance.
	 * @param QMSlim $app
	 * @param null $requestParams
	 *  When one of the request parameters is invalid.
	 */
	public function populate(QMSlim $app, $requestParams = null){
		$this->userVariableRequestParams = [];
		$this->setApplication($app);
		$this->setRequestParams($this->params());
		$this->setGroupingWidth($this->getParamNumeric('groupingWidth', null, 'groupingWidth must be numeric'));
	}
	/**
	 * @param array|null $params
	 * @return array
	 */
	private function setRequestParams(array $params = null): array{
		$this->requestParams = [];
		if($params){
			if(!is_array($params)){
				le("requestParams is not an array!");
			}
			$params = static::properlyFormatRequestParams($params, QMMeasurement::getLegacyRequestParams());
			$params = APIHelper::updateSortFieldName($params, QMMeasurement::getLegacyPropertiesToAdd(0));
			$params = static::properlyFormatRequestParams($params);
			QMAPIValidator::validateParams($this->getAllowedParams(), array_keys($params),
				'measurements/measurements_get');
			$this->validateEarliestLatestTimeParams();
			$this->requestParams = $params;
			foreach($params as $key => $value){
				$this->$key = $value;
			}
			if($this->earliestMeasurementTime && !$this->earliestFillingTime){
				$this->setEarliestFillingTime($this->earliestMeasurementTime);
			}
			if($this->latestMeasurementTime && !$this->latestFillingTime){
				$this->setLatestFillingTime($this->latestMeasurementTime);
			}
		}
		return $this->requestParams;
	}
	/**
	 * @return array
	 */
	public function getRequestParams(): array{
		return $this->requestParams ?: $this->setRequestParams();
	}
	/**
	 * @param int $groupingWidth
	 */
	public function setGroupingWidth($groupingWidth){
		$this->groupingWidth = $groupingWidth;
	}
	/**
	 * @return int
	 */
	public function getUserId(): ?int{
		return $this->userId ?: $this->setUserId();
	}
	/**
	 * @param int|null $userId
	 * @return int
	 */
	public function setUserId(int $userId = null): int{
		if($userId){
			return $this->userId = $userId;
		}
		if($uv = $this->qmUserVariable){
			return $this->userId = $uv->getUserId();
		}
		$errorMessage = 'Please provide numeric userId or log in';
		$this->userId = $this->getParamNumeric('userId');
		if(!$this->userId){
			$user = QMAuth::getQMUser();
			if(!$user){
				QMAuth::throwUnauthorizedException($errorMessage);
			}
			$this->userId = $user->id;
		}
		return $this->userId;
	}
	/**
	 * @return int
	 */
	public function getVariableId(): int{
		return $this->variableId ?: $this->setVariableId();
	}
	/**
	 * @return null|QMUnit
	 */
	public function getUserUnit(): ?QMUnit{
		if($this->qmUserVariable){
			return $this->getQMUserVariable()->getUserUnit();
		}
		return null;
	}
	/**
	 * @return QMUnit
	 */
	public function getRequestedUnit(): ?QMUnit{
		if($this->getUnitAbbreviatedName()){
			return QMUnit::findByNameOrSynonym($this->getUnitAbbreviatedName());
		}
		return null;
	}
	/**
	 * @return string
	 */
	private function setVariableName(): string{

	}
	/**
	 * @return QMUserVariable The variable to get measurements for.
	 * @deprecated
	 */
	public function getQMUserVariable(): QMUserVariable{
		if($uv = $this->qmUserVariable){
			return $uv;
		}
		if($this->getVariableName()){
			$this->setQmUserVariable();
			if(!$this->qmUserVariable){
				throw new BadRequestException("Either variable does not exist or you are not authorized to access it");
			}
		}
		if($this->getVariableId()){
			$this->setQmUserVariable();
			if(!$this->qmUserVariable){
				throw new BadRequestException("Either variable does not exist or you are not authorized to access it");
			}
		}
		return $this->qmUserVariable;
	}
	/**
	 * @return int
	 */
	public function getGroupingWidth(): ?int{
		if($this->groupingWidth){
			return $this->groupingWidth;
		}
		$param = $this->getParamNumeric('groupingWidth');
		if($param){
			return $param;
		}
		if($this->hasFillingValue()){
			return 86400;
		}
		return null;
	}
	/**
	 * @return string The unit measurements should be converted to.
	 */
	public function getUnitAbbreviatedName(): ?string{
		return $this->unitAbbreviatedName ?: $this->setUnitAbbreviatedName();
	}
	/**
	 * @param string $unitAbbreviatedName
	 * @return string
	 */
	public function setUnitAbbreviatedName($unitAbbreviatedName = null): ?string{
		if($unitAbbreviatedName !== null){
			return $this->unitAbbreviatedName = $unitAbbreviatedName;
		}
		if(isset($this->requestParams['unitAbbreviatedName'])){
			return $this->unitAbbreviatedName = $this->requestParams['unitAbbreviatedName'];
		}
		$unitNameFromRequest = $this->getParamInArray([
			'unit',
			'unitName',
			'unitAbbreviatedName',
		]);
		if($unitNameFromRequest){
			return $this->unitAbbreviatedName = $unitNameFromRequest;
		}
		if($this->getQMUserVariable()){
			if(!$this->getQMUserVariable()){
				le("No user variable");
			}
			if(!$this->getQMUserVariable()->getUserOrCommonUnit()){
				le("No unit");
			}
			return $this->unitAbbreviatedName = $this->getQMUserVariable()->getUserUnit()->abbreviatedName;
		}
		return null;
	}
	/**
	 * @return array
	 */
	public function getVariableRequestParams(): array{
		$params = $this->userVariableRequestParams ?: [];
		if(!QMAuth::loggedInUserIsAuthorizedToAccessAllDataForUserId($this->getUserId())){
			$params['isPublic'] = true;
		}
		return $this->userVariableRequestParams = $params;
	}
	/**
	 * @return int
	 */
	public function getDeleteId(): int{
		return $this->getParamNumeric('deleteId', null);
	}
	/**
	 * @return bool
	 */
	public function deleteOutsideAllowedRange(): bool{
		return $this->getParam('deleteOutsideAllowedRange', null) && $this->getUserId() && $this->getVariableId();
	}
	/**
	 * @return bool
	 */
	public function getDoNotConvert(): bool{
		return $this->doNotConvert;
	}
	/**
	 * @param bool $doNotConvert
	 */
	public function setDoNotConvert($doNotConvert){
		$this->doNotConvert = $doNotConvert;
	}
	/**
	 * @param bool $minMaxFilter
	 * @return bool
	 */
	public function setMinMaxFilter($minMaxFilter = null): bool{
		if($minMaxFilter !== null){
			$this->minMaxFilter = (bool)$minMaxFilter;
		}
		return $this->minMaxFilter;
	}
	/**
	 * @return string
	 */
	public function getSort(): string{
		$sort = $this->sort;
		if(empty($sort)){
			return 'startTime';
		}
		if(strpos($sort, 'earliestMeasurementTime') !== false){
			$sort = str_replace('earliestMeasurementTime', 'startTime', $sort);
		}
		return $sort;
	}
	/**
	 * @param string $sort
	 */
	public function setSort(string $sort){
		$this->sort = $sort;
	}
	/**
	 * @param QMUserVariable $qmUserVariable
	 * @return QMUserVariable
	 */
	public function setQmUserVariable($qmUserVariable = null): QMUserVariable{
		if($qmUserVariable !== null){
			return $this->qmUserVariable = $qmUserVariable;
		}
		$params = $this->getVariableRequestParams();
		$userId = $this->getUserId();
		if($name = $this->getVariableName()){
			return $this->qmUserVariable = QMUserVariable::findOrCreateByNameOrId($userId, $name, $params);
		}
		if($id = $this->getVariableId()){
			return $this->qmUserVariable = QMUserVariable::findOrCreateByNameOrId($userId, $id, $params);
		}
		return $this->qmUserVariable;
	}
	/**
	 * @param mixed $excludeExtendedProperties
	 */
	public function setExcludeExtendedProperties($excludeExtendedProperties){
		$this->excludeExtendedProperties = $excludeExtendedProperties;
	}
	/**
	 * @return array
	 */
	public function getAllowedParams(): array{
		$allowedParams = [
			'averageByDate',
			'createdAt',
			'connectorName',
			'connectorId',
			'doNotProcess',
			'doNotConvert',
			'earliestMeasurementTime',
			'endTime',
			'excludeExtendedProperties',
			'excludeExtendedProperties',
			'groupByDate',
			'groupingTimezone',
			'groupingWidth',
			'id',
			'lastUpdated',
			'latestMeasurementTime',
			'maximumValue',
			'minMaxFilter',
			'minimumValue',
			'sourceName',
			'startTime',
			'startTime',
			'sumByDate',
			'unitAbbreviatedName',
			'updatedAt',
			'value',
			'variableCategoryId',
			'variableCategoryName',
			'variableId',
			'variableName',
		];
		//return $allowedParams;
		$properties = get_object_vars($this);
		foreach($properties as $key => $value){
			if($value && is_string($value)){
				$allowedParams[] = $value;
			}
		}
		//$merged =  array_merge($allowedParams, $properties);
		return $allowedParams;
	}
	/**
	 * @return QMQB
	 */
	public function setQb(): QMQB{
		if($this->getVariableNameOrId()){
			$qb = self::qb();
		} else{
			$qb = self::getQBWithVariablesJoin();
		}
		$qb->where('measurements.user_id', '=', $this->getUserId());
		return $this->qb = $qb;
	}
	/**
	 * @return QMQB
	 */
	public static function getQBWithVariablesJoin(): QMQB{
		$qb =
			self::qb()->join(Variable::TABLE, 'measurements.variable_id', '=', Variable::TABLE . '.id');
		$qb->columns[] = Variable::TABLE . '.id AS variableId';
		$qb->columns[] = Variable::TABLE . '.name AS variableName';
		$qb->columns[] = Variable::TABLE . '.variable_category_id as variableCategoryId';
		return $qb;
	}
	/**
	 * @return QMQB
	 */
	public static function qb(): QMQB{
		$qb = QMMeasurement::readonly();
		$qb->columns[] = 'measurements.source_name as sourceName';
		$qb->columns[] = 'measurements.connector_id as connectorId';
		//$qb->columns[] = 'measurements.connection_id as connectionId'; Might want to uncomment but it will use a lot
		//of memory
		$qb->columns[] = 'measurements.duration as duration';
		$qb->columns[] = 'measurements.start_time AS startTime';
		$qb->columns[] = 'measurements.unit_id AS unitId';
		return $qb;
	}
	/**
	 * @return QMQB
	 */
	private function addUserVariableJoins(): QMQB{
		$qb = $this->getQb();
		if(!$this->getVariableNameOrId()){
			$userId = $this->getUserId();
			$qb->leftJoin('user_variables AS uv', function(JoinClause $join) use ($userId){
				$join->on('measurements.variable_id', '=', 'uv.variable_id')->where('uv.user_id', '=', $userId);
			});
			$qb->columns[] = 'uv.default_unit_id AS userUnitId';
			$qb->columns[] = 'uv.id AS userVariableId';
		}
		return $qb;
	}
	/**
	 * @return QMQB
	 */
	private function addEarliestAndLatestWhereClauses(): QMQB{
		$qb = $this->getQb();
		if($this->getEarliestMeasurementTime()){
			$qb->where('measurements.start_time', '>=', $this->getEarliestMeasurementTime());
		}
		if($this->getLatestMeasurementTime()){
			$qb->where('measurements.start_time', '<=', $this->getLatestMeasurementTime());
		}
		return $qb;
	}
	/**
	 * @return QMDataSource|null
	 */
	private function getDataSource(): ?QMDataSource{
		$nameOrId = $this->connectorId ?? $this->connectorName ?? null;
		if(!$nameOrId){
			return null;
		}
		return QMDataSource::getDataSourceWithoutDBQuery($nameOrId);
	}
	/**
	 * @param QMQB $qb
	 * @return QMQB
	 */
	private function addValueSelectStatements(QMQB $qb): QMQB{
		$db = ReadonlyDB::db();
		if($this->getAverageByDate()){
			$qb->columns = [];
			$qb->columns[] = $db->raw("AVG(value) AS value");
			$qb->groupBy('startDate');
		} elseif($this->getSumByDate()){
			$qb->columns = [];
			$qb->columns[] = $db->raw("SUM(value) AS value");
			$qb->groupBy('startDate');
		} else{
			$qb->columns[] = 'value AS value';
			$qb->columns[] = 'measurements.id AS id';
			$qb->columns[] = 'measurements.note AS note';
			$qb->columns[] = 'measurements.original_value AS originalValue';
			$qb->columns[] = 'measurements.original_unit_id AS originalUnitId';
			$qb->columns[] = 'measurements.source_name AS sourceName';
			$qb->columns[] = 'measurements.connector_id AS connectorId';
			$qb->columns[] = 'measurements.client_id AS clientId';
			$qb->columns[] = 'measurements.original_start_at AS originalStartAt';
		}
		return $qb;
	}
	/**
	 * @param QMQB $qb
	 * @param bool $includeConcatenatedStringFields
	 * @return QMQB
	 */
	public static function addAggregatedSelectStatements(QMQB $qb, bool $includeConcatenatedStringFields): QMQB{
		$db = ReadonlyDB::db();
		if(ReadonlyDB::isSQLite()){
			$qb->columns[] = $db->raw("strftime('%Y-%m-%d', start_time, 'unixepoch') AS startDate");
		} else{
			$qb->columns[] = $db->raw("FROM_UNIXTIME(start_time, '%Y-%m-%d') AS startDate");
		}
		if($includeConcatenatedStringFields){
			$qb->columns[] = $db->raw("GROUP_CONCAT(measurements.note, ', ') AS note");
			$qb->columns[] = $db->raw("GROUP_CONCAT(measurements.original_value, ', ') AS originalValue");
			$qb->columns[] = $db->raw("GROUP_CONCAT(measurements.original_unit_id, ', ') AS originalUnitId");
			$qb->columns[] = $db->raw("GROUP_CONCAT(measurements.id, ', ') AS id");
			$qb->columns[] = $db->raw("GROUP_CONCAT(measurements.source_name, ', ') AS sourceName");
		}
		return $qb;
	}
	/**
	 * @return QMQB
	 */
	private function addAdvancedProperties(): QMQB{
		$qb = $this->getQb();
		if(!$this->excludeExtendedProperties){
			$qb->columns[] = 'measurements.client_id as clientId';
			$qb->columns[] = 'measurements.created_at as createdAt';
			$qb->columns[] = 'measurements.deleted_at as deletedAt';
			$qb->columns[] = 'measurements.latitude AS latitude';
			$qb->columns[] = 'measurements.location AS location';
			$qb->columns[] = 'measurements.longitude AS longitude';
			$qb->columns[] = 'measurements.updated_at as updatedAt';
			if(!$this->getVariableNameOrId()){
				$qb->columns[] = Variable::TABLE . '.' . Variable::FIELD_IMAGE_URL . ' as imageUrl';
				$qb->columns[] = Variable::TABLE . '.' . Variable::FIELD_PRODUCT_URL . ' as productUrl';
				$qb->columns[] = Variable::TABLE . '.description as variableDescription';
				$qb->columns[] = Variable::TABLE . '.valence as valence';
			}
		}
		return $qb;
	}
	/**
	 * @return QMQB
	 */
	private function addSortLimitOffsetFilters(): QMQB{
		$qb = $this->getQb();
		QueryBuilderHelper::applyFilterParamsIfExist($qb, $this->getDbFieldMap(), $this->getRequestParams());
		if($this->getOffset()){
			$qb->skip($this->getOffset());
		}
		if($this->getLimit()){
			$qb->limit($this->getLimit());
		}
		QueryBuilderHelper::setSort($qb, $this->getSort(), $this->getDbFieldMap());
		if($this->sort && !$this->qb->orders){
			le("QB order not set to $this->sort");
		}
		return $qb;
	}
	/**
	 * @param string $name
	 * @return array|mixed
	 */
	private function getDbFieldMap($name = null){
		$map = [
			'value' => 'measurements.value',
			'timestamp' => 'measurements.start_time',
			//'startTime' => 'measurements.start_time',
			'startTime' => 'measurements.start_time',
			'startAt' => 'measurements.start_at',
			'lastUpdated' => 'measurements.updated_at',
			'createdAt' => 'measurements.created_at',
			'updatedAt' => 'measurements.updated_at',
			'variableCategoryId' => 'measurements.variable_category_id',
			//'variableName' => CommonVariable::TABLE.'.name',  Already done separately
			//'variableId' => 'measurements.variable_id',   Already done separately
			'sourceName' => 'measurements.source_name',
			'id' => 'measurements.id',
		];
		if($name){
			if(!isset($map[$name])){
				le("$name is not an available field!");
			}
			return $map[$name];
		}
		return $map;
	}
	/**
	 * @param string $connectorName
	 */
	public function setConnectorName(string $connectorName){
		$this->connectorName = $connectorName;
	}
	/**
	 * @param QMMeasurement[] $measurementRows
	 */
	private function checkMeasurementRowsForErrors($measurementRows){
		if(count($measurementRows) > 2000 && !QMUser::isTestUserByIdOrEmail($this->getUserId()) &&
			!AppMode::isWorker()){
			QMLog::debug('Got ' . count($measurementRows) . ' measurements for user ' . $this->getUserId());
		}
		if($this->sort === '-startTime' && isset($measurementRows[1]) &&
			$measurementRows[1]->startTime > $measurementRows[0]->startTime){
			QMLog::error("Measurements not sorted properly!");
		}
	}
	/**
	 * @return int
	 */
	public function getLimit(): ?int{
		return $this->limit;
	}
	/**
	 * @param int $limit
	 */
	public function setLimit(int $limit){
		$this->limit = $limit;
	}
	/**
	 * @return int
	 */
	public function getOffset(): int{
		return $this->offset ?: 0;
	}
	/**
	 * @param int $offset
	 */
	public function setOffset(int $offset){
		$this->offset = $offset;
	}
	/**
	 * @return bool
	 */
	public function getDoNotProcess(): bool{
		return BoolHelper::isTruthy($this->doNotProcess);
	}
	/**
	 * @param bool $doNotProcess
	 */
	public function setDoNotProcess(bool $doNotProcess){
		$this->doNotProcess = $doNotProcess;
	}
	/**
	 * @return bool
	 */
	public function getGroupByDate(): ?bool{
		return $this->groupByDate;
	}
	/**
	 * @param bool $groupByDate
	 */
	public function setGroupByDate(bool $groupByDate){
		$this->groupByDate = $groupByDate;
	}
	/**
	 * @param int $variableId
	 * @return int
	 */
	public function setVariableId($variableId = null): int{
		if($variableId){
			return $this->variableId = $variableId;
		}
		if($this->qmUserVariable){
			return $this->variableId = $this->qmUserVariable->getVariableIdAttribute();
		}
		return $this->getParamNumeric('variableId', null);
	}
	/**
	 * @return int
	 * @deprecated Stop using GetMeasurementRequest
	 */
	public function getLatestRawMeasurementTime(): ?int{
		return $this->latestRawMeasurementTime;
	}
	/**
	 * @return int
	 * @deprecated Stop using GetMeasurementRequest
	 */
	public function getEarliestRawMeasurementTime(): ?int{
		return $this->earliestRawMeasurementTime;
	}
	/**
	 * @return QMMeasurement[]
	 * @deprecated Stop using GetMeasurementRequest
	 */
	public function getMeasurementsInCommonUnit(): array{
		$this->setDoNotConvert(true);
		$measurements = [];
		$qb = $this->addUserVariableJoins();
		$qb->whereNull(Measurement::TABLE . '.' . Measurement::FIELD_DELETED_AT);
		$qb = $this->addEarliestAndLatestWhereClauses();
		if($variableId = $this->getVariableId()){
			$qb->where(Measurement::TABLE . '.' . Measurement::FIELD_VARIABLE_ID, $variableId);
		} elseif($name = $this->getVariableName()){
			$qb->where(Variable::TABLE . '.name', $name);
		}
		if($source = $this->getDataSource()){
			$qb->where(Measurement::TABLE . '.' . Measurement::FIELD_CONNECTOR_ID, $source->getId());
		}
		$qb = $this->addValueSelectStatements($qb);
		if($this->getSumByDate() || $this->getAverageByDate()){
			$qb = self::addAggregatedSelectStatements($qb, true);
		}
		if(!$this->getGroupByDate()){ // TODO: Implement aggregate select clauses
			$qb = $this->addAdvancedProperties();
			$qb = $this->addSortLimitOffsetFilters();
		}
		/** @var QMMeasurement[] $rows */
		$rows = $qb->getArray();
		$this->checkMeasurementRowsForErrors($rows);
		$uv = $this->getQMUserVariable();
		$this->logInfo("Got " . count($rows) . " Raw Measurements from DATABASE");
		$unit = $uv->getCommonUnit();
		foreach($rows as $row){
			if($this->excludeExtendedProperties){
				$m = new RawQMMeasurement($row, $this, $uv, $unit);
			} else{
				$m = new QMMeasurementExtended($row, $uv, $unit);
			}
			// Can't index by date because we get all for connector sometimes $measurements[$m->getStartAt()] = $m;
			$measurements[] = $m;
		}
		if($this->sort && !$this->qb->orders){
			le("QB order not set to $this->sort");
		}
		return $measurements;
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataString(): string{
		$str = "";
		if($c = $this->getConnector()){
			$str .= $c;
		}
		if($v = $this->getQMUserVariable()){
			$str .= $v;
		}
		return $str;
	}
	/**
	 * @return int
	 * @deprecated Stop using GetMeasurementRequest
	 */
	public function getLatestFillingTime(): ?int{
		return $this->latestFillingTime ?: $this->setLatestFillingTime();
	}
	/**
	 * @return int
	 * @deprecated Stop using GetMeasurementRequest
	 */
	public function getEarliestFillingTime(): ?int{
		if($this->earliestFillingTime !== null){
			return $this->earliestFillingTime;  // Why was this commented?
		}
		return $this->setEarliestFillingTime();
	}
	/**
	 * @param int $latestFilling
	 * @return int
	 * @deprecated Stop using GetMeasurementRequest
	 */
	public function setLatestFillingTime($latestFilling = null): ?int{
		if($latestFilling !== null){
			$this->latestFillingTime = $latestFilling;
			TimeHelper::exceptionIfEarlierThan2000($latestFilling, 'latestFilling');
		} elseif($this->getParamInArray(['latestTaggedMeasurementTime', 'endTime']) !== null){
			$latestFilling =
			$this->latestFillingTime = (int)$this->getParamInArray(['latestTaggedMeasurementTime', 'endTime']);
			TimeHelper::exceptionIfEarlierThan2000($latestFilling, 'latestFilling');
		} elseif($this->latestMeasurementTime !== null){
			$latestFilling = $this->latestFillingTime = (int)$this->latestMeasurementTime;
			TimeHelper::exceptionIfEarlierThan2000($latestFilling, 'latestFilling');
		} elseif(!$this->latestFillingTime && $variable = $this->getQMUserVariable()){
			$at = $variable->getLatestFillingAt();
			$latestFilling = $this->latestFillingTime = time_or_null($at);
			TimeHelper::exceptionIfEarlierThan2000($latestFilling, 'latestFilling');
		}
		$latestMeasurement = $this->getLatestRawMeasurementTime();
		if($latestMeasurement && $latestMeasurement < $this->latestFillingTime){
			$latestFilling = $this->latestFillingTime = $this->getLatestRawMeasurementTime();
			TimeHelper::exceptionIfEarlierThan2000($latestFilling, 'latestFilling');
		}
		if($this->earliestFillingTime === $this->latestFillingTime){
			QMLog::debug("Earliest filling is same as latest! It is: " . $this->earliestFillingTime);
		}
		if(!$this->latestFillingTime){
			//throw new \LogicException("latestFillingTime not set!");
			return null;
		}
		$this->validateFillingTimes();
		if($this->latestFillingTime !== null){
			return (int)$this->latestFillingTime;
		}
		return null;
	}
	/**
	 * @return QMMeasurement[]|DailyMeasurement[]
	 * @deprecated Stop using GetMeasurementRequest
	 */
	public function getProcessedMeasurements(): array{
		$measurements = $this->processedMeasurements;
		if(!$measurements){
			$v = $this->getQMUserVariable();
			$v->setMeasurementRequest($this);
			$measurements = $v->getProcessedMeasurements();
		}
		$withinRange = [];
		$latest = $this->getLatestMeasurementOrFillingTime();
		$earliest = $this->getEarliestMeasurementOrFillingTime();
		foreach($measurements as $m){
			$startTime = $m->getOrSetStartTime();
			if($latest && $startTime > $latest){
				continue;
			}
			if($earliest && $startTime < $earliest){
				continue;
			}
			$withinRange[] = $m;
		}
		return $withinRange;
	}
	/**
	 * @return QMMeasurement[]
	 * @deprecated Stop using GetMeasurementRequest
	 */
	public function getMeasurementsInRequestedUnit(): array{
		$common = $this->getMeasurementsInCommonUnit();
		$unit = $this->getRequestedUnit();
		if(!$unit){
			return $common;
		}
		$measurements = [];
		foreach($common as $m){
			try {
				$m->convertUnit($unit);
			} catch (IncompatibleUnitException $e) {
				/** @var RuntimeException $e */
				throw $e;
			} catch (InvalidVariableValueException $e) {
				/** @var RuntimeException $e */
				throw $e;
			}
			$measurements[] = $m;
		}
		return $measurements;
	}
	private function validateFillingTimes(){
		$this->validateEarliestAndLatestTimes($this->earliestFillingTime, $this->latestFillingTime);
	}
	/**
	 * @param int $earliestFillingTime
	 * @return int
	 * @deprecated Stop using GetMeasurementRequest
	 */
	public function setEarliestFillingTime($earliestFillingTime = null): ?int{
		if($earliestFillingTime !== null){
			$this->earliestFillingTime = $earliestFillingTime;
			$this->logErrorIfEarliestFillingSameAsLatest();
		} elseif($this->getParamInArray([
				'earliestTaggedMeasurementTime',
				'startTime',
			]) !== null){
			$this->earliestFillingTime = (int)$this->getParamInArray([
				'earliestTaggedMeasurementTime',
				'startTime',
			]);
			$this->logErrorIfEarliestFillingSameAsLatest();
		} elseif($this->earliestMeasurementTime !== null){
			$this->earliestFillingTime = (int)$this->earliestMeasurementTime;
			$this->logErrorIfEarliestFillingSameAsLatest();
		} elseif(!$this->earliestFillingTime && $this->getQMUserVariable()){
			$variable = $this->getQMUserVariable();
			$at = $variable->getEarliestFillingAt();
			$this->earliestFillingTime = strtotime($at);
			$this->logErrorIfEarliestFillingSameAsLatest();
		}
		$earliestTaggedMeasurementTime = $this->getEarliestRawMeasurementTime();
		if($earliestTaggedMeasurementTime && $earliestTaggedMeasurementTime > $this->earliestFillingTime){
			$this->earliestFillingTime = $earliestTaggedMeasurementTime;
		}
		$this->logErrorIfEarliestFillingSameAsLatest();
		if($this->earliestFillingTime !== null){
			return (int)$this->earliestFillingTime;
		}
		return null;
	}
	/**
	 * @param $earliestTime
	 * @param $latestTime
	 */
	private function validateEarliestAndLatestTimes($earliestTime, $latestTime){
		if($latestTime < $earliestTime){
			$earliestTimeString = date('Y-m-d H:i:s', $earliestTime);
			$latestTimeString = date('Y-m-d H:i:s', $latestTime);
			le("latestTime $latestTimeString ($latestTime) is earlier than " .
				" earliestTime $earliestTimeString ($earliestTime)");
		}
	}
	/**
	 * @return string
	 * @deprecated Stop using GetMeasurementRequest
	 */
	public function getVariableName(): ?string{
		if($this->qmUserVariable){
			$name = $this->qmUserVariable->getVariableName();
		} else {
			$name = rawurldecode($this->getParam('variableName'));
		}
		return $this->variableName = $name;
	}
	/**
	 * @return QMQB
	 */
	protected function getQb(): QMQB{
		return $this->qb ?: $this->setQb();
	}
	private function logErrorIfEarliestFillingSameAsLatest(){
		if($this->earliestFillingTime === $this->latestFillingTime &&
			$this->getQMUserVariable()->getNumberOfMeasurements() > 1){
			$this->getQMUserVariable()
				->logError("Earliest filling time ($this->earliestFillingTime) is same as latest filling time ($this->latestFillingTime) and number of raw measurements is " .
					$this->getQMUserVariable()->getNumberOfMeasurements());
		}
	}
	/**
	 * @return int
	 * @deprecated Stop using GetMeasurementRequest
	 */
	private function getLatestMeasurementOrFillingTime(): ?int{
		$latest = $this->getLatestFillingTime();
		$latestMeasurement = $this->getLatestMeasurementTime();
		if($latestMeasurement){
			if(!$latest || $latestMeasurement < $latest){
				$latest = $latestMeasurement;
			}
		}
		return $latest;
	}
	/**
	 * @return int
	 * @deprecated Stop using GetMeasurementRequest
	 */
	private function getEarliestMeasurementOrFillingTime(): ?int{
		$earliest = $this->getEarliestFillingTime();
		$earliestMeasurement = $this->getEarliestMeasurementTime();
		if($earliestMeasurement){
			if(!$earliest || $earliestMeasurement > $earliest){
				$earliest = $earliestMeasurement;
			}
		}
		return $earliest;
	}
	/**
	 * @return QMDataSource|null
	 */
	private function getConnector(): ?QMDataSource{
		$nameOrId = $this->connectorName ?? $this->connectorId;
		if(!$nameOrId){
			return null;
		}
		return QMDataSource::getByNameOrId($nameOrId);
	}
	/**
	 * @return int|null|string
	 */
	private function getVariableNameOrId(){
		if($name = $this->getVariableName()){
			return $name;
		}
		if($id = $this->getVariableId()){
			return $id;
		}
		return null;
	}
	/**
	 * @return string
	 */
	private function handleDeleteRequest(): string{
		QMMeasurement::writable()->where('id', $this->getDeleteId())->update([
			Measurement::FIELD_DELETED_AT => date('Y-m-d H:i:s'),
			Measurement::FIELD_ERROR => __FUNCTION__,
		]);
		QMSlim::getInstance()->writeJsonWithGlobalFields(201, [
				'status' => 201,
				'success' => true,
				'message' => 'measurement deleted',
			]);
		return 'measurement deleted';
	}
	/**
	 * @return array
	 */
	private function handleDeleteOutsideRangeRequest(): array{
		QMLog::error("deleteOutsideAllowedRange");
		MeasurementValueProperty::deleteMeasurementsOutsideAllowedRangeForUser($this);
		QMSlim::getInstance()->writeJsonWithGlobalFields(201, [
				'status' => 201,
				'success' => true,
				'message' => 'measurements outside allowed range deleted',
			]);
		return [];
	}
	/**
	 * @return QMMeasurement[]
	 */
	public function handleGetRequest(): array{
		$this->setExcludeExtendedProperties(false);
		$uv = $this->getQMUserVariable();
		if((!$uv && $this->getUserId()) || $this->doNotConvert){
			$measurements = $this->getMeasurementsInRequestedUnit();
		} elseif($uv){
			if($this->getSort() && $this->getSort() !== 'startTime'){
				$measurements = $this->getMeasurementsInRequestedUnit();
			} else{
				$measurements = $uv->getMeasurementsWithTagsJoinsChildrenInUserOrProvidedUnit($this);
			}
		} else{
			le("Could not get measurements!");
		}
		SwaggerDefinition::addOrUpdateSwaggerDefinition($measurements, 'Measurement');
		if(APIHelper::apiVersionIsBelow(3)){
			ObjectHelper::addLegacyPropertiesToObjectsInArray($measurements);
		}
		foreach($measurements as $m){
			$m->roundIfRating();
		}
		return $measurements;
	}
}
