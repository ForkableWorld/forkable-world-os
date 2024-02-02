<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\OAuth2Connector;
use App\DataSources\QMConnectorResponse;
use App\DataSources\QMTokenStorage;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Slim\Controller\Connector\ConnectorRedirectResponse;
use App\Slim\Model\Measurement\MeasurementSet;
use App\Units\DegreesCelsiusUnit;
use App\Units\IndexUnit;
use App\Units\KilogramsUnit;
use App\Units\MetersPerSecondUnit;
use App\Units\PercentUnit;
use App\VariableCategories\EnvironmentVariableCategory;
use App\VariableCategories\PhysiqueVariableCategory;
use App\VariableCategories\VitalSignsVariableCategory;
use App\Variables\CommonVariables\EnvironmentCommonVariables\IndoorTemperatureCommonVariable;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\CaloriesBurnedCommonVariable;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\DailyStepCountCommonVariable;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\WalkOrRunDistanceCommonVariable;
use App\Variables\CommonVariables\PhysiqueCommonVariables\BodyWeightCommonVariable;
use App\Variables\CommonVariables\PhysiqueCommonVariables\FatFreeMassFfmOrLeanBodyMassLbmCommonVariable;
use App\Variables\CommonVariables\PhysiqueCommonVariables\FatMassWeightCommonVariable;
use App\Variables\CommonVariables\PhysiqueCommonVariables\FatRatioCommonVariable;
use App\Variables\CommonVariables\PhysiqueCommonVariables\HeightCommonVariable;
use App\Variables\CommonVariables\VitalSignsCommonVariables\BloodPressureDiastolicBottomNumberCommonVariable;
use App\Variables\CommonVariables\VitalSignsCommonVariables\BloodPressureSystolicTopNumberCommonVariable;
use App\Variables\CommonVariables\VitalSignsCommonVariables\CoreBodyTemperatureCommonVariable;
use App\Variables\CommonVariables\VitalSignsCommonVariables\HeartRatePulseCommonVariable;
use App\Variables\QMUserVariable;
use DateTime;
use DateTimeZone;
use LogicException;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Token\AbstractToken;
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth2\Token\StdOAuth2Token;
use RuntimeException;
/** Class WithingsConnector
 * @package App\DataSources\Connectors
 * TODO: GET CO2 and TEMPERATURE DATA WITH
 * https://github.com/dhayab/homebridge-withings-air-quality/blob/24a6040a431f69b3e42f6f36b0543b443010b74d/src/lib/api.ts
 */
