<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection SpellCheckingInspection */
namespace App\DataSources\Connectors;
use App\DataSources\TooEarlyException;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\TooSlowException;
use App\Slim\Controller\Connector\ConnectorException;
use App\Types\TimeHelper;
use App\Units\PartsPerMillionUnit;
use App\Utils\Env;
use App\Utils\Stats;
use App\Variables\CommonVariables\EnvironmentCommonVariables\AirQualityIndexCommonVariable;
use Cmfcmf\OpenWeatherMap;
use DateTime;
use Http\Client\Exception\HttpException;
use Http\Factory\Guzzle\RequestFactory;
use App\Exceptions\NoGeoDataException;
use App\DataSources\LocationBasedConnector;
use App\Slim\Model\QMUnit;
use App\Units\HoursUnit;
use App\Units\IndexUnit;
use App\Units\MilesPerHourUnit;
use App\Units\MillimetersUnit;
use App\Units\PercentUnit;
use App\VariableCategories\EnvironmentVariableCategory;
use App\Variables\CommonVariables\EnvironmentCommonVariables\AverageDailyOutdoorTemperatureCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\BarometricPressureCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\CloudCoverCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\DailyHighOutdoorTemperatureCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\DailyLowOutdoorTemperatureCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\OutdoorHumidityCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\PrecipitationCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\TimeBetweenSunriseAndSunsetCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\WindSpeedCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\UVIndexCommonVariable;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
class WeatherConnector extends LocationBasedConnector {
	const POLLUTION_CO = "CO";
	const POLLUTION_NO_2 = "NO2";
	const POLLUTION_O3 = "O3";
	const POLLUTION_SO_2 = "SO2";
	protected $useFileResponsesInTesting = false; // TODO: Serialize OWM response
    protected const AFFILIATE = false;
    protected const BACKGROUND_COLOR = '#1e2023';
    protected const CLIENT_REQUIRES_SECRET = false;
    protected const DEVELOPER_CONSOLE = 'https://developer.worldweatheronline.com';
	public const DISPLAY_NAME = 'Weather';
	protected const ENABLED = 1;
	protected const GET_IT_URL = '';
	protected const LOGO_COLOR = '#2d2d2d';
    protected const LONG_DESCRIPTION = 'Automatically import temperature, humidity, and ultraviolet light exposure.';
	protected const SHORT_DESCRIPTION = 'Tracks weather.';
	
	
	public static $BASE_API_URL = 'https://api.openweathermap.org';
    /**
     * @var string The basic api url to fetch weather data from.
     */
    private $weatherUrl = 'https://api.openweathermap.org/data/2.5/weather?';

    /**
     * @var string The basic api url to fetch weather group data from.
     */
    private $weatherGroupUrl = 'https://api.openweathermap.org/data/2.5/group?';

    /**
     * @var string The basic api url to fetch weekly forecast data from.
     */
    private $weatherHourlyForecastUrl = 'https://api.openweathermap.org/data/2.5/forecast?';

    /**
     * @var string The basic api url to fetch daily forecast data from.
     */
    private $weatherDailyForecastUrl = 'https://api.openweathermap.org/data/2.5/forecast/daily?';

    /**
     * @var string The basic api url to fetch uv index data from.
     */
    private $uvIndexUrl = 'https://api.openweathermap.org/data/2.5/uvi';

    /**
     * @var string The basic api url to fetch air pollution data from.
     */
    private $airPollutionUrl = 'https://api.openweathermap.org/pollution/v1/';
    protected $allowMeasurementsForCurrentDay = true;
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $logoColor = self::LOGO_COLOR;
	public $longDescription = self::LONG_DESCRIPTION;
    public $name = self::NAME;
    public $shortDescription = self::SHORT_DESCRIPTION;
    public $synonyms = ['Weather'];
    public const ID = 13;
    public const NAME = 'worldweatheronline';

    /**
     * @return void
     * @throws ConnectorException
     * @throws InvalidVariableValueAttributeException
     * @throws TooSlowException
     */
    public function importData(): void {
        try {
            $this->importPollution();
        } catch (TooEarlyException|InvalidVariableValueAttributeException|
        NoGeoDataException|TooSlowException $e) {
            $this->exceptionIfTesting($e);
        }
        $this->importUVIndex();
        $this->importWeather();
        $this->saveMeasurements();
    }

