<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\C;
use App\DataSources\Connectors\RunKeeperConnector;
use App\Properties\Connection\ConnectionImportedDataFromAtProperty;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\CaloriesBurnedCommonVariable;
use Tests\ConnectorTests\ConnectorTestCase;
/**
 * Class RunKeeperTest
 * @package Tests\Api\Connectors3
 */
class RunKeeperTest extends ConnectorTestCase {
    public const DISABLED_UNTIL = "2023-04-01";
    public $connectorName = RunKeeperConnector::NAME;
    public function testRunKeeper(){
        $this->connectImportCheckDisconnect([
            'source'    => RunKeeperConnector::ID,
            'fromTime'  => ConnectionImportedDataFromAtProperty::generateEarliestUnixTime(),
            'variables' => [CaloriesBurnedCommonVariable::NAME]
        ]);
    }
}
