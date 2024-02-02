<?php /** @noinspection PhpUnused *//*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\OAuth2Connector;
use App\Exceptions\CredentialsNotFoundException;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\RateLimitConnectorException;
use App\Exceptions\TooSlowException;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Model\Measurement\MeasurementSet;
use App\Storage\MemoryOrRedisCache;
use App\Types\TimeHelper;
use App\Units\BeatsPerMinuteUnit;
use App\Units\CaloriesUnit;
use App\Units\CountUnit;
use App\Units\KilometersUnit;
use App\Units\MinutesUnit;
use App\Utils\AppMode;
use App\VariableCategories\PhysicalActivityVariableCategory;
use App\VariableCategories\PhysiqueVariableCategory;
use App\VariableCategories\SleepVariableCategory;
use App\VariableCategories\VitalSignsVariableCategory;
use App\Variables\CommonVariables\NutrientsCommonVariables\CaloricIntakeCommonVariable;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\CaloriesBurnedCommonVariable;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\DailyStepCountCommonVariable;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\WalkOrRunDistanceCommonVariable;
use App\Variables\CommonVariables\PhysiqueCommonVariables\BodyWeightCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\AwakeningsCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\DeepSleepDurationCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\DurationOfAwakeningsDuringSleepCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\LightSleepDurationCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\MinutesAfterWakeupStillInBedCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\PeriodsOfDeepSleepCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\PeriodsOfLightSleepCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\PeriodsOfREMSleepCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\REMSleepDurationCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\SleepDurationCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\SleepEfficiencyFromFitbitCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\SleepStartTimeCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\TimeInBedCommonVariable;
use App\Variables\CommonVariables\VitalSignsCommonVariables\HeartRatePulseCommonVariable;
use App\Variables\QMUserVariable;
use kamermans\OAuth2\Persistence\Laravel5CacheTokenPersistence;
use kamermans\OAuth2\Token\RawToken;
use App\DataSources\Connectors\Fitbit\Api\Activities;
use App\DataSources\Connectors\Fitbit\Api\Body;
use App\DataSources\Connectors\Fitbit\Api\Devices;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;
use App\DataSources\Connectors\Fitbit\Api\Food;
use App\DataSources\Connectors\Fitbit\Body\Fat\Fat;
use App\DataSources\Connectors\Fitbit\Body\Weight\Weight;
use App\DataSources\Connectors\Fitbit\Devices\Alarms;
use App\DataSources\Connectors\Fitbit\Food\Favorite\Favorites;
use App\DataSources\Connectors\Fitbit\Food\Foods\Foods;
use App\DataSources\Connectors\Fitbit\Food\Foods\Logs;
use App\DataSources\Connectors\Fitbit\Food\Meals\Meals;
use App\DataSources\Connectors\Fitbit\Food\TimeSeries;
use App\DataSources\Connectors\Fitbit\Food\Water\Goals;
use App\DataSources\Connectors\Fitbit\Friends\Friends as FriendsOperations;
use App\DataSources\Connectors\Fitbit\HeartRate\HeartRate;
use App\DataSources\Connectors\Fitbit\ServiceProvider;
use App\DataSources\Connectors\Fitbit\SleepLogs\SleepLogs;
use App\DataSources\Connectors\Fitbit\Subscriptions\Subscriptions;
use App\DataSources\Connectors\Fitbit\User\User;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\OAuth2\Token\StdOAuth2Token;
/** Class FitbitConnector
 * @package App\DataSources\Connectors
 */
