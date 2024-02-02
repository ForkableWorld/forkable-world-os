<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\LocationBasedConnector;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\TooManyMeasurementsException;
use App\Types\TimeHelper;
use App\Units\IndexUnit;
use App\Variables\CommonVariables\EnvironmentCommonVariables\PollenIndexCommonVariable;
class PollenCountConnector extends LocationBasedConnector {
    protected $allowMeasurementsForCurrentDay = true;
    public const DISABLED_UNTIL = "2021-11-01";
    protected const AFFILIATE = false;
    protected const BACKGROUND_COLOR = '#1e2023';
    protected const CLIENT_REQUIRES_SECRET = false;
	protected const DEVELOPER_CONSOLE = null;
	
	
	protected const ENABLED = 1;
	protected const GET_IT_URL = '';
	protected const LOGO_COLOR = '#2d2d2d';
    protected const LONG_DESCRIPTION = 'Automatically import pollen count for various species and find out how it could be affecting your symptoms.';
	protected const SHORT_DESCRIPTION = 'Tracks pollen count';
	
	
	public static $BASE_API_URL = 'https://www.pollen.com/api/forecast/';
	public $affiliate = self::AFFILIATE;
	public $availableOutsideUS = false;
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
    public $synonyms = ['Pollen'];
    public const DISPLAY_NAME = 'Pollen Count';
    public const ID = 91;
    public const IMAGE = "https://s3.amazonaws.com/static.quantimo.do/img/pollen.png";
    public const NAME = 'pollen-count';
    public $variableNames = [
        PollenIndexCommonVariable::NAME
    ];
	/**
	 * @return void
	 * @throws \App\DataSources\TooEarlyException
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 * @throws \App\Exceptions\TooSlowException
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
    public function importData(): void{
        $zip = $this->getValidUSZip();
        if(!$zip){
            $this->logInfo("No US zip for pollen index");
            return;
        }
        $this->getHistoricalPollenIndex($zip);
        $this->getPollenTypes($zip);
        $this->saveMeasurements();
    }
	/**
	 * @param $dataForDay
	 * @param int $startTime
	 * @throws \App\DataSources\TooEarlyException
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 * @throws \App\Exceptions\TooSlowException
	 */
    private function getTriggersForDay($dataForDay, int $startTime): void {
        $this->logInfo("saveTriggersForDay for ".count($dataForDay->Triggers)." days. day: ".json_encode($dataForDay));
        foreach($dataForDay->Triggers as $trigger){
            $v = $this->getWeatherUserVariable($trigger->Name . " " . PollenIndexCommonVariable::NAME, IndexUnit::NAME);
            $latest = $v->getLatestNonTaggedMeasurementStartAt();
            if(time_or_null($latest) > $startTime){continue;}
            $this->addWeather($v->getVariableName(), $dataForDay->Index, $startTime, IndexUnit::NAME);
        }
    }
	/**
	 * @param string $zip
	 * @return void
	 * @throws \App\DataSources\TooEarlyException
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 * @throws \App\Exceptions\TooSlowException
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
    private function getPollenTypes(string $zip) {
	    $headers = $this->getHeaders("https://www.pollen.com/forecast/current/pollen/".$zip);
        $data = $this->getRequest("current/pollen/".$zip, [], $headers);
        $location = $data->Location;
        if(empty($location->periods)){
            $this->logInfo("No pollen trigger data for zip code ".$zip);
            return;
        }
        if(!isset($location->DisplayLocation)){
            $this->logError("DisplayLocation not set in ".json_encode($location));
            return;
        }
        $this->setMeasurementLocation($location->DisplayLocation);
        $yesterday = $location->periods[0];
        try {
            if(!is_countable($yesterday->Triggers)){
                throw new \RuntimeException("Yesterday Triggers is not an array! Response is ".
                    \App\Logging\QMLog::print_r($data, true));
            }
            $this->getTriggersForDay($yesterday, time() - 86400);
        } catch (TooManyMeasurementsException $e){
            $this->logInfo(__METHOD__.": ".$e->getMessage()); // Catching because we import every 24 hours and there's no other way around this
        }
        if(!isset($location->periods[1])){
            $this->logError("No 1 index for today in response: ".\App\Logging\QMLog::print_r($location, true));
            return;
        }
        $today = $location->periods[1];
        if(!is_countable($today->Triggers)){
            $this->logError("Today Triggers is not an array! Response is ".\App\Logging\QMLog::print_r($data, true));
        } else {
            $this->getTriggersForDay($today, time());
        }
        //$this->getTriggersForDay($location->periods[2], time() + 86400);  TODO: Might want to enable tomorrow data to save API calls if necessary?
    }
	/**
	 * @param string $zip
	 * @return void
	 * @throws \App\DataSources\TooEarlyException
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 * @throws \App\Exceptions\TooSlowException
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
    private function getHistoricalPollenIndex(string $zip){
	    $headers = $this->getHeaders("https://www.pollen.com/forecast/historic/pollen/$zip");
	    $data = $this->getRequest("historic/pollen/$zip/30", [], $headers);
        $location = $data->Location;
        if(empty($location->periods)){
            $this->logInfo("No historical pollen data for zip code $zip");
            return;
        }
        if(!isset($location->DisplayLocation)){
            $this->logError("DisplayLocation not set in ".json_encode($location));
            return;
        }
        $this->setMeasurementLocation($location->DisplayLocation);
        $this->logInfo("saveHistoricalPollenIndex for ".count($location->periods)." periods. Location:".
            json_encode($location));
        foreach($location->periods as $period){
            $v = $this->getQMUserVariable(PollenIndexCommonVariable::NAME);
            $latestAt = $v->getLatestTaggedMeasurementAt();
            if(time_or_null($latestAt) >
                TimeHelper::universalConversionToUnixTimestamp($period->Period)){
                continue;
            }
            $this->addWeather(PollenIndexCommonVariable::NAME, $period->Index, $period->Period,
                IndexUnit::NAME);
        }
    }
	/**
	 * @return string[]
	 */
	private function getHeaders(string $referrer): array{
		$headers = [
			'Host' => 'www.pollen.com',
			'Referer' => $referrer,
			'sec-ch-ua' => '"Microsoft Edge";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
			'sec-ch-ua-mobile' => '?0',
			'sec-ch-ua-platform' => "Windows",
			'Sec-Fetch-Dest' => 'empty',
			'Sec-Fetch-Mode' => 'cors',
			'Sec-Fetch-Site' => 'same-origin',
			'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.54 Safari/537.36 Edg/95.0.1020.40',
		];
		return $headers;
	}
}
