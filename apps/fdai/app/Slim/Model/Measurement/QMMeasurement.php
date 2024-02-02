<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Measurement;
use App\DataSources\QMConnector;
use App\DataSources\QMDataSource;
use App\Exceptions\BadRequestException;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidTimestampException;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoChangesException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ProtectedDatabaseException;
use App\Exceptions\QMException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\UserVariableNotFoundException;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Models\Application;
use App\Models\Measurement;
use App\Models\Variable;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseLatitudeProperty;
use App\Properties\Measurement\MeasurementIdProperty;
use App\Properties\Measurement\MeasurementLongitudeProperty;
use App\Properties\Measurement\MeasurementOriginalValueProperty;
use App\Properties\Measurement\MeasurementSourceNameProperty;
use App\Properties\Measurement\MeasurementStartAtProperty;
use App\Properties\Measurement\MeasurementStartTimeProperty;
use App\Properties\Measurement\MeasurementVariableIdProperty;
use App\Properties\Variable\VariableIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\QMUnit;
use App\Slim\QMSlim;
use App\Storage\DB\QMDB;
use App\Storage\DB\QMQB;
use App\Storage\Memory;
use App\Traits\HasModel\HasUserVariable;
use App\Types\ObjectHelper;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Units\YesNoUnit;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use App\Utils\GeoLocation;
use App\Utils\Stats;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use App\Variables\QMVariableCategory;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use LogicException;
use Throwable;
/** Class Measurement
 * @package App\Slim\Model
 */
