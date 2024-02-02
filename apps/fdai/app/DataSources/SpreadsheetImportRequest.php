<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources;
use App\Astral\ConnectionBaseAstralResource;
use App\DataSources\Connectors\Exceptions\ConnectorDisabledException;
use App\DataSources\Connectors\Exceptions\RecentImportException;
use App\DevOps\XDebug;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\InvalidFilePathException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\NoChangesException;
use App\Exceptions\NoEmailAddressException;
use App\Exceptions\NoGeoDataException;
use App\Exceptions\NoVariableCategoryException;
use App\Exceptions\UnrecognizedSpreadsheetFormatException;
use App\Files\Spreadsheet\QMSpreadsheet;
use App\Logging\QMLog;
use App\Logging\SolutionButton;
use App\Models\Connection;
use App\Models\MeasurementImport;
use App\Models\User;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Connection\ConnectionConnectStatusProperty;
use App\Properties\Unit\UnitNameProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Slim\Model\Measurement\AdditionalMetaData;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\QMUnit;
use App\Slim\Model\QMUserRelatedModel;
use App\Slim\Model\User\QMUser;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\Memory;
use App\Storage\S3\S3PrivateUsers;
use App\Traits\HasModel\HasDataSource;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Utils\AppMode;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariableCategory;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use LogicException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Tests\TestGenerators\ImportTestFiles;
use Throwable;

/** Class SpreadsheetImport
 * @package App\Slim\Model
 */
