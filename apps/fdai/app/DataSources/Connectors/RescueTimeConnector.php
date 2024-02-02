<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\OAuth2Connector;
use App\DataSources\TooEarlyException;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\DuplicateFailedAnalysisException;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\QMException;
use App\Exceptions\RateLimitConnectorException;
use App\Exceptions\StupidVariableNameException;
use App\Exceptions\TooSlowException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UserVariableNotFoundException;
use App\Logging\QMLog;
use App\Models\Variable;
use App\Properties\Variable\VariableNameProperty;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Controller\Connector\ConnectorRedirectResponse;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Units\HoursUnit;
use App\Units\PercentUnit;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\EnvOverride;
use App\VariableCategories\ActivitiesVariableCategory;
use App\VariableCategories\GoalsVariableCategory;
use App\VariableCategories\SoftwareVariableCategory;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnBusinessActivitiesCommonVariable;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnConsumingNewsActivitiesCommonVariable;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnDesignAndCompositionActivitiesCommonVariable;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnEntertainmentActivitiesCommonVariable;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnReferenceAndLearningActivitiesCommonVariable;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnShoppingActivitiesCommonVariable;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnSocialNetworkingActivitiesCommonVariable;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnSoftwareDevelopmentActivitiesCommonVariable;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnSoftwareUtilitiesActivitiesCommonVariable;
use App\Variables\CommonVariables\GoalsCommonVariables\EfficiencyScoreFromRescuetimeCommonVariable;
use App\Variables\CommonVariables\GoalsCommonVariables\ProductivityPulseFromRescuetimeCommonVariable;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use Guzzle\Http\Exception\ServerErrorResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\OAuth2\Token\StdOAuth2Token;
/** Class RescueTimeConnector
 * @package App\DataSources\Connectors
 */
