<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\A;
use App\DataSources\Connectors\FoursquareConnector;
use App\Models\Measurement;
use App\Properties\Connection\ConnectionImportedDataFromAtProperty;
use Tests\ConnectorTests\ConnectorTestCase;
/**
 * Class FoursquareTest
 * @package Tests\Api\Connectors1
 */
class FoursquareTest extends ConnectorTestCase{
    public $requireNote = true;
    public function testFoursquare(){
        if(!FoursquareConnector::ENABLED){
            $this->skipTest("Auth problems");
            return;
        }
        $this->connectorName = FoursquareConnector::NAME;
        $parameters = ['variables' =>  ['Visits to Soulard Coffee Garden'],];
        //$parameters['fromTime'] = time() - 7 * 86400; // So slow!
        $parameters['fromTime'] = ConnectionImportedDataFromAtProperty::generateEarliestUnixTime();
        $this->connectImportCheckDisconnect($parameters);
        $measurements = Measurement::all();
        foreach ($measurements as $l){
            $m = $l->getDBModel();
            $containsMessage = strpos($m->note, '"message"');
            if($containsMessage){
                $this->assertFalse($containsMessage, "Note is: $m->note");
            }
            $meta = $m->getAdditionalMetaData();
            $this->assertFalse(strpos($meta->message, '"message"'));
            $this->assertNotNull($meta);
            $this->assertNotNull($meta->message);
            $this->assertNotNull($meta->getImage());
        }
    }
}
