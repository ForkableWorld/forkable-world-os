<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\B;
use App\DataSources\QMConnector;
use App\Properties\Connection\ConnectionUpdateStatusProperty;
use App\Storage\DB\Writable;
use Carbon\Carbon;
use Tests\ConnectorTests\ConnectorTestCase;
class MoodPandaTest extends ConnectorTestCase {
	const CREDENTIALS_USERNAME = 'm@mikesinn.com';
	const ENABLED              = false;
	//If this is failing, rate your mood at http://moodpanda.com/Feed/?Me=1
	public function testMoodPanda(){
		if(!self::ENABLED){
			$this->skipTest("Disabled due to strict hourly API limit");
			return;
		}
		$this->logInfo("If this is failing, rate your mood at http://moodpanda.com/Feed/?Me=1 with m@mikesinn.com pw: B1ggerstaff!");
		$this->logInfo("TODO: Uncomment variable name on 9-23-2017");
		$this->connectorName = 'moodpanda';
		$parameters = [
			'email' => static::CREDENTIALS_USERNAME,
			'source' => 35,
			'variables' => [//'Overall Mood'
			],
			'fromTime' => time() - 30 * 86400 // Max is 30 days.
		];
		$this->connect($parameters);
		$this->importAndCheckVariables($parameters);
		$connector = QMConnector::getConnectorByNameOrId($this->getConnectorName(), $this->getUserId());
		// set updatedTime in the past
		$db = Writable::db();
		$db->table('connections')->where('user_id', $this->getUserId())->where('connector_id', $connector->id)->update([
			'updated_at' => '2011-11-11',
			'update_status' => ConnectionUpdateStatusProperty::IMPORT_STATUS_UPDATED,
		]);
		// update old connectors
		$connection = $this->getConnectionModel();
		$connection->import(__METHOD__);
		// check updatedTime
		$data = $connection->l();
		$this->assertNotNull($data, 'No Connection');
		$this->assertEquals(ConnectionUpdateStatusProperty::IMPORT_STATUS_UPDATED, $data->update_status);
		$updatedTime = new Carbon($data->updated_at);
		$this->assertEquals(0, $updatedTime->diffInDays());
		$this->disconnect();
	}
}
