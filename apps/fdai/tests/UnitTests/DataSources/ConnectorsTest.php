<?php /** @noinspection PhpUnhandledExceptionInspection */ /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
namespace DataSources;
use App\DataSources\QMConnector;

class ConnectorsTest extends \Tests\SlimTests\SlimTestCase
{

    public function testGetDisabledConnectorsInternallyButNotFromAPI()
    {
        $connectors = $this->getConnectorsRequest();
        foreach ($connectors as $c) {
            $this->assertNotContains("up", $c->name);
        }
    }

    /**
     * @return QMConnector[]
     */
    protected function getConnectorsRequest(): array
    {
        $response = $this->getApiV3('connectors/list');
        /** @var QMConnector[] $connectors */
        $connectors = $response->connectors;
        foreach ($connectors as $c) {
            if (!$c->enabled) {
                le("Connectors from API should be enabled but got: " . \App\Logging\QMLog::print_r($c, true));
            }
        }
        return $connectors;
    }
}