class SpreadsheetImportRequest extends QMUserRelatedModel {
	use HasDataSource;
    protected $importEndedAt; // Not found on App\DataSources\SpreadsheetImportRequest
    protected $importStartedAt; // Not found on App\DataSources\SpreadsheetImportRequest
    protected $reasonForImport; // Not found on App\DataSources\SpreadsheetImportRequest
    private $commonVariables;
	private $errors;
	private $numberOfFailedRows = 0;
	private $failedRows = [];
	private $numberOfMeasurementsInserted;
	private $rowNumber;
	private $spreadsheetData;
	private $spreadsheetHeaderRow;
	private $spreadsheetImporter;
	private $spreadsheetRow;
	private $successfulRows = 0;
	private $timestampIndex;
	private $unitAbbreviatedNameIndex;
	private $usedIndices;
	private $userVariables;
	private $valueIndex;
	private $variableCategoryIndex;
	private $variableCategoryName;
	private $variableNameIndex;
	public $connectorId;
	public $errorMessage;
	public $file;
	public $sourceName;
	public $status;
	public $userId;
	public $internalErrorMessage;
	public $userErrorMessage;
	public const FIELD_CLIENT_ID                         = 'client_id';
	public const FIELD_CREATED_AT                        = 'created_at';
	public const FIELD_DELETED_AT                        = 'deleted_at';
	public const FIELD_ERROR_MESSAGE                     = 'error_message';
	public const FIELD_FILE                              = 'file';
	public const FIELD_ID                                = 'id';
	public const FIELD_IMPORT_ENDED_AT                   = 'import_ended_at';
	public const FIELD_IMPORT_STARTED_AT                 = 'import_started_at';
	public const FIELD_INTERNAL_ERROR_MESSAGE            = 'internal_error_message';
	public const FIELD_REASON_FOR_IMPORT                 = 'reason_for_import';
	public const FIELD_SOURCE_NAME                       = 'source_name';
	public const FIELD_STATUS                            = 'status';
	public const FIELD_TYPE_timestamp                    = 'timestamp';
	public const FIELD_TYPE_unitName                     = 'unitName';
	public const FIELD_TYPE_value                        = 'value';
	public const FIELD_TYPE_variableName                 = 'variableName';
	public const FIELD_UPDATED_AT                        = 'updated_at';
	public const FIELD_USER_ERROR_MESSAGE                = 'user_error_message';
	public const FIELD_USER_ID                           = 'user_id';
	public const STATUS_ERROR                            = 'ERROR';
	public const STATUS_IMPORTING                        = "IMPORTING";
	public const STATUS_NEVER_UPLOADED                   = 'NEVER_UPLOADED';
	public const STATUS_UPDATED                          = 'UPDATED';
	public const STATUS_WAITING                          = 'WAITING';
	public const TABLE                                   = 'measurement_imports';
	public const SECONDS_BETWEEN_IMPORTS                 = 1.5 * 86400;
	public const MINIMUM_SECONDS_BETWEEN_IMPORT_ATTEMPTS = 2 * 3600;
	public const LARAVEL_CLASS                           = MeasurementImport::class;
	/**
	 * SpreadsheetImport constructor.
	 * @param $row
	 * @param null $spreadsheetData
	 */
	public function __construct($row = null, $spreadsheetData = null){
		if(!$row){
			return;
		}
		$this->spreadsheetData = $spreadsheetData;
		$this->errorMessage = '';
		foreach($row as $key => $value){
			$this->$key = $value;
		}
	}
	/**
	 * @param string $status
	 * @return Builder
	 */
	public static function whereStatus(string $status): Builder{
		/** @var MeasurementImport $lClass */
		$lClass = static::getLaravelClassName();
		return $lClass::whereStatus($status);
	}
	/**
	 * @throws UnrecognizedSpreadsheetFormatException
	 */
	private function setIndices(): void{
		$this->usedIndices = [];
		$this->setValueIndex();
		$this->setTimestampIndex();
		$this->setVariableCategoryIndex(); // Let's just use the connector default category for now
		$this->setUnitIndex();
		$this->setVariableNameIndex();
	}
	/**
	 * @return int
	 */
	private function setVariableNameIndex(): int{
		return $this->variableNameIndex = $this->getColumnIndex([
			'prescription',
			// MedHelper
			'category',
			// Mint
			'variable name',
			'variable',
			//'description', // Mint (Too Many Variables)
		], 'variableName');
	}
	/**
	 * @return int|null
	 * @throws UnrecognizedSpreadsheetFormatException
	 */
	private function setValueIndex(): int{
		$i = $this->valueIndex = $this->getColumnIndex([
			'actual dosage',
			// MedHelper
			'amount',
			// Mint
			'value',
		]);
		if(!$i){
			throw new UnrecognizedSpreadsheetFormatException($this);
		}
		return $i;
	}
	/**
	 * @return int
	 */
	private function setVariableCategoryIndex(): ?int{
		return $this->variableCategoryIndex = $this->getColumnIndex([
			'category',
			// Mint
			'variable category name',
			'variable category',
		]);
	}
	/**
	 * @return int
	 */
	private function setUnitIndex(): ?int{
		return $this->unitAbbreviatedNameIndex = $this->getColumnIndex([
			'unit',
			// Mint
			'unit name',
			'unit abbreviated name',
			'abbreviated unit name',
		]);
	}
	/**
	 * @return int
	 */
	private function setTimestampIndex(): int{
		return $this->timestampIndex = $this->getColumnIndex([
			'actual time',
			// MedHelper
			'date',
			// Mint
			'Measurement Event Time',
		]);
	}
	protected function setStatusImporting(): void{
		$this->updateDbRow([
			self::FIELD_STATUS => self::STATUS_IMPORTING,
			self::FIELD_ERROR_MESSAGE => null,
			self::FIELD_IMPORT_STARTED_AT => now_at(),
		]);
	}
	/**
	 * @return AdditionalMetaData
	 */
	private function getNote(): AdditionalMetaData{
		$i = 0;
		$note = new AdditionalMetaData();
		while(!empty($this->spreadsheetHeaderRow[$i])){
			if(!empty($this->spreadsheetRow[$i]) && !in_array($i, $this->usedIndices, true)){
				$fieldName = $this->spreadsheetHeaderRow[$i];
				$note->$fieldName = $this->spreadsheetRow[$i];
			}
			$i++;
		}
		return $note;
	}
	/**
	 * Set status for measurement import record
	 * @param string $errorMessage
	 */
	public function setStatusError(string $errorMessage){
		if(!$errorMessage){
			$errorMessage = $this->errorMessage;
		}
		$this->updateDbRow([
			self::FIELD_STATUS => static::STATUS_ERROR,
			self::FIELD_ERROR_MESSAGE => $errorMessage,
		]);
		$this->addErrorMessage($errorMessage);
	}
	/**
	 * @param array $arr
	 * @param string|null $reason
	 * @return int
	 * @deprecated Use Eloquent model save directly
	 */
	public function updateDbRow(array $arr, string $reason = null): int{
		$importer = $this->getSpreadsheetImporter();
		$importer->populateByDbFieldNames($arr, true);
		if(isset($arr[self::FIELD_STATUS])){
			$importer->updateStatus = $arr[self::FIELD_STATUS];
		}
		if(isset($arr[self::FIELD_ERROR_MESSAGE])){
			$importer->updateError = $arr[self::FIELD_ERROR_MESSAGE];
		}
		return parent::updateDbRow($arr);
	}
	/**
	 * Set status for measurement import record
	 */
	public function setStatusCompleted(){
		$successRate = $this->numberOfFailedRows . " out of " . ($this->numberOfFailedRows + $this->successfulRows) .
			" rows failed to import.  $this->numberOfMeasurementsInserted measurements saved to database.";
		if($this->numberOfFailedRows || !$this->numberOfMeasurementsInserted){
			$status = self::STATUS_ERROR;
			$this->errorMessage = $successRate . $this->errorMessage;
			foreach($this->errors as $error){
				$this->errorMessage .= ".\n" . $error;
			}
		} else{
			$status = self::STATUS_UPDATED;
			$this->errorMessage = $this->numberOfMeasurementsInserted . " rows imported";
		}
		$this->updateDbRow([
			self::FIELD_STATUS => $status,
			self::FIELD_ERROR_MESSAGE => $this->errorMessage,
			self::FIELD_IMPORT_ENDED_AT => now_at(),
		]);
	}
	/**
	 * @param [] $spreadsheetRow
	 * @return string
	 */
	private function getVariableName(): ?string{
		$variableName = $this->spreadsheetRow[$this->variableNameIndex];
		$note = $this->getNote();
		/** @noinspection MissingIssetImplementationInspection */
		if(isset($note->transactionType)){
			if($note->transactionType === 'credit'){
				$variableName = "Income from " . $variableName;
			} else{
				$variableName = VariableNameProperty::toSpending($variableName);
			}
		}
		if(strlen($variableName) < 2){
			$this->addRowError("Could not find a variable name", $this->rowNumber);
			return false;
		}
		if(VariableNameProperty::isStupid($variableName)){
			return false;
		}
		return $variableName;
	}
	/**
	 * @param string $message
	 * @param int $rowNumber
	 */
	private function addRowError(string $message, int $rowNumber){
		$this->numberOfFailedRows++;
		$error = "$message for row $rowNumber";
		$this->errors[] = $error;
		$this->failedRows[] = $this->spreadsheetRow;
		QMLog::errorOrInfoIfTesting($error . json_encode($this->spreadsheetRow), ['row' => $this->spreadsheetRow]);
	}
	/**
	 * @return bool
	 */
	private function weShouldStop(): bool{
		$duration = Memory::getDurationInSeconds();
		if(XDebug::active()){
			return false;
		}
		if($duration > 10 && AppMode::isTestingOrStaging()){
			Memory::getDurationInSeconds("$this Import");
			QMLog::info("Stopping import early because we exceeded test time limit");
			return true;
		}
		return false;
	}
	/**
	 * @param string|null $reason
	 * @throws InvalidFilePathException
	 * @throws NoChangesException
	 * @throws NoVariableCategoryException
	 * @throws UnrecognizedSpreadsheetFormatException
	 */
	public function import(string $reason = null): void{
		$this->getPHPUnitTestUrl();
		$message = "Importing spreadsheet uploaded " . $this->getCreatedAt() . " because:
		$reason... ";
		try {
			$this->logInfo($message . $this->getQMUser()->getMailToLink());
		} catch (NoEmailAddressException | InvalidEmailException $e) {
			$this->logInfo($message);
		}
		$this->setQMSpreadsheetImporter();
		$this->setStatusImporting();
		$this->getSpreadSheetData();
		$this->setIndices();
		Memory::setStartTime();
		$this->rowNumber = 0;
		foreach($this->spreadsheetData as $row){
			$this->spreadsheetRow = $row;
			$this->rowNumber++;
			if($this->weShouldStop()){
				break;
			}
			if(!$this->getVariableName()){
				continue;
			}
			if(!$this->getStartTime()){
				continue;
			}
			try {
				$v = $this->getUserVariable();
			} catch (NoVariableCategoryException $e) {
				$this->setStatusError($e->getMessage() . " for row $this->rowNumber: " . \App\Logging\QMLog::print_r($row, true));
				return;
			}
			if(!$v){
				continue;
			}
			$m = $this->getMeasurement();
			if($this->rowNumber % 100 == 0){
				$m->logInfo("Parsed $this->rowNumber rows...");
			}
			try {
				//if($m->value === null){le("value is null");}
				$v->addToMeasurementQueue($m);
			} catch (InvalidAttributeException $e) {
				$this->addRowError($e->getMessage(), $this->rowNumber);
				continue;
			}
			$this->successfulRows++;
			if($this->successfulRows > 100 && AppMode::isTestingOrStaging()){
				break;
			}
		}
		if($this->userVariables){
			$this->saveMeasurements();
			$this->setStatusCompleted();
		} else{
			$this->setStatusError("No measurements could be imported");
		}
	}
	/**
	 * @return int
	 */
	public function getUserId(): ?int{
		return $this->userId;
	}
	/**
	 * @return bool|QMUserVariable
	 * @throws NoVariableCategoryException
	 */
	private function getUserVariable(){
		if(isset($this->userVariables[$this->getVariableName()])){
			return $this->userVariables[$this->getVariableName()];
		}
		$params = $this->getNewVariableParameters();
		$userVariable =
			QMUserVariable::findOrCreateByNameOrIdOrSynonym($this->getUserId(), $this->getVariableName(), [], $params);
		return $this->userVariables[$this->getVariableName()] = $userVariable;
	}
	/**
	 * @return array
	 * @throws NoVariableCategoryException
	 */
	private function getNewVariableParameters(): array{
		$params = [];
		$params['variableCategoryName'] = $this->getVariableCategoryNameFromSpreadsheetRowOrConnector();
		$unit = $this->getUnitAbbreviatedName();
		if($unit){
			$params['unitName'] = $unit;
		}
		$params['sourceName'] = $this->getSpreadsheetImporter()->displayName;
		return $params;
	}
	/**
	 * @return QMSpreadsheetImporter
	 */
	public function getSpreadsheetImporter(): QMSpreadsheetImporter{
		if(!$this->spreadsheetImporter){
			return $this->setQMSpreadsheetImporter();
		}
		return $this->spreadsheetImporter;
	}
	/**
	 * @return bool|QMCommonVariable
	 */
	public function getCommonVariable(){
		$name = $this->getVariableName();
		$cv = $this->commonVariables[$name] ?? QMCommonVariable::find($name);
		if(!$cv){
			$cv = false;
		}
		return $this->commonVariables[$name] = $cv;
	}
	/**
	 * @return string
	 * @throws NoVariableCategoryException
	 * @internal param string $variableName
	 * @internal param $ [] $spreadsheetRow
	 */
	private function getUnitAbbreviatedName(): ?string{
		if($i = $this->unitAbbreviatedNameIndex){
			$unitName = $this->spreadsheetRow[$i];
			if($unit = QMUnit::find($unitName)){
				return $unit->abbreviatedName;
			}
		}
		if($unit = $this->getUnitNameFromSpreadsheetRow()){
			return $unit;
		}
		$name = $this->getVariableName();
		if($unit = $this->getUnitNameFromVariableName($name)){
			return $unit;
		}
		if($cv = $this->getCommonVariable()){
			return $cv->getCommonUnit()->abbreviatedName;
		}
		$si = $this->getSpreadsheetImporter();
		if($unit = $si->defaultUnitAbbreviatedName){
			return $unit;
		}
		$cat = $this->getQMVariableCategory();
		if($unit = $cat->defaultUnitAbbreviatedName){
			return $unit;
		}
		$this->getUnitNameFromSpreadsheetRow();
		$this->setErrorMessage("Please provide Abbreviated Unit Name column for " . $this->getVariableName() .
			".  Available units are: " . UnitNameProperty::getList());
		return null;
	}
	/**
	 * @param $message
	 */
	public function setErrorMessage($message){
		QMLog::errorOrInfoIfTesting($message);
		$this->errorMessage = $message;
	}
	/**
	 * @return string
	 * @throws NoVariableCategoryException
	 */
	private function getVariableCategoryName(): string{
		if($this->variableCategoryName){
			return $this->variableCategoryName;
		}
		$variable = $this->getUserVariable();
		if($variable){
			return $variable->variableCategoryName;
		}
		return $this->getVariableCategoryNameFromSpreadsheetRowOrConnector();
	}
	/**
	 * @return string
	 * @throws NoVariableCategoryException
	 */
	private function getVariableCategoryNameFromSpreadsheetRowOrConnector(): string{
		if($this->variableCategoryIndex){
			$cat = $this->spreadsheetRow[$this->variableCategoryIndex];
			if($cat){
				$variableCategory = QMVariableCategory::findByNameOrSynonym($cat, false);
				if($variableCategory){
					return $this->variableCategoryName = $variableCategory->name;
				}
			}
		}
		$i = 0;
		foreach($this->spreadsheetRow as $value){
			if($value && $category = QMVariableCategory::findByNameOrSynonym($value, false)){
				$this->usedIndices[] = $i;
				return $this->variableCategoryName = $category->name;
			}
			$i++;
		}
		$cat = $this->getSpreadsheetImporter()->defaultVariableCategoryName;
		if(!$cat){
			throw new NoVariableCategoryException();
		}
		return $this->variableCategoryName = $cat;
	}
	/**
	 * @return QMVariableCategory
	 * @throws NoVariableCategoryException
	 */
	private function getQMVariableCategory(): QMVariableCategory{
		return QMVariableCategory::findByNameOrSynonym($this->getVariableCategoryName());
	}
	/**
	 * @return string|null
	 * @throws NoVariableCategoryException
	 */
	private function getUnitNameFromSpreadsheetRow(): ?string{
		$i = 0;
		foreach($this->spreadsheetRow as $value){
			if(QMUnit::getUnitByFullName($value)){
				$this->usedIndices[] = $i;
				return QMUnit::getUnitByFullName($value)->abbreviatedName;
			} // Preference to longer more unique names
			$i++;
		}
		$i = 0;
		foreach($this->spreadsheetRow as $value){
			if(strtolower($value) === "s" && $this->getVariableCategoryName() === TreatmentsVariableCategory::NAME){
				continue; // Avoids always using letter S as seconds in MedHelper
			}
			if(!empty($value) && QMUnit::getUnitByAbbreviatedName($value)){
				$this->usedIndices[] = $i;
				return QMUnit::getUnitByAbbreviatedName($value)->abbreviatedName;
			}
			$i++;
		}
		return null;
	}
	/**
	 * @param string $variableName
	 * @return string|null
	 */
	private function getUnitNameFromVariableName(string $variableName): ?string{
		$variableWords = explode(' ', $variableName);
		$tryLast = $variableWords[count($variableWords) - 1];
		$tryLast = preg_replace('/[0-9]+/', '', $tryLast);
		$unit = QMUnit::findByNameOrSynonym($tryLast, false);
		if($unit){
			return $unit->abbreviatedName;
		}
		//return 'count';
		return null;
	}
	/**
	 * @return array|null
	 * @throws InvalidFilePathException
	 */
	public function setSpreadsheetData(): ?array{
		$s3Path = $this->file;
		try {
			$path = S3PrivateUsers::downloadAndDecryptByS3Path($s3Path);
			$this->logInfo("Downloaded to\n$path");
		} catch (FileNotFoundException $e) {
			le($e);
		}
		$data = $this->getDataFromSpreadsheetFile($path);
		if(!$data){
			$this->setStatusError("Could not get spreadsheetData for S3 path $s3Path ");
		}
		@unlink($path);
		foreach($data as $key => $row){
			if($key === 0){
				$this->spreadsheetHeaderRow = $row;
				continue;
			}
			if(!array_filter($row)){
				$this->logInfo("All columns are empty!");
				break;
			}
			$this->spreadsheetData[] = $row;
		}
		return $this->spreadsheetData;
	}
	/**
	 * @return array
	 * @throws InvalidFilePathException
	 */
	private function getSpreadSheetData(): ?array{
		return $this->spreadsheetData ?: $this->setSpreadsheetData();
	}
	/**
	 * @return QMSpreadsheetImporter
	 */
	private function setQMSpreadsheetImporter(): QMSpreadsheetImporter{
		$importer = QMSpreadsheetImporter::getByNameAndUserId($this->sourceName, $this->getUserId());
		if(!$importer){
			$this->connectorId = null;
			$this->addErrorMessage("Could not find connector with name " . $this->sourceName);
		} else{
			$this->connectorId = $importer->id;
		}
		return $this->spreadsheetImporter = $importer;
	}
	/**
	 * @param array $headerNamesInOrderOfPreference
	 * @param null $valueType
	 * @return int|null
	 */
	private function getColumnIndex(array $headerNamesInOrderOfPreference, $valueType = null): ?int{
		if(!count($headerNamesInOrderOfPreference)){
			throw new BadRequestHttpException("Please provide headerNamesInOrderOfPreference");
		}
		$header = $this->spreadsheetHeaderRow;
		foreach($header as $index => $string){
			if(QMStr::isCaseInsensitiveMatchInArray($string, $headerNamesInOrderOfPreference)){
				$this->usedIndices[] = $index;
				return $index;
			}
		}
		if($valueType){
			for($index = 0, $iMax = count($header); $index < $iMax; $index++){
				if($this->exampleValueMatchesType($index, $valueType)){
					$this->usedIndices[] = $index;
					return $index;
				}
			}
		}
		$this->addErrorMessage("Could not find a header matching any of the following: " .
			implode(', ', $headerNamesInOrderOfPreference));
		return null;
	}
	/**
	 * @param string $errorMessage
	 * @param array $meta
	 */
	private function addErrorMessage(string $errorMessage, array $meta = []){
		$this->logError($errorMessage, $meta);
		$this->errors[] = $errorMessage;
	}
	/**
	 * @param $i
	 * @return mixed
	 */
	private function getExampleValue($i){
		return $this->spreadsheetData[1][$i];
	}
	/**
	 * @param $i
	 * @param $type
	 * @return bool
	 */
	private function exampleValueMatchesType($i, $type): bool{
		$value = $this->getExampleValue($i);
		if($type === self::FIELD_TYPE_unitName){
			if(!is_string($value)){
				return false;
			}
			if(QMUnit::findByNameOrSynonym($value)){
				return true;
			}
		}
		if(($type === self::FIELD_TYPE_timestamp) && TimeHelper::isDate($value)){
			return true;
		}
		if($type === self::FIELD_TYPE_variableName){
			if(TimeHelper::isDate($value)){
				return false;
			}
			if(QMUnit::findByNameOrSynonym($value)){
				return false;
			}
			if(is_string($value)){
				return true;
			}
		}
		if($type === self::FIELD_TYPE_value){
			if(TimeHelper::isDate($value)){
				return false;
			}
			if(is_numeric($value)){
				return true;
			}
		}
		return false;
	}
	/**
	 * @return QMQB
	 */
	public static function readonly(): QMQB{
		return ReadonlyDB::getBuilderByTable(self::TABLE);
	}
	/**
	 * @return mixed
	 */
	private function getStartTime(){
		$startTime = $this->spreadsheetRow[$this->timestampIndex];
		if(empty($startTime)){
			$this->addRowError("Could not find a valid timestamp", $this->rowNumber);
			return false;
		}
		return $startTime;
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function getUserVariables(): array{
		return $this->userVariables;
	}
	/**
	 * @return int
	 * @throws NoChangesException
	 */
	private function saveMeasurements(): int{
		$variables = $this->getUserVariables();
		foreach($variables as $v){
			try {
				$new = $v->saveMeasurements($this->getSpreadsheetImporter()->getId());
				$this->numberOfMeasurementsInserted += count($new);
			} catch (IncompatibleUnitException | InvalidVariableValueException $e) {
				le($e);
			}
		}
		return $this->numberOfMeasurementsInserted;
	}
	/**
	 * @return QMMeasurement
	 * @throws NoVariableCategoryException
	 */
	private function getMeasurement(): QMMeasurement{
		$value = $this->spreadsheetRow[$this->valueIndex];
		$m = new QMMeasurement($this->getStartTime(), $value, $this->getNote());
		$m->setSourceName($this->getSpreadsheetImporter()->getTitleAttribute());
		$u = $this->getUnitAbbreviatedName();
		$si = $this->getQMSpreadsheetImporter();
		try {
			$si->validateUnit($u);
		} catch (\Throwable $e) {
			$u = $this->getUnitAbbreviatedName();
			$si->validateUnit($u);
		}
		$m->setOriginalUnitByNameOrId($u);
		return $m;
	}
	public function getQMSpreadsheetImporter(): QMSpreadsheetImporter{
		return $this->getQMDataSource();
	}
	/**
	 * @param string $path
	 * @return array
	 */
	private function getDataFromSpreadsheetFile(string $path): array{
		QMLog::info("Reading $path spreadsheet...");
		/** Load $inputFileName to a PHPExcel Object  **/
		try {
			$spreadsheetData = QMSpreadsheet::getDataFromSpreadsheet($path, false);
		} catch (\Throwable $e) {
			$this->setStatusError("This spreadsheet does not appear to be a valid csv, xls, or xlsx file.  Please try uploading again. " .
				$e->getMessage());
			/** @var LogicException $e */
			throw $e;
		}
		return $spreadsheetData;
	}
	/**
	 * @return mixed
	 */
	public function getSourceName(): string{
		return $this->sourceName;
	}
	/**
	 * @param mixed $sourceName
	 */
	public function setSourceName(string $sourceName): void{
		$this->sourceName = $sourceName;
	}
	/**
	 * @return string
	 */
	public function getErrorMessage(): string{
		return $this->errorMessage;
	}
	/**
	 * @return string
	 */
	public function getStatus(): string{
		return $this->status;
	}
	/**
	 * @return string
	 */
	public function getFile(): string{
		return $this->file;
	}
	/**
	 * @return QMQB
	 */
	public static function whereStale(): QMQB{
		return static::whereWaiting();
	}
	/**
	 * @inheritDoc
	 */
	public static function whereWaiting(): QMQB{
		$qb = static::where(static::TABLE . '.' . static::FIELD_STATUS, static::STATUS_WAITING)->orderBy(self::TABLE .
			'.' . static::FIELD_IMPORT_STARTED_AT, 'ASC');
		QMUser::excludeTestAndDeletedUsers($qb, static::TABLE);
		return $qb;
	}
	/**
	 * @inheritDoc
	 */
	public static function whereStuck(Builder $qb = null): QMQB{
		if(!$qb){
			$qb = self::qb(true);
		}
		$qb->where(static::TABLE . '.' . static::FIELD_IMPORT_STARTED_AT, "<",
			db_date(time() - self::MINIMUM_SECONDS_BETWEEN_IMPORT_ATTEMPTS));
		$qb->where(static::TABLE . '.' . static::FIELD_STATUS, static::STATUS_IMPORTING);
		self::orderByImportStartedAt($qb);
		QMUser::excludeTestAndDeletedUsers($qb, static::TABLE);
		return $qb;
	}
	/**
	 * @return QMSpreadsheetImporter
	 */
	public function getQMDataSource(): QMDataSource{
		return $this->getSpreadsheetImporter();
	}
	/**
	 * @return int
	 */
	public function getDataSourceId(): int{
		return $this->getSpreadsheetImporter()->getId();
	}
	public function getImage(): string{
		return $this->getQMDataSource()->getImage();
	}
	public function populateDefaultFields(){
		parent::populateDefaultFields();
		if($this->status === self::STATUS_ERROR){
			if(empty($this->userErrorMessage)){
				$this->userErrorMessage = $this->errorMessage;
			}
			if(empty($this->internalErrorMessage)){
				$this->internalErrorMessage = $this->errorMessage;
			}
		}
	}
	public function getNameAttribute(): string{
		return $this->getQMDataSource()->getNameAttribute();
	}
	public function getTitleAttribute(): string{
		return $this->getQMDataSource()->getTitleAttribute();
	}
	public function getDisplayNameAttribute(): string{
		return $this->getQMDataSource()->getDisplayNameAttribute();
	}
    /**
     * @return Connection[]
     */
    public static function importWaitingStaleStuck(): array{
        $connections = [];
        $connections = array_merge($connections, static::importWaiting());
        $connections = array_merge($connections, static::importNeverImported());
        $connections = array_merge($connections, static::importStale());
        $connections = array_merge($connections, static::importStuck());
        return $connections;
    }
    /**
     * @param string $message
     */
    public function slack(string $message){
        JobTestCase::slack("$this: \n" . $message);
    }
    /**
     * @return string
     */
    public function __toString(){
        return $this->getNameAttribute();
    }
    /**
     * @return QMQB
     */
    public static function whereNeverImported(){
        return static::whereNull(static::TABLE . '.' . static::FIELD_IMPORT_STARTED_AT);
    }
    /**
     * @param \Illuminate\Database\Eloquent\Builder $qb
     */
    public static function addImportedStartedClause(\Illuminate\Database\Eloquent\Builder $qb){
        $qb->where(static::TABLE . '.' . static::FIELD_IMPORT_STARTED_AT, "<",
            db_date(time() - static::SECONDS_BETWEEN_IMPORTS));
    }
    /**
     * @return Connection[]
     */
    public static function importStale(): array{
        return static::importByQuery(self::whereStale(),
            "STALE: " . static::FIELD_IMPORT_ENDED_AT . " before more than " .
            TimeHelper::convertSecondsToHumanString(static::SECONDS_BETWEEN_IMPORTS) . " ago");
    }
    /**
     * @return Connection[]
     */
    public static function importNeverImported(): array{
        return static::importByQuery(static::whereNeverImported(),
            "NEVER IMPORTED: " . static::FIELD_IMPORT_STARTED_AT . " is null");
    }
    /**
     * @return Connection[]
     */
    public static function importStuck(): array{
        return static::importByQuery(self::whereStuck(),
            "STUCK: " . static::FIELD_IMPORT_STARTED_AT . " more than a day ago and never ended");
    }
    /**
     * @return Connection[]
     */
    public static function importWaiting(): array{
        $qb = self::whereWaiting();
        // WHY? static::addImportedStartedClause($qb);  //This prevents us from getting null import started ats
        return static::importByQuery($qb, "status is WAITING");
    }
    public static function importJobsTest(){
        JobTestCase::setMaximumJobDuration(0.00001);
        JobTestCase::resetStartTime();
        static::importWaiting();
        JobTestCase::resetStartTime();
        static::importNeverImported();
        JobTestCase::resetStartTime();
        static::importStale();
        JobTestCase::resetStartTime();
        static::importStuck();
    }
    /**
     * @return \Illuminate\Database\Eloquent\Builder|QMQB
     */
    public static function whereErrored(){
        $qb = static::whereNotNull(static::TABLE . '.' . static::FIELD_INTERNAL_ERROR_MESSAGE);
        QMUser::excludeTestAndDeletedUsers($qb, static::TABLE);
        static::orderByImportStartedAt($qb);
        return $qb;
    }
    /**
     * @param \Illuminate\Database\Eloquent\Builder|QMQB $qb
     */
    private static function orderByImportStartedAt($qb){
        $qb->orderBy(static::TABLE . '.' . static::FIELD_IMPORT_STARTED_AT, 'asc');
    }
    /**
     * @return Collection|Connection[]
     */
    public static function logErrorsFromLast24(): Collection{
        $qb = static::whereErrored();
        $qb->where(static::TABLE . '.' . static::FIELD_UPDATED_AT, ">", db_date(time() - 86400));
        $connections = $qb->get();
        if(!$connections){
            \App\Logging\ConsoleLog::info("=== No ERRORED connections to log ===");
            return [];
        }
        QMLog::table($connections, "=== ERRORS ===");
        return $connections;
    }
    /**
     * @return Collection|Connection[]
     */
    public static function logStuck(): Collection{
        $qb = self::whereStuck();
        $connections = $qb->get();
        QMLog::logLink(ConnectionBaseAstralResource::getDataLabIndexUrl(), "=== STUCK ===");
        QMLog::table($connections, "=== STUCK ===");
        return $connections;
    }
    /**
     * @param string $fieldName
     * @return string
     */
    public static function fieldString(string $fieldName): string{
        return static::TABLE . '.' . $fieldName;
    }
    /**
     * @return string
     */
    public function getPHPUnitTestUrl(): string{
        SolutionButton::reset();
        $userId = $this->userId;
        $name = $this->getDataSourceDisplayName();
        $shortClass = (new \ReflectionClass(static::class))->getShortName();
        $functions = $shortClass . '::find(' . $this->getId() . ')->test();';
        $testName = $shortClass . "User" . $userId . 'Source' . str_replace(' ', '', $name);
        $url = ImportTestFiles::getUrl($testName, $functions, __CLASS__);
        QMLog::logLink($url . " \n", "$this PHPUnit Test"); // Keeps running adjacent to INFO in console logs
        return $url;
    }

    /**
     * @throws ConnectorDisabledException
     * @throws InvalidFilePathException
     * @throws NoChangesException
     * @throws NoGeoDataException
     * @throws NoVariableCategoryException
     * @throws UnrecognizedSpreadsheetFormatException
     */
    public function test(): void{
        $this->import(__FUNCTION__);
    }
    /**
     * @param \Illuminate\Database\Eloquent\Builder|QMQB $qb
     * @param string $reason
     * @return static[]
     */
    public static function importByQuery($qb, string $reason): array{
        if(static::class === Connection::class){
            Connection::excludeNonApiUpdateAndDisabledConnectors($qb);
        }
        //$qb->where(static::TABLE . '.' . static::FIELD_USER_ID, 93394);
        $qb->whereNotIn(static::TABLE . '.' . static::FIELD_USER_ID, UserIdProperty::getTestSystemAndDeletedUserIds());
        $qb->whereNull(static::TABLE . '.' . static::FIELD_DELETED_AT);
        $requests = [];
//		$sql = $qb->getSimpleSQL();
//		QMLog::info($sql);
        while($before = $qb->count()){
            $message = "$before " . static::TABLE . " where $reason";
            \App\Logging\ConsoleLog::info($message);
            /** @var static $model */
            $model = $qb->first();
            $requests[] = $model;
            try {
                $model->getPHPUnitTestUrl();
                $model->import($reason);
            } catch (RecentImportException|ConnectorDisabledException $e) {
                QMLog::error(__METHOD__.": ".$e->getMessage());
            } catch (Throwable $e) {
                if(stripos($e->getMessage(), "can't refresh tokens on staging") !== false){
                    $model->logInfo(__METHOD__.": ".$e->getMessage());
                    continue;
                }
                /** @var LogicException $e */
                throw $e;
            }
            if(JobTestCase::jobDurationExceedsTimeLimit()){
                break;
            }
        }
        return $requests;
    }
}
