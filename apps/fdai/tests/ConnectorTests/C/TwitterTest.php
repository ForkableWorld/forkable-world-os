<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\C;
use App\DataSources\Connectors\TwitterConnector;
use Tests\ConnectorTests\ConnectorTestCase;
class TwitterTest extends ConnectorTestCase {
	//public const DISABLED_UNTIL = "2023-04-01"; // Can't reproduce failure locally (outside of CI slave)
	public $requireNote = true;
	public $connectorName = TwitterConnector::NAME;
	public function testTwitter(){
		//if ($this->weShouldSkip()) {return;}
		$this->fromTime = strtotime("2019-06-14");
		$this->connectImportCheckDisconnect();
		$this->checkConnectorLogin();
	}
}