class WithingsConnector extends OAuth2Connector{
	/**
	 * Scopes
	 *
	 * @var string
	 */
	const SCOPE_INFO  = 'user.info',
		SCOPE_METRICS   = 'user.metrics',
        SCOPE_SLEEP    = 'user.sleepevents',
		SCOPE_ACTIVITY    = 'user.activity';
	/** @var QMTokenStorage */
	protected $storage;
	/**
	 * Withings URL.
	 *
	 * @const string
	 */
	public const BASE_WITHINGS_URL = 'https://account.withings.com';
	/**
	 * Withings API URL
	 *
	 * @const string
	 */
	public static $BASE_API_URL = 'https://wbsapi.withings.net';
	/**
	 * HTTP header Accept-Language.
	 *
	 * @const string
	 */
	public const HEADER_ACCEPT_LANG = 'Accept-Language';
	/**
	 * HTTP header Accept-Locale.
	 *
	 * @const string
	 */
	public const HEADER_ACCEPT_LOCALE = 'Accept-Locale';
	/**
	 * @var string Key used in a token response to identify the resource owner.
	 */
	public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'userid';
    const SKIN_TEMPERATURE = 'Skin Temperature';
    const MUSCLE_MASS = 'Muscle Mass';
    const BONE_MASS = 'Bone Mass';
    const PULSE_WAVE_VELOCITY = 'Pulse Wave Velocity';
    const VO_2_MAX = 'VO2 max';
    const HYDRATION = 'Hydration';
    const SP_O_2 = 'SpO2';
    private $measureTypesActivity;
	private $measureTypesBody;
    protected const AFFILIATE = true;
    protected const BACKGROUND_COLOR = '#00afd8';
    protected const BASE_URL = 'https://wbsapi.withings.net/';
    protected const CLIENT_REQUIRES_SECRET = true;
    protected const CONNECTOR_ID = 9;
    protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Physical Activity';
    protected const DEVELOPER_CONSOLE = 'https://oauth.withings.com/partner/dashboard';
    public const DISPLAY_NAME = 'Withings';
    protected const ENABLED = 1;
    protected const GET_IT_URL = 'https://partners.withings.com/c/71745/58308/583';
    public const IMAGE = 'https://i.imgur.com/GZ7Kw8A.png';
    protected const LOGO_COLOR = '#2d2d2d';
    protected const LONG_DESCRIPTION = 'Withings creates smart products and apps to take care of yourself and your loved ones in a new and easy way. Discover the Withings Pulse, Wi-Fi Body Scale, and Blood Pressure Monitor.';
    protected const PREMIUM = true;
    protected const SHORT_DESCRIPTION = 'Tracks sleep, blood pressure, heart rate, weight, temperature, CO2 levels, and physical activity.';
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
    public $getItUrl = self::GET_IT_URL;
    public $id = self::ID;
    public $image = self::IMAGE;
    public $logoColor = self::LOGO_COLOR;
    public $longDescription = self::LONG_DESCRIPTION;
    public $name = self::NAME;
    public $premium = self::PREMIUM;
    public $shortDescription = self::SHORT_DESCRIPTION;
    public const ID = 9;
    public const NAME = 'withings';
    public static $OAUTH_SERVICE_NAME = 'Withings';
    public static array $SCOPES = [
        self::SCOPE_INFO,
        self::SCOPE_METRICS,
        self::SCOPE_ACTIVITY,
        self::SCOPE_SLEEP
    ];
    public $variableNames = [
        BodyWeightCommonVariable::NAME,
        //'Height',
        FatFreeMassFfmOrLeanBodyMassLbmCommonVariable::NAME,
        FatRatioCommonVariable::NAME,
        FatMassWeightCommonVariable::NAME,
        BloodPressureDiastolicBottomNumberCommonVariable::NAME,
        BloodPressureSystolicTopNumberCommonVariable::NAME,
        HeartRatePulseCommonVariable::NAME,
        DailyStepCountCommonVariable::NAME,
        WalkOrRunDistanceCommonVariable::NAME,
        CaloriesBurnedCommonVariable::NAME,
        //'Elevation',
    ];
    private static $wbsapiErrors = [
        0    => 'Operation was successful',
        247  => 'The userid provided is absent, or incorrect',
        250  => 'The provided userid and/or Oauth credentials do not match',
        286  => 'No such subscription was found',
        293  => 'The callback URL is either absent or incorrect',
        294  => 'No such subscription could be deleted',
        304  => 'The comment is either absent or incorrect',
        305  => 'Too many notifications are already set',
        342  => 'The signature (using Oauth) is invalid',
        343  => 'Wrong Notification Callback Url don\'t exist',
        601  => 'Too Many Requests',
        2554 => 'Unspecified unknown error occurred',
        2555 => 'An unknown error occurred'
    ];
    private string $signature;
    /**
     * @var mixed|null
     */
    private $nonce;

