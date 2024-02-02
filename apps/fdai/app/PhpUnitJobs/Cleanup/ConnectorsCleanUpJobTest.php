<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Cleanup;
use App\DataSources\Connectors\FacebookConnector;
use App\DataSources\Connectors\GoogleCalendarConnector;
use App\DataSources\Connectors\GoogleFitConnector;
use App\DataSources\Connectors\RescueTimeConnector;
use App\DataSources\Connectors\SleepAsAndroidConnector;
use App\DataSources\CredentialStorage;
use App\DataSources\QMDataSource;
use App\Logging\QMLog;
use App\Models\Connection;
use App\PhpUnitJobs\JobTestCase;
use Illuminate\Support\Arr;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
class ConnectorsCleanUpJobTest extends JobTestCase {
	public function testUpdateConnectorsDatabaseTableFromHardCodedConstants(){
		QMDataSource::updateDatabaseTableFromHardCodedConstants();
	}
	public static function deleteUpConnections(): void{
		Connection::writable()->where(Connection::FIELD_CONNECTOR_ID, 6)->hardDelete("up has been removed");
	}
	public function testDeleteUpConnections(){
		self::deleteUpConnections();
	}
	public function testDisconnectSleepCloud(){
		$connections = Connection::getAllConnectedForConnector(SleepAsAndroidConnector::NAME);
		foreach($connections as $c){
			$c->import(__METHOD__);
		}
	}
	public function testDeleteEmptyStringCredentials(){
		$rows = self::getAllCredentialsWithEmptyValues();
		foreach($rows as $row){
			CredentialStorage::writable()->whereNull(CredentialStorage::FIELD_DELETED_AT)
				->where(CredentialStorage::FIELD_attr_value, $row->encryptedValue)
				->where(CredentialStorage::FIELD_UPDATED_AT, $row->updatedAt)
				->where(CredentialStorage::FIELD_CREATED_AT, $row->createdAt)
				->where(CredentialStorage::FIELD_connector_id, $row->connectorId)
				->where(CredentialStorage::FIELD_USER_ID, $row->userId)->hardDelete(__METHOD__, true);
		}
	}
	/**
	 * @return array
	 */
	public static function getAllCredentialsWithEmptyValues(): array{
		$credentialRows = CredentialStorage::getAll();
		$emptyValue = [];
		foreach($credentialRows as $row){
			if(empty($row->value)){
				$connection = Connection::getConnectionById($row->userId, $row->connectorId);
				if(!$connection){
					$connectionRow = Connection::query()->where(Connection::FIELD_USER_ID, $row->userId)
						->where(Connection::FIELD_CONNECTOR_ID, $row->connectorId)->first();
					QMLog::infoWithoutContext("No connection for userId $row->userId, connectorId $row->connectorId.
                        Empty credential value created $row->createdAt
                        ");
				} else{
					$connection->logInfo("Empty credential value created $row->createdAt");
				}
				$emptyValue[] = $row;
			}
		}
		QMLog::infoWithoutContext(count($emptyValue) . " credential rows with empty values!");
		return $emptyValue;
	}
	public function testFixMissingGoogleFitTokens(){
		$this->fixMissingTokens(GoogleFitConnector::ID, false);
	}
	public function testFixMissingFacebookTokens(){
		$this->fixMissingTokens(FacebookConnector::ID, false);
	}
	public function testFixMissingGoogleCalendarTokens(){
		$this->fixMissingTokens(GoogleCalendarConnector::ID, false);
	}
	public function testFixMissingRescuetimeTokens(){
		$this->fixMissingTokens(RescueTimeConnector::ID);
	}
	/**
	 * @param int $connectorId
	 * @param bool $dryRun
	 * @return void
	 */
	private function fixMissingTokens(int $connectorId, bool $dryRun = true): void{
		$connectedConnections = $this->getConnectedConnections($connectorId);
		$disconnected = $this->disconnectConnectionsWithMissingCredentialRow($connectedConnections, $dryRun);
		$total = count($disconnected);
		$message = "$total errored connections were disconnected";
		if($total){
			QMLog::error($message);
		} else{
			QMLog::info($message);
		}
	}
	/**
	 * @param Connection[] $connections
	 * @param bool $dryRun
	 * @return Connection[]
	 */
	private function disconnectConnectionsWithMissingCredentialRow(array $connections, bool $dryRun = true): array{
		$noRow = [];
		foreach($connections as $c){
			try {
				$token = $tokens[$c->getUserId()] = $c->getQMConnector()->getToken();
			} catch (TokenNotFoundException $e) {
				$token = false;
			}
			if(!$token){
				$noToken[$c->getUserId()] = $c;
				$c->logError("No token!  Created $c->created_at. ");
				$row = $c->credentials;
				if(!$row){
					$noRow[$c->getUserId()] = $c;
					$c->logError("No credentials row!  Created $c->created_at. ");
				}
			}
		}
		if(!$dryRun){
			foreach($noRow as $c){
				$c->disconnect("Credentials row disappeared somehow");
			}
		}
		return $noRow;
	}
	/**
	 * @param int $connectorId
	 * @return Connection[]
	 */
	private function getConnectedConnections(int $connectorId): array{
		$connections = Connection::getAllForConnector($connectorId);
		$connected = Arr::where($connections, static function($c){
			/** @var Connection $c */
			return $c->isConnected();
		});
		/** @var Connection[] $connectedAndErrored */
		$connected = Arr::sort($connected, static function($c){
			/** @var Connection $c */
			return -1 * strtotime($c->getCreatedAt());
		});
		return $connected;
	}
}
