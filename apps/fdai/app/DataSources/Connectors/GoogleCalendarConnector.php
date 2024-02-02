<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\GoogleBaseConnector;
use App\Exceptions\CredentialsNotFoundException;
use App\Exceptions\TooManyMeasurementsException;
use App\Logging\QMLog;
use App\Models\Variable;
use App\Slim\Model\Measurement\AdditionalMetaData;
use App\Slim\Model\QMUnit;
use App\Types\TimeHelper;
use App\Units\HoursUnit;
use App\VariableCategories\ActivitiesVariableCategory;
use App\Variables\QMUserVariable;
use Carbon\Carbon;
use Google_Service_Calendar;
use OAuth\Common\Token\Exception\ExpiredTokenException;
/** @property Google_Service_Calendar service
 */
class GoogleCalendarConnector extends GoogleBaseConnector {
	private static $URL_CALENDARS = 'https://www.googleapis.com/calendar/v3/users/me/calendarList';
	private static $URL_EVENT_RECORDS = 'https://www.googleapis.com/calendar/v3/calendars/{CALENDAR}/events';
	protected const BACKGROUND_COLOR = '#2c6efc';
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Activities';
	public const DISPLAY_NAME = 'Google Calendar';
	protected const ENABLED = false; // TODO: Re-enable after adding Experimental warning on front end
	protected const GET_IT_URL = 'https://calendar.google.com';
	protected const LOGO_COLOR = '#d34836';
	protected const LONG_DESCRIPTION = 'Use Google Calendar to automatically track repeated events like treatments.';
	protected const SHORT_DESCRIPTION = 'Automate your tracking by creating calendar events with a title containing the value, followed by the unit, followed by the variable name. To track an apple every day, create a repeating event called "1 serving Apples."';
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
    public $providesUserProfileForLogin = false;
    public $shortDescription = self::SHORT_DESCRIPTION;
    public const ID = 51;
    public const IMAGE = 'https://i.imgur.com/AYeEK86.png';
    public const NAME = 'googlecalendar';
    public static array $SCOPES = [Google_Service_Calendar::CALENDAR_READONLY];
    /**
     * @return int|QMUserVariable[]
     * @throws CredentialsNotFoundException
     * @throws ExpiredTokenException
     */
    public function importData(): void {
        $fromTime = $this->getFromTime();
        $calendars = $this->getCalendars();
        foreach($calendars as $calendarName){
            $this->getCalendarEvents($fromTime, $calendarName);
        }
        $this->saveMeasurements();
    }
    /**
     * @return array
     * @throws CredentialsNotFoundException
     * @throws ExpiredTokenException
     */
    private function getCalendars(){
        $calendars = [];
        $optParams = [
            //'maxResults' => 10,
            'minAccessRole' => 'writer',
        ];
        $params = http_build_query($optParams);
        $nextPage = self::$URL_CALENDARS.'?'.$params;
        while(!empty($nextPage)){
            if($this->weShouldBreak()){
                break;
            }
            $responseObject = $this->fetchArray($nextPage);
            $statusCode = $this->getLastStatusCode();
            switch($statusCode){
                case 200:
                    $this->logDebug("GoogleCalendar: Received $statusCode");
                    foreach($responseObject['items'] as $calendar){
                        $calendars[] = $calendar['id'];
                    }
                    if(isset($responseObject['nextPageToken'])){
                        $params = ['nextPageToken' => $responseObject['nextPageToken']];
                        $nextPage = self::$URL_CALENDARS.'?'.http_build_query($params);
                    }else{
                        $nextPage = null;
                        break 2;
                    }
                    break;
                default:
                    $this->handleUnsuccessfulResponses($responseObject);
                    break 2;
            }
        }
        return $calendars;
    }
	/**
	 * @param array $event
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 */
    private function addCalendarMeasurement(array $event){
        if(!isset($event['summary'])){return;}
        $start = $this->getDate($event, 'start');
        $summary = $event['summary'];
        $unitName = $this->getUnitNameFromSummary($summary);
        if(!$unitName){return;}
        $variableName = $this->getVariableNameFromSummary($summary);
        if(!$variableName){return;}
        $value = $this->getValueFromSummary($summary);
        if($value === null){return;}
        $v = $this->getQMUserVariable($variableName, $unitName, $this->getDefaultVariableCategoryName(), [
            Variable::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => 60 * 30
        ]);
        try {
            $m = $this->generateMeasurement($v, $start->timestamp, $value, $unitName);
        } catch (TooManyMeasurementsException $e){ // TODO: Figure out why this is happening
            $this->logInfo(__METHOD__.": ".$e->getMessage());
            return;
        }
        $metaData = new AdditionalMetaData();
        $metaData->setUrl($event["htmlLink"]);
        $message = $event["description"] ?? $event["summary"] ?? null;
        if(!$message){$this->logInfo("No description in ".QMLog::var_export($event, true));}
        $m->setAdditionalMetaData($metaData);
        $m->setConnectorIdAndSourceName($this->getId());
        $v->addToMeasurementQueueIfNoneExist($m);
    }
	/**
	 * @param array $event
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 */
    private function addActivityMeasurement(array $event){
        $enabled = false; //Disable activities for now
        if(!$enabled){return;}
        $start = $this->getDate($event, 'start');
        $end = $this->getDate($event, 'end');
        $v = $this->getQMUserVariable($event['summary'], HoursUnit::NAME,
                                      ActivitiesVariableCategory::NAME);
        $m = $this->generateMeasurement($v, $start->timestamp, $end->diffInHours($start), HoursUnit::NAME);
        $m->setDuration($end->diffInHours($start) * 60 * 60);
        $v->addToMeasurementQueueIfNoneExist($m);
    }
    /**
     * @param int $fromTime
     * @param string $calendarName
     */
    private function getCalendarEvents(int $fromTime, string $calendarName): void {
        $baseUrl = $this->getBaseUrlForCalendar($calendarName);
        $url = $this->generateUrl($fromTime, $calendarName);
        while(!empty($url)){
            if($this->weShouldBreak()){break;}
            $responseObject = $this->fetchArray($url);
            $statusCode = $this->getLastStatusCode();
            switch($statusCode){
                case 200:
                    foreach($responseObject['items'] as $event){
                        $this->addCalendarMeasurement($event);
                        $this->addActivityMeasurement($event);
                    }
                    if(isset($responseObject['nextPageToken'])){ // Google keeps giving us same nextPageToken
                        $params = ['nextPageToken' => $responseObject['nextPageToken']];
                        if($baseUrl.'?'.http_build_query($params) == $url){
                            $url = null;
                            break 2;
                        }
                        $url = $baseUrl.'?'.http_build_query($params);
                    }else{
                        $url = null;
                        break 2;
                    }
                    break;
                default:
                    $this->handleUnsuccessfulResponses($responseObject);
                    break 2;
            }
        }
    }
    /**
     * @param $event
     * @param $key
     * @return Carbon
     */
    private function getDate($event, $key): Carbon{
        if(!isset($event[$key])){
            return Carbon::now();
        }
        if(isset($event[$key]['dateTime'])){
            return Carbon::createFromFormat(Carbon::RFC3339, $event[$key]['dateTime']);
        }
        if(isset($event[$key]['date'])){
            return Carbon::createFromFormat('Y-m-d', $event[$key]['date']);
        }
        return Carbon::now();
    }
    /**
     * @param string $summary
     * @return string|null
     */
    private function getUnitNameFromSummary(string $summary):?string{
        $summaryWords = explode(' ', $summary);
        if(!is_numeric($summaryWords[0])){return null;}
        if(!isset($summaryWords[1])){return null;}
        $unit = QMUnit::findByNameOrSynonym($summaryWords[1], false);
        if(!empty($unit)){
            return $unit->abbreviatedName;
        }
        return null;
    }
    /**
     * @param string $summary
     * @return float|null
     */
    private function getValueFromSummary(string $summary): ?float {
        $summaryWords = explode(' ', $summary);
        if(!is_numeric($summaryWords[0])){return null;}
        if(!isset($summaryWords[1])){return null;}
        return (float)$summaryWords[0];
    }
    /**
     * @param string $summary
     * @return string|null
     */
    private function getVariableNameFromSummary(string $summary):?string{
        $summaryWords = explode(' ', $summary);
        if(!is_numeric($summaryWords[0])){return null;}
        if(!isset($summaryWords[1])){return null;}
        unset($summaryWords[0], $summaryWords[1]);
        return implode(' ', $summaryWords);
    }
    /**
     * @param Carbon $timeMin
     * @param Carbon $timeMax
     * @return string
     */
    private function generateQueryString(Carbon $timeMin, Carbon $timeMax): string{
        $q = http_build_query([
            //'maxResults' => 10,
            'orderBy'      => 'startTime',
            'singleEvents' => 'true',
            'timeMin'      => $timeMin->toRfc3339String(),
            'timeMax'      => $timeMax->toRfc3339String()
        ]);
        return $q;
    }
    /**
     * @param int $fromTime
     * @param string $calendarName
     * @return string
     */
    private function generateUrl(int $fromTime, string $calendarName): string {
        $timeMin = TimeHelper::toCarbon($fromTime);
        $timeMax = TimeHelper::toCarbon(time() - (60 * 60 * 24 * 7)); // get events till 7 days ago
        $q = $this->generateQueryString($timeMin, $timeMax);
        return $this->getBaseUrlForCalendar($calendarName).'?'.$q;
    }
    /**
     * @param string $calendarName
     * @return string|string[]
     */
    private function getBaseUrlForCalendar(string $calendarName){
        $baseUrl = str_replace('{CALENDAR}', $calendarName, self::$URL_EVENT_RECORDS);
        return $baseUrl;
    }
}
