<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests;
use App\DataSources\LocationBasedConnector;
use App\DataSources\QMConnector;
use App\Exceptions\NoChangesException;
use App\Models\GlobalVariableRelationship;
use App\Models\Connection;
use App\Models\Measurement;
use App\Properties\Connection\ConnectionConnectStatusProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Types\QMArr;
use App\Units\OneToFiveRatingUnit;
use App\Utils\Stats;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use Illuminate\Support\Arr;
abstract class LocationBasedConnectorTestCase extends ConnectorTestCase {
	public const ZIP_CODE           = '62025';
	public const LATITUDE_LONGITUDE = '38.6253, -90.2694';
	public const LOCATION_NAME      = 'Illinois, United States of America';
	protected function checkVariablesAndMeasurements(){
		parent::checkVariablesAndMeasurements();
		$this->getMeasurementsAndCheckLocation();
	}
	protected function getMeasurementsAndCheckLocation(){
		/** @var QMMeasurement[] $measurements */
		$measurements = QMMeasurement::writable()->getArray();
		$possibleLocations = [
			//"Glen Carbon, IL",
			self::LOCATION_NAME,
			'Edwardsville, IL',
			'St. Louis (Metro-east)',
			'62025',
			'America/Chicago',
			'H3Z 2Y7',
		];
		$found = false;
		foreach($measurements as $measurement){
			if($measurement->location){
				$this->assertContains($measurement->location, $possibleLocations);
			}
			if($measurement->latitude || $measurement->location){
				$found = true;
			}
		}
		$this->assertTrue($found, "At least one measurement should have a location or coordinates!");
	}
	protected function connect(): void{
		parent::connect();
		$this->verifyLocationInMessage();
	}
	protected function verifyLocationInMessage(): void{
		$this->setAuthenticatedUser(1);
		$this->deleteOtherLocationConnections();
		$connections = Connection::whereUserId(1)->get();
		$this->assertCount(1, $connections); // We deleted the other connections
		//$this->assertCount($this->expectedConnections, QMAuth::getQMUser()->l()->connections);
		$postalCode = QMArr::getValue($this->credentials, ['zip', 'location']);
		$this->assertStringContainsString($postalCode, $this->getQMConnectorFromUser()->message);
		$this->assertStringContainsString($postalCode, $this->getQMConnectorFromApi()->message);
	}
	/**
	 * @return mixed
	 */
	protected function getQMConnectorFromApi(){
		$response = $this->getAndDecodeBody('/api/v3/connectors/list');
		$name = $this->getConnectorName();
		$c = Arr::first($response->connectors, static function($c) use ($name){
			/** @var QMConnector $c */
			return $c->name === $name;
		});
		return $c;
	}
	/**
	 * @return QMConnector
	 */
	protected function getQMConnectorFromUser(): QMConnector{
		$u = $this->getOrSetAuthenticatedUser(1);
		$c = $u->getQMConnector($this->getConnectorName());
		return $c;
	}
	protected function verifyAllLocationConnectorsAreConnected(){
		$u = $this->getOrSetAuthenticatedUser(1);
		$locationConnectors = $u->getLocationBasedConnectors();
		$this->assertGreaterThan(1, count($locationConnectors));
		foreach($locationConnectors as $c){
			$this->assertEquals(ConnectionConnectStatusProperty::CONNECT_STATUS_CONNECTED,
				$c->getConnectionIfExists()->getConnectStatus());
		}
	}
	protected function connectImportDisconnectLocationConnector(): void{
		GlobalVariableRelationship::deleteAll();
		if($this->weShouldSkip()){
			return;
		}
		$this->credentials = ['location' => self::ZIP_CODE];
		$this->connect([]);
		$connector = QMConnector::getConnectorByNameOrId($this->getConnectorName(), $this->getUserId());
		$gottenCredentials = $connector->getCredentialStorageFromMemory()->get();
		$this->assertEquals(self::ZIP_CODE, $gottenCredentials['zip']);
		$this->importAndCheckVariables([]);
		$this->disconnect();
		$this->getMeasurementsAndCheckLocation();
	}
	/**
	 * @param array $parameters
	 * @throws \App\Exceptions\TooSlowException
	 * @throws \Throwable
	 */
	protected function importAndCheckVariables(array $parameters = []){
		$this->makeSureConnectionHasNoMeasurementsYet();
		$connection = $this->getConnectionModel();
		$this->checkPreImportFromTime();
		$this->checkPreImportEndTime();
		$connector = $connection->getQMConnector();
		if($this->fromTime){$connector->setFromDate($this->fromTime);}
		$connection->import(__METHOD__);
		//if(EnvOverride::isLocal()){$this->dumpFixtures();}
		$this->makeSureConnectionWasUpdatedAfterImport();
		$this->checkVariablesAndMeasurements();
		$this->deleteOtherLocationConnections();
		$this->assertNull(Connection::getOldestWaitingStaleOrStuckConnection());
		$this->checkPostImportEndTime();
		$this->checkPostImportFromTime();
	}
	/**
	 * @return LocationBasedConnector
	 */
	public function getQMConnector(): QMConnector{
		return parent::getQMConnector();
	}
	protected function deleteOtherLocationConnections(): void{
		Connection::where(Connection::FIELD_CONNECTOR_ID, "<>", $this->getQMConnector()->id)->forceDelete();
		$this->assertEquals(1, Connection::count());
		$u = $this->getOrSetAuthenticatedUser(1);
		$u->setConnections(null);
		$this->assertCount(1, $u->connections()->get());
	}
	protected function checkPreImportFromTime(): void{
		$u = $this->getOrSetAuthenticatedUser(1);
		$l = $u->l();
		$registeredAt = $l->user_registered = db_date(time() - 6 * 30 * 86400);
		$l->save();
		$this->assertDateEquals($registeredAt, $u->l()->user_registered);
		$connector = $this->getQMConnector();
		$earliestMeasurementAt = $connector->getEarliestMeasurementAt();
		$this->assertNull($earliestMeasurementAt);
		$latestConnectorMeasurementAt = $connector->getOrCalculateLatestMeasurementAt();
		$this->assertNull($latestConnectorMeasurementAt);
		$earliestNonEnvironmentMeasurementAt = $connector->getEarliestNonEnvironmentMeasurementAt();
		$this->assertNull($earliestNonEnvironmentMeasurementAt);
		$fromAt = $connector->getFromAt();
		$this->assertEquals(db_date(strtotime($registeredAt) - 7 * 86400), $fromAt);
	}
	protected function checkPreImportEndTime(): void{
		$connector = $this->getQMConnector();
		$latestNonEnvironmentMeasurementAt = $connector->getLatestNonEnvironmentMeasurementAt();
		$this->assertNull($latestNonEnvironmentMeasurementAt);
	}
	/**
	 * @param $startAtTime
	 * @return QMConnector
	 * @throws NoChangesException
	 */
	protected function createMoodMeasurement($startAtTime): QMConnector{
		$m = Measurement::upsertOne([
			Measurement::FIELD_VARIABLE_ID => OverallMoodCommonVariable::ID,
			Measurement::FIELD_ORIGINAL_VALUE => 3,
			Measurement::FIELD_ORIGINAL_UNIT_ID => OneToFiveRatingUnit::ID,
			Measurement::FIELD_START_AT => $startAtTime,
			Measurement::FIELD_USER_ID => UserIdProperty::USER_ID_DEMO,
		]);
		$fromDb = Measurement::whereStartAt($m->start_at)
			->where(Measurement::FIELD_VARIABLE_ID, OverallMoodCommonVariable::ID)->first();
		$this->assertNotNull($fromDb);
		return $this->flushConnector();
	}
	protected function checkPostImportFromTime(): void{
		$connector = $this->getQMConnector();
		$latestMeasurementAt = $connector->getOrCalculateLatestMeasurementAt();
		$fromAt = $connector->getFromAt();
		$this->assertDateEquals(time_or_exception($latestMeasurementAt) + 86400, $fromAt,
			'$latestMeasurementAt plus a day', 'fromAt');
	}
	protected function checkPostImportEndTime(): void{
		$this->checkPreImportEndTime();
	}
	protected function checkFromAtAfterCreatingNonEnvironmentMeasurement(): Connection{
		$connector = $this->getQMConnector();
		$earliestConnectorAt = $connector->getEarliestMeasurementAt();
		$this->assertEquals(Measurement::min(Measurement::FIELD_START_AT), $earliestConnectorAt);
		$startTime = Stats::roundToNearestMultipleOf(strtotime($earliestConnectorAt) - 7 * 86400, 86400);
		$earliestNonEnvironmentMeasurement = db_date($startTime);
		$connector = $this->createMoodMeasurement($earliestNonEnvironmentMeasurement);
		$connector->calculateLatestNonEnvironmentMeasurementAt();
		$calculated = $connector->calculateEarliestNonEnvironmentMeasurementAt();
		$this->assertEquals($earliestNonEnvironmentMeasurement, $calculated);
		$newFromAt = $connector->getFromAt();
		$expectedNewFromAt = db_date(strtotime($earliestNonEnvironmentMeasurement) - 7 * 86400);
		$this->assertEquals($expectedNewFromAt, $newFromAt);
		return $connector->getConnectionIfExists();
	}
	/**
	 * @return Connection
	 * @throws NoChangesException
	 */
	protected function checkEndAtAfterNonEnvironmentMeasurement(): Connection{
		$connector = $this->getQMConnector();
		$latestConnectorAt = $connector->getEarliestMeasurementAt();
		$latestNonEnvironmentMeasurement = db_date(strtotime($latestConnectorAt) + 7 * 86400);
		$connector = $this->createMoodMeasurement($latestNonEnvironmentMeasurement);
		$calculated = $connector->calculateLatestNonEnvironmentMeasurementAt();
		$this->assertEquals($latestNonEnvironmentMeasurement, $calculated);
		$connector->calculateEarliestNonEnvironmentMeasurementAt();
		$endAt = $connector->getEndAt();
		$this->assertEquals($latestNonEnvironmentMeasurement, $endAt);
		return $connector->getConnectionIfExists();
	}
}
