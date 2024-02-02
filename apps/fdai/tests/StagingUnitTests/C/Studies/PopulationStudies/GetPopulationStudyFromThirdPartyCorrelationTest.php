<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\C\Studies\PopulationStudies;
use App\Computers\ThisComputer;
use App\Studies\QMStudy;
use Clockwork\Support\Laravel\Tests\UsesClockwork;
use Tests\SlimStagingTestCase;
class GetPopulationStudyFromThirdPartyCorrelationTest extends SlimStagingTestCase {
	public const DISABLED_UNTIL = "2021-07-01";
	public $expectedResponseSizes = [
		'causeVariable' => 122.605,
		'causeVariableId' => 0.008,
		'causeVariableName' => 0.018,
		'commentStatus' => 0.011,
		'effectVariable' => 150.008,
		'effectVariableId' => 0.007,
		'effectVariableName' => 0.02,
		'id' => 0.048,
		'isPublic' => 0.004,
		'joined' => 0.004,
		'participantInstructions' => 20,
		'principalInvestigator' => 0.51,
		'publishedAt' => 0.027,
		'statistics' => 14.761,
		'studyCard' => 37.805,
		'studyCharts' => 2.46,
		'studyHtml' => 121,
		'studyImages' => 1.539,
		'studyLinks' => 2.359,
		'studySharing' => 0.186,
		'studyStatus' => 0.014,
		'studyText' => 34.252,
		'studyVotes' => 0.061,
		'title' => 0.088,
		'type' => 0.018,
		//'wpPostId'                      => 0.009,
		//'postType'                      => 0.011,
		//'postStatus'                    => 0.014,
		'success' => 0.004,
		'status' => 0.009,
	];
	public $maximumResponseArrayLength = false;
	public $minimumResponseArrayLength = false;
	public function testGetPopulationStudyFromThirdPartyCorrelation(): void{
		if($this->weShouldSkip()){
			return;
		} // TODO: Remove this when we've stored charts in database
		$expectedString = '';
		$this->slimEnvironmentSettings = [
			'REQUEST_METHOD' => 'GET',
			'SCRIPT_NAME' => '',
			'PATH_INFO' => '/api/v4/study',
			'SERVER_NAME' => ThisComputer::LOCAL_HOST_NAME,
			'SERVER_PORT' => '443',
			'HTTP_COOKIE' => 'XSRF-TOKEN=eyJpdiI6InF0RkNFWFpKemhaVGI5Y29ZcmhYY3c9PSIsInZhbHVlIjoiZ2J1V0RmeWc0RE5cL0dDVjFIdW9pbFZJcGloUXNZYzhQaHp3d3l4clwvdkxHdHRtemNlaUpia01icjRJYTFmY0xqIiwibWFjIjoiZmNiNTZhNDFjZTdhNmQ1NjdlNmMwNDVhOThjNTFlOTZhZTk3MTM4MDE2ZmVjYjI0ODgyYjdkZTQyNWNiYzc4ZSJ9; laravel_session=eyJpdiI6IkVvaERDb0h5U0ZWUHlITWIwTUduZlE9PSIsInZhbHVlIjoiVVd5M3dld2piNjA5MDlTZTBkTTNpWmZmYXc3dThKZHFhMWxnM2l1bE1jVWRuclVyenBOQnVCbEx1WExhYkpBXC8iLCJtYWMiOiI2YmU0YjdmZWQ5NjBkZjRiYzIxNWE1YzgxMzE5NzU4ZThkOGJiZDEwNjQ0MDk4Yjc5YzA3ZmRlOWE4NjM3NGVkIn0%3D; driftt_aid=25885cf8-9a2e-4b88-867d-9800baa3024b; __cfduid=d9f3001780f7a04bf672c193d1d1390531582682882; driftt_sid=e75ca8f8-c7d0-4465-8dd6-fded0eb1f724; driftt_aid=25885cf8-9a2e-4b88-867d-9800baa3024b; _ga=GA1.2.853854440.1582682892; _gid=GA1.2.164814786.1582682892; DFTT_END_USER_PREV_BOOTSTRAPPED=true; driftt_sid=23305f11-0242-4ea4-8571-899d6bfd3022; final_callback_url=https%3A%2F%2Fweb.quantimo.do%2F%23%2Fapp%2Flogin%3Fclient_id%3Dquantimodo; driftt_eid=81328; _gat=1; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=test-user-dev-web-quantimo-do-test-token%7C1583892553%7C3a3896f7ddacabbefb226f50e5c656a4%7Cquantimodo',
			'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
			'HTTP_ACCEPT_ENCODING' => 'gzip',
			'HTTP_SEC_FETCH_MODE' => 'navigate',
			'HTTP_SEC_FETCH_SITE' => 'none',
			'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
			'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36',
			'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
			'HTTP_CONNECTION' => 'keep-alive',
			'CONTENT_LENGTH' => '',
			'CONTENT_TYPE' => '',
			'slim.url_scheme' => 'https',
			'slim.input' => '',
			'slim.request.query_hash' => [
				'causeVariableName' => 'Fluoxetine',
				'effectVariableName' => 'Overall Mood',
				'clientId' => 'quantimodo',
				'includeCharts' => 'true',
				'platform' => 'web',
				'studyId' => 'cause-93614-effect-1398-population-study',
			],
			'responseStatusCode' => 200,
			'unixtime' => 1582683044,
			'requestDuration' => 0,
		];
		/** @var QMStudy $responseBody */
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->compareHtmlPage("fullStudyHtml", $responseBody->studyHtml->fullStudyHtml);
		$this->compareObjectFixture("studyText", $responseBody->studyText);
		$this->checkTestDuration(19);
		$this->checkQueryCount(17);
	}
}
