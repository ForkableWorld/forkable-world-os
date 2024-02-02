<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\Location;
use App\DataSources\Connectors\Exceptions\ConnectorDisabledException;
use App\DataSources\Connectors\PollenCountConnector;
use App\Exceptions\NoGeoDataException;
use App\Variables\CommonVariables\EnvironmentCommonVariables\PollenIndexCommonVariable;
use Tests\ConnectorTests\LocationBasedConnectorTestCase;
class PollenCountTest extends LocationBasedConnectorTestCase {
	protected $connectorName = PollenCountConnector::NAME;
	public const DISABLED_UNTIL = PollenCountConnector::DISABLED_UNTIL; // Timing out for some reason
	public $variablesToCheck = [
		PollenIndexCommonVariable::NAME,
	];
	public function testPollenCountImport(){
		$this->credentials = ['location' => self::ZIP_CODE];
		$this->connectImportDisconnect();
		$this->getMeasurementsAndCheckLocation();
	}
	/**
	 * @param array $allMeasurementsByVariableFromFirstImport
	 * @throws ConnectorDisabledException
	 * @throws NoGeoDataException
	 */
	protected function deleteMeasurementsAnalyzeAndImportAgain(array $allMeasurementsByVariableFromFirstImport){
		// PollenCountConnector needs special importAgain because it always returns a month
		$connection = $this->flushConnector()->getConnectionIfExists();
		$connection->import(__METHOD__);
	}
}
