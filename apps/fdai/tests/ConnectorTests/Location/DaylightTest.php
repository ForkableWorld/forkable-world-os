<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\Location;
use App\DataSources\Connectors\DaylightConnector;
use Tests\ConnectorTests\LocationBasedConnectorTestCase;
class DaylightTest extends LocationBasedConnectorTestCase {
    protected $connectorName = DaylightConnector::NAME;
    public const DISABLED_UNTIL = "2019-11-19";
    public function testDaylightImport(){
        $this->connectImportDisconnectLocationConnector();
    }
}