    /**
     * @param array $parameters
     * @return ConnectorRedirectResponse|QMConnectorResponse
     * @throws TokenResponseException
     */
    public function connect($parameters){
        $this->logDebug(__METHOD__);
        return parent::connect($parameters);
    }
    /**
     * @return int
     * @throws InvalidVariableValueAttributeException
     */
    public function importData(): void{
        $this->setClosures();
        $this->getActivityMetrics();
        $this->getBodyMetrics();
        $this->saveMeasurements();
    }
    public function getAbsoluteFromAt(): string{
        return db_date(time() - 94608000);
    }
	/**
	 * @throws InvalidVariableValueAttributeException
	 * @throws \App\Exceptions\TooSlowException
	 */
    private function getBodyMetrics(): void {
        $fromAt = $this->getFromAt();
        $this->setCurrentFromAndEndTime($fromAt, 0);
        $url = self::BASE_URL. 'measure?action=getmeas&startdate='.strtotime($fromAt).'&enddate='.time();
        $data = $this->fetchArray($url);
        $code = $this->getLastStatusCode();
        if($code === 200){
            $wCode = $data['status'];
            if(!$wCode){
                foreach($data['body']['measuregrps'] as $group){
                    // we need only Measure category
                    if((int)$group['category'] ===1){
                        $timestamp = $group['date'];
                        foreach($group['measures'] as $measure){
                            $value = $measure['value'] * (10 ** $measure['unit']);
                            $v = $this->getUserVariableForBodyMetric($measure['type']);
                            $unit = $this->getUnitNameForBodyMetric($measure['type']);
                            $this->addMeasurement($v->name, $timestamp, $value, $unit);
                        }
                    }
                }
            }elseif(isset(self::$wbsapiErrors[$wCode])){
                $this->handleUnsuccessfulResponses($data);
                throw new RuntimeException('Withings says: '.self::$wbsapiErrors[$wCode]);
            }else{
                throw new RuntimeException('Withings says: unknown error code '.$code);
            }
        }else{
            le("Please implement handling of ".$this->getLastStatusCode()." from $this");
        }
    }
	/**
	 * @throws \App\Exceptions\TooSlowException
	 */
	private function getActivityMetrics(): void {
        $fromAt = $this->getFromAt();
        $this->setCurrentFromAndEndTime($fromAt, 0);
        $url = self::BASE_URL. 'v2/measure?action=getactivity&startdateymd='.date('Y-m-d', strtotime($fromAt)).
            '&enddateymd='.date('Y-m-d');
        $data = $this->fetchArray($url);
        if($tz = $data["body"]["activities"][0]["timezone"] ?? null){
            $u = $this->getQmUser();
            if(!$u->timezone){$u->setTimeZone($tz);}
        }
        $code = $this->getLastStatusCode();
        if($code === 200){
            $wCode = $data['status'];
            if($wCode === 0){
                $activityNameArray = array_keys($this->measureTypesActivity);
                foreach($data['body']['activities'] as $activities){
                    try {
                        $date = new DateTime($activities['date'], new DateTimeZone($activities['timezone']));
                    } catch (\Exception $e) {
                        /** @var LogicException $e */
                        throw $e;
                    }
                    $timestamp = $date->getTimestamp();
                    foreach($activityNameArray as $activity){
                        if(isset($activities[$activity])){
                            $value = $activities[$activity];
                            $v = $this->getUserVariableForActivity($activity);
                            $unit = $this->getUnitNameForActivity($activity);
                            try {
                                $this->addMeasurement($v->name, $timestamp, $value, $unit);
                            } catch (InvalidVariableValueAttributeException $e) {
                                $this->logError(__METHOD__.": ".$e->getMessage());
                            }
                        }
                    }
                }
            }elseif(isset(self::$wbsapiErrors[$wCode])){
                $this->handleUnsuccessfulResponses($data);
                le('Withings says: '.self::$wbsapiErrors[$wCode].$data["error"]." from $url");
            }else{
                le('Withings says: unknown error code '.$code);
            }
        }else{
            le("Withings: Received $code for BodyMetrics".$data);
        }
    }
    /**
     * @param string $type
     * @return QMUserVariable
     */
    private function getUserVariableForActivity(string $type): QMUserVariable {
        $measurementSet = $this->getMeasurementSetForActivity($type);
        return $this->getQMUserVariable($measurementSet->getVariableName(),
                                        $measurementSet->getOriginalUnit()->name, $measurementSet->variableCategoryName);
    }
    /**
     * @param string $type
     * @return string
     */
    private function getUnitNameForActivity(string $type): string {
        $measurementSet = $this->getMeasurementSetForActivity($type);
        return $measurementSet->getOriginalUnit()->name;
    }
    /**
     * @param string $type
     * @return MeasurementSet
     */
    private function getMeasurementSetForActivity(string $type): MeasurementSet {
        /** @var MeasurementSet $measurementSet */
        $measurementSet = $this->measureTypesActivity[$type]([]);
        return $measurementSet;
    }
    /**
     * @param string $type
     * @return MeasurementSet
     */
    private function getMeasurementSetForBodyMetric(string $type): MeasurementSet {
        /** @var MeasurementSet $measurementSet */
        $measurementSet = $this->measureTypesBody[$type]['set']([]);
        return $measurementSet;
    }
    /**
     * @param string $type
     * @return QMUserVariable
     */
    private function getUserVariableForBodyMetric(string $type): QMUserVariable {
        $measurementSet = $this->getMeasurementSetForBodyMetric($type);
        return $this->getQMUserVariable($measurementSet->getVariableName(),
                                        $measurementSet->getOriginalUnit()->name, $measurementSet->variableCategoryName);
    }
    /**
     * @param string $type
     * @return string
     */
    private function getUnitNameForBodyMetric(string $type): string {
        $measurementSet = $this->getMeasurementSetForBodyMetric($type);
        return $measurementSet->getOriginalUnit()->name;
    }
    public function setClosures(): void{
        // See https://developer.withings.com/api-reference#operation/measure-getmeas
        $this->measureTypesBody = [
            1  => [
                'name' => 'weight',
                'set'  => function($measurements){
                    return new MeasurementSet(BodyWeightCommonVariable::NAME,
                        $measurements,
                        'kg',
                        'Physique',
                        $this->displayName,
                        'MEAN');
                },
            ],
            4  => [
                'name' => 'height',
                'set'  => function($measurements){
                    return new MeasurementSet(
                        HeightCommonVariable::NAME,
                        $measurements,
                        'm', 'Physique', $this->displayName,
                        'MEAN');
                },
            ],
            5  => [
                'name' => 'fat_free_mass',
                'set'  => function($measurements){
                    return new MeasurementSet(
                        FatFreeMassFfmOrLeanBodyMassLbmCommonVariable::NAME,
                        $measurements,
                        'kg',
                        'Physique',
                        $this->displayName,
                        'MEAN');
                },
            ],
            6  => [
                'name' => 'fat_ratio',
                'set'  => function($measurements){
                    return new MeasurementSet(FatRatioCommonVariable::NAME,
                        $measurements,
                        '%', 'Physique', $this->displayName,
                        'MEAN');
                },
            ],
            8  => [
                'name' => 'fat_mass_weight',
                'set'  => function($measurements){
                    return new MeasurementSet(FatMassWeightCommonVariable::NAME,
                        $measurements,
                        'kg',
                        'Physique',
                        $this->displayName,
                        'MEAN');
                },
            ],
            9  => [
                'name' => 'diastolic_blood_pressure',
                'set'  => function($measurements){
                    return new MeasurementSet(BloodPressureDiastolicBottomNumberCommonVariable::NAME,
                        $measurements,
                        'mmHg',
                        'Vital Signs',
                        $this->displayName,
                        'MEAN');
                },
            ],
            10 => [
                'name' => 'systolic_blood_pressure',
                'set'  => function($measurements){
                    return new MeasurementSet(BloodPressureSystolicTopNumberCommonVariable::NAME,
                        $measurements,
                        'mmHg',
                        'Vital Signs',
                        $this->displayName,
                        'MEAN');
                },
            ],
            11 => [
                'name' => 'heart_pulse',
                'set'  => function($measurements){
                    return new MeasurementSet(HeartRatePulseCommonVariable::NAME,
                        $measurements,
                        'bpm',
                        'Vital Signs',
                        $this->displayName,
                        'MEAN');
                },
            ],
            12 => [
                'name' => IndoorTemperatureCommonVariable::NAME,
                'set'  => function($measurements){
                    return new MeasurementSet(IndoorTemperatureCommonVariable::NAME,
                        $measurements,
                        DegreesCelsiusUnit::ABBREVIATED_NAME,
                        EnvironmentVariableCategory::NAME,
                        $this->displayName,
                        BaseCombinationOperationProperty::COMBINATION_MEAN);
                },
            ],
            54 => [
                'name' => self::SP_O_2,
                'set'  => function($measurements){
                    return new MeasurementSet(self::SP_O_2,
                        $measurements,
                        PercentUnit::ABBREVIATED_NAME,
                        VitalSignsVariableCategory::NAME,
                        $this->displayName,
                        BaseCombinationOperationProperty::COMBINATION_MEAN);
                },
            ],
            71 => [
                'name' => CoreBodyTemperatureCommonVariable::NAME,
                'set'  => function($measurements){
                    return new MeasurementSet(CoreBodyTemperatureCommonVariable::NAME,
                        $measurements,
                        DegreesCelsiusUnit::ABBREVIATED_NAME,
                        VitalSignsVariableCategory::NAME,
                        $this->displayName,
                        BaseCombinationOperationProperty::COMBINATION_MEAN);
                },
            ],
            73 => [
                'name' => self::SKIN_TEMPERATURE,
                'set'  => function($measurements){
                    return new MeasurementSet(self::SKIN_TEMPERATURE,
                        $measurements,
                        DegreesCelsiusUnit::ABBREVIATED_NAME,
                        VitalSignsVariableCategory::NAME,
                        $this->displayName,
                        BaseCombinationOperationProperty::COMBINATION_MEAN);
                },
            ],
            76 => [
                'name' => self::MUSCLE_MASS,
                'set'  => function($measurements){
                    return new MeasurementSet(self::MUSCLE_MASS,
                        $measurements,
                        KilogramsUnit::ABBREVIATED_NAME,
                        PhysiqueVariableCategory::NAME,
                        $this->displayName,
                        BaseCombinationOperationProperty::COMBINATION_MEAN);
                },
            ],
            77 => [
                'name' => self::HYDRATION,
                'set'  => function($measurements){
                    return new MeasurementSet(self::HYDRATION,
                        $measurements,
                        KilogramsUnit::ABBREVIATED_NAME,
                        VitalSignsVariableCategory::NAME,
                        $this->displayName,
                        BaseCombinationOperationProperty::COMBINATION_MEAN);
                },
            ],
            88 => [
                'name' => self::BONE_MASS,
                'set'  => function($measurements){
                    return new MeasurementSet(self::BONE_MASS,
                        $measurements,
                        KilogramsUnit::ABBREVIATED_NAME,
                        VitalSignsVariableCategory::NAME,
                        $this->displayName,
                        BaseCombinationOperationProperty::COMBINATION_MEAN);
                },
            ],
            91 => [
                'name' => self::PULSE_WAVE_VELOCITY,
                'set'  => function($measurements){
                    return new MeasurementSet(self::PULSE_WAVE_VELOCITY,
                        $measurements,
                        MetersPerSecondUnit::ABBREVIATED_NAME,
                        VitalSignsVariableCategory::NAME,
                        $this->displayName,
                        BaseCombinationOperationProperty::COMBINATION_MEAN);
                },
            ],
            123 => [
                'name' => self::VO_2_MAX,
                'set'  => function($measurements){
                    return new MeasurementSet(self::VO_2_MAX,
                        $measurements,
                        IndexUnit::ABBREVIATED_NAME,
                        VitalSignsVariableCategory::NAME,
                        $this->displayName,
                        BaseCombinationOperationProperty::COMBINATION_MEAN);
                },
            ]
        ];
        $this->measureTypesActivity = [
            'steps'    => function($measurements){
                return new MeasurementSet('Daily Step Count',
                    $measurements,
                    'count',
                    'Physical Activity',
                    $this->displayName,
                    'SUM');
            },
            'distance' => function($measurements){
                return new MeasurementSet('Walk or Run Distance',
                    $measurements,
                    'm',
                    'Physical Activity',
                    $this->displayName,
                    'SUM');
            },
            'calories' => function($measurements){
                return new MeasurementSet('Calories Burned',
                    $measurements,
                    'kcal',
                    'Physical Activity',
                    $this->displayName,
                    'SUM');
            },
            //            'elevation' => function($measurements){
            //                return new MeasurementSet('Elevation', $measurements, 'm', 'Physical Activity', $this->displayName, 'SUM');
            //            },
        ];
    }
    public function makeSerializable(){
        parent::makeSerializable();
        $this->measureTypesBody = $this->measureTypesActivity = null;
    }
	/**
	 * Returns delimiter to scopes in getAuthorizationUri
	 * For services that do not fully respect the Oauth's RFC,
	 * and use scopes with commas as delimiter
	 *
	 * @return string
	 */
	protected function getScopesDelimiter(): string
	{
		return ',';
	}
	public function getRequestTokenEndpoint(): Uri{
		return new Uri($this->getBaseApiUrl() . 'request_token');
	}
    private function getNonce(): string{
        if($this->nonce){
            return $this->nonce;
        }
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://wbsapi.withings.net/v2/signature");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $signed_params = [
            'action' => 'getnonce',
            'client_id' => $this->getClientId(),
            'timestamp' => time(),
        ];
        ksort($signed_params);
        $data = implode(",", $signed_params);
        $signed_params['signature'] = hash_hmac('sha256', $data, $this->getOrSetConnectorClientSecret());
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($signed_params));

        $rsp = curl_exec($ch);
        curl_close($ch);

        return $this->nonce = $rsp;
    }
    public function getSignature(): string
    {
        if($this->signature){
            return $this->signature;
        }
        $signed_params = array(
            'action'     => 'activate',
            'client_id'  => $this->getClientId(),
            'nonce'      => $this->getNonce(),
        );
        ksort($signed_params);
        $data = implode(",", $signed_params);
        $signature = hash_hmac('sha256', $data, $this->getOrSetConnectorClientSecret());

        return $this->signature = $signature;

    }
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationEndpoint()
	{
		return new Uri('https://account.withings.com/oauth2_user/authorize2');
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAccessTokenEndpoint()
	{
		return new Uri('https://wbsapi.withings.net/v2/oauth2');
	}
    public function getOrRefreshToken(): AbstractToken
    {
        return parent::getOrRefreshToken(); // TODO: Change the autogenerated stub
    }

    /**
	 * Returns the url to retrieve the resource owners's profile/details.
	 * @param string $accessTokenString
	 * @return string
	 */
	public function getResourceOwnerDetailsUrl(string $accessTokenString)
	{
		return static::$BASE_API_URL.'/v2/user?action=getdevice&access_token='.$accessTokenString;
	}
    protected function getAccessTokenRequestParams(string $code): array{
        $arr = parent::getAccessTokenRequestParams($code);
        $arr['action'] = 'requesttoken';
        //$arr['nonce'] = $this->getNonce();
        //$arr['signature'] = $this->getSignature();
        return $arr;
    }
    protected function getRefreshTokenParams(TokenInterface $token): array
    {
        $arr = parent::getRefreshTokenParams($token);
        $arr['action'] = 'requesttoken';
        return $arr;
    }

    /**
	 * {@inheritdoc}
	 */
	protected function parseAccessTokenResponse($responseBody): StdOAuth2Token
	{
		return $this->parseStandardAccessTokenResponse($responseBody);
	}
	/**
	 * {@inheritdoc}
	 */
	protected function getAuthorizationMethod(): int
	{
		return static::AUTHORIZATION_METHOD_HEADER_BEARER;
	}
    public function refreshAccessToken(TokenInterface $token): TokenInterface
    {
        return parent::refreshAccessToken($token); // TODO: Change the autogenerated stub
    }
}
