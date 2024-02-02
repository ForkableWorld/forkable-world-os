<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\TooSlowException;
use App\Logging\QMLog;
use App\Slim\Controller\Connector\ConnectorException;
use App\Types\ObjectHelper;
use App\DataSources\LocationBasedConnector;
use App\Types\TimeHelper;
use App\Slim\Model\Measurement\AdditionalMetaData;
use App\Units\IndexUnit;
use App\Utils\UrlHelper;
use App\VariableCategories\EnvironmentVariableCategory;
use App\Variables\QMUserVariable;
class AirQualityConnector extends LocationBasedConnector {
    public const DISABLED_UNTIL = "2020-09-15";
    protected const AFFILIATE = false;
    protected const BACKGROUND_COLOR = '#1e2023';
    protected const CLIENT_REQUIRES_SECRET = false;
    protected const DEVELOPER_CONSOLE = 'https://www.airnowapi.org';
	public const DISPLAY_NAME = 'Air Quality';
	protected const ENABLED = 1;
	protected const GET_IT_URL = '';
	protected const LOGO_COLOR = '#2d2d2d';
    protected const LONG_DESCRIPTION = 'Automatically import particulate pollution, ozone pollution and air quality.';
	protected const SHORT_DESCRIPTION = 'Tracks pollution';
	
	
	public static $BASE_API_URL = 'http://www.airnowapi.org/aq/observation/zipCode/historical/?format=application/json';
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
    public $synonyms = ['Air Quality Index'];
    public $availableOutsideUS = false;
    public $maximumRequestTimeSpanInSeconds = 86400;
    public $variableNames = [
        self::FINE_PARTICULATE_MATTER_POLLUTION_AIR_QUALITY_INDEX,
        self::LARGE_PARTICULATE_MATTER_POLLUTION_AIR_QUALITY_INDEX,
        self::OZONE_POLLUTION_AIR_QUALITY_INDEX
    ];
    public const IMAGE = "https://static.quantimo.do/img/connectors/air-quality-connector.jpg";
    public const FINE_PARTICULATE_MATTER_POLLUTION_AIR_QUALITY_INDEX = "Fine Particulate Matter Pollution Air Quality Index";
    public const ID = 90;
    public const LARGE_PARTICULATE_MATTER_POLLUTION_AIR_QUALITY_INDEX = "Large Particulate Matter Pollution Air Quality Index";
    public const NAME = 'air-quality';
    public const OZONE_POLLUTION_AIR_QUALITY_INDEX = "Ozone Pollution Air Quality Index";
	/**
	 * @return int
	 */
	public function getMaximumRequestTimeSpanInSeconds(): int{
		return $this->maximumRequestTimeSpanInSeconds;
	}
	/**
	 * @return void
	 * @throws ConnectorException
	 * @throws InvalidVariableValueAttributeException
	 * @throws ModelValidationException
	 * @throws TooSlowException
	 */
    public function importData(): void {
        $outsideUS = $this->userIsOutsideUSA();
        if($outsideUS === true){ // Will be null if we're unsure so have to compare to false
            $this->disconnect("Could not get country name and $this->displayName is not available ".
                "outside the US!\n CredentialsArray is:\n ".
                QMLog::print_r($this->getCredentialsArray(), true),
                "Air Quality is only available for US zip codes");
        }
        $this->addAirQualityMeasurements();
        $this->saveMeasurements();
    }
    public function getLogMetaDataString(): string{
        if($zip = $this->zipCode){
            return "Zip $zip for ".$this->getQmUser()->loginName;
        }
        return "$this->displayName for ".$this->getQmUser()->loginName;
    }
    protected function getValidUSZip(): ?string{
        $zip = parent::getValidUSZip();
        if(!$zip){
            le("No valid US zip code for air quality! Credentials are: ".
                \App\Logging\QMLog::print_r($this->getCredentialsArray(), true));
        }
        return $zip;
    }
	/**
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooSlowException
	 * @throws ConnectorException
	 */
    private function addAirQualityMeasurements(){
        $latest = $this->getFromTime();
        $time = $latest + 86400;
        if($time < time() - 365 * 86400){
            $this->exceptionIfTesting("We can only get data from the past year");
            $time = time() - 365 * 86400;
        }
        $measurementsByDate = $this->getMeasurementsByDate();
        while(!$this->weShouldBreak($time)){
            $date = TimeHelper::YYYYmmddd($time);
            if(isset($measurementsByDate[$date])){
                $this->logInfo("Already have a measurement for $date");
                $time = $this->incrementCurrentFromTime();
                continue;
            }
            $this->setCurrentFromAndEndTime($time, $this->maximumRequestTimeSpanInSeconds);
            $url = $this->getUrlForDate($time);
            $response = $this->getRequest($url, $this->getGlobalUrlParams());
            if(is_string($response)){
                if($response === "[]"){
                    $this->logError("$date Pollution: Got empty array response for $url");
                    $time = $this->incrementCurrentFromTime();
                    continue;
                }
                le("Please implement handling for this response $response from $this url $url");
            }
            if(empty($response)){
                $todayDate = TimeHelper::YYYYmmddd(time());
                if($date === $todayDate){
                    $this->logError("Why are we getting measurements for today ($todayDate): $date?");
                    $result = $this->weShouldBreak($time);
                } else {
                    $this->logError("$date Pollution: Empty response from $url");
                }
                $time = $this->incrementCurrentFromTime();
                continue;
            }
            $this->responseToMeasurements($response);
            $time = $this->incrementCurrentFromTime();
        }
    }
    public function getGlobalUrlParams(): array {
        $params = parent::getGlobalUrlParams();
        $params['zipCode'] = $this->getValidUSZip();
        $params['API_KEY'] = \App\Utils\Env::get('CONNECTOR_AIR_QUALITY_API_KEY');
        $params['distance'] = 25;
        return $params;
    }
    /**
     * @param int $time
     * @return string
     */
    private function getUrlForDate(int $time): string{
        $params = $this->getGlobalUrlParams();
        $params['date'] = TimeHelper::YYYYmmddd($time)."T00-0000";
        $url = UrlHelper::addParams(self::$BASE_API_URL, $params);
        return $url;
    }
	/**
	 * @param array
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooSlowException
	 */
    private function responseToMeasurements(array $response): void{
        foreach($response as $item){
            $note = new AdditionalMetaData(null, "Level of Health Concern: ".$response[0]->Category->Name);
			$item = ObjectHelper::convertToObject($item);
            $name = $this->getVariableName($item->ParameterName);
            $v = $this->getWeatherUserVariable($name, IndexUnit::NAME);
            $m = $this->addMeasurement($v->name,
                trim($item->DateObserved),
                $item->AQI,
                IndexUnit::NAME,
                EnvironmentVariableCategory::NAME,
                [],
                86400,
                $note);
            $m->latitude = $item->Latitude ?? null;
            $m->longitude = $item->Longitude ?? null;
            $m->location = $item->ReportingArea ?? null;
            $this->setUserTimeZoneIfNecessary($item);
        }
    }
    /**
     * @param $item
     */
    private function setUserTimeZoneIfNecessary($item): void{
        $u = $this->getQmUser();
        if(!$u->timezone && isset($item->LocalTimeZone)){
            $l = $u->l();
            $l->setTimeZone($item->LocalTimeZone);
            try {
                $l->save();
            } catch (ModelValidationException $e) {
                le($e);
            }
        }
    }
    /**
     * @param $ParameterName
     * @return string
     */
    private function getVariableName(string $ParameterName): string{
        if($ParameterName === "PM2.5"){
            $name = self::FINE_PARTICULATE_MATTER_POLLUTION_AIR_QUALITY_INDEX;
        }elseif($ParameterName === "OZONE"){
            $name = self::OZONE_POLLUTION_AIR_QUALITY_INDEX;
        }elseif($ParameterName === "PM10"){
            $name = self::LARGE_PARTICULATE_MATTER_POLLUTION_AIR_QUALITY_INDEX;
        }else{
            le("Please implement saving for AQI parameter: $ParameterName");
        }
        return $name;
    }
}
