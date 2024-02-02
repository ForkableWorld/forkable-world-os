<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\A;
use App\Logging\ConsoleLog;
use App\DataSources\Connectors\WhatPulseConnector;
use Tests\ConnectorTests\ConnectorTestCase;
class WhatPulseTest extends ConnectorTestCase {
    //public const DISABLED_UNTIL = WhatPulseConnector::TESTING_DISABLED_UNTIL;
    protected $connectorName = WhatPulseConnector::NAME;
	public const DISABLED_UNTIL = '2022-01-01';
    public $requireDuration = true;
    public function testWhatPulse() {
        $user = WhatPulseConnector::TEST_USERNAME;
        ConsoleLog::info("Make sure to set $user to pulse daily");
        if($this->weShouldSkip()){return;}
        $this->fromTime = time() - 10 * 86400;
	    $this->credentials = [
		    'username' => WhatPulseConnector::TEST_USERNAME,
		    'userid' => WhatPulseConnector::TEST_USER_ID,
	    ];
	    $this->connectImportDisconnect();
    }
}
