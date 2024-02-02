<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources;
use App\Exceptions\BadRequestException;
use App\Exceptions\CredentialsNotFoundException;
use App\Exceptions\NoGeoDataException;
use App\Fields\Avatar;
use App\Models\Measurement;
use App\Models\User;
use App\Models\Variable;
use App\Properties\Base\BaseCountryProperty;
use App\Properties\Base\BaseLatitudeProperty;
use App\Properties\Base\BaseLongitudeProperty;
use App\Properties\Base\BaseZipCodeProperty;
use App\Slim\Controller\Connector\ConnectException;
use App\Slim\Controller\Connector\ConnectorNonOauthConnectResponse;
use App\Slim\Controller\Connector\ConnectorResponse;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Types\QMArr;
use App\Types\TimeHelper;
use App\Utils\IPHelper;
use App\Utils\Stats;
use App\VariableCategories\EnvironmentVariableCategory;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
abstract class LocationBasedConnector extends QMConnector {
	protected $latestNonEnvironmentMeasurementAt;
	protected $earliestNonEnvironmentMeasurementAt;
	protected $newVariablesShouldBePublic = true;
	protected $zipCode;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = EnvironmentVariableCategory::NAME;
	protected static $PLACEHOLDER = 'Enter your zip code';
	public $defaultVariableCategoryName = EnvironmentVariableCategory::NAME;
	public $mobileConnectMethod = 'ip';
	public const IMAGE = EnvironmentVariableCategory::IMAGE_URL;
	/**
	 * @param $userId
	 */
	public function __construct($userId = null){
		parent::__construct($userId);
	}
	/**
	 * @param array $parameters
	 * @return ConnectorResponse
	 * @throws ConnectException
	 */
	public function connect($parameters){
		$credentials = [];
		if($ip = $this->getIpFromProvidedParameters($parameters)){
			$credentials['ip'] = $ip;
		}
		try {
			if($zip = $this->getZipCodeFromSuppliedParameters($parameters, $ip)){
				$credentials['zip'] = $zip;
			}
		} catch (BadRequestException $e) {
			$zip = false;
			$this->logInfo(__METHOD__.": ".$e->getMessage());
		}
		if($lat = $this->getLatitudeFromParamsOrIp($parameters, $ip)){
			$credentials['latitude'] = $lat;
		}
		if($long = $this->getLongitudeFromParamsOrIp($parameters, $ip)){
			$credentials['longitude'] = $long;
		}
		if(!$zip){
			throw new ConnectException($this, 'Please provide zip or ip parameter');
		}
		$QMUser = $this->getQmUser();
		$locationConnectors = $QMUser->getLocationBasedConnectors();
		$connections = $QMUser->getConnections();
		foreach($locationConnectors as $locationConnector){
			if($locationConnector->availableOutsideUS || BaseZipCodeProperty::validUSZip($zip)){
				$userMessage = "Importing for postal code $zip. ";
				$locationConnector->storeCredentials($credentials, $userMessage);
			}
		}
		return new ConnectorNonOauthConnectResponse($this);
	}
	/**
	 * @return ConnectInstructions
	 */
	public function getConnectInstructions(): ?ConnectInstructions{
		$parameters = [new ConnectParameter('Postal Code', 'zip', 'text', self::$PLACEHOLDER)];
		return $this->getNonOAuthConnectInstructions($parameters, "Enter your postal code");
	}
	/**
	 * @param array $params
	 * @param string|null $ip
	 * @return string
	 */
	private function getZipCodeFromSuppliedParameters(array $params, string $ip = null): string{
		$zip = BaseZipCodeProperty::pluck($params);
		if($zip){
			if(!BaseCountryProperty::getCountryCodeFromZip($zip)){
				$this->logError("$zip is not a valid zip code!");
			}
			$user = $this->getQmUser();
			$userZip = $user->zipCode;
			if($userZip && $userZip !== $zip){
				$user->logError("Previous zip $userZip does not match provided zip $zip");
			}
			if(!$user->zipCode){
				$user->updateDbRow([User::FIELD_ZIP_CODE => $zip]);
			}
			return $zip;
		}
		if($ip){
			$u = $this->getQmUser();
			try {
				$location = $u->getIpGeoLocation($ip);
				return $location->zip;
			} catch (NoGeoDataException $e) {
				$this->logError(__METHOD__.": ".$e->getMessage());
			}
		}
		if(!empty($params['location']) && BaseCountryProperty::getCountryCodeFromZip($params['location'])){
			return $params['location'];
		}
		throw new BadRequestException("Could not get ip from params: " . \App\Logging\QMLog::print_r($params, true));
	}
	/**
	 * @param $params
	 * @return string|null
	 */
	private function getIpFromProvidedParameters($params): ?string{
		if(isset($params['ip'])){
			return $params['ip'];
		}
		if(!empty($params['location']) && IPHelper::validIp($params['location'])){
			return $params['location'];
		}
		return null;
	}
	/**
	 * @return string|null
	 */
	public function getIpFromCredentials(): ?string{
		return $this->getCredentialsArray('ip');
	}
	/**
	 * @return string|null
	 */
	public function getZipFromCredentials(): ?string{
		$zip = $this->getCredentialsArray('zip');
		if($zip && BaseCountryProperty::getCountryCodeFromZip($zip)){
			return $this->zipCode = $zip;
		}
		return null;
	}
	/**
	 * @return mixed
	 * @throws NoGeoDataException
	 */
	protected function getZipCountryOrTimeZone(){
		if($connection = $this->connection){
			if($credentials = $connection->credentials){
				if($zip = $credentials['zip'] ?? null){
					return $zip;
				}
			}
		}
        $user = $this->getUser();
        $str = $user->getZipCode();
        if(!$str){
            $str = $user->timezone;
        }
        if(!$str){
            $str = $user->country;
        }
        if($str){
            return $str;
        }
		$credentialStorage = $this->getCredentialStorageFromMemory();
		$credentials = $credentialStorage->get();
		// Make sure we have a locationName property (legacy stuff)
		if(array_key_exists('locationName', $credentials)){
			return $credentials['locationName'];
		}
		if(array_key_exists('location', $credentials)){
			return $credentials['location'];
		}
		return false;
	}
	/**
	 * @return bool|null
	 * @throws CredentialsNotFoundException
	 */
	protected function userIsOutsideUSA(): ?bool{
		$countryName = $this->getCountryName();
		if(!$countryName){
			return null;
		}
		return $countryName !== BaseCountryProperty::UNITED_STATES;
	}
	/**
	 * @return string
	 * @throws CredentialsNotFoundException
	 */
	protected function getCountryName(): ?string{
		return $this->getUser()->getCountryName();
	}
	/**
	 * @return array|null
	 */
	private function getCoordinates(): ?array{
		$credentials = $this->getCredentialsArray();
		if(isset($credentials['location'])){
			$coordinates = explode(',', $credentials['location']);
			if(count($coordinates) === 2){
				return $coordinates;
			}
		}
		return null;
	}
	/**
	 * @return float
	 * @throws NoGeoDataException
	 */
	public function getLongitude(): float{
		$credentials = $this->getCredentialsArray();
		if(isset($credentials['longitude'])){
			return $credentials['longitude'];
		}
		if($this->getCoordinates()){
			return $this->getCoordinates()[1];
		}
		$geoLocation = $this->getQmUser()->getIpGeoLocation();
		if($geoLocation){
            if(!$geoLocation->longitude){
                throw new NoGeoDataException("No longitude found for ip " . $this->getQmUser(), $geoLocation);
            }
			return $geoLocation->longitude;
		}
		$latLong = $this->fetchLatLongFromZipIfPossible();
		if($latLong){
			return $latLong->lng;
		}
		$this->throwNoGeoLocationDataException();
		throw new \LogicException();
	}
	public function getIPAddress(): ?string{
		$ip = $this->getIpFromCredentials();
		if($ip){
			return $ip;
		}
		$user = $this->getQmUser();
		return $user->getIPAddress();
	}
	/**
	 * @return float
	 * @throws NoGeoDataException
	 */
	public function getLatitude(): float{
		$credentials = $this->getCredentialsArray();
		if(isset($credentials['latitude'])){
			return $credentials['latitude'];
		}
		if($this->getCoordinates()){
			return $this->getCoordinates()[0];
		}
		$ip = $this->getIpFromCredentials();
		if($ip){
			$geoLocation = $this->getQmUser()->getIpGeoLocation($ip);
			if($geoLocation){
				return $geoLocation->latitude;
			}
		}
		$latLong = $this->fetchLatLongFromZipIfPossible();
		if($latLong){
			return $latLong->lat;
		}
		$this->throwNoGeoLocationDataException();
	}
	/**
	 * @return string|null
	 */
	protected function getValidUSZip(): ?string{
		$zip = $this->getZip();
		if(!BaseZipCodeProperty::validUSZip($zip)){
			return null;
		}
		return $zip;
	}
	/**
	 * @return string
	 */
	public function getZip(){
		if($this->zipCode !== null){
			return $this->zipCode;
		}
		if($zip = $this->getZipFromCredentials()){
			return $this->zipCode = $zip;
		}
		$u = $this->getQmUser();
		$zip = $u->getZipCode();
		if($zip){
			return $this->zipCode = $zip;
		}
		return $this->zipCode = false;
	}
	/**
	 * @param string $variableName
	 * @param $value
	 * @param $startTime
	 * @param string $unitName
	 * @param string $variableCategoryName
	 * @param array $varData
	 * @return QMMeasurement
	 * @throws \App\DataSources\TooEarlyException
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 * @throws \App\Exceptions\TooSlowException
	 */
	protected function addWeather(string $variableName, $value, $startTimeAt, string $unitName,
		string $variableCategoryName = EnvironmentVariableCategory::NAME, array $varData = []): ?QMMeasurement{
		if(empty($unitName)){  // OpenWeatherMap doesn't have data sometimes as indicated by empty unit
			$this->logInfo("No unit provided! $variableName, $value, $startTimeAt, $unitName,
             $variableCategoryName", $varData);
			return null;
		}
		$varData[Variable::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS] = 86400;
		$varData[Variable::FIELD_IS_PUBLIC] = true;
		$rounded = Stats::roundToNearestMultipleOf(time_or_exception($startTimeAt), 86400);
		$roundedStartAt = db_date($rounded);
		return $this->addMeasurement($variableName, $roundedStartAt, $value, $unitName, $variableCategoryName, $varData, 86400);
	}
	/**
	 * @param string $name
	 * @param string $unitName
	 * @return QMUserVariable
	 */
	public function getWeatherUserVariable(string $name, string $unitName): QMUserVariable{
		$newVariableData[Variable::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS] = 86400;
		return $this->getQMUserVariable($name, $unitName, EnvironmentVariableCategory::NAME, $newVariableData);
	}
	/**
	 * @param QMVariable|\App\Models\Variable $variable
	 * @param array $urlParams
	 * @return string
	 */
	public function setInstructionsHtml($variable, array $urlParams = []): string{
		$url = $this->getConnectWebPageUrl($urlParams);
		$connectButton = $this->getConnectButton();
		$connectButton->setTextAndTitle("setting your location here");
		$variableImage = $variable->getVariableAvatarImageHtml(6);
		$connectButton->setImage($variable->getImage());
		$pill = $connectButton->getChipSmall();
		return $this->instructionsHtml = "
			<h3 class='text-2xl font-extrabold dark:text-white'>
				Automatic 
				$variableImage
            	$variable->displayName 
				Import by Location
			</h3>
			<p class='my-4 text-xl text-gray-500'>
            	Grant access to $variable->displayName data by $pill.
            </p>
";
	}
	/**
	 * @return object
	 */
	private function fetchLatLongFromZipIfPossible(): ?object{
		$zip = $this->getZip();
		if(!$zip){
			return null;
		}
		$latLong = BaseZipCodeProperty::getLatitudeAndLongitudeFromZipCode($zip);
		if(!$latLong){
			return null;
		}
		$this->updateCredentialField('latitude', $latLong->lat);
		$this->updateCredentialField('longitude', $latLong->lng);
		return $latLong;
	}
	/**
	 * @param array $params
	 * @param string|null $ip
	 * @return mixed
	 */
	private function getLatitudeFromParamsOrIp(array $params, ?string $ip){
		$lat = QMArr::getValue($params, ['lat', 'latitude']);
		if(!$lat && $ip){
			$lat = BaseLatitudeProperty::fromIP($ip);
		}
		return $lat;
	}
	/**
	 * @param array $params
	 * @param string|null $ip
	 * @return mixed
	 */
	private function getLongitudeFromParamsOrIp(array $params, ?string $ip){
		$long = QMArr::getValue($params, ['long', 'longitude']);
		if(!$long && $ip){
			$long = BaseLongitudeProperty::fromIP($ip);
		}
		return $long;
	}
//	/**
//	 * @return string
//	 */
//	public function getEndAt(): string{
//		$at = $this->getLatestNonEnvironmentMeasurementAt();
//		if(!$at){
//			$at = $this->getQmUser()->userRegistered;
//		}
//		$yesterday = TimeHelper::getYesterdayMidnightAt();
//		if($at > $yesterday){
//			return $yesterday;
//		}
//		return $at;
//	}
	/**
	 * @throws NoGeoDataException
	 */
	protected function throwNoGeoLocationDataException(): void{
		$c = $this->getOrCreateConnection();
		$u = $this->getQmUser();
		$m = "No valid geolocation data for $u.  Credentials are " . \App\Logging\QMLog::print_r($this->getCredentialsArray(), true);
		$c->disconnect($m, "Please reconnect by entering a US-based postal code. ");
		throw new NoGeoDataException($m);
	}
	public function addMeasurement(string $variableName, $startTime, $value, string $unitName,
		string $variableCategoryName = null, array $newVariableData = [], int $durationInSeconds = null,
		$note = null): QMMeasurement{
		$m = parent::addMeasurement($variableName, $startTime, $value, $unitName, $variableCategoryName,
			$newVariableData, $durationInSeconds, $note);
        try {
	        $latitude = $this->getLatitude();
	        $m->setLatitude($latitude);
        } catch (NoGeoDataException $e) {
            $this->logInfo(__METHOD__.": ".$e->getMessage());
        }
        try {
	        $longitude = $this->getLongitude();
	        $m->setLongitude($longitude);
        } catch (NoGeoDataException $e) {
            $this->logInfo(__METHOD__.": ".$e->getMessage());
        }
        try {
            $m->setLocation($this->getMeasurementLocation() ?? $this->getZipCountryOrTimeZone());
        } catch (CredentialsNotFoundException $e) {
            $this->logInfo(__METHOD__.": ".$e->getMessage());
        }
        return $m;
	}
	protected function setMessages(): void{
		parent::setMessages();
		if($this->userId){
			try {
				$location = $this->getZipCountryOrTimeZone();
			} catch (NoGeoDataException $e) {
				$location = null;
			}
			if($location){
				$this->message .= " | Using location $location.";
			}
		}
	}
	/**
	 * @return string
	 */
	public function getEarliestNonEnvironmentMeasurementAt(): ?string{
		$at = $this->earliestNonEnvironmentMeasurementAt;
		if($at === false){
			return null;
		}
		if($at !== null){
			return $at;
		}
		return $this->calculateEarliestNonEnvironmentMeasurementAt();
	}
	/**
	 * @return string
	 * @noinspection DuplicatedCode
	 */
	public function calculateEarliestNonEnvironmentMeasurementAt(): ?string{
		$time = Measurement::whereUserId($this->getUserId())
			->where(Measurement::FIELD_VARIABLE_CATEGORY_ID, "<>", EnvironmentVariableCategory::ID)
			->min(Measurement::FIELD_START_TIME);
		if($time){
			$at = db_date($time);
			$this->logInfo("Setting end time to the earliestNonEnvironmentMeasurementTime $at");
			return $this->earliestNonEnvironmentMeasurementAt = $at;
		} else{
			$this->earliestNonEnvironmentMeasurementAt = false;
			$this->logWarning("No non-environmental measurements for earliestNonEnvironmentMeasurementTime for user "
                .$this->getUser());
			return null;
		}
	}
	/**
	 * @return string
	 */
	public function getLatestNonEnvironmentMeasurementAt(): ?string{
		$at = $this->latestNonEnvironmentMeasurementAt;
		if($at === false){
			return null;
		}
		if($at !== null){
			return $at;
		}
		return $this->calculateLatestNonEnvironmentMeasurementAt();
	}
	/**
	 * @return string
	 */
	public function calculateLatestNonEnvironmentMeasurementAt(): ?string{
		$time = Measurement::whereUserId($this->getUserId())
			->where(Measurement::FIELD_VARIABLE_CATEGORY_ID, "<>", EnvironmentVariableCategory::ID)
			->max(Measurement::FIELD_START_TIME);
		if($time){
			$at = db_date($time);
			$this->logInfo("Setting end time to the latestNonEnvironmentMeasurementTime $at");
			return $this->latestNonEnvironmentMeasurementAt = $at;
		} else{
			$this->latestNonEnvironmentMeasurementAt = false;
			$this->logError("No non-environmental measurements for latestNonEnvironmentMeasurementTime");
			return null;
		}
	}
	/**
	 * @return int
	 * Need to use connector-specific logic for this instead of general latest measurement like in case of override in
	 *     TigerviewConnector
	 */
	public function getFromTime(): int{
		if($this->fromTime){
			return $this->fromTime;
		}
		$earliestNonEnvironmentMeasurementAt = $this->getEarliestNonEnvironmentMeasurementAt();
		$latestConnectorMeasurementAt = $this->getOrCalculateLatestMeasurementAt();
		$earliestConnectorMeasurementAt = $this->getEarliestMeasurementAt();
		$userRegisteredAt = $this->getQmUser()->userRegistered;
		$week = 7 * 86400;
		if($earliestNonEnvironmentMeasurementAt &&
			$earliestNonEnvironmentMeasurementAt < $earliestConnectorMeasurementAt){
			return strtotime($earliestNonEnvironmentMeasurementAt) - $week;
		}
		if($latestConnectorMeasurementAt){
			$i = strtotime($latestConnectorMeasurementAt) + 86400;
			return $i;
		}
		return strtotime($userRegisteredAt) - $week;
	}
	public function getAbsoluteFromAt(): string{
		return db_date($this->getFromTime());
	}
}