class QMMeasurement extends AnonymousMeasurement {
	use HasUserVariable;
	const STRING_NO  = 'no';
	const STRING_YES = 'yes';
	protected $commonUnit;
	protected $date;
	protected $groupedMeasurements;
	protected $deletionReason;
	protected $hourNumber;
	protected $measurementSet;
	protected $measurementWithExtendedProperties;
	protected $monthNumber;
	protected $taggedVariable;
	protected $userVariable;
	protected $weekdayNumber;
	public $valueInCommonUnit;
	public $valueInUserUnit;
	public $clientId;
	public $connectionId;
	public $connectorId;
	public $connectorImportId;
	public $createdAt;
	public $duration;
	public $error;
	public $id;
	public $latitude;
	public $location;
	public $longitude;
	public $note;
	public $originalUnitId;
	public $originalValue;
	public $originalStartAt;
	public $sourceName;  // Provided source name, client id, connector name, or QMDataSource name should be in measurements.source_name column
	public $startAt;
	public $startTime;
	public $startTimeEpoch;
	public $originalUnit;
	public $unitAbbreviatedName;
	public $unitId;
	public $updatedAt;
	public $userId;
	public $userUnitId;
	public $userVariableId;
	public $value;
	public $valueUnitVariableName;
	public $variableCategoryId;
	public $variableName;
	public const ERROR_INVALID_COMBINATION_OPERATION = 'Combination operation "%s" is invalid, must be SUM or MEAN. Create a ticket at help.quantimo.do if you need assistance.';
	public const FIELD_CLIENT_ID                     = 'client_id';
	public const FIELD_CONNECTION_ID                 = 'connection_id';
	public const FIELD_CONNECTOR_ID                  = 'connector_id';
	public const FIELD_CONNECTOR_IMPORT_ID           = 'connector_import_id';
	public const FIELD_CREATED_AT                    = 'created_at';
	public const FIELD_DELETED_AT                    = 'deleted_at';
	public const FIELD_DURATION                      = 'duration';
	public const FIELD_ERROR                         = 'error';
	public const FIELD_ID                            = 'id';
	public const FIELD_LATITUDE                      = 'latitude';
	public const FIELD_LOCATION                      = 'location';
	public const FIELD_LONGITUDE                     = 'longitude';
	public const FIELD_NOTE                          = 'note';
	public const FIELD_ORIGINAL_UNIT_ID              = 'original_unit_id';
	public const FIELD_ORIGINAL_VALUE                = 'original_value';
	public const FIELD_SOURCE_NAME                   = 'source_name';
	public const FIELD_START_AT                      = 'start_at';
	public const FIELD_START_TIME                    = 'start_time';
	public const FIELD_UNIT_ID                       = 'unit_id';
	public const FIELD_UPDATED_AT                    = 'updated_at';
	public const FIELD_USER_ID                       = 'user_id';
	public const FIELD_USER_VARIABLE_ID              = 'user_variable_id';
	public const FIELD_VALUE                         = 'value';
	public const FIELD_VARIABLE_CATEGORY_ID          = 'variable_category_id';
	public const FIELD_VARIABLE_ID                   = 'variable_id';
	public const TABLE                               = 'measurements';
	public const DB_FIELD_NAME_TO_PROPERTY_NAME_MAP  = [
		self::FIELD_NOTE => 'additionalMetaData',
	];
	/**
	 * @var bool
	 */
	protected $valid;
	/**
	 * Measurement constructor.
	 * @param null $timeAt
	 * @param float|null $originalValue
	 * @param AdditionalMetaData|null $note
	 * @param $row
	 * @param null $originalUnitNameOrId
	 */
	public function __construct($timeAt = null, float $originalValue = null, $note = null, $row = null,
		$originalUnitNameOrId = null){
		if($timeAt instanceof CarbonInterface){
			$timeAt = (string)$timeAt;
		}
		if(is_object($timeAt)){
			$row = $timeAt;
			$this->setVariousStartTimes($row->startTime, $row);
			$this->value = $row->value;
			return;
		}
		if($row){
			parent::__construct($row);
		}
		$this->setVariousStartTimes($timeAt, $row);
		if($originalValue !== null){ // Don't overwrite if populated by row
			if($this->originalValue === null){
				$this->originalValue = $originalValue;
			}
		}
		if($note){ // Too much memory to set AdditionalMetaData all the time
			$this->setNoteAndAdditionalMetaData($note);
		}
		if($originalUnitNameOrId){
			$this->setOriginalUnitByNameOrId($originalUnitNameOrId);
		}
	}
	/**
	 * @return array
	 */
	public static function getDeprecatedProperties(): array{
		return [
			'timestamp',
			'startTime',
			'startTimeString',
		];
	}
	/**
	 * @param QMMeasurement[]|Measurement[] $combined
	 * @return array
	 */
	public static function indexMeasurementsByStartAt(array $combined): array{
		$byStartAt = [];
		foreach($combined as $m){
			$byStartAt[$m->getStartAtAttribute()] = $m;
		}
		return $byStartAt;
	}
	/**
	 * @return string
	 */
	public static function getApplicationName(): ?string{
		$app = QMSlim::getInstance();
		if($app && $app->request && $app->request->get('appName')){
			return $app->request->get('appName');
		}
		if($clientId = BaseClientIdProperty::fromRequest(false)){
			return Application::whereClientId($clientId)->value('name');
		}
		return null;
	}
	/**
	 * @param int $userId
	 * @return string
	 */
	public static function exportAllDailyMeasurementsMatrixToCsv(int $userId): string{
		$variables = QMUserVariable::getUserVariables($userId, [
			'limit' => 0,
			//'variableId' => "(ne)5955693"
		]);
		$earliest = time();
		$latest = 0;
		$header[] = 'Date';
		$variablesWithMeasurements = [];
		$matrix = [];
		foreach($variables as $v){
			$header[] = $v->name;
			$v->logInfo("getDailyMeasurementsWithTags...");
			$measurements = $v->getValidDailyMeasurementsWithTags();
			foreach($measurements as $m){
				$matrix[gmdate('Y-m-d', $m->startTime)][$m->variableName] = $m->value;
				if($earliest > $m->startTime){
					$earliest = $m->startTime;
				}
				if($latest < $m->startTime){
					$latest = $m->startTime;
				}
			}
			$variablesWithMeasurements[] = $v;
		}
		$csvFilePath = FileHelper::absPath("/tmp/Measurements-from-QuantiModo-for-$userId.txt");
		$fp = fopen($csvFilePath, 'wb');
		$delimiter = chr(9); // chr(9) means "\t".  See https://www.php.net/manual/en/function.fputcsv.php#91473
		fputcsv($fp, $header, $delimiter);
		$currentTime = $earliest;
		while($currentTime < $latest){
			$row = [];
			$row[] = gmdate('Y-m-d', $currentTime);
			foreach($variablesWithMeasurements as $v){
				if(isset($matrix[gmdate('Y-m-d', $currentTime)][$v->name])){
					$row[] = $matrix[gmdate('Y-m-d', $currentTime)][$v->name];
				} else{
					$row[] = null;
				}
			}
			fputcsv($fp, $row, $delimiter);
			$currentTime += 86400;
		}
		fclose($fp);
		return $csvFilePath;
	}
	/**
	 * @param int $userId
	 * @param array|object $body
	 * @return array
	 * @throws \App\Exceptions\ModelValidationException
	 * @throws \App\Exceptions\NoChangesException
	 */
	public static function getAndUpdateMeasurement(int $userId, $body): array{
		if($id = MeasurementIdProperty::pluckOrDefault($body)){
			$m = Measurement::findInMemoryOrDB($id);
			if(!$m || $m->user_id !== $userId){
				throw new NotFoundException("Measurement with id $id not found. ");
			}
		} else{
			$start = MeasurementStartAtProperty::pluckOrDefault($body);
			$variableId = MeasurementVariableIdProperty::pluckOrDefault($body);
			$m = Measurement::whereUserId($userId)->where(Measurement::FIELD_START_AT, $start)
				->where(Measurement::FIELD_VARIABLE_ID, $variableId)->first();
			if(!$m){
				throw new NotFoundException("Measurement not found. ");
			}
		}
		$m->fill($body);
		$m->save();
		return [$m->getDBModel()];
	}
	/**
	 * @param int $userId
	 * @param QMMeasurement[] $combined
	 * @return array
	 * @throws \App\Exceptions\IncompatibleUnitException
	 * @throws \App\Exceptions\InvalidAttributeException
	 * @throws \App\Exceptions\InvalidVariableValueException
	 * @throws \App\Exceptions\ModelValidationException
	 * @throws \App\Exceptions\NoChangesException
	 */
	public static function bulkInsert(int $userId, array $combined): array{
		Memory::addNewMeasurements($combined);
		$data = [];
		foreach($combined as $m){
			$m->userId = $userId;
			try {
				$data[] = $m->getBulkInsertArray();
			} catch (IncompatibleUnitException | InvalidVariableValueException | InvalidAttributeException $e) {
				le($e);
			}
		}
		try {
			$result = Measurement::batchInsert($data);
		} catch (\Throwable $e) {
			$mess = QMStr::truncate($e->getMessage(), 240);
			if(stripos($mess, 'Duplicate entry') !== false){
				QMLog::info("Inserting measurements individually because " . $mess);
				foreach($combined as $i){
					$i->save();
				}
				return [];
			} elseif(stripos($mess, 'Insert value list does not match column list') !== false){
				QMLog::info("Inserting measurements individually because I guess some had notes and some didn't: " .
					$mess);
				foreach($combined as $i){
					$i->save();
				}
				return [];
			} else{
				/** @var LogicException $e */
				throw $e;
			}
		}
		return $result;
	}
	/**
	 * @param $apiVersionNumber
	 * @return array
	 */
	public static function getLegacyPropertiesToAdd($apiVersionNumber = null): array{
		// legacy => current
		$legacyProperties = [];
		if(!isset($apiVersionNumber)){
			$apiVersionNumber = APIHelper::getApiVersion();
		}
		if($apiVersionNumber < 1){
			$legacyProperties = array_merge($legacyProperties, [
				'source' => 'sourceName',
				'variable' => 'variableName',
				'unit' => 'unitAbbreviatedName',
				'timestamp' => 'startTime'
				// Required by MoodiModo Android
			]);
		}
		return $legacyProperties;
	}
	/**
	 * @return array
	 */
	public static function getLegacyRequestParams(): array{
		// legacy => current
		$legacyRequestParams = [
			'timestamp' => 'startTime',
			'variable' => 'variableName',
			'unit' => 'unitAbbreviatedName',
			'source' => 'sourceName',
			'name' => 'variableName',
			'categoryName' => 'variableCategoryName',
			'category' => 'variableCategoryName',
			'createdTime' => 'createdAt',
			'abbreviatedUnitName' => 'unitAbbreviatedName',
			'endTime' => 'latestMeasurementTime',
			'startTime' => 'earliestMeasurementTime',
			'endDate' => 'latestMeasurementTime',
			'startDate' => 'earliestMeasurementTime',
			'updatedSince' => 'updatedAt',
		];
		//return array_merge($legacyRequestParams, self::getLegacyProperties());  // We can't do this because startTime is a homonym
		return $legacyRequestParams;
	}
	/**
	 * @param QMMeasurement[] $all
	 * @return QMMeasurement[]
	 */
	public static function removeInvalid(array $all): array{
		$filtered = [];
		foreach($all as $m){
			if($m->isInValid()){
				$invalidValues[] = $m;
				continue;
			}
			$filtered[] = $m;
		}
		return $filtered;
	}
	/**
	 * @param QMMeasurement[] $all
	 * @param int|string $latest
	 * @return QMMeasurement[]
	 */
	public static function removeAfter(array $all, $latest): array{
		$filtered = [];
		$latest = TimeHelper::universalConversionToUnixTimestamp($latest);
		foreach($all as $m){
			if($latest && $latest < $m->startTime){
				$outsideTimeRange[] = $m;
				continue;
			}
			$filtered[] = $m;
		}
		return $filtered;
	}
	/**
	 * @param QMMeasurement[] $all
	 * @param int|string $earliest
	 * @param int|string $latest
	 * @return QMMeasurement[]
	 */
	public static function filter(array $all, $earliest, $latest): array{
		$filtered = QMMeasurement::removeInvalid($all);
		if($latest){
			$filtered = QMMeasurement::removeAfter($filtered, $latest);
		}
		if($earliest){
			$filtered = QMMeasurement::removeBefore($filtered, $earliest);
		}
		return $filtered;
	}
	/**
	 * @param QMMeasurement[] $all
	 * @param int|string $earliest
	 * @return QMMeasurement[]
	 */
	public static function removeBefore(array $all, $earliest): array{
		$filtered = [];
		$earliest = TimeHelper::universalConversionToUnixTimestamp($earliest);
		foreach($all as $m){
			if($earliest && $earliest > $m->startTime){
				$outsideTimeRange[] = $m;
				continue;
			}
			$filtered[] = $m;
		}
		return $filtered;
	}
	/**
	 * @param QMMeasurement[]|QMMeasurementExtended[] $measurements
	 * @param int|string $providedUnitNameOrId
	 * @return QMMeasurement[]
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public static function convertToProvidedUnit(array $measurements, $providedUnitNameOrId): array{
		$converted = [];
		$toUnit = QMUnit::getByNameOrId($providedUnitNameOrId);
		foreach($measurements as $m){
			$m->convertUnit($toUnit);
			$converted[] = $m;
		}
		return $converted;
	}
	/**
	 * @return string
	 */
	public function getCreatedAt(): ?string{
		if(!isset($this->createdAt)){
			$this->logError("createdAt not set!");
			return null;
		}
		return $this->createdAt;
	}
	/**
	 * @return QMCommonVariable
	 */
	public function getCommonVariable(): QMCommonVariable{
		return QMCommonVariable::findByNameIdOrSynonym($this->getVariableIdAttribute());
	}
	/**
	 * @return string
	 */
	public function getUpdatedAt(): string{
		return $this->updatedAt;
	}
	/**
	 * @return bool|int
	 * @throws UserVariableNotFoundException
	 */
	public static function handleDeleteRequest(){
		$variableNameOrId = VariableIdProperty::nameOrIdFromRequest(true);
		$startTime = MeasurementStartTimeProperty::fromRequest();
		if(!$startTime){
			throw new BadRequestException('Please provide startTime for measurement to delete');
		}
		$userId = QMAuth::id();
		$v = QMUserVariable::getByNameOrId($userId, $variableNameOrId);
		$qb = self::writable()->where('user_id', $userId)->where(self::FIELD_START_TIME, $startTime)
			->where('variable_id', $v->getVariableIdAttribute())->whereNull('deleted_at');
		$measurementToDelete = $qb->first();
		if(!$measurementToDelete){
			$rounded = $v->roundStartTime($startTime);
			if($rounded !== $startTime){
				$qb = self::writable()->where('user_id', $userId)->where(self::FIELD_START_TIME, $rounded)
					->where('variable_id', $v->getVariableIdAttribute())->whereNull('deleted_at');
				$measurementToDelete = $qb->first();
			}
		}
		if(!isset($measurementToDelete->start_time)){
			throw new QMException(400, 'Could not find measurement to delete!', [
				'startTime' => $startTime,
				'variableId' => $v->getVariableIdAttribute(),
			]);
		}
		return $qb->softDelete([], __FUNCTION__);
	}
	/**
	 * @param int $unitId
	 */
	public function setUnitId(int $unitId){
		if($this->unitId === $unitId){
			return;
		}
		$this->unitId = $unitId;
		QMUnit::addUnitNames($this);
	}
	/**
	 * @return float
	 */
	public function getValue(): float{
		$value = $this->value;
		if($value === null){
			le("No value!");
		}
		return (float)$value;
	}
	/**
	 * @param int $significantFigures
	 * @return float
	 */
	public function getRoundedValue(int $significantFigures): float{
		return Stats::roundByNumberOfSignificantDigits($this->getValue(), $significantFigures);
	}
	/**
	 * @return int|null
	 */
	public function getDuration(): ?int{
		return $this->duration ?? null;
	}
	/**
	 * @param int $variableId
	 */
	public function setVariableId(int $variableId): void{
		$this->variableId = $variableId;
	}
	/**
	 * @param int|string|QMVariableCategory $cat
	 * @return QMVariableCategory
	 */
	public function setVariableCategory($cat): QMVariableCategory{
		$cat = QMVariableCategory::find($cat);
		$this->variableCategoryId = $cat->id;
		return $cat;
	}
	/**
	 * @return array
	 */
	public function toSpreadsheetRow(): array{
		return [
			'Value' => $this->getValue(),
			'Abbreviated Unit Name' => $this->getUnitAbbreviatedName(),
			'Variable Name' => $this->getVariableName(),
			'Measurement Event Time' => $this->getStartAt(),
			'Note' => $this->getAdditionalMetaData()->getMessage(),
		];
	}
	/**
	 * @return QMMeasurementExtended
	 */
	public function getExtended(): QMMeasurementExtended{
		if($this->measurementWithExtendedProperties){
			return $this->measurementWithExtendedProperties;
		}
		return $this->measurementWithExtendedProperties = new QMMeasurementExtended($this, $this->getQMUserVariable());
	}
	/**
	 * @return int
	 */
	public function getVariableCategoryId(): int{
		if(!$this->variableCategoryId){
			le('!$this->variableCategoryId');
		}
		return $this->variableCategoryId;
	}
	/**
	 * @return QMVariableCategory
	 */
	public function getQMVariableCategory(): QMVariableCategory{
		return QMVariableCategory::find($this->getVariableCategoryId());
	}
	/**
	 * @param QMMeasurement $m
	 */
	private function addGroupedMeasurement(QMMeasurement $m): void{
		$existing = $this->groupedMeasurements;
		$time = $m->getStartAt();
		$name = $m->getTagVariableNameOrVariableName();
		$previous = $existing[$time][$name] ?? null;
		if($previous){
			le("Why are we adding another measurement with same start time and variable $previous");
		}
		$this->groupedMeasurements[$time][$name] = clone $m;
	}
	/**
	 * @param array $group
	 */
	public function addGroupedMeasurements(array $group){
		$sourceNames = [];
		$notes = [];
		$originalValues = [];
		foreach($group as $m){
			$this->addGroupedMeasurement($m);
			$source = $m->getSourceNameOrClientId();
			if($source && !in_array($source, $sourceNames, true)){
				$sourceNames[] = $source;
			}
			if(!empty($m->additionalMetaData) && !empty($m->getAdditionalMetaData()->getMessage())){
				$notes[] = $m->getAdditionalMetaData()->getMessage();
			}
			if(!is_string($m->originalValue) && $m->originalValue !== null){
				$originalValues[] = $m->originalValue;
			}
		}
		if($sourceNames){
			$this->sourceName = implode(', ', $sourceNames);
		}
		if($notes){
			$this->note = implode(', ', $notes);
		}
		if($originalValues){
			$this->originalValue = implode(', ', $originalValues);
		}
		$this->id = null;
	}
	/**
	 * @return AdditionalMetaData
	 */
	public function getAdditionalMetaData(): AdditionalMetaData{
		$d = $this->additionalMetaData;
		if(empty($d)){
			$d = new AdditionalMetaData($this->note ?? null);
		}
		if(is_string($d)){
			$d = new AdditionalMetaData($d);
		}
		$this->additionalMetaData = $d;
		return $d;
	}
	/**
	 * @return string
	 */
	public function getSentence(): string{
		$grouped = $this->getGroupedMeasurements();
		if(count($grouped) > 1){
			$groupSentences = [];
			foreach($grouped as $m){
				$groupSentences[] = $m->getSentence();
			}
			$sentence = implode(", \n", $groupSentences);
		} else{
			$valueAndUnit = $this->getValueUnitString();
			$variableDisplayName = $this->getVariableName();
			$since = TimeHelper::timeSinceHumanString($this->getOrSetStartTime());
			$sentence = "$valueAndUnit $variableDisplayName recorded " . $since;
			$meta = $this->getAdditionalMetaData();
			if($meta && $meta->getMessage()){
				$sentence .= " (" . $meta->getMessage() . ")";
			}
		}
		return $sentence;
	}
	/**
	 * @return QMMeasurement[]
	 */
	public function getGroupedMeasurements(): array{
		if(!$this->groupedMeasurements){
			return [$this];
		}
		return Arr::flatten($this->groupedMeasurements);
	}
	/**
	 * @return QMDataSource|null
	 */
	public function getDataSource(): ?QMDataSource{
		if(!$this->connectorId){
			return null;
		}
		return $this->getUser()->getDataSource($this->connectorId);
	}
	/**
	 * @return string
	 */
	public function getDataSourceName(): string{
		$name = $this->sourceName;
		if($name){
			return $name;
		}
		if($this->connectorId){
			return $this->getDataSource()->getTitleAttribute();
		}
		return $this->getSourceNameOrClientId();
	}
	/**
	 * @return int
	 */
	public function getConnectorId(): ?int{
		return $this->connectorId;
	}
	/**
	 * @param $reason
	 * @param $hard
	 */
	public function delete(string $reason, bool $hard){
		$userVariable = $this->getQMUserVariable();
		$qb = self::writable()->where(self::FIELD_ID, $this->getId());
		$message = "-deleting $this->value" . $this->getQMUnit()->abbreviatedName .
			" measurement for $userVariable measurement";
		if(isset($this->createdAt)){
			$message .= " created " . TimeHelper::YYYYmmddd($this->getCreatedAt());
		}
		$message .= " because $reason";
		$message = $hard ? "Hard$message" : "Soft$message";
		if(!$this->getUser()->isTestUser()){
			$this->logError($message);
		}
		if($hard){
			$qb->delete($this->getId());
		} else{
			$qb->update([
				self::FIELD_DELETED_AT => date('Y-m-d H:i:s'),
				self::FIELD_ERROR => $reason,
			]);
		}
	}
	/**
	 * @return string
	 */
	public function getSourceNameOrClientId(): ?string{
		if(!empty($this->sourceName)){
			return $this->sourceName;
		}
		if(!empty($this->clientId)){
			return $this->clientId;
		}
		return null;
	}
	/**
	 * @param bool $useAbbreviatedName
	 * @param int $sigFigs
	 * @return string
	 */
	public function getValueUnitString(bool $useAbbreviatedName = false, int $sigFigs = 3): string{
		$v = Stats::roundByNumberOfSignificantDigits($this->value, $sigFigs);
		if(!$this->unitId && $this->originalUnit){
			return $this->getOriginalUnit()->getValueAndUnitString($v, $useAbbreviatedName);
		}
		$u = $this->getQMUnit();
		if(!$u){
			le("No unit!");
		}
		return $u->getValueAndUnitString($v, $useAbbreviatedName);
	}
	/**
	 * @return bool
	 */
	public function roundIfRating(): bool{
		if($this->isRating()){
			$this->setValueInCommonUnit(round($this->value));
			return true;
		}
		return false;
	}
	/**
	 * @return bool
	 */
	public function wasLessThan24HoursAgo(): bool{
		return time() < $this->getOrSetStartTime() + 86400;
	}
	/**
	 * @param QMUnit $toUnit
	 * @throws InvalidVariableValueException
	 * @throws IncompatibleUnitException
	 */
	public function convertUnit(QMUnit $toUnit): void{
		$fromUnit = $this->getQMUnit();
		if($fromUnit->id === $toUnit->id){
			return;
		}
		$newValue = QMUnit::convertValue($this->value, $fromUnit, $toUnit, $this->getQMVariable());
		$this->value = $newValue;
		$this->setUnitId($toUnit->id);
	}
	/**
	 * @return QMMeasurement
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function inUserUnit(): QMMeasurement{
		$userUnit = $this->getUserUnit();
		$commonUnit = $this->getCommonUnit();
		if($userUnit->id === $commonUnit->id){
			return $this;
		}
		$inUserUnit = clone $this;
		$inUserUnit->convertUnit($userUnit);
		return $inUserUnit;
	}
	/**
	 * @throws ProtectedDatabaseException
	 */
	public static function truncate(){
		if(!AppMode::isTestingOrStaging() || QMDB::dbIsProductionOrStaging()){
			le("Can't truncate measurements!");
		}
		static::writable()->truncate();
	}
	/**
	 * @param array $rows
	 * @return static[]
	 */
	public static function instantiateArray(array $rows): array{
		$measurements = [];
		foreach($rows as $row){
			$measurement = new static(null, null, null, $row);
			$measurements[] = $measurement;
		}
		return $measurements;
	}
	/**
	 * @param bool $instantiate
	 * @return QMQB
	 */
	public static function qb(bool $instantiate = false): QMQB{
		$qb = self::readonly()
			->join(Variable::TABLE, Variable::TABLE . '.' . Variable::FIELD_ID, '=',
				self::TABLE . '.' . self::FIELD_VARIABLE_ID);
		self::addFields($qb);
		QMCommonVariable::addImportantFieldsCamelized($qb, 'variable');
		if($instantiate){
			$qb->class = self::class;
		}
		return $qb;
	}
	/**
	 * @param QMQB $qb
	 * @param string|null $prefix
	 */
	public static function addFields(QMQB $qb, string $prefix = null){
		$fields = static::getColumns();
		foreach($fields as $field){
			$propertyName = $prefix ? static::getPropertyNameForDbField($prefix . '_' .
				$field) : static::getPropertyNameForDbField($field);
			$qb->columns[] = static::TABLE . '.' . $field . " as " . $propertyName;
		}
	}
	/**
	 * @param string $reason
	 * @param bool $countFirst
	 * @return int
	 */
	public function hardDelete(string $reason, bool $countFirst = false): int{
		$result = static::writable()->where(static::FIELD_ID, $this->getId())->hardDelete($reason, $countFirst);
		return $result;
	}
	/**
	 * @param QMUserVariable $newVariable
	 * @param string $reason
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueAttributeException
	 * @throws InvalidVariableValueException
	 * @throws NoChangesException
	 */
	public function changeVariable(QMUserVariable $newVariable, string $reason){
		$purchaseMeasurement = new QMMeasurement($this->startTime, $this->value, $this->note);
		$purchaseMeasurement->populate($this);
		$purchaseMeasurement->setUserVariable($newVariable);
		$purchaseMeasurement->setOriginalUnitByNameOrId($newVariable->getCommonUnit()->getId());
		$newVariable->addToMeasurementQueue($purchaseMeasurement);
		$newVariable->saveMeasurements();
		$this->hardDelete($reason, true);
	}
	/**
	 * @param int $durationInSeconds
	 */
	public function setDuration(int $durationInSeconds){
		$this->duration = $durationInSeconds;
	}
	/**
	 * @param string|float $valueInCommonUnit
	 * @return float
	 */
	public function setValueInCommonUnit($valueInCommonUnit): float{
		if($valueInCommonUnit === null){
			le("Null value provided!");
		}
		if(is_string($valueInCommonUnit) && strpos($valueInCommonUnit, "$") === 0){
			$valueInCommonUnit = str_replace("$", "", $valueInCommonUnit);
			$this->setOriginalUnit(QMUnit::getDollars());
		}
		if(!$this->valueInCommonUnit){
			$this->valueInCommonUnit = (float)$valueInCommonUnit;
		}
		return $this->value = (float)$valueInCommonUnit;
	}
	/**
	 * @param QMUserVariable $v
	 */
	public function setUserVariable(QMUserVariable $v): void{
		$this->userVariable = $v;
		$this->variableId = $v->getVariableIdAttribute();
		$this->userId = $v->getUserId();
		$this->variableName = $v->name;
		$this->userVariableId = $v->getUserVariableId();
		$this->userUnitId = $v->getUnitIdAttribute();
	}
	/**
	 * @param int $connectorId
	 */
	public function setConnectorIdAndSourceName(int $connectorId): void{
		$this->connectorId = $connectorId;
		$this->getConnectionId();
		$c = QMDataSource::find($connectorId);
		$this->setSourceName($c->getTitleAttribute());
		if(!$this->clientId){
			$this->clientId = $c->clientId;
		}
	}
	/**
	 * @param string $location
	 */
	public function setLocation(string $location){
		$this->location = $location;
	}
	/**
	 * @param string $sourceName
	 */
	public function setSourceName(string $sourceName){
		$this->sourceName = $sourceName;
	}
	/**
	 * @param string $error
	 */
	public function setError(string $error): void{
		$this->error = $error;
	}
	/**
	 * @return QMUserVariable
	 */
	public function getQMUserVariable(): QMUserVariable{
		/** @var QMUserVariable $v */
		if($v = $this->userVariable){
			if(!$v->unitAbbreviatedName){
				le('!$v->unitAbbreviatedName');
			}
			return $v;
		}
		$userId = $this->getUserId();
		$nameOrId = $this->getVariableNameOrId();
		try {
			$v = QMUserVariable::getByNameOrId($userId, $nameOrId);
		} catch (UserVariableNotFoundException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
		}
		if(!$v){
			$v = QMUserVariable::findUserVariableByNameIdOrSynonym($userId, $nameOrId);
		}
		return $this->userVariable = $v;
	}
	/**
	 * @param $row
	 * @param bool $overwrite
	 * @throws InvalidTimestampException
	 */
	public function populate($row, bool $overwrite = true){
		//ObjectHelper::populate($row, $this, $overwrite);  // Can't use this because it doesn't populate private
		foreach(ObjectHelper::getNonNullValuesWithCamelKeys($row) as $key => $value){
			if(!isset($this->$key) || $overwrite){
				$this->$key = $value;
			}
			if(is_string($value) && in_array($key, ["unitAbbreviatedName", "unit"])){
				$this->setOriginalUnitByNameOrId($value);
			}
		}
		MeasurementOriginalValueProperty::setAttributeBySynonyms($this, $row);
		$this->setAndValidateStartTime();
		if(!$this->userId){
			try {
				$this->userId = QMAuth::getQMUser()->id;
			} catch (UnauthorizedException $e) {
				le($e);
				throw new \LogicException();
			}
		}
		if(empty($this->note)){
			$this->note = null;
		} else{
			$this->getAdditionalMetaData()->setMessage($this->note);
		}
		if(empty($this->clientId)){
			$this->clientId = BaseClientIdProperty::fromRequest(false);
		}
	}
	/**
	 * @throws InvalidTimestampException
	 */
	public function setAndValidateStartTime(){
		if(!isset($this->startTime) && isset($this->startTime)){
			$this->setStartTime($this->startTime);
		}
		if(!isset($this->startTime) && isset($this->timestamp)){
			$this->setStartTime($this->timestamp);
		}
		$this->validateStartTimeAndFallbackToCurrentTimeOnProductionApiRequest();
	}
	/**
	 * @param int $userId
	 * @param int $variableId
	 * @param int $startTime
	 * @return QMMeasurement
	 */
	public static function getByStartTimeVariableId(int $userId, int $variableId, int $startTime): QMMeasurement{
		$row = QMMeasurement::writable()->where(Measurement::FIELD_USER_ID, $userId)
			->where(Measurement::FIELD_VARIABLE_ID, $variableId)->where(Measurement::FIELD_START_TIME, $startTime)
			->first();
		if(!$row){
			throw new NotFoundException("Measurement not found!");
		}
		$m = static::instantiateIfNecessary($row);
		return $m;
	}
	/**
	 * @return float
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueAttributeException
	 */
	public function getValueInCommonUnit(): float{
		$val = $this->value;
		if($val !== null){
			return $val;
		}
		$val = $this->valueInCommonUnit;
		if($val !== null){
			return $val;
		}
		$originalValue = $this->originalValue;
		if($originalValue === null){
			return $this->value; // We got it from database without originalValue
		}
		$originalUnit = $this->getOriginalUnit();
		$variable = $this->getQMUserVariable();
		$commonUnit = $variable->getCommonUnit();
		try {
			$converted = QMUnit::convertValue($originalValue, $originalUnit, $commonUnit, $this->getQMVariable());
			return $this->setValueInCommonUnit($converted);
		} catch (InvalidVariableValueException $e) {
			throw new InvalidVariableValueAttributeException($this->l(), Measurement::FIELD_VALUE, $originalValue,
				$e->getMessage());
		}
	}
	/**
	 * @return MeasurementSet
	 */
	public function getMeasurementSet(): ?MeasurementSet{
		return $this->measurementSet;
	}
	/**
	 * @return int|string
	 */
	private function getVariableNameOrId(){
		if($this->variableId){
			return $this->variableId;
		}
		return $this->variableName ?? $this->name ?? null;
	}
	/**
	 * @return QMUnit
	 */
	public function getOriginalUnit(): QMUnit{
		if($unit = $this->originalUnit){
			return $unit;
		}
		$id = $this->originalUnitId ?? $this->userUnitId ?? $this->unitId;
		$unit = QMUnit::find($id);
		return $this->originalUnit = $unit;
	}
	/**
	 * @return int
	 */
	public function getRoundedStartTime(): int{
		$v = $this->getQMUserVariable();
		$startTime = $this->getOrSetStartTime();
		return $v->roundStartTime($startTime);
	}
	/**
	 * @param int $seconds
	 * @return int
	 */
	public function roundStartTime(int $seconds): int{
		$startTime = $this->getOrSetStartTime();
		return Stats::roundToNearestMultipleOf($startTime, $seconds);
	}
	/**
	 * @return string
	 */
	public function getRoundedStartAt(): string{
		return db_date($this->getRoundedStartTime());
	}
	/**
	 * @param int|string $timeAt
	 * @return int
	 * @throws InvalidTimestampException
	 */
	public function setStartTime($timeAt): int{
		$timeAt = TimeHelper::universalConversionToUnixTimestamp($timeAt);
		if($timeAt < 86400 * 365 * 20){
			$this->logErrorOrInfoIfTesting("startTime $timeAt is earlier than 1980");
		}
		if($this->userVariable && $this->getQMUserVariable()->getMinimumAllowedSecondsBetweenMeasurements() === 86400){
			$date = TimeHelper::YYYYmmddd($timeAt);
			$timeAt = strtotime($date);
		}
		$this->startAt = db_date($timeAt);
		return $this->startTimeEpoch = $this->startTime = (int)$timeAt;
	}
	/**
	 * @return string
	 */
	public function getOrSetClientId(): ?string{
		if(!empty($this->clientId)){
			return $this->clientId;
		}
		if($clientId = $this->measurementSet->clientId ?? null){
			return $this->clientId = $clientId;
		}
		return $this->clientId = BaseClientIdProperty::fromRequest(false);
	}
	/**
	 * @param string|AdditionalMetaData|object $meta
	 */
	public function setAdditionalMetaData($meta){
		if(!$meta instanceof AdditionalMetaData){
			if(is_string($meta)){
				$meta = new AdditionalMetaData(null, $meta);
			} else{
				$meta = new AdditionalMetaData($meta);
			}
		}
		$this->additionalMetaData = $meta;
		$this->getNote();
	}
	/**
	 * @param string $message
	 */
	public function setMessage(string $message){
		$this->getAdditionalMetaData()->setMessage($message);
	}
	/**
	 * @return float
	 */
	public function getLatitude(): ?float{
		if(!empty($this->latitude)){
			return $this->latitude;
		}
		$set = $this->getMeasurementSet();
		if(!$set){
			return null;
		}
		$params = [];
		return $this->latitude = BaseLatitudeProperty::getDefault($params);
	}
	/**
	 * @param float $latitude
	 */
	public function setLatitude(float $latitude){
		$this->latitude = $latitude;
	}
	/**
	 * @return float
	 */
	public function getLongitude(): ?float{
		if(!empty($this->longitude)){
			return $this->longitude;
		}
		$set = $this->getMeasurementSet();
		if($set){
			$this->longitude = MeasurementLongitudeProperty::getDefault($set->variableParameters);
		}
		return $this->longitude;
	}
	/**
	 * @param float $longitude
	 */
	public function setLongitude(float $longitude){
		$this->longitude = $longitude;
	}
	/**
	 * @param string $imageUrl
	 */
	public function setImageUrl(string $imageUrl){
		$this->getAdditionalMetaData()->setImage($imageUrl);
	}
	/**
	 * @param string $url
	 */
	public function setUrl(string $url){
		$this->getAdditionalMetaData()->setUrl($url);
	}
	/**
	 * @param string|int|QMUnit $unitOrNameOrId
	 * @return null|QMUnit
	 */
	public function setOriginalUnitByNameOrId($unitOrNameOrId): ?QMUnit{
		if($unitOrNameOrId){
			if(is_string($unitOrNameOrId)){
				try {
					$unit = QMUnit::findByNameOrSynonym($unitOrNameOrId);
					return $this->setOriginalUnit($unit);
				} catch (BadRequestException $e) {
					le($e);
					throw new \LogicException();
				}
			}
			if(is_int($unitOrNameOrId)){
				return $this->setOriginalUnit(QMUnit::getUnitById($unitOrNameOrId));
			}
			return $this->setOriginalUnit($unitOrNameOrId);
		}
		return null;
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataString(): string{
		$userVariable = $this->userVariable;
		if($this->startAt){
			$at = $this->startAt;
		} elseif($this->startTime){
			$at = db_date($this->startTime);
		} else{
			$at = "startTime not set";
		}
		$str = "measurement at $at: ";
		if($userVariable){
			$str = "$userVariable " . $str;
		} else{
			$str = "$this->variableName " . $str;
		}
		return $str;
	}
	/**
	 * @return int
	 */
	public function getUnitIdAttribute(): ?int{
		$id = $this->unitId;
		if(!$id && $this->unitAbbreviatedName){
			$id = QMUnit::findByNameIdOrSynonym($this->unitAbbreviatedName)->id;
		}
		if(!$id){
			$id = $this->getQMUserVariable()->getCommonUnitId();
		}
		return $this->unitId = $id;
	}
	/**
	 * @return int
	 */
	public function getVariableIdAttribute(): ?int{
		if($id = $this->variableId){
			return $id;
		}
		return $this->variableId = $this->getQMUserVariable()->getVariableIdAttribute();
	}
	/**
	 * @return int
	 */
	public function getUserId(): ?int{
		if($this->userId){
			return $this->userId;
		}
		if($this->userVariable){
			return $this->userId = $this->getQMUserVariable()->getUserId();
		}
		$set = $this->getMeasurementSet();
		if(!$set->userId){
			le("no user id ", $this);
		}
		return $this->userId = $set->userId;
	}
	/**
	 * @param int $userId
	 */
	public function setUserId(int $userId){
		$this->userId = $userId;
	}
	/**
	 * @return string
	 */
	public function getOrSetSourceName(): ?string{
		$name = MeasurementSourceNameProperty::pluckOrDefault($this);
		if(!$name && AppMode::isApiRequest()){
			$name = MeasurementSourceNameProperty::fromRequest(false);
		}
		return $this->sourceName = $name;
	}
	/**
	 * @return QMUnit
	 */
	public function getCommonUnit(): QMUnit{
		if($unit = $this->commonUnit){
			return $unit;
		}
		$id = $this->getCommonUnitId();
		return $this->commonUnit = QMUnit::getByNameOrId($id);
	}
	/**
	 * @return string
	 */
	public function getLocation(): ?string{
		$location = $this->location;
		if($location){
			return $location;
		}
		$set = $this->getMeasurementSet();
		if(!$set){
			return null;
		}
		$params = [];
		$location = GeoLocation::getLocationFromArrayOrRequest($params);
		return $this->location = $location;
	}
	/**
	 * @return int|null
	 */
	private function getOrSetConnectorId(): ?int{
		if($this->connectorId){
			return $this->connectorId;
		}
		/** @var MeasurementSet $set */
		$set = $this->getMeasurementSet();
		if($set){
			$this->connectorId = $set->connectorId;
			$this->getConnectionId();
		}
		return $this->connectorId;
	}
	/**
	 * @return bool
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 * @throws InvalidAttributeException
	 * @throws ModelValidationException
	 * @throws NoChangesException
	 * @noinspection PhpDocRedundantThrowsInspection
	 */
	public function save(): bool{
		$this->beforeInsertOrUpdate();
		if(!$this->dbRow){
			$this->getDbRowFromDatabaseOrUserVariable();
		}
		try {
			return parent::save();
		} catch (Throwable $e) {
			$this->findLaravelModel();
			return parent::save();
		}
	}
	/**
	 * @return bool|object|void
	 */
	public function getDbRowFromDatabaseOrUserVariable(){
		$v = $this->getQMUserVariable();
		if($v->measurementsAreSet()){
			$measurements = $v->getQMMeasurements();
			foreach($measurements as $m){
				if($m->id === $this->id){
					return $this->setDbRow($m);
				}
				$mStart = $m->startTime;
				$thisStart = $this->startTime;
				if($mStart === $thisStart){
					return $this->setDbRow($m);
				}
			}
			return $this->setDbRow(false);
		}
		$qb = self::readonly();
		$qb->where(self::FIELD_USER_ID, $this->getUserId());
		if($this->id){
			$qb->where(self::FIELD_ID, $this->id);
		} else{
			$qb->where(self::FIELD_VARIABLE_ID, $this->getVariableIdAttribute());
			$qb->where(self::FIELD_START_TIME, $this->getRoundedStartTime());
		}
		$this->logInfo(__FUNCTION__ .
			" for single measurement to make sure we aren't overwriting and duplicating measurement insertions");
		$row = $qb->first();
		$this->setDbRow($row ?? false);
		return $row;
	}
	/**
	 * @param $timeStringOrUnixEpochSeconds
	 * @param $row
	 */
	private function setVariousStartTimes($timeStringOrUnixEpochSeconds, $row): void{
		if($timeStringOrUnixEpochSeconds){
			$this->setStartTime($timeStringOrUnixEpochSeconds);
		}
		$startTime = $this->startTime ?? $this->startTimeEpoch ?? null;
		if(!$startTime && isset($row->startDate)){
			$startTime = strtotime($row->startDate);
		}
		$this->startTimeEpoch = $this->startTime = $startTime;
	}
	/**
	 * @return array
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 * @throws InvalidAttributeException
	 */
	public function getBulkInsertArray(): array{
		$this->beforeInsertOrUpdate();
		$arr = $this->toDbInsertionArray();
		$arr[self::FIELD_START_AT] = db_date($arr[self::FIELD_START_TIME]);
		$str = json_encode($this->getAdditionalMetaData());
		if($str && $str !== "{}"){
			$arr[self::FIELD_NOTE] = $str;
		}
		$arr[self::FIELD_UPDATED_AT] = $this->updatedAt = now_at();
		if(!$this->createdAt){
			$this->createdAt = now_at();
		}
		$arr[self::FIELD_CREATED_AT] = $this->createdAt;
		return $arr;
	}
	/**
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 * @throws InvalidAttributeException
	 */
	private function beforeInsertOrUpdate(): void{
		$this->validate();
		$v = $this->getQMUserVariable();
		$convertedValue = $this->getValueInCommonUnit();
		$v->addToLastValuesInCommonUnitArray($convertedValue);
		$this->getLatitude();
		$this->getLocation();
		$this->getLongitude();
		$this->getOrSetClientId();
		$this->getOrSetConnectorId();
		$this->getOrSetSourceName();
		$this->startAt = db_date($this->getOrSetStartTime());
		$this->originalUnitId = $this->getOriginalUnit()->id;
		$this->setValueInCommonUnit($convertedValue);
		$this->unitId = $v->getCommonUnit()->id;
		$this->variableCategoryId = $v->getQMVariableCategory()->getId();
		$this->variableId = $v->getVariableIdAttribute();
		if($this->additionalMetaData){
			$this->getAdditionalMetaData()->compress();
		}
		//$this->userVariableId = $this->getUserVariable()->getVariableId();
	}
	/**
	 * @return void
	 */
	public function validateStartTimeAndFallbackToCurrentTimeOnProductionApiRequest(): void{
		$timestampUpperLimit = time() + 8 * 86400; // Set upper limit to a week in the future
		$timestampLowerLimit = 10 * 365 * 86400; // Lower limit is equal to 01/01/1980
		$time = $this->getOrSetStartTime();
		if($time > $timestampUpperLimit){
			$message = 'startTime ' . date('Y-m-d H:i:s', $time) . ' too far in the future ';
			if(AppMode::isProductionApiRequest()){
				$this->logError($message . ' so setting to current time and saving anyway');
				$this->setStartTime(time());
			}
			throw new InvalidTimestampException($message);
		}
		if($time < $timestampLowerLimit && AppMode::isProduction()){
			$message = 'startTime ' . date('Y-m-d H:i:s', $time) . ' too far in the past ';
			if(AppMode::isProductionApiRequest()){
				$this->logError($message . ' so setting to current time and saving anyway');
				$this->setStartTime(time());
			}
			throw new InvalidTimestampException($message);
		}
	}
	/**
	 * @return string
	 */
	public function getImage(): string{
		if($this->additionalMetaData){
			$img = $this->getAdditionalMetaData()->getImage();
			if($img){
				return $img;
			}
		}
		if($this->userVariable){
			return $this->getQMUserVariable()->getImage();
		}
		return $this->getQMVariableCategory()->getImage();
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		$ext = $this->getExtended();
		return $ext->getUrl();
	}
	/**
	 * @return QMVariable
	 */
	public function getQMVariable(): QMVariable{
		return $this->getQMUserVariable();
	}
	/**
	 * @param array $arr
	 * @param string|null $tableAlias
	 * @param string|null $propertyPrefix
	 * @return array
	 */
	public static function addSelectFields(array $arr, string $tableAlias = null, string $propertyPrefix = null): array{
		//$arr = CommonVariable::addSelectFields($arr); // TODO: Fix me
		return parent::addSelectFields($arr, $tableAlias, $propertyPrefix);
	}

    /**
     * @throws InvalidAttributeException
     * @throws InvalidVariableValueAttributeException
     */
	public function validate(): void {
		if(!$this->connectorId){
			$this->getDataSource();
		}
		$this->validateValue();
		$this->validateStartTimeAndFallbackToCurrentTimeOnProductionApiRequest();
	}
	/**
	 * @return int|null
	 */
	public function getConnectionId(): ?int{
		$id = $this->getConnectorId();
		if(!$id){
			return null;
		}
		$u = $this->getUser();
		$connection = $u->findConnectionByConnectorId($id);
		if($connection){
			return $this->connectionId = $connection->getId();
		}
		return null;
	}
	/**
	 * @param array $meta
	 * @return array
	 */
	public function getLogMetaData(?array $meta = []): array{
		$meta[self::FIELD_START_AT] = $this->getStartAt();
		$meta[self::FIELD_VALUE] = $this->getValueUnitString(true);
		$variable = $this->variableName ?? $this->variableId;
		if($variable){
			$meta['variable'] = $variable;
		}
		return $meta;
	}
	/**
	 * @return Measurement
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function l(){
		if($this->laravelModel){
			return $this->laravelModel;
		}
		if($this->id){
			$l = new Measurement();
			$l->exists = true;
			foreach($this as $key => $value){
				if($value === null){
					continue;
				}
				if($camel = static::getDbFieldNameForProperty($key)){
					$l->setAttribute($camel, $value);
				}
			}
			$l->syncOriginal();
			return $this->laravelModel = $l;
		}
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return parent::l();
	}
	/**
	 * @return Measurement
	 */
	public function firstOrNewLaravelModel(): Measurement{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return parent::firstOrNewLaravelModel();
		//        if($m = $this->laravelModel){return $m;}
		//        if($this->hasId()){if($m = $this->firstLaravelModel()){return $m;}}
		//        return $this->newLaravelModel();
	}
	/**
	 * @param $noteOrJsonMetaData
	 */
	public function setNoteAndAdditionalMetaData($noteOrJsonMetaData): void{
		$this->additionalMetaData = new AdditionalMetaData($noteOrJsonMetaData);
		$this->getNote();
	}
	public function getNote(): ?string{
		$meta = $this->getAdditionalMetaData();
		return $this->note = $meta->toHumanString();
	}
	/**
	 * @return float
	 * @throws \App\Exceptions\IncompatibleUnitException
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 * @throws \App\Exceptions\InvalidVariableValueException
	 */
	public function getValueInUserUnit(): float{
		if($this->valueInUserUnit !== null){
			return $this->valueInUserUnit;
		}
		$userUnitId = $this->getUserUnitId();
		$thisUnitId = $this->unitId = (int)$this->unitId;
		$inCommonUnit = $this->getValueInCommonUnit();
		if($userUnitId === $thisUnitId){
			return $this->valueInUserUnit = $inCommonUnit;
		}
		return $this->valueInUserUnit =
			QMUnit::convertValue($inCommonUnit, $thisUnitId, $userUnitId, $this->getQMVariable());
	}
	public function getUserUnitId(): int{
		if($this->userUnitId !== null){
			return $this->userUnitId;
		}
		return $this->userUnitId = $this->getQMUserVariable()->getUnitIdAttribute();
	}
	/**
	 * @param QMUnit $originalUnit
	 * @return QMUnit
	 */
	public function setOriginalUnit(QMUnit $originalUnit): QMUnit{
		$this->originalUnitId = $originalUnit->id;
		$this->originalUnit = $originalUnit;
		$val = $this->originalValue;
		if($originalUnit->id === YesNoUnit::ID && is_string($val)){
			$this->setOriginalValue(YesNoUnit::toNumber($val));
		}
		return $originalUnit;
	}
	/**
	 * @param float|string $originalValue
	 * @return float
	 */
	public function setOriginalValue($originalValue): float{
		$unitId = $this->originalUnitId;
		if($unitId === YesNoUnit::ID){
			$originalValue = YesNoUnit::toNumber($originalValue);
		}
		return $this->originalValue = $originalValue;
	}
	/**
	 * @return float
	 */
	public function getOriginalValue(): float{
		return $this->originalValue;
	}
	public function isInValid(): bool{
		$valid = $this->valid;
		if($valid !== null){
			return $valid;
		}
		$uv = $this->getQMUserVariable();
		$val = $this->value;
		$message = $uv->valueInvalidForCommonVariableOrUnit($val, 'measurement', $this->duration);
		return $this->valid = (bool)$message;
	}
	public function getCommonUnitId(): int{
		if($unit = $this->commonUnit){
			return $unit->id;
		}
		return $this->unitId;
	}
	/**
	 * @return float
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueAttributeException
	 */
	function getDBValue(): float{
		return $this->getValueInCommonUnit();
	}
	public function getVariable(): Variable{
		return Variable::findInMemoryOrDB($this->getVariableIdAttribute());
	}
	public function getConnector(): QMConnector{
		return QMConnector::find($this->connectorId);
	}
	/**
	 * @return int
	 */
	public function getUserVariableId(): int{
		return $this->userVariableId;
	}

    public function getVariableId(): int
    {
        return $this->variableId;
    }

    /**
	 * @param string $message
	 * @throws InvalidVariableValueAttributeException
	 */
	protected function throwInvalidVariableValueException(string $message){
		throw new InvalidVariableValueAttributeException($this->l(), Measurement::FIELD_VALUE, $this->value, $message);
	}
}
