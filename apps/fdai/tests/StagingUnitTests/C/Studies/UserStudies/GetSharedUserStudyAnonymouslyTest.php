<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\C\Studies\UserStudies;
use Tests\SlimStagingTestCase;
class GetSharedUserStudyAnonymouslyTest extends SlimStagingTestCase
{
    public function testGetSharedUserStudyAnonymously(): void{
		$expectedString = '';
		$this->slimEnvironmentSettings = [
  'REQUEST_METHOD' => 'GET',
  'SCRIPT_NAME' => '',
  'PATH_INFO' => '/api/v3/studies',
  'SERVER_NAME' => 'studies.crowdsourcingcures.org',
  'SERVER_PORT' => '443',
  'HTTP_COOKIE' => '__cfduid=dc64009821bb7dad2f90be3b7dd95e2901615476888; _ga=GA1.2.2122388961.1615476891; _gid=GA1.2.96656971.1615476891; drift_aid=fa84b41b-be31-492f-8a59-bdfab1391f60; driftt_aid=fa84b41b-be31-492f-8a59-bdfab1391f60',
  'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
  'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
  'HTTP_SEC_FETCH_DEST' => 'document',
  'HTTP_SEC_FETCH_USER' => '?1',
  'HTTP_SEC_FETCH_MODE' => 'navigate',
  'HTTP_SEC_FETCH_SITE' => 'none',
  'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
  'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.190 Safari/537.36',
  'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
  'HTTP_SEC_CH_UA_MOBILE' => '?0',
  'HTTP_SEC_CH_UA' => '"Chromium";v="88", "Google Chrome";v="88", ";Not A Brand";v="99"',
  'HTTP_CONNECTION' => 'keep-alive',
  'CONTENT_LENGTH' => '',
  'CONTENT_TYPE' => '',
  'slim.url_scheme' => 'https',
  'slim.input' => '',
  'slim.request.query_hash' =>
  [
    'causeVariableName' => 'Inflammatory Pain',
    'effectVariableName' => 'Overall Mood',
    'clientId' => 'quantimodo',
    'includeCharts' => 'true',
    'platform' => 'web',
    'studyId' => 'cause-1340-effect-1398-user-230-user-study',
  ],
  'responseStatusCode' => NULL,
  'unixtime' => 1615477414,
  'requestDuration' => 0,
		];
		$responseBody = $this->callAndCheckResponse($expectedString, 200);
		$this->checkTestDuration(9);
		$this->checkQueryCount(20);
	}
}
