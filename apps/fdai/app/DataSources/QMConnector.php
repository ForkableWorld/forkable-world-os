<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpMultipleClassDeclarationsInspection */
namespace App\DataSources;
use App\Buttons\Import\ImportConnectorButton;
use App\Buttons\Import\DisconnectButton;
use App\Buttons\Import\ConnectorUpdateButton;
use App\Buttons\Import\ImportingButton;
use App\Buttons\QMButton;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\BadRequestException;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\CredentialsNotFoundException;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoChangesException;
use App\Exceptions\QMException;
use App\Exceptions\RateLimitConnectorException;
use App\Exceptions\TooManyMeasurementsException;
use App\Exceptions\TooSlowException;
use App\Exceptions\UnauthorizedException;
use App\Files\FileHelper;
use App\Http\Urls\FinalCallbackUrl;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\Connection;
use App\Models\Connector;
use App\Models\ConnectorImport;
use App\Models\ConnectorRequest;
use App\Models\OAAccessToken;
use App\Models\User;
use App\Models\Variable;
use App\Models\WpUsermetum;
use App\Parameters\StateParameter;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BasePlatformProperty;
use App\Properties\Base\BaseUserErrorMessageProperty;
use App\Properties\Base\BaseUserLoginProperty;
use App\Properties\Connection\ConnectionConnectStatusProperty;
use App\Properties\Connection\ConnectionUpdateStatusProperty;
use App\Properties\Connector\ConnectorIdProperty;
use App\Properties\Connector\ConnectorNameProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\User\UserUserLoginProperty;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Controller\Connector\ConnectorResponse;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Measurement\MeasurementSet;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Traits\ModelTraits\ConnectorTrait;
use App\Traits\Scrapes;
use App\Types\ObjectHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\SecretHelper;
use App\Utils\Stats;
use App\Utils\UrlHelper;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;
use OAuth\Common\Http\Uri\Uri;
use stdClass;
abstract class QMConnector extends QMDataSource {
	use ConnectorTrait, Scrapes;
	const        ACTION_CONNECT                = 'connect';
	const        ACTION_DISCONNECT             = 'disconnect';
	const        ACTION_UPDATE                 = 'update';
	public const CONNECTOR_USER_ID_META_SUFFIX = '_connector_user_id';
	const        PLACEHOLDER_END_DATE          = "[end_date]";
	const        PLACEHOLDER_START_DATE        = "[start_date]";
	const        RESPONSE_TYPE_JSON            = 'json';
	protected $allowMeasurementsForCurrentDay = false;
	protected $connectorRequests;
	protected $connectorUserId;
	protected $connectorUserProfile;
	protected $createRemindersForNewVariables = true;
	protected $credentialsArray;
	protected $currentEndTime;
	protected $currentFromTime;
	protected $endAt;
	protected $endpointVariables = [];
	protected $endTime;
	protected $fromTime;
	protected $importStartTime;
	protected $logger;
	protected $maximumRequestTimeSpanInSeconds;
	protected $maximumTestDurationInSeconds = 30;
	protected $measurementLocation;
	protected $connectorUserMeta;
	protected $newConnectorRequests = [];
	protected $newVariablesShouldBePublic = true;
	protected $numberOfNewMeasurements = 0;
	protected $providesEmail = true;
	protected $Connection;
	protected $requestIntervalInSeconds;
	protected $responseType = self::RESPONSE_TYPE_JSON;
	protected $useFileResponsesInTesting = true;
	protected $qmUserVariables = [];
	protected $variableName;
	
	
	
