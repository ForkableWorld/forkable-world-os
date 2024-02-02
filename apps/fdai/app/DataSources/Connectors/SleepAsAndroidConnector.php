<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\TooSlowException;
use App\Slim\Controller\Connector\ConnectorException;
use OAuth\OAuth2\Service\Google;
use App\Exceptions\NoResponseException;
use App\DataSources\GoogleBaseConnector;
use App\Units\EventUnit;
use App\Units\HoursUnit;
use App\Units\OneToFiveRatingUnit;
use App\Units\ZeroToOneRatingUnit;
use App\VariableCategories\SleepVariableCategory;
use App\Variables\CommonVariables\SleepCommonVariables\SleepDurationCommonVariable;
class SleepAsAndroidConnector extends GoogleBaseConnector {
    public static $BASE_API_URL = 'https://sleep-cloud.appspot.com/fetchRecords?timestamp=';
	protected const BACKGROUND_COLOR = '#124191';
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Sleep';
	public const DISPLAY_NAME = 'Sleep as Android';
	protected const ENABLED = 1;
	protected const GET_IT_URL = 'https://sites.google.com/site/sleepasandroid/sleepcloud';
	protected const LOGO_COLOR = '#d34836';
	protected const LONG_DESCRIPTION = 'Smart alarm clock with sleep cycle tracking. Wakes you gently in optimal moment for pleasant mornings.';
	protected const SHORT_DESCRIPTION = 'Tracks sleep duration and quality.';
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
	public $shortDescription = self::SHORT_DESCRIPTION;
	public const ID = 14;
    public const IMAGE = 'https://i.imgur.com/J6jqiOI.png';
    public const NAME = 'sleepcloud';
    public static array $SCOPES = [Google::SCOPE_USERINFO_EMAIL];
	/**
	 * @return void
	 * @throws InvalidVariableValueAttributeException
	 * @throws ModelValidationException
	 * @throws TooSlowException
	 * @throws ConnectorException
	 */
    public function importData(): void {
        $fromTime = $this->getFromTime();
        $millis = $fromTime * 1000;
        $url = self::$BASE_API_URL.$millis;
        try {
            $responseObject = $this->getRequest($url);
        } catch (NoResponseException $e) {
            $this->logInfo(__METHOD__.": ".$e->getMessage());
            $code = $this->getLastStatusCode();
            if($code === 200){ // I guess it returns null when there are no measurements?
                return;
            }
           throw $e;
        }
        $statusCode = $this->getLastStatusCode();
        if($statusCode == 200){
            $this->logDebug("SleepCloud: Received $statusCode");
            foreach($responseObject->sleeps as $sleepRecord){
                $this->addMeasurementItemsFromSleepRecord($sleepRecord);
            }
        }
        $this->saveMeasurements();
    }
	/**
	 * @param $sleepRecord
	 * @throws InvalidVariableValueAttributeException
	 * @throws \App\Exceptions\TooSlowException
	 */
    private function addMeasurementItemsFromSleepRecord($sleepRecord): void{
        $startTime = $sleepRecord->fromTime / 1000;
        $toTime = $sleepRecord->toTime / 1000;
        $durationInHours = $this->calculateDurationInHours($toTime, $startTime);
        try {
            $this->addMeasurement(SleepDurationCommonVariable::NAME,
                $startTime,
                $durationInHours,
                HoursUnit::NAME,
                SleepVariableCategory::NAME,
                [],
                $durationInHours * 3600);
        } catch (InvalidVariableValueAttributeException $e) {
            $this->logError(__METHOD__.": ".$e->getMessage());
            return;
        }
        if(property_exists($sleepRecord, 'cycles') && $sleepRecord->rating > 0){
            $this->addMeasurement('Sleep Cycles',
                $startTime,
                $sleepRecord->cycles,
                EventUnit::NAME,
                SleepVariableCategory::NAME, [],$durationInHours*3600);
        }
        if(property_exists($sleepRecord, 'rating') && $sleepRecord->rating > 0){
            $value = $sleepRecord->rating * 4 / 5 + 1;
            $this->addMeasurement('Sleep Quality Rating',
                $startTime,
                $value,
                OneToFiveRatingUnit::ABBREVIATED_NAME,
                SleepVariableCategory::NAME);
        }
        if(property_exists($sleepRecord, 'noiseLevel') && $sleepRecord->noiseLevel > 0){
            $this->addMeasurement('Sleep Noise Level',
                $startTime,
                $sleepRecord->noiseLevel,
                ZeroToOneRatingUnit::NAME,
                SleepVariableCategory::NAME);
        }
        if(property_exists($sleepRecord, 'deepSleep') && $sleepRecord->deepSleep > 0){
            $this->addMeasurement('Deep Sleep',
                $startTime,
                $sleepRecord->deepSleep,
                ZeroToOneRatingUnit::NAME,
                SleepVariableCategory::NAME);
        }
    }
    /**
     * @param $toTime
     * @param $startTime
     * @return int
     */
    private function calculateDurationInHours($toTime, $startTime): int{
        $durationInHours = $toTime - $startTime;
        if($durationInHours > 1440){
            $durationInHours /= 60;
        }
        if($durationInHours > 24){
            $durationInHours /= 60;
        }
        return $durationInHours;
    }
}
