<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\ConnectInstructions;
use App\DataSources\ConnectParameters\UsernameConnectParameter;
use App\DataSources\PasswordConnector;
use App\DataSources\TooEarlyException;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\TooSlowException;
use App\Logging\QMLog;
use App\Slim\Controller\Connector\ConnectException;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Controller\Connector\ConnectorNonOauthConnectResponse;
use App\Types\QMArr;
use App\Types\TimeHelper;
use App\Units\EventUnit;
use App\Utils\Stats;
use App\VariableCategories\ActivitiesVariableCategory;
class WhatPulseConnector extends PasswordConnector {
    //public const CREDENTIALS_USER_ID   = '711522';
    //public const CREDENTIALS_USERNAME = 'quantimodo';
    const INSTRUCTIONS_SCREENSHOT = 'https://prnt.sc/r75rrq';
    protected $maximumTestDurationInSeconds = 60;  // Needs more time to get enough measurements
    protected const AFFILIATE = false;
    protected const BACKGROUND_COLOR = '#29a2b0';
    protected const CLIENT_REQUIRES_SECRET = false;
    protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Activities';
	protected const DEVELOPER_CONSOLE = null;
	
	
	public const DISPLAY_NAME = 'WhatPulse';
	protected const ENABLED = 1;
	protected const GET_IT_URL = 'http://www.whatpulse.org/downloads/';
	protected const LOGO_COLOR = '#2d2d2d';
	protected const LONG_DESCRIPTION = 'WhatPulse is a small application that measures your keyboard/mouse usage, down- & uploads and your uptime. It sends these statistics here, to the website, where you can use these stats to analyze your computing life, compete against or with your friends and compare your statistics to other people.';
	protected const SHORT_DESCRIPTION = 'Tracks keyboard and mouse usage.';
	
	public static $BASE_API_URL = 'http://www.whatpulse.org';
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
	public $variableNames = ['Mouse Clicks', 'Keystrokes'];
    public const ID = 3;
    public const IMAGE = 'https://i.imgur.com/EEZxqtd.png';
    public const NAME = 'whatpulse';
    public const DISABLED_UNTIL = "2020-09-16";
    public const TEST_USER_ID = '637652';
    public const TEST_USERNAME = 'mikepsinn';
    /**
     * @return ConnectInstructions
     */
    public function getConnectInstructions(): ConnectInstructions{
        $param = new UsernameConnectParameter();
        $text = 'Enter your Whatpulse username found next to your avatar on the WhatPulse My Stats page at whatpulse.org. '.
            self::whatpulseSettingsInstruction();
        $param->setHelpText($text);
        return $this->getNonOAuthConnectInstructions([$param], $text);
    }
    public static function whatpulseSettingsInstruction():string{
        return 'Make sure to set the WhatPulse app to pulse daily like so: '.WhatPulseConnector::INSTRUCTIONS_SCREENSHOT;
    }
	/**
	 * @param array $parameters
	 * @return ConnectException|ConnectorNonOauthConnectResponse
	 * @throws ConnectException
	 * @throws ConnectorException
	 */
    public function connect($parameters): ConnectorNonOauthConnectResponse {
        if(empty($parameters['username'])){
            throw new ConnectException($this, 'No username specified');
        }
        $url = 'http://api.whatpulse.org/user.php?format=json&user='.$parameters['username'];
        $response = $this->getRequest($url);
        if(!property_exists($response, 'AccountName') ||
           !property_exists($response, 'UserID')){
            le('Unexpected response from WhatPulse', $response);
        }
	    if(property_exists($response, 'error')){
		    $this->logError('Cannot connect to WhatPulse:'.$response['error']);
		    throw new ConnectException($this,
		                                            "WhatPulse couldn't find this user, is your profile public?");
	    }
	    $this->storeCredentials(['username' => $response->AccountName, 'userid'   => $response->UserID]);
	    return new ConnectorNonOauthConnectResponse($this);
    }

    /**
     * @return void
     * @throws ConnectorException
     * @throws InvalidVariableValueAttributeException
     * @throws TooSlowException
     * @throws TooEarlyException
     */
    public function importData(): void {
        $fromTime = $this->getFromTime();
        $providedFromTime = $fromTime;
        $mouse = $this->getQMUserVariable('Mouse Clicks', EventUnit::NAME,
                                          ActivitiesVariableCategory::NAME);
        $keystrokes = $this->getQMUserVariable('Keystrokes', EventUnit::NAME,
                                               ActivitiesVariableCategory::NAME);
        $fromTime = $this->determineInitialFromTime($providedFromTime, $mouse, time() - 90 * 86400);
        $fromTime = strtotime(TimeHelper::YYYYmmddd($fromTime)); // Round to midnight
        while($fromTime < time() - 86400){
            $mouseValues = $keystrokeValues = [];
            $endAt = db_date($fromTime + 86400); // We can only seem to import a day's worth of pulses at a time
            $endTime = strtotime($endAt);
            $url = 'http://api.whatpulse.org/pulses.php?format=json&user='. $this->getConnectorUserName().
                "&start=$fromTime&end=$endTime";
            $fromAt = db_date($fromTime);
            $span = "from $fromAt to $endAt";
            $this->logInfoWithoutContext("Getting data $span from $url...");
            $response = $this->getRequest($url);
			if($response === 'No pulses found!'){
				$this->logError($response." on $fromAt");
				$fromTime += 86400;
				continue;
			}
			if(is_string($response)){
				$this->logError("Unexpected response from WhatPulse $span ".QMLog::print_r($response, true));
				$fromTime += 86400;
				continue;
			}
            foreach($response as $pulse){
	            $pulse = QMArr::toArray($pulse);
                if(!isset($pulse['Timestamp'])){
                    $this->logInfo(self::whatpulseSettingsInstruction().
                        QMLog::print_r($response, true));
                    continue;
                }
                $startDate = TimeHelper::YYYYmmddd($pulse['Timestamp']);
                //$mouse->alreadyHaveMeasurementWithThisStartTime($startTime, true);
                if(strtotime($startDate) < $fromTime){
                    $this->logError("We got a pulse at $startDate but it's before our fromTime $fromAt");
                    continue;
                }
                //$this->logInfo(\App\Logging\QMLog::print_r($pulse, true));
                if($value = $pulse['Clicks']){
                    $mouseValues[$startDate][] = $value;
                }
                if($value = $pulse['Keys']){
                    $keystrokeValues[$startDate][] = $value;
                }
            }
            foreach($mouseValues as $date => $values){
                $this->addMeasurement($mouse->name,
                    $date,
                    Stats::sum($values),
                    EventUnit::NAME,
                    $mouse->variableCategoryName,
                    [],
                    86400);
            }
            foreach($keystrokeValues as $date => $values){
                $this->addMeasurement($keystrokes->name,
                    $date,
                    Stats::sum($values),
                    EventUnit::NAME,
                    $mouse->variableCategoryName,
                    [],
                    86400);
            }
            $fromTime += 86400;
            if ($this->weShouldBreak()) {
                break;
            }
        }
        $this->saveMeasurements();
    }
}
