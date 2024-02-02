<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\B;
use App\DataSources\Connectors\GoogleLoginConnector;
use Tests\ConnectorTests\ConnectorTestCase;
/**
 * @package Tests\Api\Connectors
 */
class GoogleLoginTest extends ConnectorTestCase {
    protected const DISABLED_UNTIL = "2023-04-01";
	protected const REASON_FOR_SKIPPING = "Refresh token keeps being missing";
    public $connectorName = GoogleLoginConnector::NAME;
    public function testGoogleLogin() {
        $this->checkConnectorLogin();
    }
}