class RescueTimeConnector extends OAuth2Connector {
	/**
	 * Defined scopes
	 */
	//This is the least restrictive scope
	public const SCOPE_TIME_DATA = 'time_data';
	public const SCOPE_CATEGORY_DATA = 'category_data';
	public const SCOPE_PRODUCTIVITY_DATA = 'productivity_data';
	public const ENDPOINT_VARIABLES = [];
	public const TEST_WEBSITE = 'github.com';
	public const ACTIVITIES_SUFFIX = ' Activities';
	const        PATH_DAILY_SUMMARY_FEED = '/daily_summary_feed';
	const        PATH_DATA = '/data';
	const        KIND_EFFICIENCY = 'efficiency';
	protected const AFFILIATE = true;
	protected const BACKGROUND_COLOR = '#2f78bd';//1 month 1 day(to get today's data in tests) in seconds
	protected const CLIENT_REQUIRES_SECRET = false;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Activities';
	protected const DEVELOPER_CONSOLE = null;
	public const    DISPLAY_NAME = 'RescueTime';
	protected const ENABLED = 1;
	protected const GET_IT_URL = 'https://www.rescuetime.com/rp/quantimodo/plans';
	public const    IMAGE = 'https://applets.imgix.net/https%3A%2F%2Fassets.ifttt.com%2Fimages%2Fchannels%2F1829789558%2Ficons%2Fon_color_large.png%3Fversion%3D0?ixlib=rails-2.1.3&w=240&h=240&auto=compress&s=3b62550176f3456071514c8e510e8ef2';
	protected const KIND_ACTIVITIES = 'activities';
	protected const KIND_CATEGORY = 'category';
	protected const KIND_PRODUCTIVITY = 'productivity';
	protected const LOGO_COLOR = '#2d2d2d';
	protected const LONG_DESCRIPTION = 'Detailed reports show which applications and websites you spent time on. Activities are automatically grouped into pre-defined categories with built-in productivity scores covering thousands of websites and applications. You can customize categories and productivity scores to meet your needs.';
	protected const PREMIUM = true;
	protected const SHORT_DESCRIPTION = 'Tracks productivity, phone, and computer usage.';
	public const    TIME_SPENT = "Time Spent ";
	public const    TIME_SPENT_ON = self::TIME_SPENT."On ";
	public const    TIME_SPENT_USING = self::TIME_SPENT."Using ";
	protected const VARIABLE_MODERATELY_UNPRODUCTIVE = self::TIME_SPENT.
	                                                   'Moderately Unproductively'; // Always keep On capitalized to avoid complexity associated with case-sensitive model file names and case-insensitive mysql
	protected const VARIABLE_NAME_NEUTRAL_HOURS = "Neutral Hours";
	protected const VARIABLE_NAME_TOTAL_HOURS_FROM_RESCUE_TIME = "Total Hours From RescueTime";
	protected const VARIABLE_NAME_UNCATEGORIZED_HOURS_FROM_RESCUE_TIME = "Uncategorized Hours From RescueTime";
	protected const VARIABLE_VERY_UNPRODUCTIVE = self::TIME_SPENT.'Very Unproductively';
	public const APP_OR_WEBSITE_SUFFIX = ' usage';
	public const GET_APPS_AND_WEBSITES = false;
	public const ID = 11;
	public const NAME = 'rescuetime';
	private const TYPE_TO_VARIABLE_NAME = [
		'business_hours' => TimeSpentOnBusinessActivitiesCommonVariable::NAME,
		'social_networking_hours' => TimeSpentOnSocialNetworkingActivitiesCommonVariable::NAME,
		'design_and_composition_hours' => TimeSpentOnDesignAndCompositionActivitiesCommonVariable::NAME,
		'entertainment_hours' => TimeSpentOnEntertainmentActivitiesCommonVariable::NAME,
		'news_hours' => TimeSpentOnConsumingNewsActivitiesCommonVariable::NAME,
		'software_development_hours' => TimeSpentOnSoftwareDevelopmentActivitiesCommonVariable::NAME,
		'reference_and_learning_hours' => TimeSpentOnReferenceAndLearningActivitiesCommonVariable::NAME,
		'shopping_hours' => TimeSpentOnShoppingActivitiesCommonVariable::NAME,
		'utilities_hours' => TimeSpentOnSoftwareUtilitiesActivitiesCommonVariable::NAME,
	];
	public static $BASE_API_URL = 'https://www.rescuetime.com/api/oauth';
	public static $OAUTH_SERVICE_NAME = 'RescueTime';
	public static array $SCOPES = [
		'time_data',
		'category_data',
		'productivity_data',
	];
	public static $stupidVariableNames = [
		'Resting Heart Rate (count)',
		self::TIME_SPENT.'Productively',
		// Let's only use SCORES, not TIME because more TOTAL time logged causes us to get positive user_variable_relationships with both productive and unproductive time for the same variable
		self::TIME_SPENT.'Unproductively',
		// Let's only use SCORES, not TIME because more TOTAL time logged causes us to get positive user_variable_relationships with both productive and unproductive time for the same variable
		self::TIME_SPENT.'Moderately Productively',
		// Let's only use SCORES, not TIME because more TOTAL time logged causes us to get positive user_variable_relationships with both productive and unproductive time for the same variable
		self::VARIABLE_MODERATELY_UNPRODUCTIVE,
		// Let's only use SCORES, not TIME because more TOTAL time logged causes us to get positive user_variable_relationships with both productive and unproductive time for the same variable
		self::TIME_SPENT.'Very Productively',
		// Let's only use SCORES, not TIME because more TOTAL time logged causes us to get positive user_variable_relationships with both productive and unproductive time for the same variable
		self::VARIABLE_VERY_UNPRODUCTIVE,
		// Let's only use SCORES, not TIME because more TOTAL time logged causes us to get positive user_variable_relationships with both productive and unproductive time for the same variable
		self::VARIABLE_NAME_NEUTRAL_HOURS,
		self::VARIABLE_NAME_TOTAL_HOURS_FROM_RESCUE_TIME,
		self::VARIABLE_NAME_UNCATEGORIZED_HOURS_FROM_RESCUE_TIME,
		'General Utilities Activities',
		// Let's only use SCORES, not TIME because more TOTAL time logged causes us to get positive user_variable_relationships with both productive and unproductive time for the same variable
		'Moderately Productive Time',
		// Let's only use SCORES, not TIME because more TOTAL time logged causes us to get positive user_variable_relationships with both productive and unproductive time for the same variable
		'Very Productive Time',
		// Let's only use SCORES, not TIME because more TOTAL time logged causes us to get positive user_variable_relationships with both productive and unproductive time for the same variable
		'Very Unproductive Time',
		// Let's only use SCORES, not TIME because more TOTAL time logged causes us to get positive user_variable_relationships with both productive and unproductive time for the same variable
		'Moderately Unproductive Time',
		// Let's only use SCORES, not TIME because more TOTAL time logged causes us to get positive user_variable_relationships with both productive and unproductive time for the same variable
	];
	private static $LIMIT = 2678400;
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $image = self::IMAGE; // TOO SLOW
	public $logoColor = self::LOGO_COLOR;
	public $logoutUrl = 'https://www.rescuetime.com/logout';
	public $longDescription = self::LONG_DESCRIPTION;
	public $name = self::NAME;
	public $premium = self::PREMIUM;
	public $shortDescription = self::SHORT_DESCRIPTION;
	public $variableCategoryName = SoftwareVariableCategory::NAME;
	/**
	 * RescueTimeConnector constructor.
	 * @param $userId
	 */
	public function __construct($userId){
		parent::__construct($userId);
		$this->setCreateRemindersForNewVariables(false);
	}
	/**
	 * @throws DuplicateFailedAnalysisException
	 * @throws AlreadyAnalyzedException
	 * @throws StupidVariableNameException
	 * @throws ModelValidationException
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 * @throws AlreadyAnalyzingException
	 */
	public static function renameRescuetimeVariables(): void{
		$rows = Variable::whereVariableCategoryId(14)->where(Variable::FIELD_DEFAULT_UNIT_ID, 34)
		                ->where(Variable::FIELD_IS_PUBLIC, 1)
			//->whereRaw("name like '% Usage%'")
			            ->get();
		foreach($rows as $row){
			$synonyms = $row->synonyms;
			$synonyms[] = $row->name;
			$oldName = $row->name;
			$newName = RescueTimeConnector::rescuetimeCategoryToVariableName($oldName);
			if($newName === $oldName){
				continue;
			}
			\App\Logging\ConsoleLog::info("Renaming
             $oldName to
                  $newName");
			VariableNameProperty::replaceVariableNameAndUpdate($oldName, $newName);
		}
	}
	public static function rescuetimeCategoryToVariableName(string $category): string{
		$new = self::TIME_SPENT_ON.$category.self::ACTIVITIES_SUFFIX;
		$new = str_replace(self::TIME_SPENT_ON.self::TIME_SPENT_ON, self::TIME_SPENT_ON, $new);
		$new = str_replace("Time Spent on Time Spent On ", self::TIME_SPENT_ON, $new);
		$new = str_replace("Activities Activities", "Activities", $new);
		$new = str_replace("Time Spent on Time Spent ", self::TIME_SPENT_ON, $new);
		$new = str_replace("Time Spent on Using ", self::TIME_SPENT_ON, $new);
		$new = str_replace(" Hours Activities", " Activities", $new);
		$new = str_replace("  ", " ", $new);
		$new = str_replace("♥", "Love", $new);
		$new = str_replace("Time Spent On Time Spent on ", self::TIME_SPENT_ON, $new);
		$new = str_replace("Time Spent On Time Spent ", self::TIME_SPENT_ON, $new);
		return $new;
	}
	public static function generateActivitiesHardCodeVariableModels(){
		$variables = Variable::query()->where(Variable::FIELD_NAME, \App\Storage\DB\ReadonlyDB::like(), '%Time Spent %')->get();
		/** @var Variable $v */
		foreach($variables as $v){
			$cv = $v->getDBModel();
			$cv->generateChildModelCode();
		}
	}
	/**
	 * @param array $parameters
	 * @return ConnectorRedirectResponse
	 */
	public function connect($parameters): ConnectorRedirectResponse{
		if(!$this->getConnectorClientId()){
			le("We don't have a rescuetime API key for the domain ".$this->getCallbackRedirectUrl());
		}
		return parent::connect($parameters);
	}
	/**
	 * @return string
	 */
	public function getConnectorClientId(): ?string{
		$url = $this->getCallbackRedirectUrl();
		if(stripos($url, 'app.quantimo.do') !== false || stripos($url, 'staging.quantimo.do') !== false){
			return config('services.rescuetime.client_id');
		}
		return "no-client-id-yet-for-callback-".$url;
	}
	/**
	 * @return int|QMUserVariable[]
	 * @throws ConnectorException
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooEarlyException
	 * @throws TooSlowException
	 * @throws TooEarlyException
	 */
	public function importData(): void{
		$fromTime = $this->getFromTime();
		$providedFromTime = $fromTime;
		$this->currentUrl = "TODO: Loop through existing measurements to get any gaps from previous imports";
		$pp = $this->getProductivityPulseUserVariable();
		$fromTime = $this->determineInitialFromTime($providedFromTime, $pp);
		$this->getMeasurementItemsFromRescuetime($fromTime, self::KIND_CATEGORY);
		if(self::GET_APPS_AND_WEBSITES){
			$this->getMeasurementItemsFromRescuetime($fromTime, self::KIND_ACTIVITIES);
		}
		$this->getDailySummary();
		$this->getEfficiencyScore($fromTime);
		$this->saveMeasurements();
	}
	/**
	 * @return QMUserVariable
	 */
	private function getProductivityPulseUserVariable(): QMUserVariable{
		try {
			return $this->getQMUserVariable(ProductivityPulseFromRescuetimeCommonVariable::NAME);
		} catch (ModelValidationException $e) {
			le($e);
		}
	}
	/**
	 * @param string $variableName
	 * @param string|null $defaultUnitName
	 * @param string|null $variableCategoryName
	 * @param array $params
	 * @return QMUserVariable|null
	 * @throws ModelValidationException
	 */
	public function getQMUserVariable(string $variableName, string $defaultUnitName = null,
	                                  string $variableCategoryName = null, array $params = []): ?QMUserVariable{
		$params[Variable::FIELD_MANUAL_TRACKING] = false;
		$uv = parent::getQMUserVariable($variableName, $defaultUnitName, $variableCategoryName, $params);
		$v = $uv->getVariable();
		if($v->manual_tracking !== false){
			$this->logError("Setting $v manual_tracking to false");
			$v->manual_tracking = false;
			$v->save();
			$uv->manualTracking = false;
		}
		if($v->manual_tracking !== false){
			le("$v");
		}
		if($uv->manualTracking !== false){
			le("$uv");
		}
		return $uv;
	}
	/**
	 * @param int $fromTime
	 * @param string $kind
	 * @throws ConnectorException
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooEarlyException
	 * @throws TooSlowException
	 * @throws RateLimitConnectorException
	 */
	private function getMeasurementItemsFromRescuetime(int $fromTime, string $kind){
		while($fromTime < time()){
			if($this->weShouldBreak()){
				break;
			}
			$params = $this->getRequestParameters($fromTime, $kind);
			try {
				$data = $this->fetchArray(self::PATH_DATA, $params);
			} catch (ServerErrorResponseException $e) { // Not sure why but we always get 502 Bad Gateways after about 20 requests
				$this->logError(__METHOD__.": ".$e->getMessage());
				if($e->getResponse()->getStatusCode() === 502){
					$fromTime += self::$LIMIT;
					continue;
				}
				throw $e;
			}
			if($this->getLastStatusCode() === 200){
				$this->logInfo(count($data['rows'])." rows of $kind data");
				if(isset($data['rows'])){
					if(!count($data['rows'])){
						$fromTime += self::$LIMIT;
						continue;
					}
					foreach($data['rows'] as $row){
						$this->addHoursMeasurement($kind, $row);
					}
					$fromTime += self::$LIMIT;
				}
			} else{
				$this->handleUnsuccessfulResponses($data);
			}
		}
	}
	/**
	 * @param int $fromTime
	 * @param string $kind
	 * @return array
	 */
	private function getRequestParameters(int $fromTime, string $kind): array{
		$endTime = $fromTime + self::$LIMIT;
		if($endTime > time()){
			$endTime = time();
		}
		return [
			'format' => 'json',
			'version' => 0,
			'perspective' => 'interval',
			// rank, interval, member
			'resolution_time' => 'day',
			// month, week, day, hour
			'restrict_kind' => $kind,
			// overview, category, activity, productivity, efficiency
			'restrict_begin' => date('Y-m-d', $fromTime),
			'restrict_end' => date('Y-m-d', $endTime),
		];
	}
	/**
	 * @param string $kind
	 * @param $row
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooEarlyException
	 * @throws TooSlowException
	 * @throws TooEarlyException
	 */
	private function addHoursMeasurement(string $kind, $row): void{
		//$measurementArray = {array} [6]
		//      0 = "Date"
		//      1 = "Time Spent (seconds)"
		//      2 = "Number of People"
		//      3 = "Activity"
		//      4 = "Category"
		//      5 = "Productivity"
		/** @noinspection MultiAssignmentUsageInspection */
		$activity = $row[3];
		if($kind === self::KIND_ACTIVITIES && AppMode::isTestingOrStaging() &&
		   stripos($activity, self::TEST_WEBSITE) === false){
			return;
		}
		self::checkEditingIDEVariable();
		if($kind === self::KIND_CATEGORY){
			$variableName = self::rescuetimeCategoryToVariableName($activity);
		} else{
			$variableName = self::rescuetimeAppOrWebsiteToVariableName($activity);
		}
		if(VariableNameProperty::isStupid($variableName)){
			return;
		}
		if(strpos($activity, '.') !== false){
			$variableName = $activity.self::ACTIVITIES_SUFFIX;
		}
		$m = $this->addMeasurement($variableName, $row[0], $row[1] / 3600, HoursUnit::NAME,
		                           ActivitiesVariableCategory::NAME, []);
		self::checkEditingIDEVariable();
		if($m){
			$m->setMessage($activity);
		}
	}
	public static function checkEditingIDEVariable(int $userId = null){
		self::assertNotManualTracking("Editing & IDEs Usage", $userId);
	}
	public static function assertNotManualTracking(string $name, int $userId = null){
		if(!AppMode::isTestingOrStaging()){
			return;
		}
		return; // TODO: uncomment after generating hard coded variables
		$db = Variable::findByName($name);
		if($db && $db->manual_tracking){
			throw new \LogicException("$db DB row should not be manual tracking!");
		}
		$cv = QMCommonVariable::findByNameOrId($name);
		if($cv && $cv->manualTracking){
			throw new \LogicException("$cv common variable should not be manual tracking!");
		}
		if($userId){
			try {
				$uv = QMUserVariable::getByNameOrId($userId, EfficiencyScoreFromRescuetimeCommonVariable::ID);
				if($uv->manualTracking){
					throw new \LogicException("$uv user variable should not be manual tracking!");
				}
			} catch (UserVariableNotFoundException $e) {
			}
		}
	}
	public static function rescuetimeAppOrWebsiteToVariableName(string $name): string{
		return self::TIME_SPENT_USING.$name;
	}
	/**
	 * @param string $variableName
	 * @param $startTime
	 * @param $value
	 * @param string $unitName
	 * @param string|null $variableCategoryName
	 * @param array $newVariableData
	 * @param int|null $durationInSeconds
	 * @param null $note
	 * @return QMMeasurement
	 * @throws TooEarlyException
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooSlowException
	 */
	public function addMeasurement(string $variableName, $startTime, $value, string $unitName,
	                               string $variableCategoryName = null, array $newVariableData = [],
	                               int    $durationInSeconds = null, $note = null): QMMeasurement{
		$m = parent::addMeasurement($variableName, $startTime, $value, $unitName, $variableCategoryName,
		                            $newVariableData, $durationInSeconds, $note);
		$uv = $m->getQMUserVariable();
		if($uv->manualTracking){
			$lUV = $uv->l();
			$v = $lUV->variable;
			le($uv->name." should not be manualTracking", $uv);
		}
		if($uv->getVariable()->manual_tracking){
			le('$uv->getVariable()->manual_tracking');
		}
		return $m;
	}
	/**
	 * @throws ConnectorException
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooEarlyException
	 * @throws TooSlowException
	 */
	private function getDailySummary(){
		$url = $this->getUrlForPath(self::PATH_DAILY_SUMMARY_FEED, ['format' => 'json']);
		$dailySummaries = $this->fetchArray($url);
		/** @noinspection TypeUnsafeComparisonInspection */
		if($this->getLastStatusCode() == 200){
			$this->addDailySummaryItems($dailySummaries);
		} else{
			$this->handleUnrecognizedStatusCode($url, $dailySummaries, $this->getLastStatusCode());
		}
	}
	/**
	 * @param array $dailySummaries
	 * @throws TooEarlyException
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooSlowException
	 */
	private function addDailySummaryItems(array $dailySummaries){
		foreach($dailySummaries as $dailySummary){
            if(!is_array($dailySummary)){
                $dailySummary = json_decode(json_encode($dailySummary), true);
            }
			$durationInHours = $dailySummary['total_hours'];
			$this->addMeasurement(ProductivityPulseFromRescuetimeCommonVariable::NAME, $dailySummary["date"],
			                      $dailySummary['productivity_pulse'], PercentUnit::NAME,
			                      ActivitiesVariableCategory::NAME, [], $durationInHours * 3600);
			foreach(self::TYPE_TO_VARIABLE_NAME as $type => $variableName){
				$hours = $dailySummary[$type];
				$this->addMeasurement($variableName, $dailySummary["date"], $hours, HoursUnit::NAME,
				                      ActivitiesVariableCategory::NAME, [], $hours * 3600);
			}
		}
	}
	/**
	 * @param int $fromTime
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooSlowException
	 * @throws ConnectorException
	 */
	private function getEfficiencyScore(int $fromTime){
		$maxSecondsForEndPoint = 86400; // Only returns one day for some reason
		while($fromTime < time()){
			if($this->weShouldBreak()){
				break;
			}
			$params = $this->getRequestParameters($fromTime, self::KIND_EFFICIENCY);
			try {
				$data = $this->fetchArray(self::PATH_DATA, $params);
			} catch (ServerErrorResponseException $e) {
				$this->logError(__METHOD__.": ".$e->getMessage());
				$fromTime += $maxSecondsForEndPoint;
				continue;
			}
			switch($code = $this->getLastStatusCode()) {
				case 200:
					if(isset($data['rows'])){
						if(!count($data['rows'])){
							$fromTime += self::$LIMIT;
							$this->logInfo('RescueTime: No more records ');
							/** @noinspection SwitchContinuationInLoopInspection */
							break;
						}
						foreach($data['rows'] as $row){
							$this->addEfficiencyMeasurement($row);
						}
						$fromTime += $maxSecondsForEndPoint;
					}
					break;
				case 403:
				case QMException::CODE_UNAUTHORIZED:
					$this->handleUnauthorizedResponse($code, $this->getCurrentUrl(), $data);
					break;
				case 409:
					$this->logWarning('RescueTime: Rate limit reached');
					$this->sleep();
					break;
				default:
					$this->handleUnrecognizedStatusCode($this->getCurrentUrl(), $data, $code);
			}
		}
	}
	/**
	 * @param $data
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooEarlyException
	 * @throws TooSlowException
	 * @throws TooEarlyException
	 */
	private function addEfficiencyMeasurement($data){
		$date = $data[0];
		$durationInSeconds = $data[1];
		$efficiencyPercent = $data[4];
		self::checkEfficiencyScoreVariable();
		$this->addMeasurement(EfficiencyScoreFromRescuetimeCommonVariable::NAME, $date, $efficiencyPercent,
		                      PercentUnit::NAME, GoalsVariableCategory::NAME, [], $durationInSeconds);
		self::checkEfficiencyScoreVariable();
	}
	public static function checkEfficiencyScoreVariable(int $userId = null){
		self::assertNotManualTracking(EfficiencyScoreFromRescuetimeCommonVariable::NAME, $userId);
	}
	/**
	 * @return string
	 */
	public function getOrSetConnectorClientSecret(): string{
		$url = $this->getCallbackRedirectUrl();
		if(stripos($url, 'app.quantimo.do') !== false || stripos($url, 'staging.quantimo.do') !== false){
			$secret = config('services.rescuetime.client_secret');
			$host = Env::getFormatted('APP_URL');
			if(EnvOverride::isLocal() && $host !== "https://local.quantimo.do"){
				le("Why is root .env APP_URL $host");
			}
			if(empty($secret)){
				le("No rescuetime secret for CallbackRedirectUrl $url");
			}
		} else{
			$secret = "We don't have a rescuetime API secret for the domain ".$url;
		}
		return $secret;
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationUri(array $additionalParameters = []): UriInterface{
		$url = $this->getCallbackRedirectUrl();
		if(stripos($url, 'app.quantimo.do') === false && stripos($url, 'staging.quantimo.do') === false){
			return new Uri("https://we-do-not-have-a-client-for-".\App\Utils\Env::getAppUrl()."-yet");
		}
		$parameters = array_merge($additionalParameters, [
			'client_id' => $this->getConnectorClientId(),
			'redirect_uri' => $url,
			'response_type' => 'code',
		]);
		$parameters['scope'] = implode(' ', $this->getScopes());
		// Build the url
		$url = clone $this->getAuthorizationEndpoint();
		foreach($parameters as $key => $val){
			$url->addToQuery($key, $val);
		}
		return new Uri($url);
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationEndpoint(){
		return new Uri('https://www.rescuetime.com/oauth/authorize');
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAccessTokenEndpoint(){
		return new Uri('https://www.rescuetime.com/oauth/token');
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
	protected function parseAccessTokenResponse($responseBody): StdOAuth2Token{
		$data = $this->jsonDecodeAccessTokenResponse($responseBody);
		return $this->newStdOAuth2Token($data);
	}
}
