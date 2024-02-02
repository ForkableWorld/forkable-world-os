<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\B;
use App\DataSources\Connectors\NetatmoConnector;
use App\Logging\QMLog;
use App\Models\Connection;
use Tests\ConnectorTests\ConnectorTestCase;
/**
 * @package Tests\Api\Connectors3
 */
class NetatmoTest extends ConnectorTestCase{
    protected const DISABLED_UNTIL = "2020-04-26";
    protected const REASON_FOR_SKIPPING = 'NAApiErrorType: Service Unavailable';
    public $connectorName = NetatmoConnector::NAME;
    public $requireMeasurementLocation = true;
    public function testNetatmo(): void{
        Connection::logAll();
        if($this->weShouldSkip()){return;}
        //$this->fromTime = ConnectionImportedDataFromAtProperty::generateEarliestUnixTime();
        $this->fromTime = time() - 30 * 86400;
        try {
            $this->connectImportCheckDisconnect([]);
        } catch (\Throwable $e){
            QMLog::error(__METHOD__.": ".$e->getMessage());
            $this->skipTest("Skipping because ".$e->getMessage());
        }
    }
}
