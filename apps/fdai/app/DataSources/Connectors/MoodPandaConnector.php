<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\ConnectInstructions;
use App\DataSources\Connectors\Responses\MoodPandaResponse;
use App\DataSources\ConnectParameter;
use App\DataSources\PasswordConnector;
use App\Exceptions\CredentialsNotFoundException;
use App\Exceptions\RateLimitConnectorException;
use App\Logging\QMLog;
use App\Slim\Controller\Connector\ConnectException;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Controller\Connector\ConnectorNonOauthConnectResponse;
use App\Units\OneToFiveRatingUnit;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
/** Class MoodPandaConnector
 * @package App\DataSources\Connectors
 */
class MoodPandaConnector extends PasswordConnector {
    protected const DEVELOPER_CONSOLE = null;
	protected const AFFILIATE = false;
	protected const BACKGROUND_COLOR = '#e4405f';
	protected const CLIENT_REQUIRES_SECRET = false;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Emotions';
	public const DISPLAY_NAME = 'MoodPanda';
	protected const ENABLED = 0;
	protected const GET_IT_URL = 'https://moodpanda.com/';
	public const ID = 10;
	public const IMAGE = 'https://i.imgur.com/G7YBiwO.png';
	protected const LOGO_COLOR = '#00aced';
	protected const LONG_DESCRIPTION = 'MoodPanda.com is a mood tracking website and iphone app. You rate your happiness on a 0-10 scale, and optionally add a brief twitter-like comment on what is influencing your mood. MoodPanda is also a large community of friendly people, sharing their moods, celebrating each others\' happiness, and supporting each other when they\'re down.';
	public const NAME = 'moodpanda';
	protected const SHORT_DESCRIPTION = 'Tracks mood.';
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
	public $shortDescription = self::SHORT_DESCRIPTION;
    protected $crappy = true;
    // Keys for callback https://local.quantimo.do/api/connectors/moodpanda/connect
    // Dev Console: http://www.moodpanda.com/api/apply.aspx
    // Key is emailed to you.
    // Test User: connector@quantimo.do
    // NOTE: These keys are overwritten if you have values in the database.
    public static $BASE_API_URL = 'http://www.moodpanda.com/api/user';
    private static $URL_USER = '_URL_/data.ashx?email=_EMAIL_&format=xml&key=_KEY_';
    private static $URL_MOODS = '_URL_/feed/data.ashx?userid=_USERID_&from=_FROMDATE_&to=_TODATE_&format=xml&key=_KEY_';
    private $moodPandaUserId;
    /**
     * @return ConnectInstructions
     */
    public function getConnectInstructions(): ConnectInstructions{
        $parameters = [new ConnectParameter('Email', 'email', 'text')];
        return $this->getNonOAuthConnectInstructions($parameters, 'Enter Your MoodPanda Email');
    }
	/**
	 * @param array $parameters
	 * @throws ConnectException
	 * @throws RateLimitConnectorException
	 */
    public function connect($parameters): ConnectorNonOauthConnectResponse {
        if(empty($parameters['email'])){
            throw new ConnectException($this, 'No email specified');
        }
        // Attempt to get the user's "mood feed".
        $moodPandaUserIdByEmail = $this->getMoodPandaUserId($parameters['email']);
        if(is_int($moodPandaUserIdByEmail)){
            // Otherwise we apparently got a valid moods page back, so we store the credentials
            $credentials = ['userId' => $moodPandaUserIdByEmail];
            $this->storeCredentials($credentials);
            return new ConnectorNonOauthConnectResponse($this);
        }
        throw new ConnectException($this,
                                                "We couldn't contact MoodPanda to get your user ID, please try again later!");
    }
    /**
     * @return string
     */
    private function getEmail():string {
        return $this->getCredentialsArray('email');
    }
	/**
	 * @return int
	 * @throws RateLimitConnectorException
	 */
    private function setMoodPandaUserIdFromCredentialsOrWebsite(): int{
        $credentials = $this->getCredentialsArray();
        if(!$credentials){
            throw new CredentialsNotFoundException($this);
        }
        if(!array_key_exists('userId', $credentials)){
            $id = $this->getMoodPandaUserId($credentials['email']);
        }
        if(array_key_exists('userId', $credentials)){
            $id = (int)$credentials['userId'];
        }
        if(empty($id)){
            throw new CredentialsNotFoundException($this);
        }
        return $this->moodPandaUserId = $id;
    }
	/**
	 * @return int
	 * @throws RateLimitConnectorException
	 */
    private function getMoodPandaUserIdFromCredentialsOrWebsite(): int{
        return $this->moodPandaUserId ?: $this->setMoodPandaUserIdFromCredentialsOrWebsite();
    }
    /**
     * @return void
     * @throws CredentialsNotFoundException
     */
    public function importData(): void {
        $this->getMoodFeed(); // Get the moods from MoodPanda
    }
	/**
	 * @param string $email
	 * @return int
	 * @throws ConnectorException
	 */
    private function getMoodPandaUserId(string $email): int{
        $url = str_replace([
            '_EMAIL_',
            '_KEY_',
            '_URL_'
                           ], [
            $email,
            self::$API_KEY,
            self::$BASE_API_URL
                           ], self::$URL_USER);
        $userId = null;
	    $responseXml = $this->getRequest($url);
	    $userId = (int)$responseXml->User->UserID;
        if(!$userId){
            throw new CredentialsNotFoundException($this,
                "I couldn't find this MoodPanda user. Please check https://moodpanda.com/Account/Privacy/ ".
                "and disable privacy mode or contact help@curedao.org for help");
        }
        return $userId;
    }
    /**
     * @param int|null $fromTime
     * @return int
     */
    public function getFromTime(int $fromTime = null): int{
        if($fromTime){
			$this->setFromDate($fromTime);
        }
        $aMonthAgo = time() - 29 * 86400;
        if($this->fromTime < $aMonthAgo){
            QMLog::warning("fromTime cannot be earlier than one month ago! Falling back to a month ago", ['fromDate' => date('Y-m-d', $this->fromTime)]);
            $this->fromTime = $aMonthAgo;
        }
        return $this->fromTime;
    }
    /**
     * @param Response $response
     * @return void
     * @internal param MeasurementItem[] $moodMeasurementItems
     */
    private function saveMoodMeasurements($response): void{
        $v = OverallMoodCommonVariable::getUserVariableByUserId($this->getUserId());
        $responseXml = $response->xml();
        $moodMeasurementItems = [];
        /** @var MoodPandaResponse $moodData */
        foreach($responseXml->children() as $moodData){
            $m = $this->generateMeasurement($v, $moodData->Date, (int)$moodData->Rating / 2, OneToFiveRatingUnit::NAME);
            $moodMeasurementItems[] = $m;
        }
        if(!count($moodMeasurementItems)){
	        $this->handleNoNewMeasurements();
	        return;
        }
	    $this->saveMeasurements();
    }
    /**
     * Get the mood feed of the user.
     * MoodPanda lets us request the mood feed up to 12 months earlier.
     * @return void
     * @throws CredentialsNotFoundException
     */
    private function getMoodFeed(): void{
        //	Replace values in pre-defined URL
        $replaceParams = [
            '_USERID_',
            '_FROMDATE_',
            '_TODATE_',
            '_KEY_',
            '_URL_',
        ];
        $replaceValues = [
            $this->getMoodPandaUserIdFromCredentialsOrWebsite(),
            $this->getFromDate(),
            date('Y-m-d', round(microtime(true))),
            self::$API_KEY,
            self::$BASE_API_URL,
        ];
        $url = str_replace($replaceParams, $replaceValues, self::$URL_MOODS);
	    $response = $this->getRequest($url);
		$content = $response->getOriginalContent();
        if($content === "<Feed />"){
	        $this->handleNoNewMeasurements();
        } else {
	        $this->saveMoodMeasurements($content);
        }
    }
	protected function getNoNewMeasurementsMessage():string{
		return parent::getNoNewMeasurementsMessage()." for ".$this->getEmail();
	}
}
