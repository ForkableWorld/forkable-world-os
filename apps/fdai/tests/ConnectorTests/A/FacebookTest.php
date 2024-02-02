<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\A;
use App\DataSources\Connectors\FacebookConnector;
use App\Types\QMArr;
use App\Variables\QMUserVariable;
use Tests\ConnectorTests\ConnectorTestCase;
/**
 * Class FacebookTest
 * @package Tests\Api\Connectors1
 */
class FacebookTest extends ConnectorTestCase{
    public const FACEBOOK_DISABLED_UNTIL = "2023-04-01";
    public $requireNote = true;
    public function testFacebook(){
        if(time() < strtotime(self::FACEBOOK_DISABLED_UNTIL)){
            $this->skipTest('Might need to click CONTINUE and go through app review again at '.FacebookConnector::APP_PERMISSIONS_PAGE);
            return;
        }
        // You can generate new access tokens at https://developers.facebook.com/apps/225078261031461/roles/test-users/
        $parameters = [ 'source' => 30,
            'variables' => [
                //FacebookConnector::VARIABLE_NAME_COMMENTS_ON_YOUR_POSTS,
                FacebookConnector::VARIABLE_NAME_LIKES_OF_YOUR_POSTS,
                FacebookConnector::VARIABLE_NAME_YOUR_PAGE_LIKES,
                FacebookConnector::VARIABLE_NAME_YOUR_POSTS,
            ],
            'fromTime' => time() - 63113852
        ];
        $this->connectImportCheckDisconnect($parameters);
        $posts = QMUserVariable::getByNameOrId(1, FacebookConnector::VARIABLE_NAME_YOUR_POSTS);
        $raw = $posts->getQMMeasurements();
        $this->assertGreaterThan(1, count($raw));
        $rawZeros = QMArr::getElementsWithPropertyMatching('value', 0, $raw);
        $this->assertEmpty($rawZeros);
        $processedDailyMeasurements = $posts->getValidDailyMeasurementsWithTagsAndFilling();
        $zeros = QMArr::getElementsWithPropertyMatching('value', 0, $processedDailyMeasurements);
        $this->assertNotEmpty($zeros);
        $days = $posts->getNumberOfDaysBetweenEarliestAndLatestTaggedMeasurement();
        if(count($raw) < $days){$days = count($raw);}
        $this->assertGreaterThan($days - 1, count($processedDailyMeasurements));
        $charts = $posts->getChartGroup();
        $lineChart = $charts->lineChartWithSmoothing;
        $data = $lineChart->getHighchartConfig()->series[0]['data'];
        $zeroCount = 0;
        foreach ($data as $datum){if($datum[1] == 0){$zeroCount++;}}
        $this->assertGreaterThan(0, $zeroCount);
        $this->checkConnectorLogin();
    }
}
