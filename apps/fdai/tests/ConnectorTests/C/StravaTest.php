<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\C;
use App\DataSources\Connectors\StravaConnector;
use App\DataSources\QMConnector;
use App\Properties\Connection\ConnectionImportedDataFromAtProperty;
use Tests\ConnectorTests\ConnectorTestCase;
class StravaTest extends ConnectorTestCase
{
    public $requireNote = true;
    public function testStrava(){
        if (time() < strtotime(StravaConnector::DISABLED_UNTIL)) {
            $this->skipTest('Need to update scopes');
            return;
        }
        /*
        ## Updating expired OAuth access tokens when Connector tests fail
        Occasionally, the OAuth tokens we've hard coded in our [connector tests](Api/Connectors) expire. Take these quick steps to update them and fix the tests.
        - Retrieve token manually by connecting to the provider at https://web.quantimo.do/index.html#/app/import
        - Set $connectorCredentialsUserId to your QM id in tests/bootstrap.php
        - Run tests
        - Set $connectorCredentialsUserId to null in tests/bootstrap.php
        - Commit the updated test database
        */
        $this->connectorName = 'strava';
        $parameters = ['source' => 7, 'variables' =>  ['Run Distance', 'Average Run Speed'],];
        $parameters['fromTime'] = ConnectionImportedDataFromAtProperty::generateEarliestUnixTime();
	    QMConnector::$testAccessToken = '6fe305afe2ddc2da39dc875db64c020d06653bec';
        $this->connectImportCheckDisconnect($parameters);
        $this->blackfireEndProfileProbeIfNecessary();
        //$this->checkConnectorLogin();
    }
}
