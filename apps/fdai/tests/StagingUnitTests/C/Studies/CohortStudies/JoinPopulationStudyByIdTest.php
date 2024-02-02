<?php /** @noinspection SpellCheckingInspection */
/** @noinspection SpellCheckingInspection */
/** @noinspection SpellCheckingInspection */
/** @noinspection SpellCheckingInspection */
/** @noinspection SpellCheckingInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\C\Studies\CohortStudies;
use App\Exceptions\DiffException;
use App\Exceptions\QMFileNotFoundException;
use App\Exceptions\TooManyQueriesException;
use App\Properties\Base\BaseAccessTokenProperty;
use Tests\SlimStagingTestCase;

class JoinPopulationStudyByIdTest extends SlimStagingTestCase {
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public $retry = true;
	/**
	 * @throws DiffException
	 * @throws QMFileNotFoundException
	 * @throws TooManyQueriesException
	 * @covers \App\Slim\Controller\Study\JoinStudyController
	 */
	public function testJoinPopulationStudyById(): void{
        //VariableManualTrackingProperty::fixInvalidRecords();
		$expectedString = '';
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->checkTestDuration(18);
		$this->checkQueryCount(20);
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = [
        'REQUEST_METHOD' => 'POST',
        'REMOTE_ADDR' => '10.190.186.216',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v3/study/join',
        'SERVER_NAME' => '_',
        'SERVER_PORT' => '443',
        'HTTP_CF_CONNECTING_IP' => '2607:f2c0:ec98:142e:34f3:723f:241e:8351',
        'HTTP_COOKIE' => '__cfduid=de01e6b4101bbba0169dc4201447c3e741545183900; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=Woahb_Kk%7C1546393751%7Cf4d7bcc45c130ae07894d5b514adfb79%7Cquantimodo',
        'HTTP_ACCEPT_LANGUAGE' => 'en-us',
        'HTTP_X_FRAMEWORK' => 'ionic',
        'HTTP_X_PLATFORM' => 'android',
        'HTTP_X_CLIENT_ID' => 'quantimodo',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 4.4.2; 7040T Build/KVT49L) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Crosswalk/22.52.561.4 Mobile Safari/537.36',
        'HTTP_X_APP_VERSION' => '2.8.1031',
        'HTTP_ORIGIN' => 'file://',
        'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
        'HTTP_CF_VISITOR' => '{"scheme":"https"}',
        'HTTP_X_FORWARDED_PROTO' => 'https',
        'HTTP_CF_RAY' => '48b650aeeb2827d4-YYZ',
        'HTTP_X_FORWARDED_FOR' => '108.162.241.44',
        'HTTP_CF_IPCOUNTRY' => 'CA',
        'HTTP_ACCEPT_ENCODING' => 'gzip',
        'CONTENT_LENGTH' => '0',
        'CONTENT_TYPE' => 'application/json',
        'slim.url_scheme' => 'https',
        'slim.input' => '',
        'slim.request.form_hash' =>
  [],
        'slim.request.query_hash' =>
  [
    'studyId' => 'cause-5956921-effect-6057115-population-study',
  ],
        'responseStatusCode' => NULL,
        'unixtime' => 1545185242,
        'requestDuration' => 0.5288209915161133,
    ];
}
