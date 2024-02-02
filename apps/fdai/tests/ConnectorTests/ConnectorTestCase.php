<?php
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests;
use App\DataSources\Connectors\GoogleCalendarConnector;
use App\DataSources\Connectors\MoodscopeConnector;
use App\DataSources\Connectors\TigerViewConnector;
use App\DataSources\OAuthConnector;
use App\DataSources\QMConnector;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\ExceptionHandler;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\Connection;
use App\Models\ConnectorImport;
use App\Models\ConnectorRequest;
use App\Models\Measurement;
use App\Models\User;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Connection\ConnectionConnectStatusProperty;
use App\Properties\Connection\ConnectionUpdateStatusProperty;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\QMMeasurementExtended;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\Measurement\GetMeasurementRequest;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Utils\AppMode;
use App\Utils\Env;
use App\VariableCategories\EnvironmentVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use Exception;
use Illuminate\Support\Arr;
use LogicException;
use OAuth\Common\Token\AbstractToken;
use OAuth\OAuth2\Token\StdOAuth2Token;
use ReflectionClass;
use Tests\SlimTests\SlimTestCase;
/**
 * Class ConnectorTestCase
 * @package Tests\Api\Connectors
 */
abstract class ConnectorTestCase extends \Tests\SlimTests\SlimTestCase {
	protected $connector;
	protected $connectorName;
	protected $credentials = null;
	protected $fromTime;
	protected $userId = 1;
	protected $variablesToCheck;
	protected $allowGapsInData = false;
	protected $requireMeasurementLocation = false;
	protected $requireDuration = false;
	protected $requireNote = false;
	protected $connectionBeforeFlush;
	protected $latestConnectionBeforeDelete;
	protected $originalLastMeasurement;
	protected $newLastImportedAt;
	protected $newByVariable;
	protected $deletedByVariable;
	protected $latestConnectionAfterDelete;
	protected array $connectorsWhereMeasurementsMayNotMatchFromTimes = [
		TigerViewConnector::NAME,
		MoodscopeConnector::NAME,
	];
	/**
	 * @param string|int $fromTime
	 */
	public function setFromTime($fromTimeAt): void{
		$this->fromTime = TimeHelper::universalConversionToUnixTimestamp($fromTimeAt);
	}
	/**
	 * @return void
	 */
	protected function connectorSpecificPreImportPostConnectionTasks(): void{
		$this->logInfo("You can update the connection prior to import in this method if necessary: " . __METHOD__);
	}
	protected function setUp(): void{
		parent::setUp();
		Measurement::deleteAll();
		UserVariable::deleteAll();
		ConnectorRequest::deleteAll();
		ConnectorImport::deleteAll();
		Connection::deleteAll();
		Variable::query()
			->whereNotNull(Variable::FIELD_DATA_SOURCES_COUNT)
			->update([Variable::FIELD_DATA_SOURCES_COUNT => NULL]);
		$this->assertEquals(0, UserVariable::count());
	}
	/**
	 * @return QMMeasurement
	 */
	public function getOriginalLastMeasurement(): QMMeasurement{
		return $this->originalLastMeasurement;
	}
	protected function disconnectAndReconnect(): void{
		$this->disconnect();
		$this->connect();
		Memory::flush();
		$connector = QMUser::find($this->getUserId())->getConnectorByName($this->getConnectorName());
		$this->assertStringNotContainsString("disconnected", $connector->message);
	}
	/**
	 * @return QMConnector
	 */
	public function getQMConnector(): QMConnector{
		return QMConnector::getConnectorByNameOrId($this->getConnectorName(), $this->getUserId());
	}
	/**
	 * @return array
	 */
	protected function getTestCredentials(): array{
		if($this->credentials){
			return $this->credentials;
		}
		return['connectorCredentials' => $this->getQMConnector()->getCredentialStorageFromMemory()->get()];
	}
	/**
	 * @return int
	 */
	public function getUserId(): ?int{
		return $this->userId;
	}
	protected function connect(): void{
		$this->setAuthenticatedUser(1);
		$user = QMUser::find($this->getUserId());
		$user->updateQMClientId(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
		BaseClientIdProperty::setInMemory(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
		$connector = QMConnector::getConnectorByNameOrId($this->getConnectorName(), $this->getUserId());
		AppMode::setIsApiRequest(true);
		$connector->connect($this->getTestCredentials());
		AppMode::setIsApiRequest(false);
		$this->assertGreaterThan(0, Connection::count());
		$fromDatabase = $connector->getConnection();
		$this->assertNotNull($fromDatabase, 'Connection failed');
		$this->assertEquals(ConnectionUpdateStatusProperty::IMPORT_STATUS_WAITING, $fromDatabase->update_status);
		$credentialStorage = $connector->getCredentialStorageFromMemory();
		$this->assertTrue((bool)$credentialStorage->hasCredentials(), "Credential doesn't exist");
	}
	/**
	 * @return array
	 */
	protected function makeSureConnectionWasUpdatedAfterImport(): array{
		$connection = $this->getConnectionModel();
		$this->assertGreaterThan(0, Connection::count());
		$lConnection = Connection::find($connection->id);
		$carbon = now();
		$hourAgo = $carbon->subHour();
		if(!$lConnection->import_ended_at){
			le("No import ended at!");
		}
		$this->assertGreaterThan($hourAgo->toDateTime(), $lConnection->import_ended_at->toDateTime());
		$this->assertGreaterThan($hourAgo->toDateTime(), $lConnection->import_started_at->toDateTime());
		$connector = $connection->getQMConnector();
		$message = $connector->message;
		if($e = $connection->getException()){
			throw $e;
		}
		$this->assertNull($lConnection->internal_error_message);
		try {
			$this->assertContains('seconds', $message);
		} catch (\Throwable $e) {
			throw new LogicException("internal_error_message should not be null when " . $e->getMessage());
		}
		$measurementsByVariable = $connection->getOrSetNewMeasurementsByVariableName();
		if(!$measurementsByVariable){
			le("No measurements by variable!".Measurement::indexUrl());
		}
		$this->assertGreaterThan(0, count($measurementsByVariable));
		$this->checkLatestMeasurement($connection);
		$this->checkPostImportStatus($connection);
		return $measurementsByVariable;
	}
	/**
	 * @param array $parameters
	 */
	protected function importAndCheckVariables(array $parameters = []){
		if(isset($parameters['variables'])){
			$this->variablesToCheck = $parameters['variables'];
		}
		QMCommonVariable::setGetAmazonProductForNewVariables(true);
		$this->makeSureConnectionHasNoMeasurementsYet();
		$connection = $this->getConnectionModel();
		$connection->getQMConnector()->setFromDate($this->getFromTime($parameters));
		$connection->import(__METHOD__);
		$this->makeSureConnectionWasUpdatedAfterImport();
		if(!Env::get('DEBUG_CONNECT')){
			$this->checkVariablesAndMeasurements();
		}
		$this->assertNull(Connection::getOldestWaitingStaleOrStuckConnection(),
			"Should not have any OldestWaitingStaleOrStuckConnection");
		$this->assertGreaterThan(0, Connection::count());
		// TODO: maybe fix this $this->deleteMeasurementsAnalyzeAndImportAgain($measurementsByVariable);
	}
	public function disconnect(): void{
		$connector = QMConnector::getConnectorByNameOrId($this->getConnectorName(), $this->getUserId());
		$connection = $connector->getConnectionIfExists();
		$connection->disconnect("we're testing");
		$data = Connection::find($connection->getId());
		$this->assertNotNull($data,
			"We should still have a connection even after it's disconnected! " . Connection::getAstralIndexPath());
		$this->assertEquals(ConnectionConnectStatusProperty::CONNECT_STATUS_DISCONNECTED, $data->connect_status);
		$this->assertDisconnected();
	}
	/**
	 * Creates credentials for connectors using OAuth
	 * @param string $key
	 * @param string $token binary token retrieved from the DB
	 */
	protected function createCredentials(string $key, string $token){
		if($token){
			$connector = QMConnector::getConnectorByNameOrId($this->getConnectorName(), $this->getUserId());
			$db = Writable::db();
			$db->table('credentials')->insert([
				'user_id' => $this->getUserId(),
				'connector_id' => $connector->id,
				'attr_key' => $key,
				'attr_value' => base64_decode($token),
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			]);
		}
	}
	protected function connectImportDisconnect(){
		if($this->weShouldSkip()){
			return;
		}
		$this->connect();
		$this->importAndCheckVariables();
		$this->disconnectAndReconnect();
	}
	/**
	 * @param array $parameters
	 */
	protected function connectImportCheckDisconnect(array $parameters = []){
		if($this->weShouldSkip()){
			return;
		}
		$this->setUserAndClientId();
		$this->assertFalse(AppMode::isApiRequest());
		$this->createConnection();
		$this->connectorSpecificPreImportPostConnectionTasks();
		$this->importAndCheckVariables($parameters);
		$this->checkConnectorsPostImport();
		$this->disconnectAndReconnect();
	}
	/**
	 * @return string
	 */
	protected function getConnectorName(): string{
		if($this->connectorName){
			return $this->connectorName;
		}
		$r = new ReflectionClass($this);
		$class = $r->getShortName();
		$name = str_replace([
			'Test',
			'Connector',
		], '', $class);
		$name = strtolower($name);
		return $name;
	}
	/**
	 * @return void
	 */
	public function checkConnectorLogin(): void{
		if($this->weShouldSkip()){return;}
		$token = $this->getTestToken();
		$connector = $this->getQMConnector();
		$connector->logInfo("token getEndOfLife: " . TimeHelper::timeSinceHumanString($token->getEndOfLife()));
		$connector->logInfo("token getExtraParams: " .
			QMStr::before('id_token', QMLog::var_export($token->getExtraParams(), true)) . '...');
		$firstUser = $this->connectAndGetUser();
		$secondUser = $this->connectAndGetUser();
		$this->assertEquals($firstUser->id, $secondUser->id, "The first time we logged in this " . "user, the id was $firstUser->id but the second time we got $secondUser->id.
            urlUserDetails: " . $connector->urlUserDetails() . "
            " . User::getDataLabIndexUrl());
	}
	/**
	 * @param null $credentials
	 * @return QMUser
	 */
	protected function connectAndGetUser($credentials = null): QMUser{
		$this->setAuthenticatedUser(null);
		BaseClientIdProperty::setInMemory(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
		$c = OAuthConnector::getConnectorByNameOrId($this->getConnectorName(), $this->getUserId());
		if(!$credentials){
			$credentials = $c->getCredentialStorageFromMemory()->getTestCredentials();
		}
		try {
			$c->connect(['connectorCredentials' => $credentials]);
		} catch (Exception $e) {
			if(stripos($e->getMessage(), 'invalid') !== false){
				$c->getCredentialStorageFromMemory()->deleteTestCredentials();
			}
			ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
		}
		$profile = $c->getConnectorUserProfile();
		$c->logInfo("getConnectorUserProfile from " . $c->urlUserDetails() . " returned " . \App\Logging\QMLog::print_r($profile, true));
		$this->assertNotNull($profile);
		$user = $c->getQmUser();
		$this->assertNotNull($user);
		return $user;
	}
	protected function checkVariablesAndMeasurements(){
		$measurementsWithConnectorId = Measurement::whereNotNull(Measurement::FIELD_CONNECTOR_ID)->count();
		$this->assertEquals(Measurement::count(), $measurementsWithConnectorId,
			"All measurements should have a connector id");
		$this->assertGreaterThan(0, $measurementsWithConnectorId);
		$connector = $this->getQMConnector();
		$variableNames = $this->variablesToCheck ?? $connector->getVariableNames();
		$variablesWithMeasurements = $this->getVariableNamesWithMeasurements();
		$variablesNotImported = array_diff($variableNames, $variablesWithMeasurements);
		if($variablesNotImported){
			$this->fail("The following variables were not imported: \n\t - " . 
			            implode("\n\t - ", $variablesNotImported).
			            "\n\nWe have measurements for the following variables: \n\t" .
			            QMLog::var_export($variablesWithMeasurements));
		}
		foreach($variableNames as $name){
			$cv = QMCommonVariable::findByNameIdOrSynonym($name);
			if(!$cv){
				$cv = QMCommonVariable::findByNameIdOrSynonym($name);
				le("Variable $name not found! Variables with measurements are: " .
				   QMLog::var_export($this->getVariableNamesWithMeasurements(), true));
			}
			if($cv->numberOfMeasurements){
				$this->assertDataSourcesCount(1, $cv);
			}
			$this->checkMeasurements($cv->getVariableIdAttribute(), $name);
			$this->checkUserVariable($name);
			$this->checkCommonVariableForConnector($name);
		}
	}
	public function getVariableNamesWithMeasurements(): array{
		$uvs = UserVariable::query()
           ->where(UserVariable::FIELD_NUMBER_OF_MEASUREMENTS, ">", 0)
	        ->pluck('id')
	        ->all();
		$names = [];
		foreach($uvs as $uv){
			$uv = UserVariable::findInMemoryOrDB($uv);
			$names[] = $uv->getVariableName();
		}
		return $names;
	}
	protected function checkConnectorsPostImport(){
		$connectors = QMConnector::getForRequest();
		foreach($connectors as $c){
			if($c->name === $this->getConnectorName()){
				$this->assertContains(' seconds ', $c->message);
				$this->assertEquals(true, $c->connected);
				if($c->numberOfMeasurements){
					$this->assertCount(3, $c->buttons);
				} else{
					$this->assertCount(2, $c->buttons);
				}
				$this->assertEquals(ConnectionConnectStatusProperty::CONNECT_STATUS_CONNECTED, $c->connectStatus,
					$c->connectError ?: "");
				$this->assertEquals(ConnectionUpdateStatusProperty::IMPORT_STATUS_UPDATED, $c->updateStatus,
					$c->updateError ?: "");
			}
		}
	}
	/**
	 * @param int $variableId
	 * @param string $variableName
	 */
	protected function checkMeasurements(int $variableId, string $variableName){
		$c = $this->getQMConnector();
		$numberWithConnectorId = Measurement::whereConnectorId($c->getId())->count();
		$this->assertGreaterThan(0, $numberWithConnectorId);
		$numberWithSourceName = Measurement::whereSourceName($c->getTitleAttribute())->count();
		$this->assertGreaterThan(0, $numberWithSourceName, "No measurements by source name!");
		$measurements = $this->getAllMeasurementsForVariable($variableId);
		$numberOfMeasurements = count($measurements);
		if(!$numberOfMeasurements){$c->logNewMeasurementsToTable();}
		$this->assertGreaterThan(0, $numberOfMeasurements, "No $variableName measurements!");
		foreach($measurements as $m){
			$this->assertNotNull($m->getImage(), "We should have a connector, face, or other image");
			if(str_contains($m->pngPath, "img/https")){le("pngPath is $m->pngPath");}
			$this->assertMeasurementMatchesConnector($c, $m);
			$this->assertGreaterThan(db_date(time() - 3600), $m->getCreatedAt(), "Created at is " . $m->getCreatedAt());
			if($this->requireNote){
				$this->checkMeasurementNote($m);
			}
			if($this->requireMeasurementLocation){
				$this->assertMeasurementLocationNotNull($m);
			}
			if($this->requireDuration){
				$this->assertNotNull($m->duration);
			}
		}
	}
	/**
	 * @param string $variableName
	 */
	protected function checkCommonVariableForConnector(string $variableName){
		$connector = $this->getQMConnector();
		$userVariables = UserVariable::where(UserVariable::FIELD_MOST_COMMON_CONNECTOR_ID, "<>", $connector->id)->get();
		if(!$userVariables->count()){
			ConsoleLog::info("No user variables where MOST_COMMON_CONNECTOR_ID <> $connector->id");
		}
		/** @var UserVariable[] $userVariables */
		foreach($userVariables as $uv){
			$actual = QMConnector::find($uv->most_common_connector_id);
			$uv->logError("Common Variable MOST_COMMON_CONNECTOR_ID is $actual->id $actual->name but should be $connector->name $connector->id");
		}
		$variables = Variable::where(Variable::FIELD_MOST_COMMON_CONNECTOR_ID, "<>", $connector->id)->get();
		if(!$variables->count()){
			ConsoleLog::info("No variables where MOST_COMMON_CONNECTOR_ID <> $connector->id");
		}
		/** @var Variable[] $variables */
		foreach($variables as $v){
			if(Measurement::whereVariableId($v->getId())->count()){
				$actual = QMConnector::find($v->most_common_connector_id);
				$v->logError("Common Variable MOST_COMMON_CONNECTOR_ID is $actual->id $actual->name but should be $connector->name $connector->id");
			}
		}
		$measurements = Measurement::where(Measurement::FIELD_CONNECTOR_ID, "<>", $connector->id)->get();
		if(!$measurements->count()){
			ConsoleLog::info("No Measurements where COMMON_CONNECTOR_ID <> $connector->id");
		}
		/** @var Measurement[] $measurements */
		foreach($measurements as $m){
			$actual = QMConnector::find($m->connection_id);
			$m->logError("connection_id is $actual->id $actual->name but should be $connector->name $connector->id");
		}
		$variable = QMCommonVariable::findByNameIdOrSynonym($variableName);
		$needToAnalyze = $variable->needToAnalyze();
		if(!$needToAnalyze){
			$variable->needToAnalyze();
		}
		$this->assertTrue($needToAnalyze,
			"We should have added measurements so we should need to analyze: 
		". $variable->name."
		".$variable->getUrl()."
		Last Analyzed: ".$variable->getTimeSinceLastAnalyzedHumanString());
		try {
			$variable->analyzeFullyIfNecessary(__FUNCTION__);
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			$id = $variable->l()->best_effect_variable_id;
			$variable->logError("best_effect_variable_id is $id but should be $variable->id");
			$best = Variable::find($id);
			$variable->logError("best_effect_variable is $best");
			$variable->analyzeFullyIfNecessary(__FUNCTION__);
		}
		if($variable->mostCommonConnectorId !== $connector->id){
			$variable->analyzeFully(__FUNCTION__);
		}
		$actualConnector = QMConnector::find($variable->mostCommonConnectorId);
		$this->assertGreaterThan(0, Measurement::whereVariableId($variable->id)->count(),
			"We should have measurements for $variableName");
		$this->assertEquals($connector->id, $variable->mostCommonConnectorId, "$variableName should have mostCommonConnectorId $connector->id ($connector)
            but is $actualConnector->id $actualConnector
            " . Measurement::getDataLabIndexUrl() . "\n" . $variable->getUrl());
		$this->checkEnvironmentVariables($variableName, $variable);
	}
	/**
	 * @param string $variableName
	 * @throws AlreadyAnalyzingException
	 */
	protected function checkUserVariable(string $variableName){
		$c = $this->getQMConnector();
		$v = QMUserVariable::findUserVariableByNameIdOrSynonym(1, $variableName);
		//$v->analyzeFully(__FUNCTION__);
		$this->assertEquals($c->id, $v->mostCommonConnectorId,
			"$v->name mostCommonConnectorId should be $c->id but is actually $v->mostCommonConnectorId");
		$dataSourceNames = $v->getDataSourceNames();
		$this->assertContains($c->displayName, $dataSourceNames);
		//$this->assertEquals(0, $v->calculateNumberOfTrackingReminders()); // TODO:  Uncomment
		if($v->isTreatment()){
			$this->assertEquals(TreatmentsVariableCategory::MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS,
				$v->getMinimumAllowedSecondsBetweenMeasurements());
		}
		if($v->manualTracking !== true){
			$this->assertContains("Import", $v->getTrackingInstructionsHtml());
		}
	}
	/**
	 * @param $parameters
	 * @return int|null
	 */
	protected function getFromTime(array $parameters): ?int{
		if($this->fromTime){
			return $this->fromTime;
		}
		if(isset($parameters['fromTime'])){
			$fromTime = $parameters['fromTime'];
		} else{
			$this->logInfo("Setting fromTime to 3 months back since it wasn't provided");
			$fromTime = time() - 3 * 30 * 86400;
		}
		return $this->fromTime = $fromTime;
	}
	/**
	 * @param string $variableName
	 * @param QMCommonVariable $variable
	 */
	protected function checkEnvironmentVariables(string $variableName, QMCommonVariable $variable): void{
		if(QMStr::isCaseInsensitiveMatch("UV Index", $variableName)){
			$this->assertEquals("UV Index", $variable->name, $variableName);
			$this->assertEquals(BaseCombinationOperationProperty::COMBINATION_MEAN,
				$variable->getOrSetCombinationOperation(), $variableName);
			$this->assertEquals(EnvironmentVariableCategory::NAME, $variable->getVariableCategoryName(), $variableName);
			$this->assertTrue($variable->getIsPublic(), $variableName);
		}
		if(stripos($variableName, "Pollution")){
			$this->assertEquals(BaseCombinationOperationProperty::COMBINATION_MEAN,
				$variable->getOrSetCombinationOperation(), $variableName);
			$this->assertEquals(EnvironmentVariableCategory::NAME, $variable->getVariableCategoryName(), $variableName);
			$this->assertTrue($variable->getIsPublic(), $variableName);
		}
	}
	/**
	 * @param Connection $connection
	 */
	protected function checkLatestMeasurement(Connection $connection){
		$latest = $connection->getLatestMeasurementAtAttribute();
		$this->assertGreaterThan(0, $latest, "Latest Measurement Time should be greater than 0");
		$time = QMMeasurement::readonly()->where(Measurement::FIELD_CONNECTOR_ID, $connection->getConnectorId())
			->max(Measurement::FIELD_START_TIME);
		$this->assertDateEquals($latest, $time, "latest", "time");
		$row = $connection->l();
		$this->assertDateEquals($latest, $row->latest_measurement_at, "latest", "row->latest_measurement_at");
	}
	/**
	 * @param Connection|null $connection
	 */
	protected function checkPostImportStatus(?Connection $connection): void{
		$connector = $this->getQMConnector();
		/** @var ConnectorImport $lastImport */
		$lastImport =
			ConnectorImport::whereUserId($this->getUserId())->where(ConnectorImport::FIELD_CONNECTOR_ID, $connector->id)
				->first();
		$numberOfMeasurements = $lastImport->number_of_measurements;
		$this->assertGreaterThan(0, $numberOfMeasurements);
		if($numberOfMeasurements > 1){
			$this->assertDateNotEquals($lastImport->earliest_measurement_at, $lastImport->latest_measurement_at,
				"lastImport->earliest_measurement_at", "lastImport->latest_measurement_at",
				"If we have multiple measurements, the earliest and latest should not be equal.  " .
				Measurement::getDataLabIndexUrl());
		}
		// This fails randomly and irreproducibly.  I'm not sure why.  I'm commenting it out for now.
		//$this->assertDateGreaterThanOrEqual($lastImport->import_started_at, $lastImport->import_ended_at,
		//	"import_started_at ($lastImport->import_started_at) should be less than import_ended_at
		// ($lastImport->import_ended_at)");
		$updateError = $connection->getUpdateErrorString();
		$this->assertEquals(ConnectionUpdateStatusProperty::IMPORT_STATUS_UPDATED, $connection->getUpdateStatus(),
			"UPDATE ERROR: $updateError");
		$this->setAuthenticatedUser($this->getUserId());
		$this->assertGreaterThan(time() - 120, $lastImport->import_ended_at->timestamp,
			"Last import timestamp should have been within the last 2 minutes but was " .
			TimeHelper::timeSinceHumanString($lastImport->import_ended_at));
		$this->assertEquals(1, ConnectorImport::count());
		$requests = $connector->getConnectorRequests();
		$this->assertGreaterThan(0, $requests->count(),
			"No requests for $connector->name: " . ConnectorRequest::getDataLabIndexUrl());
	}
	protected function makeSureConnectionHasNoMeasurementsYet(){
		$connector = $this->getQMConnector();
		$connection = $connector->getConnection();
		$latest = $connection->getOrCalculateLatestMeasurementAt();
		$this->assertEquals(null, $latest, Measurement::getDataLabIndexUrl() . "\n" . Connection::getDataLabIndexUrl());
		$this->assertNull($connection->getLatestMeasurementAtAttribute());
		$this->assertEquals(0, UserVariable::count(), "We should have deleted the user variables");
		$this->assertCount(0, QMUserVariable::getAllFromMemoryIndexedByUuidAndId(),
			"There should be no user variables in memory yet");
	}
	protected function assertConnectedAndWaiting(){
		$userConnectors = QMConnector::getForRequest();
		foreach($userConnectors as $connector){
			if($connector->name === $this->getConnectorName()){
				$this->assertEquals(true, $connector->connected, "Connected is not true!");
				$this->assertEquals('CONNECTED', $connector->connectStatus);
				$this->assertEquals('WAITING', $connector->updateStatus);
//				if($connector->providesUserProfileForLogin){
//					$connection = $connector->getConnection();
//					$this->assertNotNull($connection->connector_user_id);
//					$meta = $connection->meta;
//					$this->assertNotEmpty($meta);
//				}
			}
		}
	}
	protected function assertDisconnected(): void{
		$userConnectors = QMConnector::getForRequest();
		foreach($userConnectors as $connector){
			if($connector->name === $this->getConnectorName()){
				$this->assertEquals(false, $connector->connected);
				$this->assertEquals(ConnectionConnectStatusProperty::CONNECT_STATUS_DISCONNECTED,
					$connector->connectStatus);
				$this->assertEquals(ConnectionConnectStatusProperty::CONNECT_STATUS_DISCONNECTED,
					$connector->updateStatus);
			}
		}
	}
	protected function setUserAndClientId(): void{
		$this->setAuthenticatedUser(1);
		$user = QMUser::find($this->getUserId());
		$user->updateQMClientId(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
		BaseClientIdProperty::setInMemory(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
	}
	/**
	 * @return Connection
	 */
	protected function getConnectionModel(): Connection{
		return $this->getQMConnector()->getConnectionIfExists();
	}
	/**
	 * @param array $allMeasurementsByVariableFromFirstImport
	 */
	protected function deleteMeasurementsAnalyzeAndImportAgain(array $allMeasurementsByVariableFromFirstImport){
		$this->deleteMeasurements();
		$this->importAgain($allMeasurementsByVariableFromFirstImport);
	}
	protected function deleteMeasurements(){
		sleep(1); // Avoid too fast duplicate import record
		$connectionBeforeFlush = $this->getConnectionModel();
		$originalLatestConnectionAt = $this->latestDBMeasurementAtForConnection();
		$connectorName = $connectionBeforeFlush->getConnectorName();
		$originalLastMeasurement = $this->getConnectionModel()->getLastMeasurement();
		$newImportEndTime = strtotime($originalLatestConnectionAt) - 3 * 86400;
		if($connectorName === TigerViewConnector::NAME){
			$newImportEndTime = db_date(strtotime('previous monday', $newImportEndTime));
		}
		$newImportEnd = db_date($newImportEndTime);
		$qb =
			QMMeasurement::writable()->where(Measurement::FIELD_CONNECTOR_ID, $connectionBeforeFlush->getConnectorId())
				->where(Measurement::FIELD_START_AT, ">", $newImportEnd);
		$newByVariable = QMMeasurement::getIndexedByVariableName([
			Measurement::FIELD_USER_ID => 1,
			Measurement::FIELD_CONNECTOR_ID => $connectionBeforeFlush->getConnectorId(),
		]);
		$deletedByVariable = QMMeasurement::getIndexedByVariableName([
			Measurement::FIELD_START_AT => "(gt)$newImportEnd",
			Measurement::FIELD_CONNECTOR_ID => $connectionBeforeFlush->getConnectorId(),
		]);
		$this->assertGreaterThan(0, count($deletedByVariable));
		$numberDeleted = $qb->delete();
		Memory::flush();
		/** @var QMMeasurement[] $measurements */
		foreach($deletedByVariable as $measurements){
			$m = QMMeasurement::getFirst($measurements);
			$uv = UserVariable::find($m->userVariableId);
			/** @var QMUserVariable $dbm */
			$dbm = $uv->getDBModel();
			$dbm->updateNewestDataAt();
			$dbm->analyzeFullyAndSave(__FUNCTION__);
		}
		$deletedForAllVariables = Arr::flatten($deletedByVariable, 1);
		$numberGoingToDelete = count($deletedForAllVariables);
		$latestConnectionAfterDelete = $this->latestDBMeasurementAtForConnection();
		$this->assertEquals($numberGoingToDelete, $numberDeleted,
			"Should have deleted $numberGoingToDelete but got $numberDeleted from qb->delete()");
		$this->assertNotNull($latestConnectionAfterDelete,
			"Why aren't there measurements for newLatestDBMeasurementTime?");
		$this->assertDateLessThanOrEqual($newImportEnd, $latestConnectionAfterDelete, "newLastImportedAt",
			"newLatestDBMeasurementAt");
		$this->assertGreaterThan(0, Connection::count());
		$connectionBeforeFlush->updateDbRow([
			Connection::FIELD_LATEST_MEASUREMENT_AT => $latestConnectionAfterDelete,
			Connection::FIELD_TOTAL_MEASUREMENTS_IN_LAST_UPDATE => $connectionBeforeFlush->total_measurements_in_last_update -
				$numberDeleted,
			Connection::FIELD_IMPORT_ENDED_AT => $newImportEnd,
			Connection::FIELD_IMPORT_STARTED_AT => db_date(strtotime($newImportEnd) - 1),
			// Must be less than import end or it's not considered stale
		]);
		Memory::flush();
		$this->connectionBeforeFlush = $connectionBeforeFlush;
		$this->latestConnectionBeforeDelete = $originalLatestConnectionAt;
		$this->connectorName = $connectorName;
		$this->originalLastMeasurement = $originalLastMeasurement;
		$this->newByVariable = $newByVariable;
		$this->deletedByVariable = $deletedByVariable;
		$this->latestConnectionAfterDelete = $latestConnectionAfterDelete;
	}
	/**
	 * @param array $allMeasurementsByVariableFromFirstImport
	 */
	protected function importAgain(array $allMeasurementsByVariableFromFirstImport): void{
		$this->assertGreaterThan(0, Connection::count());
		$this->makeSureThereIsOneStaleConnection();
		$connectionAfterFlush = $this->getOldestWaitingStaleOrStuckConnection();
		$this->validateFromTimeBeforeSecondImport($connectionAfterFlush);
		//$connection->getConnector()->setEndTime($originalLatestTime);
		$connectionAfterFlush->import(__METHOD__);
		$this->assertEquals(2, ConnectorImport::count());
		$reImportMeasurementsByVariable = $connectionAfterFlush->getOrSetNewMeasurementsByVariableName();
		if($connectionAfterFlush->getConnectorName() == GoogleCalendarConnector::NAME){
			$this->logInfo("Not doing other import checks because it's too slow and doesn't work with shouldWeBreak " .
				"function for " . $connectionAfterFlush->getConnectorName());
			return;
		}
		$this->checkMeasurementsAfterSecondImport($allMeasurementsByVariableFromFirstImport, $connectionAfterFlush,
			$reImportMeasurementsByVariable);
	}
	protected function makeSureThereIsOneStaleConnection(): void{
		$staleQB = Connection::whereStale();
		$stale = $staleQB->get();
		$this->assertEquals(1, $stale->count());
	}
	/**
	 * @return Connection|null
	 */
	protected function getOldestWaitingStaleOrStuckConnection(): ?Connection{
		$connectionAfterFlush = Connection::getOldestWaitingStaleOrStuckConnection();
		$this->assertNotEquals($connectionAfterFlush, $this->connectionBeforeFlush);
		if(!$connectionAfterFlush){
			Connection::logAll();
		}
		$this->assertNotNull($connectionAfterFlush, "getOldestWaitingStaleOrStuckConnection returned nothing!");
		return $connectionAfterFlush;
	}
	/**
	 * @param Connection|null $connectionAfterFlush
	 */
	protected function validateFromTimeBeforeSecondImport(?Connection $connectionAfterFlush){
		$getOrCalculateLatestMeasurementAt = $connectionAfterFlush->getOrCalculateLatestMeasurementAt();
		$latestDBAt = $this->latestDBMeasurementAtForConnection();
		$latestConnectionBeforeDelete = $this->latestConnectionBeforeDelete;
		$this->assertDateEquals($latestDBAt, $getOrCalculateLatestMeasurementAt, "this->newLatestDBMeasurementAt",
			"connectionAfterFlush->getOrCalculateLatestMeasurementAt()");
		$connectorAfterFlush = $connectionAfterFlush->getQMConnector();
		$fromAtMinusOne = db_date($connectorAfterFlush->getFromTime() - 1);
		if($getOrCalculateLatestMeasurementAt !== $latestDBAt){
			$this->getOldestWaitingStaleOrStuckConnection();
			$connectionAfterFlush->getOrCalculateLatestMeasurementAt();
			throw new LogicException("connectionAfterFlush still returning old " .
				"getOrCalculateLatestMeasurementAt even though it should have been flushed ");
		}
		$this->assertDateLessThan($latestConnectionBeforeDelete, $getOrCalculateLatestMeasurementAt,
			"this->originalLatestMeasurementAt", "newConnectionLatestMeasurementAt",
			"We should have just deleted measurements after $latestConnectionBeforeDelete! \n" .
			Measurement::generateDataLabIndexUrl());
		$this->assertDateLessThanOrEqual($getOrCalculateLatestMeasurementAt, $fromAtMinusOne,
			"newConnectionLatestMeasurementAt", "fromAtMinusOne");
		$this->assertDateLessThanOrEqual($latestConnectionBeforeDelete, $fromAtMinusOne, "newLatestDBMeasurementAt",
			"fromAtMinusOne");
	}
	/**
	 * @param array $allMeasurementsByVariableFromFirstImport
	 * @param Connection|null $connectionAfterFlush
	 * @param array $reImportMeasurementsByVariable
	 */
	protected function checkMeasurementsAfterSecondImport(array $allMeasurementsByVariableFromFirstImport,
		?Connection $connectionAfterFlush, array $reImportMeasurementsByVariable): void{
		$fromAt = $connectionAfterFlush->getQMConnector()->getFromAt();
		$newLastMeasurement = $this->getConnectionModel()->getLastMeasurement();
		if($this->getOriginalLastMeasurement()->getStartAt() !== $newLastMeasurement->getStartAt()){
			le("New last measurement from connection should be equal to the old " .
				"last measurement from connection but \n" . "new one is: " . $newLastMeasurement->getStartAt() . "\n" .
				"and old one is: " . $this->getOriginalLastMeasurement()->getStartAt() . "\n");
		}
		$newLatestMeasurementAt = $connectionAfterFlush->getLatestMeasurementAtAttribute();
		if($this->latestConnectionBeforeDelete > $newLatestMeasurementAt){
			le("New connector last measurement should be at least as big as old one " .
				"($this->latestConnectionBeforeDelete) but is $newLatestMeasurementAt");
		}
		if($this->latestConnectionBeforeDelete > $this->latestDBMeasurementAtForConnection()){
			le("newLatestDBMeasurementTime should be at least as big as old one (" .
				db_date($this->latestConnectionBeforeDelete) . ") but is " .
				db_date($this->latestDBMeasurementAtForConnection()));
		}
		if(!in_array($this->connectorName, $this->connectorsWhereMeasurementsMayNotMatchFromTimes)){
			$this->makeSureThatAllDeletedMeasurementsWereReImported($allMeasurementsByVariableFromFirstImport,
				$reImportMeasurementsByVariable, $this->deletedByVariable, $this->latestConnectionBeforeDelete,
				$this->newByVariable, $this->connectorName, $fromAt);
		}
	}
	/**
	 * @return mixed
	 */
	protected function latestDBMeasurementAtForConnection(): string{
		$connection = $this->getConnectionModel();
		$id = $connection->getId();
		$latestConnection = Measurement::whereConnectionId($id)->max(Measurement::FIELD_START_AT);
		if(!$latestConnection){
			le('!$latestConnection');
		}
		return $latestConnection;
	}
	/**
	 * @param QMMeasurement $measurement
	 */
	private function assertMeasurementLocationNotNull(QMMeasurement $measurement): void{
		$this->assertNotNull($measurement->latitude);
		$this->assertNotNull($measurement->longitude);
		$this->assertNotNull($measurement->location);
	}
	/**
	 * @param QMConnector $c
	 * @param QMMeasurement $m
	 */
	protected function assertMeasurementMatchesConnector(QMConnector $c, QMMeasurement $m): void{
		if($c->getTitleAttribute() !== $m->getDataSourceName()){
			le("Measurement Should have SourceName: ".$c->getTitleAttribute()." ".QMStr::print($m, ));
		}
		$this->assertEquals($c->getTitleAttribute(), $m->getDataSourceName());
		$this->assertEquals($c->getTitleAttribute(), $m->sourceName);
		$this->assertEquals($c->getId(), $m->connectorId, "connectorId");
		$connection = $c->getConnectionIfExists();
		if($m->connectionId){ // Might want to add connectionId at some point but it might use a lot of memory
			$this->assertEquals($connection->getId(), $m->connectionId, "connectorId");
		}
	}
	/**
	 * @param $m
	 */
	protected function checkMeasurementNote($m): void{
		$this->assertNotNull($m->note);
		$this->assertFalse(strpos($m->note, "{"), "Note not parsed:  " . $m->note);
		$this->assertNotNull($m->additionalMetaData);
	}
	/**
	 * @param int $variableId
	 * @return QMMeasurement[]|QMMeasurementExtended[]
	 */
	protected function getAllMeasurementsForVariable(int $variableId): array{
		$r = new GetMeasurementRequest(['limit' => 0]);
		$r->setUserId($this->getUserId());
		$r->setExcludeExtendedProperties(false);
		$r->setVariableId($variableId);
		$r->setConnectorName($this->getQMConnector()->getTitleAttribute());
		return $r->getMeasurementsInCommonUnit();
	}
	/**
	 * @param array $allMeasurementsByVariableFromFirstImport
	 * @param array $reImportMeasurementsByVariable
	 * @param array $deletedByVariable
	 * @param $originalLatestTime
	 * @param array $newByVariable
	 * @param string $connectorName
	 * @param string $fromAt
	 */
	protected function makeSureThatAllDeletedMeasurementsWereReImported(array $allMeasurementsByVariableFromFirstImport,
		array $reImportMeasurementsByVariable, array $deletedByVariable, $originalLatestTime, array $newByVariable,
		string $connectorName, string $fromAt): void{
		/** @var QMMeasurement[] $reImported */
		foreach($reImportMeasurementsByVariable as $variableName => $reImported){
			if(!$reImported){
				continue;
			}
			$first = QMMeasurement::getFirst($reImported);
			if(!isset($deletedByVariable[$variableName])){
				$allForVariableFromFirstImport = $allMeasurementsByVariableFromFirstImport[$variableName];
				if(!is_array($allForVariableFromFirstImport)){
					$this->logInfo("no $variableName from allMeasurementsByVariableFromFirstImport " .
						" so maybe we created the variable but never got measurements");
					continue;
				}
				if($first->startTime + 86400 > $originalLatestTime){
					continue;
				}  // The end time is variable
				if(count($newByVariable) !== count($deletedByVariable)){
					$this->logError("Not deleting measurements for all variables.  Was import cut off too early? " .
						"You might need to increase maximumTestDurationInSeconds property on $connectorName. ");
				}
				QMLog::table($allForVariableFromFirstImport, "All For $variableName From First Import");
				QMLog::table(Arr::flatten(array_values($reImportMeasurementsByVariable), 1),
					"Re-Imported Measurements");
				throw new LogicException("No deleted measurements for $variableName but we got a new one at " .
					$first->getStartAt() . " and imported " . count($allForVariableFromFirstImport) .
					" initially. First initially imported start time was " .
					$allForVariableFromFirstImport[0]->getStartAt());
			}
			$deletedForVariable = $deletedByVariable[$variableName];
			$numberDeletedForVariable = count($deletedForVariable);
			$numberReImportedForVariable = count($reImported);
			if($numberDeletedForVariable > $numberReImportedForVariable){
				// Doesn't have to equal because we might re-import more on the re-import due to the fact that importing can
				// stop as soon as we have a week of data.  So we might get a week with more measurements in some cases
				// and we might have stopped earlier than the current time
				throw new LogicException("variableName deleted $numberDeletedForVariable but got " .
					"$numberReImportedForVariable on reimport");
			}
			if($connectorName !== TigerViewConnector::NAME){
				foreach($reImported as $m){
					$this->assertGreaterThan($fromAt, db_date($m->getOrSetStartTime()),
						"We imported a measurement before the fromTime: $m");
				}
			}
		}
	}
	protected function flushConnector(): QMConnector{
		$this->connector = null;
		Memory::flush();
		$c = $this->getQMConnector();
		$c->getConnectionIfExists()->import_started_at = null;
		return $c;
	}
	protected function createConnection(): void{
		$connector = $this->getQMConnector();
		$credentials = $this->getTestCredentials();
		$connector->connect($credentials);
		$this->assertConnectedAndWaiting();
	}
	/**
	 * @return AbstractToken
	 */
	protected function getTestToken(): AbstractToken {
		$connector = $this->getQMConnector();
		$credentials = $connector->getCredentialStorageFromMemory()->getTestCredentials();
		/** @var StdOAuth2Token $token */
		if(is_array($credentials)){
			$token = $credentials['token'];
			if(is_string($token)){
				$token = unserialize($token);
			}
		} else{
			$token = $credentials;
		}
		return $token;
	}
}