class FitbitConnector extends OAuth2Connector {
	protected const ACTIVITIES_HEART                     = 'activities/heart';
	protected const AFFILIATE                            = true;
	protected const BACKGROUND_COLOR                     = '#cc73e1';  // Broken
	protected const CLIENT_REQUIRES_SECRET               = false;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME       = 'Physical Activity';
	protected const DEVELOPER_CONSOLE                    = 'https://dev.fitbit.com/apps';
	public const    DISABLED_UNTIL                       = "2020-10-06";  // 99 Days
	public const    DISPLAY_NAME                         = 'Fitbit';
	protected const ENABLED                              = 1;
	protected const GET_IT_URL                           = 'https://www.amazon.com/Fitbit-Charge-Heart-Fitness-Wristband/dp/B01K9S260E/ref=as_li_ss_tl?ie=UTF8&qid=1493518902&sr=8-3&keywords=fitbit&th=1&linkCode=ll1';
	public const    ID                                   = 7;
	protected const LOGO_COLOR                           = '#4cc2c4';
	protected const LONG_DESCRIPTION                     = 'Fitbit makes activity tracking easy and automatic.';
	public const    MAXIMUM_DATE_RANGE_FOR_REQUEST       = 3 * 365 * 86400;
	private const   MAXIMUM_DATE_RANGE_FOR_SLEEP_REQUEST = 99 * 86400;
	public const    NAME                                 = 'fitbit';
	public const    PATHS                                = [
		'food' => self::PATH_FOOD,
		'water' => self::PATH_WATER,
	];
	const           PATH_FOOD                            = "/1/user/-/foods/log/caloriesIn/date/" .
	self::PLACEHOLDER_START_DATE . "/" . self::PLACEHOLDER_END_DATE . ".json";
	const           PATH_WATER                           = "/1/user/-/foods/log/water/date/" .
	self::PLACEHOLDER_START_DATE . "/" . self::PLACEHOLDER_END_DATE . ".json";
	protected const PREMIUM                              = true;
	/**
	 * Scopes
	 * @var string
	 */
	const SCOPE_ACTIVITY = 'activity', SCOPE_HEARTRATE = 'heartrate', SCOPE_LOCATION = 'location', SCOPE_NUTRITION = 'nutrition',
	 SCOPE_PROFILE = 'profile', SCOPE_SETTINGS = 'settings', SCOPE_SLEEP = 'sleep', SCOPE_SOCIAL = 'social',
	  SCOPE_WEIGHT      = 'weight';
	protected const                                                                                                                                                                                                                       SHORT_DESCRIPTION = 'Tracks sleep, diet, and physical activity.';
	public static $BASE_API_URL = "https://api.fitbit.com";
	public static $OAUTH_SERVICE_NAME = 'FitBit';
	public static array $SCOPES = [
		self::SCOPE_ACTIVITY,
		self::SCOPE_HEARTRATE,
		self::SCOPE_LOCATION,
		self::SCOPE_NUTRITION,
		self::SCOPE_SLEEP,
		self::SCOPE_WEIGHT,
		self::SCOPE_PROFILE,
		self::SCOPE_SETTINGS,
		self::SCOPE_SOCIAL,
	];
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $logoColor = self::LOGO_COLOR;
	public $longDescription = self::LONG_DESCRIPTION;
	public $name = self::NAME;
	public $premium = self::PREMIUM;
	public $shortDescription = self::SHORT_DESCRIPTION;
	private $endpointMeasurementSets;
	/**
	 * {@inheritdoc}
	 */
	public function getAccessTokenEndpoint(){
		return new Uri('https://api.fitbit.com/oauth2/token');
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationEndpoint(){
		return new Uri('https://www.fitbit.com/oauth2/authorize');
	}
	/**
	 * {@inheritdoc}
	 */
	protected function getAuthorizationMethod(): int{
		return static::AUTHORIZATION_METHOD_HEADER_BEARER;
	}
	/**
	 * {@inheritdoc}
	 */
	protected function getExtraOAuthHeaders(): array{
		return [
			'Authorization' => 'Basic ' .
				base64_encode($this->getConnectorClientId() . ':' . $this->getOrSetConnectorClientSecret()),
		];
	}
	/**
	 * @return void
	 * @throws ConnectorException
	 * @throws InvalidAttributeException
	 * @throws ModelValidationException
	 * @throws RateLimitConnectorException
	 * @throws TooSlowException
	 */
	public function importData(): void{
		$this->setClosures();
		//$profile = $this->getConnectorUserProfile();
		$this->importSleepV12();
		if(!\App\Utils\Env::get('DEBUG_CONNECT')){
			$this->importHeartRate();
			$this->importDistance();
			$this->importSleepV1();
			foreach($this->endpointMeasurementSets as $path => $set){
				$this->importForEndpoint($path, $set);
			}
		}
		$this->saveMeasurements();
	}
	public function setClosures(): void{
		$this->endpointMeasurementSets = [
			'body/fat' => new MeasurementSet('Body Fat', [], '%', PhysiqueVariableCategory::NAME),
			'activities/calories' => new MeasurementSet(CaloriesBurnedCommonVariable::NAME, [], 'cal',
				PhysicalActivityVariableCategory::NAME),
			'activities/steps' => new MeasurementSet(DailyStepCountCommonVariable::NAME, [], 'count',
				PhysicalActivityVariableCategory::NAME),
			'body/weight' => new MeasurementSet(BodyWeightCommonVariable::NAME, [], 'kg',
				PhysiqueVariableCategory::NAME),
			'body/bmi' => new MeasurementSet('Body Mass Index or BMI', [], 'index', PhysiqueVariableCategory::NAME),

			'foods/log/caloriesIn' => new MeasurementSet(CaloricIntakeCommonVariable::NAME, [], 'cal', 'Nutrients'),
			'foods/log/water' => new MeasurementSet('Water (mL)', [], 'mL', 'Foods'),
			'sleep/startTime' => new MeasurementSet(SleepStartTimeCommonVariable::NAME, [], 'h',
				SleepVariableCategory::NAME),
			'sleep/timeInBed' => new MeasurementSet(TimeInBedCommonVariable::NAME, [], 'minutes',
				SleepVariableCategory::NAME),
			'sleep/awakeningsCount' => new MeasurementSet(AwakeningsCommonVariable::NAME, [], 'count',
				SleepVariableCategory::NAME),
			'sleep/minutesToFallAsleep' => new MeasurementSet('Minutes to Fall Asleep', [], 'minutes',
				SleepVariableCategory::NAME),
			'sleep/minutesAfterWakeup' => new MeasurementSet(MinutesAfterWakeupStillInBedCommonVariable::NAME, [],
				'minutes', SleepVariableCategory::NAME),
			'sleep/efficiency' => new MeasurementSet(SleepEfficiencyFromFitbitCommonVariable::NAME, [], '%',
				SleepVariableCategory::NAME),
		];
	}
	/**
	 * @throws ConnectorException
	 * @throws InvalidAttributeException
	 * @throws RateLimitConnectorException
	 * @throws TooSlowException
	 */
	public function importAwakeningsV1(): void{
		$this->importForEndpoint('sleep/minutesAwake',
			new MeasurementSet(DurationOfAwakeningsDuringSleepCommonVariable::NAME, [], MinutesUnit::NAME,
				SleepVariableCategory::NAME));
	}
	/**
	 * @param string $endpoint
	 * @param MeasurementSet $set
	 * @param string|null $startDate
	 * @throws ConnectorException
	 * @throws InvalidAttributeException
	 * @throws TooSlowException
	 * @throws RateLimitConnectorException
	 */
	private function importForEndpoint(string $endpoint, MeasurementSet $set, string $startDate = null): void{
		$v = $this->getQMUserVariable($set->getVariableName(), $set->getOriginalUnit()->name,
			$set->variableCategoryName);
		if(!$startDate){
			$startDate = $this->getFitbitStartDate($v, "2010-01-01");
		}
		if(!$startDate){
			return;
		}
		while(strtotime($startDate) < time() - 86400){
			$endDate = $this->generateEndDate($startDate, self::MAXIMUM_DATE_RANGE_FOR_REQUEST);
			$response = $this->fetchArray($this->getUrlForPath('/1/user/-/' . $endpoint . '/date/') . $startDate . '/' .
				$endDate . '.json');
			/** @noinspection TypeUnsafeComparisonInspection */
			if($this->getLastStatusCode() == 200){
				$setName = $this->getSetName($endpoint);
				$fbMeasurements = $response[$setName];
				if($endpoint === self::ACTIVITIES_HEART){ // heart rate has a different array structure
					$this->addHeartRateMeasurements($fbMeasurements);
				} else{
					$this->addMeasurementSet($fbMeasurements, $setName, $set);
					if($v->getNewMeasurements() && AppMode::isTestingOrStaging()){
						break;
					}
				}
			} else{
				$this->handleUnsuccessfulResponses($response);
			}
			$startDate = date('Y-m-d', strtotime($endDate) + 86400);
		}
	}
	/**
	 * @param QMUserVariable $v
	 * @param string $default
	 * @return string
	 */
	protected function getFitbitStartDate(QMUserVariable $v, string $default): ?string{
		$ats = [];
		$ats[] = db_date($default);
		$fromAt = $this->getFromAt();
		if($fromAt){
			$ats[] = $fromAt;
		}
		$latestTaggedMeasurementAt = $v->getLatestNonTaggedMeasurementStartAt();
		if($latestTaggedMeasurementAt){
			$ats[] = db_date(strtotime($latestTaggedMeasurementAt) + 86400);
		}
		$max = max($ats);
		if(strtotime($max) > time() - 86400){
			$this->logInfo("Not getting $v->name measurements because most recent measurement was " .
				TimeHelper::timeSinceHumanString($latestTaggedMeasurementAt));
			return null;
		}
		return date('Y-m-d', strtotime($max));
	}
	/**
	 * @param string|null $startDate
	 * @param int $maxRange
	 * @return string
	 */
	private function generateEndDate(?string $startDate, int $maxRange){
		$startTime = strtotime($startDate);
		$endTime = $startTime + $maxRange;
		if($endTime > time()){
			$endTime = time();
		}
		return date('Y-m-d',
			$endTime - 86400); // -86400  to allow a buffer for maximum and don't get incomplete data from today
	}
	/**
	 * @param string $endpoint
	 * @return string
	 */
	private function getSetName(string $endpoint): string{
		$setName = str_replace('/', '-', $endpoint);
		return $setName;
	}
	/**
	 * @param $response
	 * @throws InvalidAttributeException
	 * @throws TooSlowException
	 */
	private function addHeartRateMeasurements($response){
		foreach($response as $heartRates){
			foreach($heartRates['value']['heartRateZones'] as $heartRateZone){
				if(isset($heartRateZone['caloriesOut']) && $heartRateZone['caloriesOut'] > 0 &&
					false === stripos($heartRateZone['name'], 'out of range')){
					$variableName = $heartRateZone['name'] . ' Heart Rate Zone Calories Out';
					$this->addMeasurement($variableName, $heartRates['dateTime'], $heartRateZone['caloriesOut'],
						CaloriesUnit::NAME, PhysicalActivityVariableCategory::NAME);
					$variableName = $heartRateZone['name'] . ' Heart Rate Zone Minutes';
					$this->addMeasurement($variableName, $heartRates['dateTime'], $heartRateZone['minutes'],
						MinutesUnit::NAME, PhysicalActivityVariableCategory::NAME);
				}
			}
			if(isset($heartRates['value']['restingHeartRate']) && $heartRates['value']['restingHeartRate'] > 0){
				$variableName = 'Resting Heart Rate (Pulse)';
				$this->addMeasurement($variableName, $heartRates['dateTime'], $heartRates['value']['restingHeartRate'],
					BeatsPerMinuteUnit::NAME, VitalSignsVariableCategory::NAME);
			}
		}
	}
	/**
	 * @param $fbMeasurements
	 * @param string $setName
	 * @param MeasurementSet $set
	 * @throws TooSlowException
	 */
	private function addMeasurementSet($fbMeasurements, string $setName, MeasurementSet $set): void{
		$dateValues = [];
		foreach($fbMeasurements as $fbMeasurement){
			$dateValues[$fbMeasurement["dateTime"]] = $fbMeasurement["value"];
		}
		foreach($dateValues as $date => $value){
			$value = $this->getValue($setName, $value);
			$unit = $set->getOriginalUnit()->getNameAttribute();
			$name = $set->getVariableName();
			try {
				$uv = $this->getQMUserVariable($name, $set->unitAbbreviatedName, $set->variableCategoryName, $set->variableParameters);
				$min = $uv->getCommonMinimumAllowedValue();
				if(!$value && $min){
					$this->logInfo("Skipping $value $unit $name...");
					continue;
				}
				$this->addMeasurement($name, $date, $value, $unit, $set->variableCategoryName);
			} catch (InvalidAttributeException $e) {
				$this->logError("Skipping $value $unit $name" . " measurement because " . $e->getMessage());
			}
		}
	}
	/**
	 * @param string $setName
	 * @param string $value
	 * @return float
	 */
	private function getValue(string $setName, string $value): float{
		if($setName === 'sleep-startTime'){
			$h = (int)substr($value, 0, 2);
			//number of hours past noon
			if($h < 12){
				$value = $h + 12;
			} else{
				$value = $h - 12;
			}
		}
		return $value;
	}
	/**
	 * @throws ConnectorException
	 * @throws InvalidAttributeException
	 * @throws RateLimitConnectorException
	 * @throws TooSlowException
	 */
	public function importDistance(): void{
		$this->importForEndpoint('activities/distance',
			new MeasurementSet(WalkOrRunDistanceCommonVariable::NAME, [], KilometersUnit::NAME,
				PhysicalActivityVariableCategory::NAME));
	}
	/**
	 * @throws ConnectorException
	 * @throws InvalidAttributeException
	 * @throws RateLimitConnectorException
	 * @throws TooSlowException
	 */
	public function importSleepV1(): void{
		$this->importForEndpoint('sleep/minutesAsleep',
			new MeasurementSet(SleepDurationCommonVariable::NAME, [], MinutesUnit::NAME, SleepVariableCategory::NAME));
		$this->importAwakeningsV1();
	}
	/**
	 * @throws InvalidAttributeException
	 * @throws TooSlowException
	 * @throws ConnectorException
	 */
	private function importSleepV12(){
		$rem = $this->getQMUserVariable(REMSleepDurationCommonVariable::NAME, MinutesUnit::NAME,
			SleepVariableCategory::NAME);
		$startDate = $this->getFitbitStartDate($rem, "2015-01-01");  // I think this is when they started measuring REM, etc
		if(!$startDate){
			return;
		}
		$hasRemMeasurements =
			false;  // Crappier Fitbit devices don't track sleep stages so their sleep measurements are obtained from other endpoints
		while(strtotime($startDate) < time() - 86400){
			$endDate = $this->generateEndDate($startDate, self::MAXIMUM_DATE_RANGE_FOR_SLEEP_REQUEST);
			$url = $this->getUrlForPath("/1.2/user/-/sleep/date/$startDate/$endDate.json?");
			$startCarbon = TimeHelper::toCarbon($startDate);
			$endCarbon = TimeHelper::toCarbon($endDate);
			$response = $this->getRequest($url);
			$fbSleepItems = $response->sleep;
			foreach($fbSleepItems as $fbSleepItem){
				$hasRemMeasurements = $this->addSleepStagesMeasurementsIfREMIsPresentInSummary($fbSleepItem);
			}
			$startDate = date('Y-m-d', strtotime($endDate) + 86400);
			if(!$hasRemMeasurements){
				$this->logInfo("No sleep stage measurements from $url");
			}
			if($hasRemMeasurements && AppMode::isTestingOrStaging()){
				break;
			}
		}
	}
	/**
	 * @return Fitbit
	 */
	public function fitbit(): Fitbit{
		$p = new Laravel5CacheTokenPersistence(MemoryOrRedisCache::redisCacheRepository());
		try {
			$t = $this->getToken();
		} catch (TokenNotFoundException $e) {
			le($e);
		}
		$t = new RawToken($t->getAccessToken(), $t->getRefreshToken(), $t->getEndOfLife());
		$p->saveToken($t);
		$fb = (new ServiceProvider())->build($p, $this->getClientId(), $this->getOrSetConnectorClientSecret(),
            static::getConnectUrlWithoutParams());
		return $fb;
	}
	/**
	 * @param $fbSleepItem
	 * @return bool
	 * @throws InvalidAttributeException
	 * @throws TooSlowException
	 */
	private function addSleepStagesMeasurementsIfREMIsPresentInSummary($fbSleepItem): bool{
		$date = $fbSleepItem->dateOfSleep;
		$summary = $fbSleepItem->levels->summary;
		if(!isset($summary->rem)){
			return false;
		}
		try {
			$this->addMeasurement(DeepSleepDurationCommonVariable::NAME, $date, $summary->deep->minutes,
				MinutesUnit::NAME, SleepVariableCategory::NAME);
			$this->addMeasurement(PeriodsOfDeepSleepCommonVariable::NAME, $date, $summary->deep->count, CountUnit::NAME,
				SleepVariableCategory::NAME);
		} catch (InvalidVariableValueAttributeException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());  // Sometimes it's 0 if you take a nap
		}
		$this->addMeasurement(LightSleepDurationCommonVariable::NAME, $date, $summary->light->minutes,
			MinutesUnit::NAME, SleepVariableCategory::NAME);
		$this->addMeasurement(REMSleepDurationCommonVariable::NAME, $date, $summary->rem->minutes, MinutesUnit::NAME,
			SleepVariableCategory::NAME);
		$this->addMeasurement(DurationOfAwakeningsDuringSleepCommonVariable::NAME, $date, $summary->wake->minutes,
			MinutesUnit::NAME, SleepVariableCategory::NAME);
		$this->addMeasurement(PeriodsOfLightSleepCommonVariable::NAME, $date, $summary->light->count, CountUnit::NAME,
			SleepVariableCategory::NAME);
		$this->addMeasurement(PeriodsOfREMSleepCommonVariable::NAME, $date, $summary->rem->count, CountUnit::NAME,
			SleepVariableCategory::NAME);
		$this->addMeasurement(AwakeningsCommonVariable::NAME, $date, $summary->wake->count, CountUnit::NAME,
			SleepVariableCategory::NAME);
		return true;
	}
	/**
	 * @throws ConnectorException
	 * @throws InvalidAttributeException
	 * @throws RateLimitConnectorException
	 * @throws TooSlowException
	 */
	protected function importHeartRate(): void{
		$dummyMeasurementSet = new MeasurementSet(HeartRatePulseCommonVariable::NAME, [], BeatsPerMinuteUnit::NAME,
			VitalSignsVariableCategory::NAME);
		$latestConnectionDate = $this->getConnectionIfExists()->getOrCalculateLatestMeasurementDate();
		$fromAt = $this->getFromAt();
		$fromAt = db_date(max(strtotime($latestConnectionDate), strtotime($fromAt)));
		$date = TimeHelper::YYYYmmddd($fromAt);
		$this->logInfo("Using last connector measurement time as start date $date for heart rate measurements because that endpoint pulls " .
			"for multiple variables preventing us from using variable latest measurement times");
		$this->importForEndpoint(self::ACTIVITIES_HEART, $dummyMeasurementSet, $date);
	}
	public function makeSerializable(){
		$this->endpointMeasurementSets = null;
		parent::makeSerializable();
	}
	/**
	 * {@inheritdoc}
	 */
	protected function parseAccessTokenResponse($responseBody): StdOAuth2Token{
		return $this->parseStandardAccessTokenResponse($responseBody);
	}
	public function setMessages(): void{
		parent::setMessages();
	}
	/**
	 * @return string
	 */
	public function urlUserDetails(): string{
		return "https://api.fitbit.com/1/user/-/profile.json";
	}
	/**
	 * @param string $startDate
	 * @param string $endDate
	 * @return \stdClass|\stdClass[]
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
	public function getFood(string $startDate, string $endDate){
		return $this->getFitbit(self::PATH_WATER, $startDate, $endDate);
	}
	/**
	 * @param string $path
	 * @param $start
	 * @param $end
	 * @return object|\stdClass[]
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
	protected function getFitbit(string $path, $start, $end){
		try {
			return $this->getRequest($path, [], [
				self::PLACEHOLDER_START_DATE => TimeHelper::YYYYmmddd($start),
				self::PLACEHOLDER_END_DATE => TimeHelper::YYYYmmddd($end),
			]);
		} catch (CredentialsNotFoundException $e) {
			le($e);
		}
	}
	/**
	 * @param string $startDate
	 * @param string $endDate
	 * @return \stdClass|\stdClass[]
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
	public function getWater(string $startDate, string $endDate){
		return $this->getFitbit(self::PATH_FOOD, $startDate, $endDate);
	}
	public function activities(): Activities{
		return new Activities($this->fitbit());
	}
	public function user(): User{
		return new User($this->fitbit());
	}
	public function heartRate(): HeartRate{
		return new HeartRate($this->fitbit());
	}
	public function sleepLogs(): SleepLogs{
		return new SleepLogs($this->fitbit());
	}
	public function friends(): FriendsOperations{
		return new FriendsOperations($this->fitbit());
	}
	public function devices(): Devices{
		return new Devices($this->fitbit());
	}
	public function body(): Body{
		return new Body($this->fitbit());
	}
	public function food(): Food{
		return new Food($this->fitbit());
	}
	public function subscriptions(): Subscriptions{
		return new Subscriptions($this->fitbit());
	}
	/**
	 * @return \App\DataSources\Connectors\Fitbit\Food\Foods\Foods
	 */
	public function foodService(): Foods{
		return new Foods($this->fitbit());
	}
	/**
	 * @return \App\DataSources\Connectors\Fitbit\Food\Foods\Goals
	 */
	public function foodGoals(): \App\DataSources\Connectors\Fitbit\Food\Foods\Goals{
		return new \App\DataSources\Connectors\Fitbit\Food\Foods\Goals($this->fitbit());
	}
	/**
	 * @return \App\DataSources\Connectors\Fitbit\Food\Water\Goals
	 */
	public function waterGoals(): Goals{
		return new Goals($this->fitbit());
	}
	/**
	 * @return \App\DataSources\Connectors\Fitbit\Food\Water\Logs
	 */
	public function waterLogs(): \App\DataSources\Connectors\Fitbit\Food\Water\Logs{
		return new \App\DataSources\Connectors\Fitbit\Food\Water\Logs($this->fitbit());
	}
	/**
	 * @return \App\DataSources\Connectors\Fitbit\Food\TimeSeries
	 */
	public function foodTimeSeries(): TimeSeries{
		return new TimeSeries($this->fitbit());
	}
	/**
	 * @return \App\DataSources\Connectors\Fitbit\Food\Meals\Meals
	 */
	public function meals(): Meals{
		return new Meals($this->fitbit());
	}
	/**
	 * @return \App\DataSources\Connectors\Fitbit\Food\Foods\Logs
	 */
	public function foodLogs(): Logs{
		return new Logs($this->fitbit());
	}
	/**
	 * @return \App\DataSources\Connectors\Fitbit\Food\Favorite\Favorites
	 */
	public function foodFavorites(): Favorites{
		return new Favorites($this->fitbit());
	}
	/**
	 * @return \App\DataSources\Connectors\Fitbit\Body\Fat\Fat
	 */
	public function fat(): Fat{
		return new Fat($this->fitbit());
	}
	/**
	 * @return \App\DataSources\Connectors\Fitbit\Body\Goals\Goals
	 */
	public function bodyGoals(): \App\DataSources\Connectors\Fitbit\Body\Goals\Goals{
		return new \App\DataSources\Connectors\Fitbit\Body\Goals\Goals($this->fitbit());
	}
	/**
	 * @return \App\DataSources\Connectors\Fitbit\Body\Weight\Weight
	 */
	public function weight(): Weight{
		return new Weight($this->fitbit());
	}
	/**
	 * @return \App\DataSources\Connectors\Fitbit\Body\TimeSeries
	 */
	public function bodyTimeSeries(): \App\DataSources\Connectors\Fitbit\Body\TimeSeries{
		return new \App\DataSources\Connectors\Fitbit\Body\TimeSeries($this->fitbit());
	}
	/**
	 * @return \App\DataSources\Connectors\Fitbit\Devices\Alarms
	 */
	public function alarms(): Alarms{
		return new Alarms($this->fitbit());
	}
}