    /**
     * @throws ConnectorException
     * @throws InvalidVariableValueAttributeException
     * @throws NoGeoDataException
     * @throws TooEarlyException
     * @throws TooSlowException
     */
	private function importPollution(){
        $lat = $this->getLatitude();
        $lon = $this->getLongitude();
        $start = $this->getFromTime();
		$fromAt = db_date($start);
        $end = $this->getEndTime();
		$endAt = db_date($end);
		if($start > $end){
			le("Start time $fromAt is after end time $endAt");
		}
		/** @noinspection HttpUrlsUsage */
		$response = $this->getRequest(
            "http://api.openweathermap.org/data/2.5/air_pollution/history?lat=$lat&lon=$lon&start=$start&end=$end&appid=".$this->getAppId());
		$typesByDate = [];
		foreach ($response->list as $day) {
			$timestamp = $day->dt;
			$rounded = Stats::roundToNearestMultipleOf($timestamp, 86400);
			$startAt = db_date($rounded);
			foreach ($day->components as $type => $value) {
				$typesByDate[$startAt][$type][] = $value;
			}
			$typesByDate[$startAt][AirQualityIndexCommonVariable::NAME][] = $day->main->aqi;
		}
		foreach ($typesByDate as $startAt => $types) {
			if($startAt <= $fromAt){
				continue;
			}
            foreach ($types as $type => $values) {
				$avg = array_sum($values) / count($values);
				if($type === AirQualityIndexCommonVariable::NAME){
					$this->addWeather(AirQualityIndexCommonVariable::NAME, $avg, $startAt, IndexUnit::NAME);
				} else {
					$this->savePollutionComponent($type, $avg, $startAt);
				}
            }
        }
		//$this->getPollution(self::POLLUTION_O3);
		//$this->getPollution(self::POLLUTION_NO_2);
		//$this->getPollution(self::POLLUTION_SO_2);
		//$this->getPollution(self::POLLUTION_CO);
	}
	/**
	 * @param int|string $type
	 * @param mixed $value
	 * @param mixed $day
	 * @return void
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooEarlyException
	 * @throws TooSlowException
	 */
	public function savePollutionComponent(string $type, float $value, string $startAt): void{
		$name = strtoupper($type)." Pollution";
		if($type === "no"){
			$name = "Nitrous Oxide NO Pollution";
		}
		if($type === "co"){
			$name = "Carbon Monoxide CO Pollution";
		}
		if($type === "nh3"){
			$name = "Ammonia NH3 Pollution";
		}
		if($type === "o3"){
			$name = "Ground-Level Ozone (O3) Pollution Pollution";
		}
		if($type === "pm2_5"){
			$name = "PM2.5 Fine Particulate Matter Pollution";
		}
		if($type === "pm10"){
			$name = "PM10 Particlulate Matter Pollution";
		}
		if($type === "so2"){
			$name = "Sulfur Dioxide SO2 Pollution";
		}
		$this->addWeather($name, $value, $startAt, PartsPerMillionUnit::NAME);
	}
	/**
	 * @param string $type
	 * @throws \App\DataSources\TooEarlyException
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 * @throws \App\Exceptions\NoGeoDataException
	 * @throws \App\Exceptions\TooSlowException
	 * @throws \Cmfcmf\OpenWeatherMap\Exception
	 */
	private function getPollution(string $type){
		$owm = $this->getOpenWeatherMapClient();
		try {
			$data = $owm->getAirPollution($type, (string)$this->getLatitude(), (string)$this->getLongitude());
			$this->addWeather("$type Pollution", $data->value->getValue(), $data->time->getTimestamp(),
				$data->value->getUnit());
		} catch (HttpException $e){
		    if(strpos($e->getMessage(), "404 Not Found") !== false ||
                strpos($e->getMessage(), "502") !== false){
                $geo = $this->getGeoData();
                $str = $geo->print();
				$this->logError("Could not get $type pollution for $str beause: ".$e->getMessage());
		    } else {
				le($e);
		    }
		}
	}
	/**
	 * @throws InvalidVariableValueAttributeException
	 * @throws OpenWeatherMap\Exception
	 * @throws TooSlowException
	 */
    private function importWeather() {
        $owm = $this->getOpenWeatherMapClient();
	    $this->setCurrentUrl($this->weatherUrl);
        $weather = $owm->getWeather($this->getLatLongQueryParams());
        //$this->saveConnectorRequestResponse(__FUNCTION__.": Can't get it from OpenWeatherMap", $weather);
	    $this->savePrecipitation($weather);
	    $this->savePressure($weather);
	    $this->saveCloudCover($weather);
	    $this->saveHumidity($weather);
	    $this->saveTemp($weather);
	    $this->addTimeBetweenSunriseAndSunsetMeasurement($weather);
	    $this->saveWind($weather);
    }
	/**
	 * @param $weather
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooSlowException
	 */
    private function addTimeBetweenSunriseAndSunsetMeasurement($weather){
        $set = $weather->sun->set->getTimestamp();
        $rise = $weather->sun->rise->getTimestamp();
        $hoursBetweenSunriseAndSunset = ($set - $rise) / 3600;
        $this->addWeather(TimeBetweenSunriseAndSunsetCommonVariable::NAME,
            $hoursBetweenSunriseAndSunset, $rise, HoursUnit::NAME);
    }
	/**
	 * @throws TooSlowException
	 * @throws InvalidVariableValueAttributeException
	 * @throws \Exception
	 */
	public function importUVIndex() {
        $v = $this->getWeatherUserVariable(UVIndexCommonVariable::NAME, IndexUnit::NAME);
        $latest = $v->getLatestTaggedMeasurementAt();
        if(strtotime($latest) > time() - 86400){
            $this->logInfo("Not importing UVIndex because getLatestTaggedMeasurementAt is ".
                TimeHelper::timeSinceHumanString($latest));
            return;
        }
        if (!$latest) {
            $latest = $this->getFromAt();
            //$latest = "2012-11-15"; // I already tried increasing this but it didn't fix 504 Gateway Time-out
            if(strtotime($latest) > time() - 86400){
                $this->logInfo("Not importing UVIndex because getFromAt is ".
                    TimeHelper::timeSinceHumanString($latest));
                return;
            }
        }
        $owm = $this->getOpenWeatherMapClient();
        $this->setCurrentFromAndEndTime($latest, 0);
        try {
            $history = $owm->getHistoricUVIndex($this->getLatitude(), $this->getLongitude(),
                new DateTime($latest), new DateTime("now"));
            $this->saveConnectorRequestResponse($this->uvIndexUrl.": Can't get full URL from OpenWeatherMap", $history);
        } catch (OpenWeatherMap\Exception $e) {
            $this->logError("Could not get UV Index because ". $e->getMessage());
            return;
        } catch (NoGeoDataException $e) {
            $this->logError(__METHOD__.": ".$e->getMessage());
            return;
        }
        $this->setCurrentUrl($this->uvIndexUrl);
        foreach ($history as $day) {
            $this->addMeasurement($v->name,
                $day->time->getTimestamp(),
                $day->uvIndex,
                IndexUnit::NAME,
                EnvironmentVariableCategory::NAME,
                [],
                86400);
        }
    }
    /**
     * @return OpenWeatherMap
     */
    private function getOpenWeatherMapClient(): OpenWeatherMap {
        // Create OpenWeatherMap object.
        // Don't use caching (take a look into Examples/Cache.php to see how it works).
        // You can use every PSR-17 compatible HTTP request factory
        // and every PSR-18 compatible HTTP client. This example uses
        // `http-interop/http-factory-guzzle` and `php-http/guzzle6-adapter`
        // which you need to install separately.
	    $c = new GuzzleAdapter($this->getHttpClient());
		//$c = $this->getHttpClient();
        return new OpenWeatherMap($this->getAppId(), $c,  new RequestFactory());
    }
	/**
	 * @param OpenWeatherMap\CurrentWeather $weather
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooSlowException
	 */
    private function savePressure(OpenWeatherMap\CurrentWeather $weather): void{
	    $startTime = $weather->lastUpdate->getTimestamp();
        $uv = $this->getQMUserVariable(BarometricPressureCommonVariable::NAME);
        $userUnit = $uv->getUserUnit();
        $providedUnit = QMUnit::find($weather->pressure->getUnit());
        try {
            $inUserUnit = $providedUnit->convertTo($weather->pressure->getValue(), $userUnit->id, $uv);
        } catch (IncompatibleUnitException | InvalidVariableValueException $e) {
            /** @var \LogicException $e */
            throw $e;
        }
	    $this->addWeather(BarometricPressureCommonVariable::NAME,
            $inUserUnit,
            $startTime,
            $userUnit->name);
    }
	/**
	 * @return array
	 */
	private function getLatLongQueryParams(){
		try {
			$query = [
				'lat' => $this->getLatitude(),
				'lon' => $this->getLongitude()
			];
		} catch (NoGeoDataException $e) {
			$this->logInfo(__METHOD__.": ".$e->getMessage());
			if($zip = $this->getZip()){
				$query = 'zip:' . $zip;
			}
		}
		return $query;
	}
	/**
	 * @param OpenWeatherMap\CurrentWeather $weather
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooSlowException
	 * @throws TooSlowException
	 */
	private function saveTemp(OpenWeatherMap\CurrentWeather $weather): void{
		$startTime = $weather->lastUpdate->getTimestamp();
		$low = $weather->temperature->min->getValue();
		$high = $weather->temperature->max->getValue();
		$tempUnit = $weather->temperature->now->getUnit();
		$avg = ($low + $high) / 2;
		if($avg < -20){le("Low is $low $tempUnit and high is $high $tempUnit and avg is $avg $tempUnit");}
		$this->addWeather(AverageDailyOutdoorTemperatureCommonVariable::NAME, $avg, $startTime,
		                  $tempUnit);
		$this->addWeather(DailyHighOutdoorTemperatureCommonVariable::NAME, $high, $startTime, $tempUnit);
		$this->addWeather(DailyLowOutdoorTemperatureCommonVariable::NAME, $low, $startTime, $tempUnit);
	}
	/**
	 * @param OpenWeatherMap\CurrentWeather $weather
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooSlowException
	 */
	private function saveWind(OpenWeatherMap\CurrentWeather $weather): void{
		$startTime = $weather->lastUpdate->getTimestamp();
		$this->addWeather(WindSpeedCommonVariable::NAME, $weather->wind->speed->getValue(), $startTime,
			MilesPerHourUnit::NAME);
	}
	/**
	 * @param OpenWeatherMap\CurrentWeather $weather
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooSlowException
	 */
	private function saveHumidity(OpenWeatherMap\CurrentWeather $weather): void{
		$startTime = $weather->lastUpdate->getTimestamp();
		$this->addWeather(OutdoorHumidityCommonVariable::NAME, $weather->humidity->getValue(), $startTime,
			$weather->humidity->getUnit());
	}
	/**
	 * @param OpenWeatherMap\CurrentWeather $weather
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooSlowException
	 */
	private function saveCloudCover(OpenWeatherMap\CurrentWeather $weather): void{
		$startTime = $weather->lastUpdate->getTimestamp();
		$this->addWeather(CloudCoverCommonVariable::NAME, $weather->clouds->getValue(), $startTime, PercentUnit::NAME);
	}
	/**
	 * @param OpenWeatherMap\CurrentWeather $weather
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooSlowException
	 */
	private function savePrecipitation(OpenWeatherMap\CurrentWeather $weather): void{
		$startTime = $weather->lastUpdate->getTimestamp();
		$this->addWeather(PrecipitationCommonVariable::NAME, $weather->precipitation->getValue(), $startTime,
			MillimetersUnit::NAME);
	}

    /**
     * @throws NoGeoDataException
     */
    private function getGeoData(): \App\Models\IpDatum{
        return $this->getUser()->getIpGeoLocation();
    }

    private function getAppId(): string{
        return Env::getRequired('CONNECTOR_OPEN_WEATHER_MAP_API_KEY');
    }
}
