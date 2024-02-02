<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\LocationBasedConnector;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\NoGeoDataException;
use App\Exceptions\TooSlowException;
use App\Models\UserVariable;
use App\Slim\Controller\Connector\ConnectorException;
use App\Storage\Memory;
use App\Types\TimeHelper;
use App\UI\ImageUrls;
use App\Units\HoursUnit;
use App\VariableCategories\EnvironmentVariableCategory;
use App\Variables\CommonVariables\EnvironmentCommonVariables\TimeBetweenSunriseAndSunsetCommonVariable;
use App\Variables\QMUserVariable;
use stdClass;
class DaylightConnector extends LocationBasedConnector {
    protected const AFFILIATE = false;
    protected const BACKGROUND_COLOR = '#1e2023';
    protected const CLIENT_REQUIRES_SECRET = false;
    protected const DEVELOPER_CONSOLE = 'https://sunrise-sunset.org';
	public const DISPLAY_NAME = 'Daylight';
	protected const ENABLED = 1;
	protected const GET_IT_URL = '';
	public const IMAGE = ImageUrls::AGRICULTURE_SUN;
    protected const LOGO_COLOR = '#2d2d2d';
	protected const LONG_DESCRIPTION = 'Automatically import the number of hours between sunrise and sunset to see if you might be affected by Seasonal Affective Disorder.';
	protected const SHORT_DESCRIPTION = 'Tracks hours of daylight';
	
	
	public static $BASE_API_URL = 'https://api.sunrise-sunset.org/json';
	public $affiliate = self::AFFILIATE;
	public $availableOutsideUS = true;
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
    public $synonyms = ['Hours of Daylight'];
    public $maximumRequestTimeSpanInSeconds = 86400;
    public const ID = 92;
    public const NAME = 'daylight';
    public $variableNames = [
        TimeBetweenSunriseAndSunsetCommonVariable::NAME
    ];
    /**
     * @return int|QMUserVariable[]
     * @throws NoGeoDataException
     */
    public function importData(): void {
        $v = $this->getWeatherUserVariable(TimeBetweenSunriseAndSunsetCommonVariable::NAME, HoursUnit::NAME);
        $v->logTimeSinceLatestMeasurement();
        $sunriseMeasurements = $v->getValidDailyMeasurementsWithTags();
        $time = $this->getFromTime();
        Memory::setStartTime();
        $this->setCurrentFromAndEndTime($time, $this->maximumRequestTimeSpanInSeconds);
        while(!$this->weShouldBreak($time)){
            $date = TimeHelper::YYYYmmddd($time);
            if(!isset($sunriseMeasurements[$date])){
                lei($date === TimeHelper::YYYYmmddd(time()),
                    "Why are we getting measurements for current date: $date");
                $this->getDataAndAddMeasurement($date, $v, $time);
            } else {
                $this->logInfo("Skipping $date because we already have a measurement for it");
            }
            $time = $this->incrementCurrentFromTime();
        }
        $this->saveMeasurements();
    }
	/**
	 * @param string $date
	 * @return stdClass
	 * @throws NoGeoDataException
	 * @throws ConnectorException
	 */
    private function getByDate(string $date): stdClass{
        $data = $this->getRequest("", [
            "lat"=>$this->getLatitude(),
            "lng"=>$this->getLongitude(),
            "date"=>$date,
            "formatted"=>0
        ]);
        return json_decode(json_encode($data));
    }

    /**
     * @param string $date
     * @param QMUserVariable $v
     * @param int|null $time
     * @throws ConnectorException
     * @throws InvalidVariableValueAttributeException
     * @throws NoGeoDataException
     * @throws TooSlowException
     * @throws \App\DataSources\TooEarlyException
     */
    private function getDataAndAddMeasurement(string $date, QMUserVariable $v, ?int $time): void{
        $data = $this->getByDate($date);
        $diff = (strtotime($data->results->sunset) - strtotime($data->results->sunrise)) / 3600;
        $this->logInfo("$diff hours on $date");
        $this->addMeasurement($v->name,
            $time,
            round($diff, 2),
            HoursUnit::NAME,
            EnvironmentVariableCategory::NAME,
            []);
    }
    /**
     * @param $fromTime
     * @param QMUserVariable $v
     * @return int|null
     */
    public function getFromTimeForVariable($fromTime, QMUserVariable $v): ?int {
        $variableTime = parent::getFromTimeForVariable($fromTime, $v);
        $userTime = UserVariable::whereUserId($this->getId())
            ->min(UserVariable::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT);
        $userTime = $userTime ?: time() - 5 * 86400 * 365;
        if($variableTime > $userTime){
            return $variableTime;
        }
        return $userTime;
    }
}
