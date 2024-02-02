<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnusedPrivateMethodInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace App\DataSources\Connectors;
use App\DataSources\GoogleBaseConnector;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\QMException;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Slim\Model\Measurement\MeasurementSet;
use App\Types\TimeHelper;
use App\Units\BeatsPerMinuteUnit;
use App\Units\CountUnit;
use App\Units\KilocaloriesUnit;
use App\Units\KilogramsUnit;
use App\Units\MetersUnit;
use App\Utils\AppMode;
use App\VariableCategories\PhysicalActivityVariableCategory;
use App\VariableCategories\PhysiqueVariableCategory;
use App\VariableCategories\VitalSignsVariableCategory;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\CaloriesBurnedCommonVariable;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\HourlyStepCountCommonVariable;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\WalkOrRunDistanceCommonVariable;
use App\Variables\CommonVariables\PhysiqueCommonVariables\BodyWeightCommonVariable;
use App\Variables\CommonVariables\VitalSignsCommonVariables\HeartRatePulseCommonVariable;
use App\Variables\QMUserVariable;
use Google_Service_Fitness;
use LogicException;
/** Class GoogleFitConnector
 * @package App\DataSources\Connectors
 */
class GoogleFitConnector extends GoogleBaseConnector {
	protected const BACKGROUND_COLOR = '#00a3ad';
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = PhysicalActivityVariableCategory::NAME;
	public const DISPLAY_NAME = 'Google Fit';
	protected const ENABLED = 1;
	protected const GET_IT_URL = 'https://fit.google.com/';
	public const ID = 61;
	public const IMAGE = 'https://i.imgur.com/QGtGtGT.png';
	protected const LOGO_COLOR = '#d34836';
	protected const LONG_DESCRIPTION = 'Use Google Fit to import your fitness data.';
	public const NAME = 'googlefit';
	protected const SHORT_DESCRIPTION = 'Tracks Calories Burned, Heart Rate, Body Weight, Hourly Step Count, and Walk or Run Distance';
	public static $BASE_API_URL = 'https://www.googleapis.com/fitness/v1/users/me/';
	public static array $SCOPES = [
		Google_Service_Fitness::FITNESS_ACTIVITY_READ,
		Google_Service_Fitness::FITNESS_BODY_READ,
		Google_Service_Fitness::FITNESS_LOCATION_READ,
	];
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $logoColor = self::LOGO_COLOR;
	public $longDescription = self::LONG_DESCRIPTION;
	public $name = self::NAME;
	public $providesUserProfileForLogin = false;
	public $shortDescription = self::SHORT_DESCRIPTION;
	public $variableNames = [
		CaloriesBurnedCommonVariable::NAME,
		//'Hourly Step Count',  // Too slow
		//BodyWeightCommonVariable::NAME,  // Stopped returning weight for some reason
		HeartRatePulseCommonVariable::NAME,
		WalkOrRunDistanceCommonVariable::NAME  // Too slow
	];
	private $dataTypes;
	/**
	 * @return void
	 * @throws \App\Exceptions\TooSlowException
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
	public function importData(): void{
		$this->setDataTypes();
		$dataSources = $this->getSources();
		foreach($dataSources as $dataSource){
			$this->getDataSets($dataSource);
			if($this->weShouldBreak()){
				break;
			}
		}
		$this->saveMeasurements();
	}
	public function setDataTypes(): void{
		$this->dataTypes = [
			'com.google.calories.expended' => function($measurements = []){
				return new MeasurementSet(CaloriesBurnedCommonVariable::NAME, $measurements, KilocaloriesUnit::NAME,
					PhysicalActivityVariableCategory::NAME, $this->displayName,
					BaseCombinationOperationProperty::COMBINATION_SUM);
			},
			'com.google.heart_rate.bpm' => function($measurements = []){
				return new MeasurementSet('Heart Rate (Pulse)', $measurements, BeatsPerMinuteUnit::NAME,
					VitalSignsVariableCategory::NAME, $this->displayName,
					BaseCombinationOperationProperty::COMBINATION_MEAN);
			},
			'com.google.weight' => function($measurements = []){
				return new MeasurementSet(BodyWeightCommonVariable::NAME, $measurements, KilogramsUnit::NAME,
					PhysiqueVariableCategory::NAME, $this->displayName,
					BaseCombinationOperationProperty::COMBINATION_MEAN);
			},
			'com.google.step_count.delta' => function($measurements = []){
				return new MeasurementSet(HourlyStepCountCommonVariable::NAME, $measurements, CountUnit::NAME,
					PhysicalActivityVariableCategory::NAME, $this->displayName,
					BaseCombinationOperationProperty::COMBINATION_SUM, ['durationOfAction' => 86400]);
			},
			'com.google.distance.delta' => function($measurements = []){
				return new MeasurementSet(WalkOrRunDistanceCommonVariable::NAME, $measurements, MetersUnit::NAME,
					PhysicalActivityVariableCategory::NAME, $this->displayName,
					BaseCombinationOperationProperty::COMBINATION_SUM, ['durationOfAction' => 86400]);
			},
		];
	}
	/**
	 * @return array
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
	private function getSources(): array{
		$sources = [];
		$dataTypeNames = array_keys($this->dataTypes);
		$optParams = [];
		foreach($dataTypeNames as $dataTypeName){
			$optParams['dataTypeName'][] = $dataTypeName;
		}
		$params = http_build_query($optParams);
		//Google wants them to go like dataTypeName=x&dataTypeName=y
		/** @noinspection NotOptimalRegularExpressionsInspection */
		$params = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $params);
		$nextPage = self::$BASE_API_URL . 'dataSources?' . $params;
		while(!empty($nextPage)){
			$response = $this->fetchArray($nextPage);
			if(!$response){
				$this->logError("No response from $nextPage");
				$this->handleUnsuccessfulResponses($response);
			}
			$statusCode = $this->getLastStatusCode();
			if($statusCode === 200){
				$this->logDebug("GoogleFit: Received $statusCode");
				if(array_key_exists('dataSource', $response) && is_array($response['dataSource'])){
					foreach($response['dataSource'] as $dataSource){
						$fieldFormat = 'intVal';
						if($dataSource['dataType']['field'][0]['format'] === 'floatPoint'){
							$fieldFormat = 'fpVal';
						}
						$sources[] = [
							'dataTypeName' => $dataSource['dataType']['name'],
							'dataStreamId' => $dataSource['dataStreamId'],
							'fieldFormat' => $fieldFormat,
						];
					}
				} else{
					$this->logWarning("GoogleFit: No dataSource array found", [
						'$responseObject' => $response,
					]);
				}
				if(isset($response['nextPageToken'])){
					$params = ['nextPageToken' => $response['nextPageToken']];
					$nextPage = self::$BASE_API_URL . 'dataSources' . '?' . http_build_query($params);
				} else{
					$nextPage = null;
				}
			} else{
				$this->handleUnsuccessfulResponses($response);
				break;
			}
		}
		return $sources;
	}
	/**
	 * @param array $dataSource
	 * @return void
	 * @throws \App\Exceptions\TooSlowException
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
	private function getDataSets(array $dataSource): void {
		$fromTime = $this->getFromTime();
		\App\Logging\ConsoleLog::info(\App\Logging\QMLog::print_r($dataSource, true));
		$v = $this->getUserVariableForDataType($dataSource['dataTypeName']);
		$fromTime = $this->getFromTimeForVariable($fromTime, $v);
		if($fromTime > time() - 86400){
			$v->logInfo("Not importing because last measurement less than a day ago: " .
				TimeHelper::timeSinceHumanString($fromTime));
			return;
		}
		$fromAt = db_date($fromTime);
		$v->logInfo("Updating from " . TimeHelper::timeSinceHumanString($fromTime) . ' to ' .
			TimeHelper::YYYYmmddd(time()));
		// https://www.googleapis.com/fitness/v1/users/me//datasets/1405722665000000000-1455722665000000000
		$minNanos = $fromTime * 1000000000; //Yep google wants timestamp in nanoseconds
		$endAt = $this->getEndAt();
		$maxNanos = strtotime($endAt) * 1000000000; // - (60*60*24*2)
		$url = self::$BASE_API_URL . 'dataSources/' . $dataSource['dataStreamId'] . '/datasets/' . $minNanos . '-' .
			$maxNanos;
		$object = $this->fetchArray($url);
		$code = $this->getLastStatusCode();
		switch($code) {
			case 200:
				$data = $object['point'] ?? null;
				if(!$data){
					$this->logInfo("NO DATA between $fromAt and $endAt from $url");
					return;
				}
				$this->logInfo("Got " . count($data) . " data points between $fromAt and $endAt from $url");
				$this->addToMeasurementsQueue($dataSource, $object, $maxNanos, $minNanos);
				break;
			case 400:
			case QMException::CODE_UNAUTHORIZED:
			case 403:
				$this->handleUnauthorizedResponse($code, $url, $object);
				break;
			default:
				$this->logError("GoogleFit: Received $code for " . $url);
				break;
		}
	}
	/**
	 * @param string $type
	 * @return QMUserVariable
	 */
	private function getUserVariableForDataType(string $type): QMUserVariable{
		$variableInfo = $this->dataTypes[$type]();
		$userVariable = $this->getQMUserVariable($variableInfo->variableName, $variableInfo->unitAbbreviatedName,
			$variableInfo->variableCategoryName);
		return $userVariable;
	}
	/**
	 * @param array $dataSource
	 * @param array $response
	 * @param int $maxNanos
	 * @param int $minNanos
	 * @throws \App\DataSources\TooEarlyException
	 * @throws \App\Exceptions\TooSlowException
	 */
	private function addToMeasurementsQueue(array $dataSource, array $response, int $maxNanos, int $minNanos){
		$v = $this->getUserVariableForDataType($dataSource['dataTypeName']);
		$unitName = $this->getUnitAbbreviatedName($dataSource);
		$valuesByDate = [];
		$connectionFromAt = $this->getFromAt();
		$minAt = db_date($minNanos / 1000000000);
		$maxAt = db_date($maxNanos / 1000000000);
		foreach($response['point'] as $dataPoint){
			$nanos = $dataPoint['startTimeNanos'];
			$actualStartAt = db_date($nanos / 1000000000);
			if($nanos > $maxNanos){
				continue;
			}
			if($nanos < $minNanos){
				$this->logInfo("startTime $actualStartAt less than timeMin $minAt");
				continue;
			}
			$roundedStartAt = $this->roundStartAt($actualStartAt, $v);
			if(strtotime($roundedStartAt) < strtotime($connectionFromAt)){
				$this->logInfo("Skipping because roundedStartAt $roundedStartAt < connectionFromAt $connectionFromAt");
				continue;
			}
			if(!isset($dataPoint['value'])){
				continue;
			} //No data in this data point yet
			foreach($dataPoint['value'] as $valueArray){
				$value = $valueArray[$dataSource['fieldFormat']];
				if(empty($value)){
					continue;
				}
				$valuesByDate[$roundedStartAt][] = $value;
			}
		}
		foreach($valuesByDate as $roundedStartAt => $valueArray){
			try {
				$combined = $v->combineValues($valueArray);
				$m =
					$this->addMeasurement($v->name, $roundedStartAt, $combined, 
					                      $unitName, $v->variableCategoryName,
					                      []);
			} catch (InvalidVariableValueAttributeException $e) { // Google has stupid low calories burned values
				if($v->name == CaloriesBurnedCommonVariable::NAME || $v->name == WalkOrRunDistanceCommonVariable::NAME){
					$this->logWarning("$v->name value from Google Fit is invalid", ['exception' => $e]);
					continue;
				}
				le($e);
			} catch (InvalidAttributeException $e) {
				le($e);
			}
		}
	}
	/**
	 * @param array $dataSource
	 * @return string
	 */
	private function getUnitAbbreviatedName(array $dataSource): string{
		/** @var MeasurementSet $variableInfo */
		$variableInfo = $this->dataTypes[$dataSource['dataTypeName']]();
		return $variableInfo->unitAbbreviatedName;
	}
	/**
	 * @param int|null $currentFromTime
	 * @return bool
	 */
	public function weShouldBreak(int $currentFromTime = null): bool{
		if(!AppMode::isTestingOrStaging()){
			return false;
		}
		return $this->haveMeasurementsForAllVariables();
	}
	public function makeSerializable(){
		parent::makeSerializable();
		$this->dataTypes = null;
	}
	/**
	 * @param array $dataSource
	 * @return string
	 */
	private function getVariableCategoryName(array $dataSource): string{
		/** @var MeasurementSet $variableInfo */
		$variableInfo = $this->dataTypes[$dataSource['dataTypeName']]();
		return $variableInfo->variableCategoryName;
	}

}