	public $availableOutsideUS = true;
	public $connectError;
	public $connected;
	public $connectInstructions;
	public $connectorUserEmail;
	public $connectStatus;
	public $fontAwesome = Connection::FONT_AWESOME;
	public $importViaApi = true;
	public $lastSuccessfulUpdatedAt;
	public $logoutUrl;
	public $message;
	public $minimumAllowedSecondsBetweenMeasurements = 86400;
	public $mobileConnectMethod;
	public $newMeasurements;
	public $providesUserProfileForLogin;
	public $qmClient;
	public $spreadsheetUpload;
	public $updateError;
	public $updateRequestedAt;
	public $updateStatus;
	public $variableNames = [];
	public const DISABLED_UNTIL          = null;
	public const ENDPOINT_VARIABLES      = [];
	public const FIELD_ENABLED           = 'enabled';
	public const FIELD_ID                = 'id';
	public const ID                      = null;
	public const NAME                    = null;
	public const RECONNECT_MESSAGE       = "Please try disconnecting and re-connecting at https://app.quantimo.do/import or create a ticket at https://help.quantimo.do.";
	public const TABLE                   = 'connectors';
	public const USER_DISCONNECT_REQUEST = "user disconnect_request";
	public static $anonymousConnectors;
	public static $BASE_API_URL = null;
	public static $testAccessToken;
	public $platforms = [
		BasePlatformProperty::PLATFORM_IOS,
		BasePlatformProperty::PLATFORM_ANDROID,
		BasePlatformProperty::PLATFORM_WEB,
		BasePlatformProperty::PLATFORM_CHROME,
	];
	/**
	 * @var Connection|null
	 */
	protected ?Connection $connection = null;
	/**
	 * @param int|null $userId
	 */
	public function __construct(int $userId = null){
		$this->clientId = static::NAME;
		parent::__construct();
		$this->dataSourceType = QMDataSource::TYPE_CONNECTOR;
		if($userId){
			$this->userId = $userId;
		}
		if($this->enabled && AppMode::isApiRequest() && QMRequest::urlContains('connectors')){
			$this->addExtendPropertiesForRequest();
		}
	}
	/**
	 * @return static[]
	 */
	public static function getLoginConnectors(): array{
		return collect(static::getEnabled())->where('providesUserProfileForLogin', true)->all();
	}
	/**
	 * @return bool
	 */
	protected function isWaiting(): bool{
		return $this->updateStatus === ConnectionUpdateStatusProperty::IMPORT_STATUS_WAITING &&
			$this->connectStatus !== ConnectionConnectStatusProperty::CONNECT_STATUS_DISCONNECTED;
	}
	/**
	 * @param \App\Slim\Model\Measurement\QMMeasurement $m
	 */
	protected function populateMeasurement(QMMeasurement $m): void{
		$connection = $this->getConnectionIfExists();
		$last = $connection->getLatestMeasurementAtAttribute();
		$time = $m->getOrSetStartTime();
		if($time > $last){$connection->setLatestMeasurementAtAttribute($time);}
		$m->connectionId = $connection->id;
		$m->connectorImportId = $this->getConnectorImport()->id;
		$m->clientId = $this->name;
		$source = $m->getDataSource();
		if(!$source || $source->getId() !== $this->getId()){
			$this->exceptionIfTesting("getDataSource from measurement was $source but should be $this", (array)$m);
		}
		if(!$this->allowMeasurementsForCurrentDay &&
			TimeHelper::YYYYmmddd($m->getOrSetStartTime()) === TimeHelper::YYYYmmddd(time())){
			le("Why are we saving a {$m->getVariableName()} measurement for the current date?");
		}
	}
	/**
	 * @return string
	 */
	protected function getCurrentEndAt(): string{
		return db_date($this->getCurrentEndTime());
	}
	/**
	 * @return int
	 */
	public function getCurrentEndTime(): int{
		if(!$this->currentEndTime){
			$this->currentEndTime = $this->getCurrentFromTime() + $this->getMaximumRequestTimeSpanInSeconds();
		}
		return $this->currentEndTime;
	}
	/**
	 * @return void
	 * @throws\App\Exceptions\TemporaryConnectionException
	 * @throws CredentialsNotFoundException
	 */
	abstract public function importData(): void;
	/**
	 * @param string|int $nameOrId
	 * @param int|null $userId
	 * @return QMConnector
	 */
	public static function getConnectorByNameOrId($nameOrId, int $userId = null): QMConnector{
		if(is_int($nameOrId)){
			$QMConnectors = self::getConnectors($userId);
			foreach($QMConnectors as $c){
				if($nameOrId === $c->id){
					return $c;
				}
			}
		}
		$c = self::getConnectorByName($nameOrId, false, $userId);
		if(!$c){
			$c = self::getConnectorByName($nameOrId, false, $userId);
			le("no connector for $nameOrId and user $userId");
		}
		return $c;
	}
	/**
	 * @return QMConnector[]
	 */
	public static function getUnauthenticated(): array{
		$userConnectors = self::getForRequest();
		/** @var stdClass[]|QMConnector[] $userConnectors */
		foreach($userConnectors as $c){
			unset($c->userId);
			$c->connected = false;
			$c->connectError = null;
			$c->connectStatus = ConnectionConnectStatusProperty::CONNECT_STATUS_DISCONNECTED;
		}
		return $userConnectors;
	}
	/**
	 * @param string $name
	 * @param bool $throwException
	 * @param int|null $userId
	 * @return QMConnector
	 */
	public static function getConnectorByName(string $name, bool $throwException = true,
		int $userId = null): ?QMConnector{
		$connectors = self::getConnectors($userId);
		foreach($connectors as $c){
			if($name === $c->name){
				return $c;
			}
		}
		// TODO:  Get rid of numbers in https://local.quantimo.do/api/v1/connect/mobile!  Too error-prone in case we have connectors with numbers
		$name = QMStr::removeNumbersFromEndOfString($name);  // Remove id's corresponding to device numbers
		foreach($connectors as $c){
			if($name === $c->name){
				return $c;
			}
		}
		foreach($connectors as $c){
			if(!isset($c->name)){
				throw new LogicException(json_encode($c));
			}
			if(QMStr::isCaseInsensitiveMatch($c->name, $name)){
				return $c;
			}
		}
		$c = self::getConnectorByDisplayName($name, $userId);
		if($c){
			return $c;
		}
		if($throwException){
			throw new InvalidArgumentException('Incorrect connector name: ' . $name);
		}
		return null;
	}
	/**
	 * @return QMConnector[]
	 */
	public static function getAnonymousConnectors(): array{
		if($arr = self::$anonymousConnectors){
			return $arr;
		}
		$arr = [];
		$connectorsPath = abs_path('app/DataSources/Connectors');
		$connectorsPath .= (substr($connectorsPath, -1) ===
		DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR);  // Add a trailing slash if it wasn't added yet
		$handle = opendir($connectorsPath);
		$ns = FileHelper::folderToNamespace($connectorsPath);
		while(false !== ($fileName = readdir($handle))){  // Loop through contents
			if(str_ends_with($fileName, '.php')){  // If this entry is a PHP script
				$className = $ns . str_replace('.php', '', $fileName);
				try {
					$connector = new $className(null);
				} catch (\Throwable $e){
				    QMLog::error("Could not instantiate $className because: ".$e->getMessage());
					continue;
				}
				$arr[$connector->name] = $connector;
				static::$ID_TO_NAME[$connector->id] = $connector->name;
			}
		}
		closedir($handle);
		ksort($arr);
		return self::$anonymousConnectors = $arr;
	}
	/**
	 * @return ConnectorResponse
	 */
	public function requestImport(): ?ConnectorResponse{
		if(!$this->isImportViaApi()){
			return null;
		}
		$connection = $this->getOrCreateConnection();
		return $connection->requestImport();
	}
	/**
	 * @param Connection|null $connection
	 */
	public function addConnectionInfo(Connection $connection){
		if($this->name === 'fitbit'){
			//debugger("fitbit");
		}
		$this->connection = $connection;
		$arr = $connection->attributesToArray();
		foreach(ObjectHelper::getNonNullValuesWithCamelKeys($arr) as $key => $value){
			if($key !== "id" && property_exists($this, $key)){
				$this->$key = $value;
			}
		}
		$this->connected = $connection->connect_status === ConnectionConnectStatusProperty::CONNECT_STATUS_CONNECTED;
		$this->setMessages();
	}
	/**
	 * @return QMButton[]
	 */
	public function setDefaultButtons(): array{
		$buttons = [];
		$connection = $this->connection;
		if(!$connection || !$this->isConnected()){
			$buttons[] = new ImportConnectorButton($this);
			if($this->affiliate && $this->name !== BaseClientIdProperty::CLIENT_ID_QUANTIMODO){
				$buttons[] = $this->getGetItHereButton();
			}
		}
		if($this->isConnected()){
			$buttons[] = new DisconnectButton($this);
			if(!$this->isWaiting() || $connection->isStuck()){
				$buttons[] = new ConnectorUpdateButton($this);
			}
			if($this->isWaiting()){
				$buttons[] = new ImportingButton($this);
			}
		}
		if($connection){
			if($b = $connection->getMeasurementHistoryButton()){
				$buttons[] = $b;
			}
		}
		return $this->buttons = $buttons;
	}
	/**
	 * @return Connection
	 */
	public function getConnectionIfExists(): ?Connection{
		if($this->connection){
			return $this->connection;
		}
		if(!$this->userId){
			return null;
		}
		$user = $this->getUser();
		$connections = $user->getConnections();
		return $connections->firstWhere(Connection::FIELD_CONNECTOR_ID, $this->getId());
	}
	public function getUser(): User{
		return User::findInMemoryOrDB($this->getUserId());
	}
	/**
	 * @param string|null $userMessage
	 * @return Connection
	 */
	public function getOrCreateConnection(): Connection{
		/** @var Connection $c */
		if($c = $this->getConnectionIfExists()){
			$this->addConnectionInfo($c);
			return $c;
		}
		$c = $this->getConnectionIfExists();
		return $this->createConnection(null, $this->connectorUserProfile ?? []);
	}
	/**
	 * @param string $message
	 */
	public static function validateUserMessage(string $message){
		if(empty($message)){
			le("empty message");
		}
		foreach(BaseUserErrorMessageProperty::SHOULD_NOT_CONTAIN_STRINGS as $item){
			if(stripos($message, $item) !== false){
				QMLog::exceptionIfUnitTest("$message is not a valid user message!");
			}
		}
	}
	/**
	 * @return string
	 */
	public function getUpdateUrl(): string{
		return $this->getActionUrl(self::ACTION_UPDATE);
	}
	/**
	 * @return string
	 */
	public function getDisconnectUrl(): string{
		return $this->getActionUrl(self::ACTION_DISCONNECT);
	}
	/**
	 * @return string
	 */
	public function getConnectUrlWithParams(): string{
		return $this->getActionUrl(self::ACTION_CONNECT);
	}
	/** 
	 * @param string $action
	 * @return string
	 */
	private function getActionUrl(string $action): string{
		$url = UrlHelper::getApiUrlForPath('v1/connectors/' . $this->name . '/' . $action);
		if($u = QMAuth::getQMUserIfSet()){
			$token = $u->getAccessTokenStringIfSet();
			if($token){
				$url = UrlHelper::addParams($url, [OAAccessToken::FIELD_ACCESS_TOKEN => $token]);
			}
		}
		return $url;
	}
	/**
	 * @return string
	 */
	public static function getWindowCloseUrl(): string{
		return Env::getAppUrl() . '/api/v1/window/close';
	}
	/**
	 * @return QMConnector
	 */
	public static function getCurrentlyImportingConnector(): ?QMConnector{
		return Memory::get(Memory::CURRENTLY_IMPORTING_CONNECTOR, Memory::MISCELLANEOUS);
	}
	/**
	 * @param string $connectorDisplayName
	 * @param null $userId
	 * @return QMConnector
	 */
	public static function getConnectorByDisplayName(string $connectorDisplayName, $userId = null): ?QMConnector{
		$connectors = self::getConnectors($userId);
		foreach($connectors as $connector){
			if(strtolower($connector->displayName) === strtolower($connectorDisplayName)){
				return $connector;
			}
		}
		return null;
	}
	/**
	 * @param int $connectorId
	 * @param int|null $userId
	 * @return QMConnector
	 */
	public static function getConnectorById(int $connectorId, int $userId = null): ?QMConnector{
		$connectors = self::getConnectors($userId);
		foreach($connectors as $connector){
			if($connector->id === $connectorId){
				return $connector;
			}
		}
		QMLog::error("Could not find connector with id " . $connectorId);
		return null;
	}
	/**
	 * @return QMConnector
	 */
	public static function fromRequest(): ?QMConnector{
		$connectorNameOrId = ConnectorIdProperty::fromRequest(false);
		if(!$connectorNameOrId){
			$connectorNameOrId = ConnectorNameProperty::fromRequest();
		}
		if($connectorNameOrId){
			return self::getConnectorByNameOrId($connectorNameOrId);
		}
		return null;
	}
	/**
	 * @param int|null $userId
	 * @return QMConnector[]
	 */
	public static function getConnectors(int $userId = null): array{
		if($userId){
			$u = QMUser::find($userId);
			return $u->getOrSetConnectors();
		}
		if($u = QMAuth::getQMUser()){
			return $u->getOrSetConnectors();
		}
		return self::getAnonymousConnectors();
	}
	/**
	 * @return Connection
	 */
	public static function getCurrentConnection(): ?Connection{
		if(!self::getCurrentlyImportingConnector()){
			return null;
		}
		return self::getCurrentlyImportingConnector()->getConnectionIfExists();
	}
	/**
	 * @param QMConnector|null $currentConnector
	 * @return QMConnector
	 */
	public static function setCurrentlyImportingConnector(?QMConnector $currentConnector): ?QMConnector{
		return Memory::set(Memory::CURRENTLY_IMPORTING_CONNECTOR, $currentConnector);
	}
	/**
	 * @param string $finishUrl
	 * @param string $tokenName
	 * @param array $params
	 * @return RedirectResponse
	 * @throws ClientNotFoundException
	 */
	public static function addParamsToUrlAndRedirect(string $finishUrl, string $tokenName, array $params = []){
		$user = QMAuth::getQMUser();
		$clientId = BaseClientIdProperty::fromRequest(false);
		if($clientId === BaseClientIdProperty::CLIENT_ID_UNKNOWN){
			$clientId = BaseClientIdProperty::CLIENT_ID_QUANTIMODO;
		}
		if(!$clientId){
			if(str_contains($finishUrl, 'quantimo.do') || str_contains($finishUrl, 'quantimodo.com')){
				$clientId = BaseClientIdProperty::CLIENT_ID_QUANTIMODO;
			}
		}
		//if($user){$params['quantimodoUserId'] = $user->id;}
		if($clientId && !str_contains($finishUrl, Env::getAppUrl())){
			$params[BaseAccessTokenProperty::URL_PARAM_NAME] = $params[$tokenName] = $user->getOrSetAccessTokenString($clientId);
			$params['quantimodoClientId'] = $clientId;
		}
		//$params['apiOrigin'] = Env::getAppUrl();
		//$params['apiUrl'] = Env::getAppUrl(); // TODO: Remove this after updating web.quantimo.do
		//if(!BaseClientSecretProperty::fromRequest()){
			//$params['quantimodoClientSecret'] = BaseClientSecretProperty::fromRequest();
		//}
		//$finishUrl = UrlHelper::addQueryParamToUrl($finishUrl, 'state', QMConnector::getEncodedStateParam());  // Too long
		// Do we need this? $finishUrl = QMRequest::addProvidedAndRequestQueryParamsToUrl($finishUrl, $params);
		$finishUrl = UrlHelper::addParams($finishUrl, $params);
//		$finishUrl = UrlHelper::removeParams($finishUrl, QMClient::FIELD_CLIENT_SECRET);
//		$finishUrl = UrlHelper::removeParams($finishUrl, 'error');
//		$finishUrl = UrlHelper::removeParams($finishUrl, 'logout');
//		$finishUrl = UrlHelper::removeParams($finishUrl, 'state');
//		$finishUrl = UrlHelper::removeParams($finishUrl, 'code');
//		$finishUrl = UrlHelper::removeParams($finishUrl, 'scope');
//		$finishUrl = UrlHelper::removeParams($finishUrl, 'hd');
//		$finishUrl = UrlHelper::removeParams($finishUrl, 'prompt');
		return UrlHelper::redirect($finishUrl, 302);
	}
	/**
	 * @return string
	 */
	public function getErrorMessage(): ?string{
		return $this->errorMessage;
	}
	/**
	 * @return string
	 */
	public function getMessage(): ?string{
		return $this->message;
	}
	public function logNewMeasurementsToTable(){
		$measurements = $this->getNewMeasurements();
		QMLog::table($measurements, "New $this Measurements");
	}
	/**
	 * @param string $url
	 * @param $body
	 * @param array $headers
	 * @param string $method
	 * @param int $code
	 */
	public function saveConnectorRequestResponse(string $url, $body, array $headers = [], string $method = 'GET',
		int $code = 200): void{
		$cr = $this->newConnectorRequest($url, $method, $headers);
		$cr->code = $code;
		if(!is_string($body)){
			$body = json_encode($body);
		}
		$cr->response_body = QMStr::truncate(json_encode($body), 280);
		$key = now_at() . ":$url";
		if(isset($this->newConnectorRequests[$key])){ // TODO: use URL as key and check DB for previous request
			le("Why did we make duplicate request to $key?");
		}
		try {
			$cr->save();
		} catch (ModelValidationException $e) {
			le($e, $this);
		}
	}
	/**
	 * @return ConnectorRequest[]|\Illuminate\Database\Eloquent\Collection
	 */
	public function getConnectorRequests(): Collection{
		return $this->connectorRequests = $this->getConnection()->connector_requests()->get();
	}
	/**
	 * @return ConnectorRequest[]
	 */
	public function saveConnectorRequests(): array{
		return $this->newConnectorRequests = $this->getHttpClient()->saveConnectorRequests();
	}
	/**
	 * @param string $responseBody
	 */
	public function disconnectBecauseUnAuthorized(string $responseBody): void{
		$this->disconnect($responseBody,
			"We got an unauthorized response when trying to fetch your data at " . TimeHelper::humanTime() . ". ");
	}
	public function setEndpointVariable(QMUserVariable $uv){
		$this->endpointVariables[$this->getCurrentUrl()][$uv->name] = $uv;
	}
	/**
	 * @return string
	 */
	public function getResponseType(): string{
		return $this->responseType;
	}
	public function getConnection(): Connection{
		$connection = $this->getOrCreateConnection();
		return $connection->l();
	}
	/**
	 * @return false|int|string|null
	 */
	public function getIncrementalFromTime(): int{
		$absoluteEarliestFromTimeForConnector = $this->getAbsoluteFromAt();
		$at = $this->getOrCalculateLatestMeasurementAt();
		$latestMeasurementTime = time_or_exception($at) + 1;
		if($latestMeasurementTime > strtotime($absoluteEarliestFromTimeForConnector)){
			$this->logInfo("Using getOrCalculateLatestMeasurementTime for connector as fromTime");
			$fromTime = $latestMeasurementTime;
		} else{
			$this->logInfo("Using absoluteEarliestFromTimeForConnector for connector as fromTime");
			$fromTime = strtotime($absoluteEarliestFromTimeForConnector);
		}
		return $fromTime;
	}
	public function getEndAt(): string{
		$t = $this->endTime;
		if(!$t){
			$t = TimeHelper::getYesterdayMidnightTimestamp();
		}
		return db_date($t);
	}
	/**
	 * @return array
	 */
	public function getVariableNames(): array{
		return $this->variableNames;
	}
	/**
	 * @param int|string $currentFromTime
	 * @param int $maximumRequestTimeSpanInSeconds
	 * @return int
	 */
	public function setCurrentFromAndEndTime($currentFromTime, int $maximumRequestTimeSpanInSeconds): int{
		$this->setMaximumRequestTimeSpanInSeconds($maximumRequestTimeSpanInSeconds);
		$this->setCurrentFromTime($currentFromTime);
		$end = time() - 86400;
		if($maximumRequestTimeSpanInSeconds){
			$end = $this->currentFromTime + $maximumRequestTimeSpanInSeconds;
		}
		if($end > (time() - 86400)){
			$end = time() - 86400; // Don't want to fetch partials of the current day
		}
		$this->setCurrentEndTime($end);
		$this->logDebug("Getting data between " . db_date($currentFromTime) . " to " . db_date($end) . "...");
		return $this->currentFromTime;
	}
	/**
	 * @param int|string $currentEndTime
	 * @return int
	 */
	protected function setCurrentEndTime($currentEndTime): int{
		if($currentEndTime){
			$this->currentEndTime = TimeHelper::universalConversionToUnixTimestamp($currentEndTime);
		} else{
			$this->currentEndTime = 0;
		}
		$at = db_date($this->currentEndTime);
		$this->logInfo("Set currentEndTime to $at");
		return $this->currentEndTime;
	}
	/**
	 * @return int
	 */
	public function getCurrentFromTime(): int{
		return $this->currentFromTime;
	}
	/**
	 * @return int
	 */
	private function getMaximumRequestTimeSpanInSeconds(): int{
		return $this->maximumRequestTimeSpanInSeconds;
	}
	/**
	 * @return int
	 */
	protected function incrementCurrentFromTime(): int{
		$span = $this->getMaximumRequestTimeSpanInSeconds();
		if(!$span){
			le("Please set MaximumRequestTimeSpanInSeconds");
		}
		$new = $this->getCurrentFromTime() + $span;
		return $this->setCurrentFromAndEndTime($new, $span);
	}
	/**
	 * @param int $maximumRequestTimeSpanInSeconds
	 */
	private function setMaximumRequestTimeSpanInSeconds(int $maximumRequestTimeSpanInSeconds): void{
		$this->maximumRequestTimeSpanInSeconds = $maximumRequestTimeSpanInSeconds;
	}
	/**
	 * @return int
	 */
	public function getTimeSpanOfNewMeasurements(): ?int{
		$max = $this->getMaximumStartTimeOfNewMeasurements();
		$min = $this->getMinimumStartTimeOfNewMeasurements();
		return $max - $min;
	}
	/**
	 * @return array
	 */
	public function getNewMeasurements(): array{
		$newMeasurements = $this->getOrSetNewMeasurementsByVariableName();
		return Arr::flatten($newMeasurements, 1);
	}
	/**
	 * @return array
	 */
	public function setNewMeasurements(): array{
		$newMeasurements = $this->setNewMeasurementsByVariableName(false);
		return Arr::flatten($newMeasurements, 1);
	}
	/**
	 * @return mixed
	 */
	public function getImportStartTime(): int{
		if(!$this->importStartTime){
			$this->importStartTime = time();
		}
		return $this->importStartTime;
	}
	/**
	 * @param int $seconds
	 * @return bool
	 */
	public function importDurationExceeds(int $seconds): bool{
		$currentDuration = time() - $this->getImportStartTime();
		return $currentDuration > $seconds;
	}
	public function getConnectorId(){
		return $this->id;
	}
	/**
	 * @return CredentialStorage
	 */
	public function getCredentialStorageFromMemory(): CredentialStorage{
		$cs = CredentialStorage::findInMemoryWhere(['userId' => $this->getUserId(), 'connectorId' => $this->getConnectorId()]);
		if(!$cs){
			$cs = new CredentialStorage($this->connection ?? $this->getConnection());
		}
		if($this->userId){
			$cs->setUserId($this->userId);
		}
		return $cs;
	}
	/**
	 * @param int|string $fromTimeAt
	 */
	public function setFromDate($fromTimeAt): void{
		TimeHelper::assertPast($fromTimeAt);
		$date = TimeHelper::YYYYmmddd($fromTimeAt); // Let's make sure we don't exclude measurements from the same day
		$this->fromTime = time_or_exception($date);
	}
	/**
	 * @param bool $createRemindersForNewVariables
	 */
	public function setCreateRemindersForNewVariables(bool $createRemindersForNewVariables){
		$this->createRemindersForNewVariables = $createRemindersForNewVariables;
	}
	/**
	 * @return bool
	 */
	public function isImportViaApi(): bool{
		return $this->importViaApi;
	}
	/**
	 * @return bool
	 */
	private function temporarilyDisabled(): bool{
		if(!static::DISABLED_UNTIL){
			return false;
		}
		return time() < strtotime(static::DISABLED_UNTIL);
	}
	/**
	 * @param string $url
	 * @param string $responseBody
	 * @param int $statusCode
	 * @param string|null $message
	 */
	public function logErrorResponse(string $url, string $responseBody, int $statusCode, string $message = null){
		if(!$message){
			$message = $this->displayName . ": Unrecognized status code " . $statusCode;
		}
		$this->logError($message, [
			'statusCode' => $statusCode,
			'url' => $url,
			'responseBody' => $responseBody,
		]);
	}
	/**
	 * @param $responseBody
	 * @throws \App\Exceptions\RateLimitConnectorException
	 */
	public function handleUnsuccessfulResponses($responseBody){
		$statusCode = $this->getLastStatusCode();
		$url = $this->getCurrentUrl();
		if(!is_string($responseBody)){
			$responseBody = QMLog::print($responseBody, "Response for $this");
		}
		switch($statusCode) {
			case 403:
			case QMException::CODE_UNAUTHORIZED:
				$this->logErrorResponse($url, $responseBody, $statusCode,
					$this->getTitleAttribute() . " $statusCode Response: Disconnecting and expiring");
				$this->disconnectBecauseUnAuthorized($responseBody);
				break;
			case 409:
				$this->logErrorResponse($url, $responseBody, $statusCode, 'Rate limit reached.  Trying again later.');
				throw new RateLimitConnectorException($url, $responseBody, $this, "GET", $statusCode);
			case 429:
				$this->logErrorResponse($url, $responseBody, $statusCode,
					$this->displayName . " made too many requests.  Trying again later. ");
				throw new RateLimitConnectorException($url, $responseBody, $this, "GET", $statusCode);
			case 2555:  //all credential related errors
				$this->logErrorResponse($url, $responseBody, $statusCode,
					$this->getTitleAttribute() . " $statusCode Response: Disconnecting and expiring");
				$this->disconnectBecauseUnAuthorized($responseBody);
				break;
			case 2554:
				$responseObject = json_decode($responseBody, true);
				throw new LogicException($responseObject["error"] . " from $url");
			default:
				$this->logErrorResponse($url, $responseBody, $statusCode, "Unrecognized status code!");
		}
	}
	/**
	 * @param string $responseBody
	 * @param string $userMessage
	 */
	public function disconnect(string $internalMessage, string $userMessage = null): void {
		$connection = $this->getConnectionIfExists();
		if(!$connection){
			throw new BadRequestException("No existing connection to $this->name to disconnect");
		}
		$connection->disconnect($internalMessage, $userMessage);
	}
	/**
	 * @param int|null $currentFromTime
	 * @return bool
	 */
	protected function weShouldBreak(int $currentFromTime = null): bool{
		if($currentFromTime){
			$this->currentFromTime = $currentFromTime;
		}
		if($this->currentFromTime > time() - 86400){
			$this->logInfo("Breaking because from time is within last 24 hours");
			return true;
		}
		if($endTime = $this->currentEndTime){
			$currentEndAt = $this->getCurrentEndAt();
			$endAt = $this->getEndAt();
			if($this->currentEndTime > strtotime($endAt)){
				$this->logInfo("Breaking because currentEndTime $currentEndAt exceeds absolute endTime $endAt");
				return true;
			}
		}
		Memory::setStartTimeIfNotSet();
		if(!AppMode::isTestingOrStaging()){
			return false;
		}
		$seconds = 60 * 20;
		$spanInDays = $this->getTimeSpanOfNewMeasurements() / 86400;
		if(!$spanInDays){
			return false;
		}
		if($spanInDays < 7){
			return false; // We need 7 days for testing
		}
		if($this->importDurationExceeds($seconds)){
			$this->logInfo("Breaking because import duration has exceeded $seconds seconds");
			return true; // Some guy's GitHub account takes hours!
		}
		$maximumTestDuration = $this->maximumTestDurationInSeconds;
		$s = Memory::getDurationInSeconds();
		if($s > $maximumTestDuration && AppMode::isTestingOrIsTestUser($this->getUserId())){
			QMLog::info("Breaking because duration is $s seconds");
			Memory::getDurationInSeconds("$this Import");
			Memory::setStartTime();
			return true;
		}
		QMLog::debug("NOT breaking because duration is $s seconds");
		return false;
	}
	/**
	 * @return string
	 */
	public function getOrCalculateLatestMeasurementAt(): ?string{
		$c = $this->getConnectionIfExists();
		if(!$c){
			return null;
		}
		return $c->getOrCalculateLatestMeasurementAt();
	}
	/**
	 * @return string
	 */
	public function calculateLatestMeasurementAt(): ?string{
		$c = $this->getConnectionIfExists();
		if(!$c){
			return null;
		}
		return $c->calculateLatestMeasurementAt();
	}
	/**
	 * @return string
	 */
	public function getEarliestMeasurementAt(): ?string{
		$c = $this->getConnectionIfExists();
		if(!$c){
			return null;
		}
		return $this->getConnection()->getEarliestMeasurementAt();
	}
	/**
	 * @param null $key
	 * @param bool $throwException
	 * @return array|string
	 */
	public function getCredentialsArray($key = null, bool $throwException = false){
		$storage = $this->getCredentialStorageFromMemory();
		$credentials = $storage->get();
		if($key){
			$value = QMArr::getValueForSnakeOrCamelCaseKey($credentials, $key);
			if(!$value && $throwException){
				le("Could not get $key!");
			}
			return $value;
		}
		return $credentials;
	}
	public function getEndDate(): string{
		return TimeHelper::YYYYmmddd($this->getEndAt());
	}
	/**
	 * @param QMMeasurement $m
	 * @param QMUserVariable $v
	 * @throws TooManyMeasurementsException
	 */
	protected function validateMeasurementTime(QMMeasurement $m, QMUserVariable $v): void{
		$m->setUserVariable($v);
		$existingMeasurements = $v->getMeasurementsIndexedByRoundedStartAt();
		$originalAt = $m->getStartAt();
		$roundedAt = $m->getRoundedStartAt();
		$fromAt = $this->getFromAt();
		$fromTimeMinusRequestInterval = strtotime($fromAt) - $this->requestIntervalInSeconds;
		// Moodscope inevitably gets extra because min resolution is a month
		if(strtotime($roundedAt) < $fromTimeMinusRequestInterval){
			$message = "$v ROUNDED measurement time $roundedAt is less than fromTime $fromAt. Original " .
				"measurement time was $originalAt.";
			throw new TooManyMeasurementsException($message);
		}
		$connectionFromAt = $this->getFromAt();
		$connectionFromTimeMinusRequestInterval = strtotime($connectionFromAt) - $this->requestIntervalInSeconds;
		// Moodscope inevitably gets extra because min resolution is a month
		if(strtotime($originalAt) < $connectionFromTimeMinusRequestInterval){
			throw new TooManyMeasurementsException("measurementAt $originalAt < connectionFromAt minus request interval " .
				db_date($connectionFromTimeMinusRequestInterval));
		}
		if(!$this->allowMeasurementsForCurrentDay &&
			TimeHelper::YYYYmmddd($roundedAt) === TimeHelper::YYYYmmddd(time())){
			$m = "Why are we saving a measurement for current date? " .
				"Maybe the day isn't over yet and there will be more data added.";
			//$this->logInfo($m);
			throw new TooManyMeasurementsException($m);
		}
	}
	public function getPHPUnitTestUrl(): string{
		return $this->getOrCreateConnection()->getPHPUnitTestUrl();
	}
	/**
	 * @param QMUserVariable $uv
	 * @param $startTime
	 * @param float $originalValue
	 * @param string $originalUnitName
	 * @param int|null $durationInSeconds
	 * @param null $note
	 * @return QMMeasurement
	 * @throws TooManyMeasurementsException
	 */
	protected function generateMeasurement(QMUserVariable $uv, $startTime, float $originalValue,
		string $originalUnitName, int $durationInSeconds = null, $note = null): QMMeasurement{
		$startTime = $uv->roundStartTime($startTime);
		$m = new QMMeasurement($startTime, $originalValue, $note, null, $originalUnitName);
		if($l = $this->getMeasurementLocation()){
			$m->setLocation($l);
		}
		if($durationInSeconds){
			$m->setDuration($durationInSeconds);
		}
		$m->setUserVariable($uv);
		$this->setEndpointVariable($uv);
		$m->setConnectorIdAndSourceName($this->getId());
		$uv->logDebug($m->getValueUnitString() . " on " . $m->getStartAt());
		return $m;
	}
	/**
	 * @return mixed
	 */
	public function getMeasurementLocation(){
		return $this->measurementLocation;
	}
	/**
	 * @param mixed $measurementLocation
	 */
	public function setMeasurementLocation($measurementLocation): void{
		$this->measurementLocation = $measurementLocation;
	}
	/**
	 * @param int $providedFromTime
	 * @param QMUserVariable $userVariableForEndpoint
	 * @param int|null $absoluteMinimum
	 * @return float|int|mixed|null
	 */
	protected function determineInitialFromTime(int $providedFromTime, QMUserVariable $userVariableForEndpoint,
		int $absoluteMinimum = null): int{
		$fromTime = $providedFromTime;
		$latestTaggedMeasurementAt = $userVariableForEndpoint->getLatestNonTaggedMeasurementStartAt();
		if(strtotime($latestTaggedMeasurementAt) > $fromTime){
			$fromTime = strtotime($latestTaggedMeasurementAt) + 1;
			$this->logInfo("Using LatestNonTaggedMeasurementTime for $userVariableForEndpoint: " .
				"$latestTaggedMeasurementAt as fromTime because it's later that the provided fromTime " .
				db_date($providedFromTime));
		}
		$lastImportTime = $this->getConnectionIfExists()->getLastImportTime();
		if($lastImportTime > $fromTime){
			$fromTime = $lastImportTime;
			$this->logInfo("Using lastImportTime: " . db_date($lastImportTime) .
				" as fromTime because it's later that the provided fromTime " . db_date($providedFromTime));
		}
		if($absoluteMinimum && $fromTime < $absoluteMinimum){
			$fromTime = time() - 90 * 86400;  // We can only get one day at a time, so we don't want to overdo it
			$this->logInfo("Using absoluteMinimum: " . db_date($absoluteMinimum) .
				" as fromTime because it's later that the provided fromTime " . db_date($providedFromTime));
		}
		return $fromTime;
	}
	/**
	 * @param string $statusCode
	 * @param $url
	 * @param string|object|array $responseBody
	 */
	protected function handleUnauthorizedResponse(string $statusCode, $url, $responseBody){
		$responseBody = QMStr::toString($responseBody);
		$this->logDebug("$this->name: Received $statusCode for $url " . $responseBody);
		$this->logErrorResponse($url, $responseBody, $statusCode,
			$this->getTitleAttribute() . " $statusCode Response: Disconnecting and expiring");
		$this->disconnectBecauseUnAuthorized($responseBody);
	}
	/**
	 * @param string $url
	 * @param $responseBody
	 * @param int $statusCode
	 */
	protected function handleUnrecognizedStatusCode(string $url, $responseBody, int $statusCode){
		$this->logError("$this->name: Unrecognized status code $statusCode for $url " . $responseBody);
		//$this->disconnect();
		//$this->expireConnection();
	}
	/**
	 * @param string $connectorAvatarUrl
	 */
	protected function updateUserAvatarIfNecessary(string $connectorAvatarUrl){
		if(!$connectorAvatarUrl){
			return;
		}
		if($this->userId){
			$user = $this->getQmUser();
			if(!$user->hasNonGravatarOrNonDefaultAvatar()){
				$user->updateAvatar($connectorAvatarUrl);
			}
		}
	}
	/**
	 * @param string|null $message
	 * @return void
	 */
	protected function setMessages(): void{
		if($connection = $this->getConnectionIfExists()){
			$message = $connection->getMessage();
			if($err = $connection->user_error_message){
				$this->setErrorMessage($err);
			}
		}
		if(empty($message)){$message = $this->longDescription;}
		$this->setMessage($message);
	}
	public function setMessage(string $message){
		if($this->connected && str_contains($message, "Disconnected ")){
			QMLog::error("Connector $this->name has connected=true but message=$message");
			$message = null;
		} else {
			self::validateUserMessage($message);
		}
		$this->message = $message;
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataString(): string{
		return " ";
	}
	/**
	 * @return string
	 */
	public function __toString(){
		if($this->userId){
			return $this->name . " for " . $this->getQmUser();
		}
		return $this->name;
	}
	/**
	 * @param bool $throwException
	 * @return array|string
	 */
	protected function getConnectorUserName(bool $throwException = false){
		if($name = $this->getCredentialsArray('username', $throwException)){
			return $name;
		}
		if(method_exists($this, 'getConnectorUserProfile')){
			if($r = $this->getConnectorUserProfile()){
				$name = UserUserLoginProperty::pluck($r);
			}
		}
		if(!$name && $this->userId){
			foreach(UserUserLoginProperty::SYNONYMS as $SYNONYM){
				if($name = $this->getUserMetaValue($SYNONYM)){
					return $name;
				}
			}
		}
		return $name;
	}
	/**
	 * @return bool
	 */
	public function isConnected(): bool{
		return $this->connectStatus === ConnectionConnectStatusProperty::CONNECT_STATUS_CONNECTED;
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function getQMUserVariables(): array{
		return $this->qmUserVariables;
	}
	/**
	 * @param bool $log
	 * @return array
	 */
	public function getOrSetNewMeasurementsByVariableName(bool $log = false): array{
		$arr = $this->newMeasurements;
		if(!$arr){
			$arr = $this->setNewMeasurementsByVariableName($log);
		}
		foreach($arr as $variableName => $measurements){
			if(!$measurements){
				unset($arr[$variableName]);
			}
		}
		return $this->newMeasurements = $arr;
	}
	/**
	 * @param string $variableName
	 * @param $startTime
	 * @param $value
	 * @param string $unitName
	 * @param string|null $variableCategoryName
	 * @param array $newVariableData
	 * @param int|null $durationInSeconds
	 * @param null $note
	 * @return QMMeasurement
	 * @throws InvalidAttributeException
	 * @throws TooSlowException
	 */
	public function addMeasurement(string $variableName, $startTime, $value, string $unitName,
		string $variableCategoryName = null, array $newVariableData = [], int $durationInSeconds = null,
		$note = null): QMMeasurement{
		if(!$startTime){
			le("No start time!");
		}
		if(APIHelper::timeLimitExceeded()){
			$msg = "This $this->displayName import is taking a while, so I'm going to ".
			       "have to queue it.  Feel free to check again in an hour or so. ";
			$this->setMessage($msg);
			$this->setErrorMessage($msg);
			throw new TooSlowException($msg);
		}
		$this->assertNotTooEarly($startTime);
		if(!$variableCategoryName){
			$variableCategoryName = $this->getDefaultVariableCategoryName();
		}
		$this->logDebug("Adding $value $unitName $variableName measurement from " . TimeHelper::YYYYmmddd($startTime));
		$uv = $this->qmUserVariables[$variableName] ?? null;
		if(!$uv){
			$newVariableData['variableCategoryName'] = $variableCategoryName;
			$newVariableData['unitName'] = $unitName;
			$newVariableData[Variable::FIELD_MANUAL_TRACKING] = false;
			$uv = QMUserVariable::findOrCreateByNameOrId($this->getUserId(), $variableName, [], $newVariableData);
			$this->setUserVariableByName($uv);
		}
		$m = $this->generateMeasurement($uv, $startTime, $value, $unitName, $durationInSeconds, $note);
		try {
			$this->validateMeasurement($m);
			$uv->addToMeasurementQueueIfNoneExist($m);
		} catch (InvalidVariableValueAttributeException $e) {
			if(!Stats::isZero($value)){  // We get 0's from connectors when they should be null sometimes.
				le($e); // But otherwise we might be converting incorrectly in which case we should throw exception
			}
			$this->logError($e->getMessage() .
				". Value from $this->displayName not valid! Skipping it and continuing...");
		} catch (TooManyMeasurementsException $e) {
			$this->logInfo("Skipping because " . $e->getMessage());
		}
		return $m;
	}
	/**
	 * @return int
	 */
	protected function getMaximumStartTimeOfNewMeasurements(): ?int{
		$measurements = $this->setNewMeasurements();
		if(!$measurements){
			return null;
		}
		return QMArr::max($measurements, 'startTime');
	}
	/**
	 * @return int
	 */
	protected function getMinimumStartTimeOfNewMeasurements(): ?int{
		$measurements = $this->setNewMeasurements();
		if(!$measurements){
			return null;
		}
		return QMArr::min($measurements, 'startTime');
	}
	/**
	 * @return int
	 * @throws ModelValidationException
	 */
	public function saveMeasurements(): int{
		$variables = $this->getQMUserVariables();
		$numSaved = 0;
		if(!$variables){
			$this->logInfo("No user variables with measurements to save");
		}
		$c = $this->getOrCreateConnection();
		$latestAtByVariable = [];
		$this->setNewMeasurementsByVariableName(false); // Make sure measurements are set before saving
		/** @noinspection PhpArrayUsedOnlyForWriteInspection */
		$byVariable = [];
		foreach($variables as $v){
			$byVariable[$v->name] = $v->getNewAndExistingMeasurementsIndexedByStartAt();
			$QMMeasurements = $v->getCombinedNewQMMeasurements();
            //$collection = $v->getCombinedNewMeasurements();
            //$this->getConnection()->measurements->concat($collection);
            //$this->getConnectorImport()->measurements->concat($collection);
			if($QMMeasurements){
				foreach($QMMeasurements as $m){
					$this->populateMeasurement($m);
				}
				try {
					$savedMeasurements = $v->saveMeasurements($this->id);
					$numSaved += count($savedMeasurements);
					$v->getLatestTaggedMeasurementAt();
				} catch (IncompatibleUnitException | InvalidVariableValueException | NoChangesException $e) {
					le($e);
				}
				$allMeasurements = $v->getRawMeasurementsForConnector($this->getId());
				$latest = end($allMeasurements);
				$latestAtByVariable[$v->name] = $latest->getStartAt();
			}
		}
		if(!$latestAtByVariable){
			$this->logInfo("No new measurements!");
			return 0;
		}
		$latestForAllVariables = max($latestAtByVariable);
		$previousLatestForConnector = $c->getLatestMeasurementAtAttribute();
		if(time_or_null($latestForAllVariables) > time_or_null($previousLatestForConnector)){
			$c->setLatestMeasurementAtAttribute($latestForAllVariables);
		}
		$c->validatePostImport();
		return $numSaved;
	}
	/**
	 * @param int $userId
	 */
	public function setUserId(int $userId): void{
		$this->userId = $userId;
		$this->getTokenStorage();
	}
	/**
	 * @return int
	 * Need to use connector-specific logic for this instead of general latest measurement like in case of override in
	 *     TigerviewConnector
	 */
	public function getFromTime(): int{
		$t = $this->fromTime;
		if($t){
			return $t;
		}
		$at = $this->getOrCalculateLatestMeasurementAt();
		if(!$at){
			return strtotime($this->getAbsoluteFromAt());
		}
		$fromTime = $this->getIncrementalFromTime();
		return $fromTime;
	}
	/**
	 * @return CarbonInterface
	 */
	public function getFromCarbon(): CarbonInterface{
		return TimeHelper::toCarbon($this->getFromTime());
	}
	public function getEndCarbon(): CarbonInterface{
		return TimeHelper::toCarbon(strtotime($this->getEndAt()));
	}
	/**
	 * @return string
	 */
	public function getFromAt(): string{
		$t = $this->getFromTime();
		if(!$t){
			$t = $this->getFromTime();
		}
		return db_date($t);
	}
	/**
	 * @param array $credentials
	 * @param string|null $userMessage
	 */
	protected function storeCredentials(array $credentials, string $userMessage = null){
		foreach($credentials as $key => $value){
			if(empty($value)){
				unset($credentials[$key]);
			}
		}
		$credentialStorage = $this->getCredentialStorageFromMemory();
		$credentialStorage->store($credentials);
		if(!$userMessage && isset($credentials["username"])){
			$userMessage = "Connected as user " . $credentials["username"] . ". ";
		}
		$c = $this->getOrCreateConnection();
		$c->connect($userMessage);
		$this->addConnectionInfo($c); // Don't remove this!
	}
	/**
	 * @param string $key
	 * @param $value
	 * @return bool
	 */
	protected function updateCredentialField(string $key, $value): bool{
		$credentials = $this->getCredentialsArray();
		if(isset($credentials[$key]) && $credentials[$key] === $value){
			$this->logInfo("$key has not changed so no  need to update");
			return false;
		}
		$credentials[$key] = $value;
		$this->storeCredentials($credentials);
		return true;
	}
	/**
	 * @param string $variableName
	 * @param string|null $defaultUnitName
	 * @param string|null $variableCategoryName
	 * @param array $params
	 * @return QMUserVariable
	 */
	public function getQMUserVariable(string $variableName, string $defaultUnitName = null,
		string $variableCategoryName = null, array $params = []): ?QMUserVariable{
		if($uv = $this->qmUserVariables[$variableName] ?? null){
			return $uv;
		}
		$params = $this->getNewVariableParams($variableCategoryName, $params, $defaultUnitName);
		$uv = QMUserVariable::findOrCreateByNameOrId($this->getUserId(), $variableName, [], $params);
		if($uv->isStupidVariable()){
			le($uv . " is stupid!");
		}
		return $this->setUserVariableByName($uv);
	}
	protected function setUserVariableByName(QMUserVariable $uv): QMUserVariable{
		return $this->qmUserVariables[$uv->name] = $uv;
	}
	/**
	 * @param array $parameters
	 * @param string $text
	 * @return ConnectInstructions
	 */
	protected function getNonOAuthConnectInstructions(array $parameters, string $text): ConnectInstructions{
		return $this->connectInstructions =
			new ConnectInstructions(static::getCallbackRedirectUrl(), $parameters, false, $text);
	}
	public function outputRequests(): array{
		$container = $this->requestContainer;
		// Iterate over the requests and responses
		foreach($container as $transaction){
			echo $transaction['request']->getMethod();
			//> GET, HEAD
			if($transaction['response']){
				echo $transaction['response']->getStatusCode();
				//> 200, 200
			} elseif($transaction['error']){
				echo $transaction['error'];
				//> exception
			}
			var_dump($transaction['options']);
			//> dumps the request options of the sent request.
		}
		return $container;
	}
	/**
	 * @param MeasurementSet[] $measurementSets
	 * @return int|QMUserVariable[]
	 * @throws NoChangesException
	 * @deprecated Use saveMeasurements
	 */
	protected function saveMeasurementSets(array $measurementSets){
		foreach($measurementSets as $set){
			$set->setClientId($this->name);
			$set->setConnectorId($this->id);
			$set->userId = $this->userId;
			foreach($set->measurementItems as $m){
				$this->populateMeasurement($m);
			}
		}
		try {
			$numberOfNewMeasurements = MeasurementSet::saveMeasurementSets($this->userId, $measurementSets, $this->id);
		} catch (IncompatibleUnitException | InvalidVariableValueException $e) {
			le($e);
		}
		/** @noinspection AdditionOperationOnArraysInspection */
		return $numberOfNewMeasurements;
	}
	/**
	 * @param QMVariable|\App\Models\Variable $variable
	 * @param array $urlParams
	 * @return mixed
	 */
	public function setInstructionsHtml($variable, array $urlParams = []): string{
		$variableName = $variable->getOrSetVariableDisplayName();
		$variableImage = $variable->getVariableAvatarImageHtml(6);
		$sourceName = $this->getDisplayNameAttribute();
		$getItButton = $this->getGetItHereButton();
		$getItButton->setImage($this->getImage());
		$getItButton->setTextAndTitle("Get $this->displayName here");
		$getItPill = $getItButton->getChipSmall();
		$connectButton = $this->getConnectButton();
		$connectButton->setTextAndTitle("import your data here");
		$importPill = $connectButton->getChipSmall();
		$paragraph = "
<div class='tracking-instructions' id='$this->name-tracking-instructions'>
	<h3 class='text-2xl font-extrabold dark:text-white'>
	$variableImage
	Automatic Import of $variableName via $sourceName
	</h3>
	<p class='my-4 text-xl text-gray-500'>
	$getItPill and use it to record your $variableName.  Then, $importPill.
	</p>
</div>";
		return $this->instructionsHtml = $paragraph;
	}
	/**
	 * @param int|null $userId
	 * @return static
	 */
	public static function getByUserId(int $userId = null): ?self{
		$connector = self::getConnectorByNameOrId(static::NAME, $userId);
		if($userId){
			$connector->setUserId($userId);
		}
		return $connector;
	}
	/**
	 * @return QMConnector[]
	 */
	public static function getAll(): array{
		return self::getConnectors();
	}
	/**
	 * @return string
	 */
	public static function getLoginUrl(): string{
		$name = static::NAME;
		$url = config("services.$name.redirect", null);
		if(!$url){
			$url = Env::getAppUrl() . "/api/v1/connectors/$name/connect";
		}
		$finalCallback = FinalCallbackUrl::getIfSet();
		if($finalCallback){
			$url = UrlHelper::addParam($url, FinalCallbackUrl::NAME, $finalCallback);
		}
		return $url;
	}
	/**
	 * @return string
	 */
	public static function getCallbackRedirectUrl(): string{
		$name = static::NAME;
		$url = config("services.$name.redirect", null);
		if(!empty($url)){return $url;}
		if($url = StateParameter::getValueFromStateParam('redirect_uri', $name)){
			return $url;
		}
		$strtoupper = strtoupper($name);
		if($env = Env::get($strtoupper.'_REDIRECT')){return $env;}
		if($env = Env::get($strtoupper.'_CALLBACK')){return $env;}
		$callbackUrl = static::getConnectUrlWithoutParams();
		return $callbackUrl;
	}
	/**
	 * @param array $params
	 * @return QMConnector[]
	 */
	public static function get(array $params = []): array{
		$userId = QMArr::getValueForSnakeOrCamelCaseKey($params, Connection::FIELD_USER_ID);
		$connectors = self::getConnectors($userId);
		return QMArr::filter($connectors, $params);
	}
	/**
	 * @return static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function instance(){
		try {
			return self::getByUserId(QMAuth::id());
		} catch (UnauthorizedException $e) {
			return new static();
		}
	}
	/**
	 * @return bool
	 */
	public function isDisabled(): bool{
		return !$this->enabled || $this->temporarilyDisabled();
	}
	/**
	 * @param string|null $userMessage
	 * @param array $meta
	 * @return Connection
	 */
	public function createConnection(string $userMessage = null, array $meta = []): Connection{
		$connection = new Connection();
		$connection->connector_id = $this->getId();
		$connection->user_id = $this->getUserId();
		$this->connection = $connection;
		$connection->connect($userMessage, $meta);
		$user = $this->getUser();
		$connections = $user->connections;
		$connections->push($connection);
		$u = $this->getQmUser();
		$u->setQMConnectors();
		return $connection;
	}
	public function addExtendPropertiesForRequest(): void{
		$this->getConnectInstructions();
		$this->setDefaultButtons(); // For Amazon Connector
	}
	/**
	 * @param bool $log
	 * @return array
	 */
	private function setNewMeasurementsByVariableName(bool $log): array{
		$this->newMeasurements = [];
		$variables = $this->getQMUserVariables();
		foreach($variables as $v){
			$combined = $v->getCombinedNewQMMeasurements(true);
			if($log){
				$v->logInfo(count($combined) . " combined new measurements");
			}
			if($log){
				foreach($combined as $m){
					$m->logInfo("");
				}
			}
			$this->newMeasurements[$v->name] = $combined;
		}
		return $this->newMeasurements;
	}
	/**
	 * @return string
	 */
	public function getAbsoluteFromAt(): string{
		return db_date("2010-01-01");
	}
	/**
	 * @param $fromTime
	 * @param QMUserVariable $v
	 * @return int|null
	 */
	protected function getFromTimeForVariable($fromTime, QMUserVariable $v): ?int{
		$latestForVariableAt = $v->getLatestNonTaggedMeasurementStartAt();
		$fromAt = db_date($fromTime);
		$fromTime = time_or_exception($fromTime);
		$minSeconds = $v->getMinimumAllowedSecondsBetweenMeasurements();
		if(strtotime($latestForVariableAt) >= $fromTime){
			$v->logInfo("Using latestForVariableAt $latestForVariableAt plus $minSeconds seconds as fromTime instead of " .
				"the connector provided fromTime $fromAt. ");
		}
		if(strtotime($latestForVariableAt) > $fromTime){
			$fromTime = strtotime($latestForVariableAt) + $minSeconds;
		}
		return $fromTime;
	}
	/**
	 * @return bool
	 */
	protected function haveMeasurementsForAllVariables(): bool{
		foreach($this->getVariableNames() as $name){
			if(!isset($this->qmUserVariables[$name])){
				return false;
			}
			$v = $this->getQMUserVariable($name);
			if(!$v->getNewMeasurements()){
				return false;
			}
		}
		return true;
	}
	public function detectRecursion(){
		if(isset($this->connector)){
			le("Recursion!");
		}
	}
	/**
	 * @return string
	 */
	public static function getPathToHardCodedConstantFiles(): string{
		$path = FileHelper::absPath("app/DataSources/Connectors");
		return $path;
	}
	public function logVariables(){
		$variables = $this->getQMUserVariables();
		QMLog::table($variables, "Variables for $this");
	}
	public function logMeasurements(){
		$this->getConnectionIfExists()->logMeasurementsTable();
	}
	/**
	 * @return QMTokenStorage
	 */
	public function getTokenStorage(): QMTokenStorage{
		$key = __FUNCTION__ . $this->name . '-' . $this->userId;
		$s = Memory::get($key, Memory::MISCELLANEOUS);
		if(!$s){
			$s = new QMTokenStorage($this);
			Memory::set($key, $s);
		}
		return $s;
	}
	/**
	 * @param QMMeasurement $m
	 * @throws InvalidAttributeException
	 * @throws InvalidVariableValueAttributeException
	 */
	protected function validateMeasurement(QMMeasurement $m): void{
		$this->validateMeasurementTime($m, $m->getQMUserVariable());
        $m->validate();
	}
	public function getGlobalUrlParams(): array{
		return [];
	}
	/**
	 * @return string
	 */
	public function getFromDate(): string{
		return date('Y-m-d', $this->getFromTime());
	}
	public function getImportRange(): string{ return "{$this->getFromDate()} to {$this->getEndDate()}"; }
	public static function exportClientIdsAndSecretsForService(){
		$connectors = OAuthConnector::getConnectors();
		$envs = "";
		foreach($connectors as $connector){
			if(!method_exists($connector, 'getClientSecret')){
				continue;
			}
			/** @var OAuth2Connector $connector */
			$clientId = $connector->getConnectorClientId();
			if(empty($clientId)){
				continue;
			}
			$secret = $connector->getOrSetConnectorClientSecret();
			$name = $connector->getNameAttribute();
			$upper = QMStr::toScreamingSnakeCase($name);
			$uri = static::getCallbackRedirectUrl();
			ConsoleLog::info("
    '$name' => [
        'client_id' => Env::get('CONNECTOR_$upper" . "_CLIENT_ID'),
        'client_secret' => Env::get('CONNECTOR_$upper" . "_CLIENT_SECRET'),
    ]");
			Env::addToAllEnvs("CONNECTOR_$upper" . "_CLIENT_ID", $clientId);
			Env::addToAllEnvs("CONNECTOR_$upper" . "_CLIENT_SECRET", $secret);
		}
		ConsoleLog::info($envs);
	}
	public static function getClientSecrets(): array{
		$secrets = [];
		foreach($_ENV as $key => $value){
			if(!empty($value) && str_contains($key, 'SECRET')){
				$secrets[$key] = $value;
			}
		}
		return $secrets;
	}
	/**
	 * @return BaseModel|Connector
	 */
	public function l(): Connector{
		return $this->attachedOrNewLaravelModel();
	}
	protected function validateCredentials(): void{
		// Add to child models
	}
	public function getMeasurementsByDate(): array{
		$byDate = $this->getOrCreateConnection()->getMeasurementsByDate();
		return $byDate;
	}
	/**
	 * @return QMButton[]
	 */
	public function getButtons(): array{
		if(!$this->buttons){
			$this->setDefaultButtons();
		}
		return $this->buttons;
	}
	protected function analyzeVariables(){
		$variables = $this->getQMUserVariables();
		foreach($variables as $uv){
			try {
				$uv->analyzeFully(__METHOD__);
			} catch (AlreadyAnalyzedException | AlreadyAnalyzingException | ModelValidationException $e) {
				le($e);
			}
		}
	}
	/**
	 * @param string|null $variableCategoryName
	 * @param array $params
	 * @param string|null $defaultUnitName
	 * @return array
	 */
	private function getNewVariableParams(?string $variableCategoryName, array $params,
		?string $defaultUnitName): array{
		if($category = $variableCategoryName ?: $this->defaultVariableCategoryName){
			$params['variableCategoryName'] = $category;
		}
		if(!isset($params[Variable::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS])){
			$params[Variable::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS] = 86400;
		}
		if(!isset($params[Variable::FIELD_IS_PUBLIC])){
			$params[Variable::FIELD_IS_PUBLIC] = $this->newVariablesShouldBePublic;
		}
		if($defaultUnitName){
			$params['unitName'] = $defaultUnitName;
		}
		return $params;
	}
	protected function getBaseApiUrl(): string{
		if(!static::$BASE_API_URL){
			le("Please add static::\$BASE_API_URL to " . static::class);
		}
		return static::$BASE_API_URL;
	}
	/**
	 * @return Collection|Connection[]
	 */
	public static function getConnections(): Collection{
		return Connection::whereConnectorId(static::ID)->get();
	}
	/**
	 * @return static[]
	 */
	public static function getQMConnectors(): array{
		$connections = static::getConnections();
		$connectors = [];
		foreach($connections as $c){
			$connectors[$c->getUserLoginName()] = $c->getQMConnector();
		}
		return $connectors;
	}
	/**
	 * @return array
	 */
	public function getUserMeta(): array{
		if($this->connectorUserMeta){
			return $this->connectorUserMeta;
		}
		$byKey = [];
		$u = $this->getUser();
		if($u->relationLoaded('wp_usermeta')){
			$userMeta = $u->wp_usermeta->filter(function(WpUsermetum $um){
				return stripos($um->meta_key, $this->name . '_') !== false;
			})->all();
		} else {
			$userMeta = WpUsermetum::whereUserId($this->getUserId())
				->where(WpUsermetum::FIELD_META_KEY, Writable::like(), $this->name . '_%')
                ->get();
		}
		foreach($userMeta as $one){
			$byKey[$one->meta_key] = $one->meta_value;
		}
		return $this->connectorUserMeta = $byKey;
	}
	/**
	 * @param string $key
	 * @return string|int|object|null
	 */
	public function getUserMetaValue(string $key){
		return QMArr::getValueForSnakeOrCamelCaseKey($this->getUserMeta(), $this->toUserMetaKey($key));
	}
	/**
	 * @param array $profile
	 */
	protected function updateUserMeta(array $profile){
		$username = BaseUserLoginProperty::pluck($profile);
		if($username){
			$profile['username'] = $username;
		}
		$id = UserIdProperty::pluck($profile);
		if($id){
			$profile[self::CONNECTOR_USER_ID_META_SUFFIX] = $id;
		}
		$connection = $this->getConnection();
		$u = $this->getUser();
		$all = $u->getUserIndexedByKeyMeta();
		foreach($profile as $key => $value){
			if(str_contains($key, '*')){continue;}
			if($value === null){continue;}
			$snake = Str::snake($key);
			$connection->addMeta($key, $value);
			if(User::hasColumn($snake) && !SecretHelper::containsSecretyString($snake)){
				if($u->$snake === null){
					$this->logInfo("Updating user $snake to $value from connector profile for " . $this->getUserId());
					$u->$snake = $value;
					try {
						$u->save();
					} catch (ModelValidationException $e) {
						le($e);
					}
				}
			}
		}
		if(isset($profile['id'])){$connection->connector_user_id = $profile['id'];}
		if(isset($profile['email'])){$connection->connector_user_email = $profile['email'];}
		$connection->save();
	}
	/**
	 * @param string $path
	 * @param array $params
	 * @param array $extraHeaders
	 * @param array $options
	 * @return array|object|string
	 * @throws ConnectorException
	 */
	public function getRequest(string $path, array $params = [], array $extraHeaders = [], array $options = []){
		// if($response = $this->getLocalDataIfPossible($url)){return $response;} TODO: uncomment after all tests passing again
		if(isset($extraHeaders['Referer'])){
			// Get some cookies for API auth
			$html = $this->request($extraHeaders['Referer'], "GET", $extraHeaders);
		}
		$this->sleepIfNotApi();
		if($params){
			$path = UrlHelper::addParams($path, $params);
		}
        $str = $this->request($path, 'GET', $params, $extraHeaders);
		return QMStr::jsonDecodeIfNecessary($str);
	}
	/**
	 * @param $path
	 * @param string $method
	 * @param null $body
	 * @param array $extraHeaders
	 * @return string
	 * @throws ConnectorException
	 * @noinspection PhpMissingParamTypeInspection
	 * must be compatible with OAuth\Common\Service\ServiceInterface::request($path, $method = 'GET', $body = NULL,
	 *     array $extraHeaders = Array)
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function request($path, $method = 'GET', $body = null, array $extraHeaders = []){
		$uri = $this->determineRequestUriFromPath($path, new Uri($this->getBaseApiUrl()));
		$c = $this->getHttpClient();
		$content = $c->retrieveResponse($uri, $body, $extraHeaders, $method);
		return $content;
	}
	/**
	 * @param $parameters
	 * @return mixed
	 * @throws \App\Slim\Controller\Connector\ConnectException
	 */
	abstract public function connect($parameters);
	protected static function getMemoryPrimaryKey(): string{ return QMConnector::getShortClassName(); }
	public static function getAllFromMemoryIndexedByUuidAndId(): array{
		$mem = parent::getAllFromMemoryIndexedByUuidAndId();
		if(!$mem){
			$all = self::getAnonymousConnectors();
			foreach($all as $one){
				$one->addToMemory();
			}
		}
		return parent::getAllFromMemoryIndexedByUuidAndId();
	}
	public function save(): bool{
		return $this->getConnection()->save();
	}
	/**
	 * @return void
	 */
	protected function handleNoNewMeasurements(): void{
		$this->logInfo($this->getNoNewMeasurementsMessage());
		$this->getConnection()->user_message = $this->getNoNewMeasurementsMessage();
		try {
			$this->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
	}
	protected function getNoNewMeasurementsMessage(): string{
		return "No new measurements between " . $this->getFromDate() . " and " . $this->getEndDate();
	}
	/**
	 * @param string $url
	 * @param string $method
	 * @param array $headers
	 * @return ConnectorRequest
	 */
	private function newConnectorRequest(string $url, string $method, array $headers): ConnectorRequest{
		$cr = new ConnectorRequest();
		if($this->userId){
			$cr->user_id =
				$this->userId; // Don't use getUserId because it causes infinite loop when logging in with connectors.  Save this later after user is gotten
		}
		$cr->connector_id = $this->getId();
		$connection = $this->getConnectionIfExists();
		if($connection){
			$cr->connection_id = $connection->getId();
		}
		$cr->connector_import_id = $this->getConnectorImport()->id;
		$cr->uri = $url;
		$cr->method = $method;
		$cr->request_headers = $headers;
		return $cr;
	}
	/**
	 * @param string|int|CarbonInterface $currentFromTime
	 */
	protected function setCurrentFromTime($currentFromTime): void{
		$this->currentFromTime = TimeHelper::universalConversionToUnixTimestamp($currentFromTime);
	}
	/**
	 * @param string $roundedStartAt
	 * @return bool
	 */
	protected function earlierThanLatestMeasurementFromPreviousImport(string $roundedStartAt): bool{
		$latestForConnectorAt = $this->getConnectionIfExists()->getLatestMeasurementAtAttribute();
		if(strtotime($roundedStartAt) < strtotime($latestForConnectorAt)){
			$this->logError(__FUNCTION__,[], 
			                "roundedStartAt $roundedStartAt <= latestForConnector $latestForConnectorAt");
			return true;
		}
		return false;
	}

    /**
     * @param string $actualStartAt
     * @return bool
     */
	protected function assertNotTooEarly(string $actualStartAt): bool{
		$fromTime = $this->getFromTime() - 2 * 86400;
        $fromDate = TimeHelper::YYYYmmddd($fromTime);
        $actualStartDate = TimeHelper::YYYYmmddd($actualStartAt);
		if($actualStartDate < $fromDate){
            $this->logErrorIfNotTesting("Measurement too early", [],  
                                        "Measurement date $actualStartAt is earlier than connector fromAt $fromDate");
			//throw new TooEarlyException("actualStartAt $actualStartAt < connectionFromAt $fromAt for $this");
		}
		return false;
	}
	/**
	 * @param string $actualStartAt
	 * @param \App\Variables\QMUserVariable $uv
	 * @return string
	 * @throws \App\DataSources\TooEarlyException
	 */
	protected function roundStartAt(string $actualStartAt, QMUserVariable $uv): string{
		$roundedStartAt = $uv->getRoundedStartAt($actualStartAt);
		$this->assertNotTooEarly($actualStartAt);
		$this->earlierThanLatestMeasurementFromPreviousImport($roundedStartAt);
		return $roundedStartAt;
	}
	/**
	 * @return ConnectorImport
	 */
	public function getConnectorImport(): ConnectorImport{
		if(isset($this->connectorImport)){
			return $this->connectorImport;
		}
		$i = new ConnectorImport();
		$i->user_id = $this->getUserId();
		$i->connector_id = $this->getId();
		$i->connection_id = $this->getOrCreateConnection()->getId();
		$i->import_started_at = $this->getOrCreateConnection()->import_started_at ?? now_at();
		try {
			$i->save();
		} catch (ModelValidationException $e) {
			if(AppMode::isApiRequest()){
				QMLog::error("Could not save connector import for $this because " . $e->getMessage());
			} else {
				le($e);
			}
		}
		return $this->connectorImport = $i;
	}
	/**
	 * @param $key
	 * @return string
	 */
	private function toUserMetaKey($key): string{
		return $this->name . "_" . $key;
	}
	/**
	 * @return int
	 */
	protected function getEndTime(): int {
		return $this->endTime ?? time() - 86400;
	}
	/**
	 * @return static
	 */
	public static function mikepsinn(): self {
		$mike = User::mike();
		$connectorId = static::ID;
		if(!$connectorId){
			le("No connector id for " . static::class);
		} else {
			$connection = $mike->findConnectionByConnectorId($connectorId);
			return $connection->getQMConnector();
		}
	}
	public function hasInputFields(): bool{
		return $this instanceof LocationBasedConnector || $this instanceof PasswordConnector;
	}
	protected function logConnectUrl(){
		$this->logDebug("Connect URL: " . $this->getConnectUrlWithParams());
	}
	/**
	 * @return string
	 */
	public static function getConnectUrlWithoutParams(): string{
		$appUrl = Env::getAppUrl();
		return $appUrl."/api/v1/connectors/".static::NAME."/connect";
	}
	public function getUpdateButton(): ConnectorUpdateButton {
		return new ConnectorUpdateButton($this);
	}
	public function getConnectButton(): ImportConnectorButton {
		return new ImportConnectorButton($this);
	}
	/**
	 * @param $nameOrId
	 * @return static|null
	 */
	public static function findByNameIdOrSynonym($nameOrId){
		try {
			return self::getDataSourceByNameOrIdOrSynonym($nameOrId, QMAuth::id(false));
		} catch (UnauthorizedException $e) {
			le($e);
		}
	}
}
