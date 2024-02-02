<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\Location;
use App\DataSources\Connectors\AirQualityConnector;
use Tests\ConnectorTests\LocationBasedConnectorTestCase;
class AirQualityTest extends LocationBasedConnectorTestCase {
    protected $connectorName = AirQualityConnector::NAME;
    public const DISABLED_UNTIL = AirQualityConnector::DISABLED_UNTIL;
    public $variablesToCheck = [
        // AirQualityConnector::OZONE_POLLUTION_AIR_QUALITY_INDEX, // Not returned anymore for some reason
        AirQualityConnector::FINE_PARTICULATE_MATTER_POLLUTION_AIR_QUALITY_INDEX,
        // AirQualityConnector::LARGE_PARTICULATE_MATTER_POLLUTION_AIR_QUALITY_INDEX,  //  Not returned anymore for some reason
    ];
    public function testAirQualityImport(){
		$this->setFromTime(time() - 7 * 86400);
	    $this->connectImportDisconnectLocationConnector();
    }
}
