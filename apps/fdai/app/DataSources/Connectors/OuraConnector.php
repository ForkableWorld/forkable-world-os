<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\OAuth2Connector;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Units\CaloriesUnit;
use App\Units\CountUnit;
use App\Units\DegreesCelsiusUnit;
use App\Units\MetersUnit;
use App\Units\PercentUnit;
use App\Units\SecondsUnit;
use App\VariableCategories\PhysicalActivityVariableCategory;
use App\VariableCategories\SleepVariableCategory;
use App\VariableCategories\VitalSignsVariableCategory;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\CaloriesBurnedCommonVariable;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\DailyStepCountCommonVariable;
use OAuth\Common\Http\Uri\Uri;
use OAuth\OAuth2\Token\StdOAuth2Token;
/** Class OuraConnector
 * @package App\DataSources\Connectors
 */
class OuraConnector extends OAuth2Connector {
	protected const AFFILIATE                            = true;
	protected const BACKGROUND_COLOR                     = '#cc73e1';  // Broken
	protected const CLIENT_REQUIRES_SECRET               = false;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME       = 'Physical Activity';
	protected const DEVELOPER_CONSOLE                    = 'https://cloud.ouraring.com';
	public const    DISABLED_UNTIL                       = "2020-10-06";  // 99 Days
	public const    DISPLAY_NAME                         = 'Oura';
	protected const ENABLED                              = 1;
	protected const GET_IT_URL                           = 'https://ouraring.com';
	public const    ID                                   = 98;
	public const    IMAGE                                = 'https://static.quantimo.do/img/connectors/oura-connector.png';
	protected const LOGO_COLOR                           = '#4cc2c4';
	protected const LONG_DESCRIPTION                     = 'Oura makes activity tracking easy and automatic.';
	public const    NAME                                 = 'oura';
	/**
	 * Scopes
	 * @var string
	 */
	const SCOPE_HEARTRATE = 'heartrate';
	protected const                                                                                                                                                                                                                       SHORT_DESCRIPTION = 'Tracks sleep, diet, and physical activity.';
	public static $BASE_API_URL = "https://api.ouraring.com/v2/usercollection/";
	public static $OAUTH_SERVICE_NAME = 'Oura';
	public static array $SCOPES = [
		self::SCOPE_HEARTRATE,
        'session',
        'tag',
        'workout',
        'daily',
        'personal',
        'email',
        'session',
	];
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public bool $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $logoColor = self::LOGO_COLOR;
	public $longDescription = self::LONG_DESCRIPTION;
	public $name = self::NAME;
	public $shortDescription = self::SHORT_DESCRIPTION;
    public $providesUserProfileForLogin = true;
    public function __construct(int $userId = null)
    {
        parent::__construct($userId);
    }
    /**
	 * {@inheritdoc}
	 */
	public function getAccessTokenEndpoint(){
		return new Uri('https://api.ouraring.com/oauth/token');
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationEndpoint(){
		return new Uri('https://cloud.ouraring.com/oauth/authorize');
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
     */
	public function importData(): void{
        $this->import_daily_readiness();
        $this->import_daily_activity();
        $this->import_daily_sleep();
		$this->saveMeasurements();
	}
    /**
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse($responseBody): StdOAuth2Token
    {
        return $this->parseStandardAccessTokenResponse($responseBody);
    }
    public function getRequest(string $path, array $params = [], array $extraHeaders = [], array $options = [])
    {
        $start = $this->getFromDate();
        $end = $this->getEndDate();
        return parent::getRequest($path."?start_date=$start&end_date=$end", $params, $extraHeaders, $options);
    }
    private function import_daily_readiness()
    {
        $response = $this->getRequest("daily_readiness");
        foreach ($response->data as $item){
            $startAt = db_date($item->timestamp);
            $this->addVitalSignsMeasurement("Daily Readiness Score", $startAt, $item->score,
                PercentUnit::NAME);
            $this->addVitalSignsMeasurement("Temperature Deviation", $startAt, $item->temperature_deviation,
                DegreesCelsiusUnit::NAME);
            $this->addVitalSignsMeasurement("Temperature Trend Deviation", $startAt, $item->temperature_trend_deviation,
                DegreesCelsiusUnit::NAME);
	        if(isset($item->body_temperature)){
		        $this->addVitalSignsMeasurement("Body Temperature Score", $startAt, $item->body_temperature,
		                                        PercentUnit::NAME);
	        }
			if(isset($item->previous_night)){
				$this->addVitalSignsMeasurement("Previous Night Readiness Score Contributor", $startAt,
				                                $item->previous_night,
				                                PercentUnit::NAME);
			}
	        if(isset($item->recovery_index)){
		        $this->addVitalSignsMeasurement("Recovery Index Score", $startAt, $item->recovery_index,
		                                        PercentUnit::NAME);
	        }
	        if(isset($item->resting_heart_rate)){
		        $this->addVitalSignsMeasurement("Resting Heart Rate Score", $startAt, $item->resting_heart_rate,
		                                        PercentUnit::NAME);
	        }
	        if(isset($item->sleep_balance)){
		        $this->addSleepMeasurement("Sleep Balance Score", $startAt, $item->sleep_balance, PercentUnit::NAME);
	        }
	        if(isset($item->activity_balance)){
		        $this->addPhysicalActivityMeasurement("Activity Balance Score", $startAt, $item->activity_balance,
		                                              PercentUnit::NAME);
	        }
	        if(isset($item->hrv_balance)){
		        $this->addVitalSignsMeasurement("Hrv Balance Score", $startAt, $item->hrv_balance, PercentUnit::NAME);
	        }
	        if(isset($item->previous_day_activity)){
		        $this->addPhysicalActivityMeasurement("Previous Day Activity Score", $startAt,
		                                              $item->previous_day_activity, PercentUnit::NAME);
	        }
        }
    }
    private function import_daily_activity()
    {
        $response = $this->getRequest("daily_activity");
        foreach ($response->data as $item){
            $startAt = db_date($item->timestamp);
            $this->addPhysicalActivityMeasurement("Calories Burned While Active", $startAt, $item->active_calories,
                CaloriesUnit::NAME);
            $this->addPhysicalActivityMeasurement("Equivalent Walking Distance", $startAt,
                $item->equivalent_walking_distance, MetersUnit::NAME);
            $this->addPhysicalActivityMeasurement("High Activity Time", $startAt, $item->high_activity_time, SecondsUnit::NAME);
            $this->addPhysicalActivityMeasurement("Inactivity Alerts", $startAt, $item->inactivity_alerts, CountUnit::NAME);
            $this->addPhysicalActivityMeasurement("Low Activity Time", $startAt, $item->low_activity_time, SecondsUnit::NAME);
            $this->addPhysicalActivityMeasurement("Medium Activity Time", $startAt, $item->medium_activity_time, SecondsUnit::NAME);
            $this->addPhysicalActivityMeasurement("Non Wear Time", $startAt, $item->non_wear_time, SecondsUnit::NAME);
            $this->addPhysicalActivityMeasurement("Resting Time Score", $startAt, $item->resting_time, PercentUnit::NAME);
            $this->addPhysicalActivityMeasurement("Daily Activity Score", $startAt, $item->score, PercentUnit::NAME);
            $this->addPhysicalActivityMeasurement("Sedentary Time", $startAt, $item->sedentary_time, SecondsUnit::NAME);
            $this->addPhysicalActivityMeasurement(DailyStepCountCommonVariable::NAME, $startAt, $item->steps,
                CountUnit::NAME);
            $this->addPhysicalActivityMeasurement(CaloriesBurnedCommonVariable::NAME, $startAt, $item->total_calories, CaloriesUnit::NAME);
            $this->addPhysicalActivityMeasurement("Meet Daily Targets Score", $startAt,
                $item->contributors->meet_daily_targets, PercentUnit::NAME);
            $this->addPhysicalActivityMeasurement("Move Every Hour Score", $startAt, $item->contributors->move_every_hour, PercentUnit::NAME);
            $this->addPhysicalActivityMeasurement("Recovery Time Score", $startAt, $item->contributors->recovery_time, PercentUnit::NAME);
            $this->addPhysicalActivityMeasurement("Stay Active Score", $startAt, $item->contributors->stay_active, PercentUnit::NAME);
            $this->addPhysicalActivityMeasurement("Training Frequency Score", $startAt, $item->contributors->training_frequency, PercentUnit::NAME);
            $this->addPhysicalActivityMeasurement("Training Volume Score", $startAt, $item->contributors->training_volume, PercentUnit::NAME);
        }
    }
    private function import_daily_sleep()
    {
        $response = $this->getRequest("daily_sleep");
        foreach ($response->data as $item){
            $startAt = db_date($item->timestamp);
            $this->addSleepMeasurement("Overall Sleep Score", $startAt, $item->score, PercentUnit::NAME);
            $this->addSleepMeasurement("Deep Sleep Score", $startAt, $item->score, PercentUnit::NAME);
            $this->addSleepMeasurement("Sleep Efficiency Score", $startAt, $item->score, PercentUnit::NAME);
            $this->addSleepMeasurement("Sleep Latency Score", $startAt, $item->score, PercentUnit::NAME);
            $this->addSleepMeasurement("Sleep Restfulness Score", $startAt, $item->score, PercentUnit::NAME);
            $this->addSleepMeasurement("Sleep Timing Score", $startAt, $item->score, PercentUnit::NAME);
            $this->addSleepMeasurement("Sleep Duration Score", $startAt, $item->score, PercentUnit::NAME);
        }
    }
    public function addSleepMeasurement(string $variableName, $startTime, $value, string $unitName){
        if($value === null){
            $this->logInfo("No value for $variableName at $startTime");
            return;
        }
        $this->addMeasurement($variableName, $startTime, $value, $unitName,
            SleepVariableCategory::NAME);
    }
    public function addPhysicalActivityMeasurement(string $variableName, $startTime, $value, string $unitName){
        if($value === null){
            $this->logInfo("No value for $variableName at $startTime");
            return;
        }
        $this->addMeasurement($variableName, $startTime, $value, $unitName,
            PhysicalActivityVariableCategory::NAME);
    }
    public function addVitalSignsMeasurement(string $variableName, $startTime, $value, string $unitName){
        if($value === null){
            $this->logInfo("No value for $variableName at $startTime");
            return;
        }
        $this->addMeasurement($variableName, $startTime, $value, $unitName,
            VitalSignsVariableCategory::NAME);
    }
    public function addMeasurement(string $variableName, $startTime, $value, string $unitName,
                                   string $variableCategoryName = null, array $newVariableData = [], int $durationInSeconds = null,
                                          $note = null): QMMeasurement{

        return parent::addMeasurement($variableName." (from Oura)", $startTime, $value, $unitName,
            $variableCategoryName,
            $newVariableData, $durationInSeconds, $note);
    }
    /**
     * @return string
     */
    public function urlUserDetails(): ?string {return 'https://api.ouraring.com/v2/usercollection/personal_info';}
}
