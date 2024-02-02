<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\OAuth2Connector;
use App\Exceptions\CredentialsNotFoundException;
use App\Exceptions\InvalidTimestampException;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Products\AmazonHelper;
use App\Types\TimeHelper;
use App\Units\KilocaloriesUnit;
use App\Units\SecondsUnit;
use App\VariableCategories\PhysicalActivityVariableCategory;
use App\Variables\QMUserVariable;
use LogicException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Token\Exception\ExpiredTokenException;
use OAuth\OAuth2\Token\StdOAuth2Token;
/** RunKeeper test credentials.
 * Dev Portal
 * http://runkeeper.com/partner/applications/view
 * Redirect URI https://app.quantimo.do/api/connectors/runkeeper/connect
 */
class RunKeeperConnector extends OAuth2Connector {
	protected const AFFILIATE                      = false;
	protected const BACKGROUND_COLOR               = '#3d55a6';
	protected const CLIENT_REQUIRES_SECRET         = false;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Physical Activity';
	public const    DISPLAY_NAME                   = 'RunKeeper';
	protected const ENABLED                        = 1;
	protected const GET_IT_URL                     = 'http://www.amazon.com/gp/product/B004Z2TYTC/ref=as_li_qf_sp_asin_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B004Z2TYTC&linkCode=as2';
	public const    ID                             = 2;
	public const    IMAGE                          = 'https://i.imgur.com/GHhb4wb.png';
	protected const LOGO_COLOR                     = '#2d2d2d';
	protected const LONG_DESCRIPTION               = "RunKeeper is the simplest way to improve fitness, whether you're just deciding to get off the couch for a 5k, biking every day, or even deep into marathon training.\nTrack your runs, walks, bike rides, training workouts and all of the other fitness activities using the GPS in your Android Phone.";
	public const    NAME                           = 'runkeeper';
	protected const PREMIUM                        = true;
	protected const SHORT_DESCRIPTION              = 'Tracks your workouts.';
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
	public static $OAUTH_SERVICE_NAME = 'RunKeeper';
	private static $URL_FITNESS_ACTIVITIES = 'https://api.runkeeper.com/fitnessActivities';
	public static array $SCOPES = [];
	/**
	 * @return int|QMUserVariable[]
	 * @throws CredentialsNotFoundException
	 * @throws ExpiredTokenException
	 */
	public function importData(): void{
		$this->getFitnessActivities();
	}
	/**
	 * Get fitness activity (cardio)
	 * @return void
	 * @throws CredentialsNotFoundException
	 * @throws ExpiredTokenException
	 */
	private function getFitnessActivities(): void{
		$caloriesBurned = $this->getQMUserVariable('Calories Burned', KilocaloriesUnit::NAME);
		$latestCaloriesString = $caloriesBurned->getLatestTaggedMeasurementAt();
		$fromTime = $latestConnectorUnixtime = $this->getOrCalculateLatestMeasurementAt();
		$this->logInfo("Setting fromTime to latest connector time ($latestConnectorUnixtime) because Runkeeper API won't filter by date");
		// Loop through the feed until we no longer have a nextPage
		$minSeconds = $caloriesBurned->getMinimumAllowedSecondsBetweenMeasurements();
		// TODO: Figure out why API doesn't respect start_date parameter
		// It seems to return data in descending chronological order
		//$time = $latestConnectorUnixtime + $minSeconds;
		//$startDate = StringHelper::getStringBeforeSubString('+', date('c', $time));
		//$alternativeStartDate =  date('c', $time);
		// Doesn't filter by date: $url = self::$URL_FITNESS_ACTIVITIES.'?start_date='.$startDate;
		// Doesn't filter by date: $url = self::$URL_FITNESS_ACTIVITIES.'?start_date_time='.$alternativeStartDate;
		$url = self::$URL_FITNESS_ACTIVITIES;
		while($url){
			if($this->weShouldBreak()){
				break;
			}
			$responseObject = $this->getRequest($url);
			$statusCode = $this->getLastStatusCode();
			switch($statusCode) {
				case 200:
					$url = $this->updateUrl($statusCode, $responseObject);
					$itemsInDescendingChronologicalOrder = $responseObject->items;
					foreach($itemsInDescendingChronologicalOrder as $currentActivity){
						$currentItemStartTime = $currentActivity->start_time;
						if(strtotime($currentItemStartTime) < $latestConnectorUnixtime){
							$this->logInfo("Breaking because current item $currentItemStartTime is less than fromTime " .
								TimeHelper::YYYYmmddd($latestConnectorUnixtime) .
								" and results are returned in descending order.");
							$url = null;
							break;
						}
						$this->addCalorieAndActivityMeasurements($currentActivity, $caloriesBurned);
					}
					break;
				case 409:
				case 403:
					$m = "RunKeeper: Received $statusCode for " . self::$URL_FITNESS_ACTIVITIES;
					$this->logError($m);
					throw new LogicException($m);
				default:
					$this->handleUnsuccessfulResponses($responseObject);
			}
		}
		$this->saveMeasurements();
	}
	/**
	 * @param int $statusCode
	 * @param $responseObject
	 * @return string|null
	 */
	private function updateUrl(int $statusCode, $responseObject): ?string{
		$this->logDebug("RunKeeper: Received $statusCode");
		if(property_exists($responseObject, 'next')){
			$url = $responseObject->next;
			$this->logDebug("RunKeeper: Next page: $url");
		} else{
			$url = null;
			$this->logDebug('RunKeeper: No more pages available');
		}
		return $url;
	}
	/**
	 * @param $currentActivity
	 * @param int|string $dateStringOrUnixtime
	 * @param float $duration
	 * @throws InvalidVariableValueAttributeException
	 */
	private function addActivityTypeMeasurement($currentActivity, $dateStringOrUnixtime, float $duration): void{
		$typeVariable =
			$this->getQMUserVariable($currentActivity->type, SecondsUnit::NAME, PhysicalActivityVariableCategory::NAME);
		if($duration > 86400){
			$this->logInfo("Dividing duration $duration by 1000 because I assume it was mistakenly reported in milliseconds");
			$duration = $duration / 1000;
		}
		$m = $this->generateMeasurement($typeVariable, $dateStringOrUnixtime, $duration, SecondsUnit::NAME);
		$typeVariable->addToMeasurementQueueIfNoneExist($m);
	}
	/**
	 * @param $currentActivity
	 * @param QMUserVariable $caloriesBurned
	 * @throws InvalidVariableValueAttributeException
	 * @throws \App\Exceptions\TooSlowException
	 */
	private function addCalorieAndActivityMeasurements($currentActivity, QMUserVariable $caloriesBurned): void{
		$duration = round($currentActivity->duration);
		$value = $currentActivity->total_calories;
		$startTime = $currentActivity->start_time;
		try {
			$result = $this->addMeasurement($caloriesBurned->name, $startTime, $value, KilocaloriesUnit::NAME,
				$caloriesBurned->variableCategoryName, [], $duration);
		} catch (InvalidTimestampException $e) {
			le($e);
		}
		if(!$result){
			$caloriesBurned->logInfo("$startTime $value kcal measurement already exists. " .
				"Skipping activity measurement...");
		} else{
			$this->addActivityTypeMeasurement($currentActivity, $startTime, $duration);
		}
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationEndpoint(){
		return new Uri('https://runkeeper.com/apps/authorize');
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAccessTokenEndpoint(){
		return new Uri('https://runkeeper.com/apps/token');
	}
	/**
	 * {@inheritdoc}
	 */
	protected function getAuthorizationMethod(): int{
		return static::AUTHORIZATION_METHOD_HEADER_BEARER;
	}
	/**
	 * {@inheritdoc}
	 */
	protected function parseAccessTokenResponse($responseBody): StdOAuth2Token{
		$data = $this->jsonDecodeAccessTokenResponse($responseBody);
		return $this->newStdOAuth2Token($data);
	}
}
