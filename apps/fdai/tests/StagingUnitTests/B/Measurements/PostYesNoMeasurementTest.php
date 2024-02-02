<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\B\Measurements;
use App\Models\BaseModel;
use App\Models\Measurement;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Variables\QMCommonVariable;
use Tests\SlimStagingTestCase;
class PostYesNoMeasurementTest extends SlimStagingTestCase
{
    const VARIABLE_NAME = 'High Salt Intake';
    //const START_TIME = '1588469659';
    public function testPostYesNoMeasurement(): void{
		$expectedString = '';
		$startTime = time();
		$this->slimEnvironmentSettings = array (
            'REQUEST_METHOD' => 'POST',
            'REMOTE_ADDR' => '173.245.52.152',
            'SCRIPT_NAME' => '',
            'PATH_INFO' => '/api/v3/measurements',
            'SERVER_NAME' => 'app.quantimo.do',
            'SERVER_PORT' => '443',
            'HTTP_CDN_LOOP' => 'cloudflare',
            'HTTP_CF_CONNECTING_IP' => '2600:100a:b112:7956:57b5:fe2:a27f:7773',
            'HTTP_CF_REQUEST_ID' => '0279c4f1e80000e1e2a0a9a200000001',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            'HTTP_REFERER' => 'https://web.quantimo.do/',
            'HTTP_SEC_FETCH_DEST' => 'empty',
            'HTTP_SEC_FETCH_MODE' => 'cors',
            'HTTP_SEC_FETCH_SITE' => 'same-site',
            'HTTP_ORIGIN' => 'https://web.quantimo.do',
            'HTTP_CONTENT_TYPE' => 'application/json',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 10; Pixel 3a XL) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.117 Mobile Safari/537.36',
            'HTTP_AUTHORIZATION' => 'Bearer '.\App\Models\User::mike()->getOrCreateAccessTokenString('quantimodo'),
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_CF_VISITOR' => '{"scheme":"https"}',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_CF_RAY' => '58d63dc97978e1e2-EWR',
            'HTTP_X_FORWARDED_FOR' => '2600:100a:b112:7956:57b5:fe2:a27f:7773',
            'HTTP_CF_IPCOUNTRY' => 'US',
            'HTTP_ACCEPT_ENCODING' => 'gzip',
            'HTTP_CONNECTION' => 'Keep-Alive',
            'CONTENT_LENGTH' => '352',
            'CONTENT_TYPE' => 'application/json',
            'slim.url_scheme' => 'https',
            'slim.input' => '[{"variableName":"'.
                self::VARIABLE_NAME.
                '","value":"Yes","note":"","startTimeEpoch":'.
                $startTime.
                ',"unitAbbreviatedName":"yes/no","variableCategoryName":"Foods","latitude":null,"longitude":null,"location":null,"sourceName":"QuantiModo for web","valueUnitVariableName":"Yes yes/no High Salt Intake","icon":"ion-fork","pngPath":"img/variable_categories/foods.png"}]',
            'slim.request.form_hash' =>
  array (
  ),
            'slim.request.query_hash' =>
  array (
    'appName' => 'QuantiModo',
    'appVersion' => '2.10.416',
    'accessToken' => 'mike-test-token',
    'clientId' => 'quantimodo',
    'platform' => 'web',
  ),
            'responseStatusCode' => NULL,
            'unixtime' => 1588469685,
            'requestDuration' => 0.7307231426239014,
);
		$v = QMCommonVariable::find(self::VARIABLE_NAME);
		$v->logInfo("Unit is ".$v->getUnitAbbreviatedName());
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->checkTestDuration(15);
		$this->checkQueryCount(15);
		$m = Measurement::whereVariableId($v->getVariableIdAttribute())
            ->orderBy(Measurement::UPDATED_AT, BaseModel::ORDER_DIRECTION_DESC)
            ->first();
		$this->assertEquals($v->getCommonUnitId(), $m->unit_id);
	}
	public $expectedResponseSizes = [];
}
