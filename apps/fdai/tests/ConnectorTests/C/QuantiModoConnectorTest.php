<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\C;
use Tests\ConnectorTests\ConnectorTestCase;
/**
 * @package Tests\Api\Connectors3
 */
class QuantiModoConnectorTest extends ConnectorTestCase {
	const DISABLED_UNTIL = "2023-04-01";
	const REASON_FOR_SKIPPING = "Staging might be messed up";
    public function testQuantiModoConnector(){
        $this->assertVariableExistsInDb(1398);
		$connector = $this->getQMConnector();
	    $credentials = $connector->getCredentialStorageFromMemory()->getTestCredentials();
        $this->checkConnectorLogin();
    }
}
