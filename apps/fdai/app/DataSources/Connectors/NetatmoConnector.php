<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
require_once 'Netatmo-API-PHP/src/Netatmo/autoload.php';
use App\DataSources\OAuth2Connector;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\TooSlowException;
use App\Logging\QMLog;
use Netatmo\Clients\NAWSApiClient;
use Netatmo\Exceptions\NAApiErrorType;
use Netatmo\Exceptions\NACurlErrorType;
use Netatmo\Exceptions\NAInternalErrorType;
use Netatmo\Exceptions\NAJsonErrorType;
use Netatmo\Exceptions\NANotLoggedErrorType;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth2\Service\Netatmo;
use App\Units\DecibelsUnit;
use App\Units\DegreesCelsiusUnit;
use App\Units\MillibarUnit;
use App\Units\PartsPerMillionUnit;
use App\Units\PercentUnit;
use App\VariableCategories\EnvironmentVariableCategory;
use App\Variables\CommonVariables\EnvironmentCommonVariables\IndoorCo2CommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\IndoorHumidityCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\IndoorNoiseCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\IndoorPressureCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\IndoorTemperatureCommonVariable;
/** Class NetatmoConnector
 * @package App\DataSources\Connectors
 */
class NetatmoConnector extends OAuth2Connector{
    protected $useFileResponsesInTesting = false; // TODO: merge SDK into this connector
    private $city;
    private $latitude;
    private $longitude;
    private $netatmoClient;
    protected const AFFILIATE = true;
    protected const BACKGROUND_COLOR = '#388bbe';
    protected const CLIENT_REQUIRES_SECRET = true;
    protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Environment';
    protected const DEVELOPER_CONSOLE = 'https://dev.netatmo.com/myaccount/';
	
	
	public const DISPLAY_NAME = 'Netatmo';
	protected const ENABLED = 1;
	protected const GET_IT_URL = 'https://amzn.to/2uCqcIH';
	public const IMAGE = 'https://is4-ssl.mzstatic.com/image/thumb/Purple118/v4/c0/e1/e3/c0e1e38b-4eda-ea50-cb83-1e1d43b9e9bb/AppIcon-1x_U007emarketing-85-220-0-6.png/246x0w.jpg';
	protected const LOGO_COLOR = '#2d2d2d';
	protected const LONG_DESCRIPTION = 'Experience the comfort of a Smart Home: Smart Thermostat, Security Camera with Face Recognition, Weather Station.';
	protected const PREMIUM = true;
	protected const SHORT_DESCRIPTION = 'Tracks humidity and temperature';
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
    public const ID = 74;
    public const NAME = 'netatmo';
    public static $OAUTH_SERVICE_NAME = 'Netatmo';
    public static $BASE_API_URL = "https://api.netatmo.com";
    public static array $SCOPES = [
        Netatmo::SCOPE_STATION_READ,
        Netatmo::SCOPE_THERMOSTAT_READ
    ];
    public $variableNames = [
        IndoorHumidityCommonVariable::NAME,
        IndoorNoiseCommonVariable::NAME,
        IndoorCo2CommonVariable::NAME,
        IndoorPressureCommonVariable::NAME,
        IndoorTemperatureCommonVariable::NAME
    ];
    /**
     * @return int
     *  https://dev.netatmo.com/resources/technical/reference/common/getmeasure
     * @throws NAApiErrorType
     * @throws NACurlErrorType
     * @throws NAInternalErrorType
     * @throws NAJsonErrorType
     * @throws NANotLoggedErrorType
     */
    public function importData(): void{
        $fromTime = $this->getFromTime();
        $stationsData = $this->getNetatmoClient()->getData(NULL, TRUE);  //retrieve all stations belonging to the user, and also his favorite ones
        if(empty($stationsData['devices'])){
            $this->logError('No devices affiliated to user');
        }else{
            $device = $stationsData['devices'][0];
            $this->getDailyMeasurementsFromMainModule($fromTime, $device);
            //$this->getDataFromSubModules($device);
            //$this->getGeneralInfoAboutLastMonthFromMainDevice($device);
        }

    }
    /**
     * @param $measures
     * @param $mostRecent
     * @return int
     */
    private static function getMostRecentFromTime(array $measures, int $mostRecent): int {
        foreach($measures as $time => $data){
            if((int)$time > $mostRecent){
                $mostRecent = (int)$time;
            }
        }
        return $mostRecent;
    }
	/**
	 * @param $fromTime
	 * @param $device
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooSlowException
	 * @throws TokenNotFoundException
	 */
    private function getDailyMeasurementsFromMainModule($fromTime, $device){
        $this->latitude = $device["place"]["location"][0];
        $this->longitude = $device["place"]["location"][1];
        $this->city = $device["place"]["city"];
        // now get some daily measurements for the last 30 days
        $type = "temperature,Co2,humidity,noise,pressure";
        $numberOfNew = 1024;
        $limit = 1024;
        $client = $this->getNetatmoClient();
        while($numberOfNew > $limit - 1){
            $this->setCurrentUrl($this->getUrlForPath('getmeasure', [
                'scale' => "1day",
                'start' => $fromTime,
                'end' => time(),
                'limit' => $limit,
            ]));
            $newMeasures = $client->getMeasure($device['_id'], NULL, "1day", $type,
                $fromTime, time(), $limit, FALSE, FALSE);
            $this->saveConnectorRequestResponse($client->getRequestUri(), $newMeasures);
            $numberOfNew = count($newMeasures);
            foreach($newMeasures as $time => $data){
                if(isset($data[0])){
                    $this->addNetatmoMeasurementItem(IndoorTemperatureCommonVariable::NAME, $data[0], $time, DegreesCelsiusUnit::NAME);
                }
                if(isset($data[1])){
                    $this->addNetatmoMeasurementItem(IndoorCo2CommonVariable::NAME, $data[1], $time, PartsPerMillionUnit::NAME);
                }
                if(isset($data[2])){
                    $this->addNetatmoMeasurementItem(IndoorHumidityCommonVariable::NAME, $data[2], $time, PercentUnit::NAME);
                }
                if(isset($data[3])){
                    $this->addNetatmoMeasurementItem(IndoorNoiseCommonVariable::NAME, $data[3], $time, DecibelsUnit::NAME);
                }
                if(isset($data[4])){
                    $this->addNetatmoMeasurementItem(IndoorPressureCommonVariable::NAME, $data[4], $time, MillibarUnit::NAME);
                }
            }
            $fromTime = self::getMostRecentFromTime($newMeasures, $fromTime);
            if($this->weShouldBreak()){break;}
        }
        $this->saveMeasurements();
    }
	/**
	 * @param string $variableName
	 * @param float $value
	 * @param int $time
	 * @param string $unitName
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooSlowException
	 */
    private function addNetatmoMeasurementItem(string $variableName, float $value, int $time, string $unitName){
        $v = $this->getQMUserVariable($variableName, $unitName, EnvironmentVariableCategory::NAME);
        $m = $this->addMeasurement($v->name,
            $time,
            $value,
            $unitName,
            $v->variableCategoryName,
            []);
        if($m){
            $m->setLatitude($this->latitude);
            $m->setLongitude($this->longitude);
            $m->setLocation($this->city);
        }
    }
	/**
	 * @return NAWSApiClient
	 * @throws TokenResponseException
	 * @throws TokenNotFoundException
	 */
    public function getNetatmoClient(): NAWSApiClient{
        if($this->netatmoClient){
            return $this->netatmoClient;
        }
        $token = $this->getOrRefreshToken();
        if(!$token){
            le("Could not get token!");
        }
        $netatmoClient = new NAWSApiClient([
            'access_token'  => $token->getAccessToken(),
            'refresh_token' => $token->getRefreshToken()
        ]);
        $netatmoClient->setExpiresAt($token->getEndOfLife());
        return $this->netatmoClient = $netatmoClient;
    }
    public function getCurrentUrl(): string{
        try {
            return parent::getCurrentUrl();
        } catch (\Throwable $e){
            QMLog::error(__METHOD__.": ".$e->getMessage());
            return "no url set";
        }
    }
	protected function parseAccessTokenResponse(string $responseBody): TokenInterface{
		return $this->parseStandardAccessTokenResponse($responseBody);
	}
	public function getAuthorizationEndpoint(): Uri{
		return new Uri( "https://api.netatmo.com/oauth2/authorize");
	}
	public function getAccessTokenEndpoint(): Uri{
		return new Uri( "https://api.netatmo.com/oauth2/token");
	}
	public function getBaseApiUrl(): string{
		return "https://api.netatmo.com/api";
	}
	/**
	 * Returns a class constant from ServiceInterface defining the authorization method used for the API
	 * Header is the sane default.
	 *
	 * @return int
	 */
	protected function getAuthorizationMethod(): int{
		return static::AUTHORIZATION_METHOD_HEADER_BEARER;
	}
}
