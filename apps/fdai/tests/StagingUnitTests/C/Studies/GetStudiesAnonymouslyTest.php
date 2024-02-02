<?php /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\C\Studies;
use App\Computers\ThisComputer;
use App\Logging\QMLog;
use App\Models\UserVariableRelationship;
use App\Studies\StudyListResponseBody;
use Tests\SlimStagingTestCase;
class GetStudiesAnonymouslyTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
	public function testGetStudiesAnonymously(): void{
		$expectedString = '';
        /** @var StudyListResponseBody $responseBody */
        $responseBody = $this->callAndCheckResponse($expectedString);
		$studies = $responseBody->studies;
		$this->assertCount(10, $studies);
        $titles = [];
		foreach($studies as $study){
		    QMLog::info($study->title);
            $stats = $study->statistics;
            $titles[] = $study->title;
            $numberOfUserVariableRelationships = UserVariableRelationship::whereCauseVariableId($study->causeVariableId)
                ->where(UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $study->effectVariableId)
                ->count();
            $this->assertGreaterThan(1, $numberOfUserVariableRelationships,
                $study->title." only has $numberOfUserVariableRelationships user variable relationships");
        }
		$this->assertArrayEquals(array (
            0 => 'Higher Daily Step Count Predicts Very Slightly Higher Overall Mood for Population',
            1 => 'Higher Calories Burned Predicts Slightly Lower Guiltiness for Population',
            2 => 'Higher Daily Step Count Predicts Slightly Lower Shame for Population',
            3 => 'Higher Daily Step Count Predicts Slightly Lower Guiltiness for Population',
            4 => 'Higher Polyunsaturated Fat Intake Predicts Very Slightly Lower Overall Mood for Population',
            5 => 'Higher Multivitamin Predicts Slightly Higher Overall Mood for Population',
            6 => 'Higher Walk Or Run Distance Predicts Very Slightly Higher Overall Mood for Population',
            7 => 'Higher Walk Or Run Distance Predicts Very Slightly Lower Guiltiness for Population',
            8 => 'Higher Meditation Predicts Very Slightly Lower Irritability for Population',
            9 => 'Higher Carbs Intake Predicts Very Slightly Higher Guiltiness for Population',
        ), $titles);
		$this->checkTestDuration(78);
		$this->checkQueryCount(15);
	}
	public $expectedResponseSizes = [
        'studies'       => 2082.109,
        //'principalInvestigator' => 0.002,
        'ionIcon'       => 0.022,
        'image'         => 0.142,
        'startTracking' => 0.291,
        'description'   => 0.062,
        'title'         => 0.023,
        'html'          => ['min' => 20, 'max' => 100],
        'success'       => 0.004,
        'status'        => 0.009,
        'code'          => 0.006,
        'summary'       => 0.023,
        //'errors' => 0.006,
        //'sessionTokenObject' => 0.002,
        'avatar'        => 0.055,
    ];
	public $slimEnvironmentSettings = [
        'REQUEST_METHOD' => 'GET',
        'REMOTE_ADDR' => '10.0.2.2',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v3/studies',
        'SERVER_NAME' => ThisComputer::LOCAL_HOST_NAME,
        'SERVER_PORT' => '443',
        'HTTP_COOKIE' => '_ga=GA1.2.886244037.1556757156; _gid=GA1.2.232072824.1556757156; __cfduid=dd245b758149edc7ab2f862c0dd3f81151556757300',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Safari/537.36',
        'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
        'HTTP_CACHE_CONTROL' => 'no-cache',
        'HTTP_PRAGMA' => 'no-cache',
        'HTTP_CONNECTION' => 'keep-alive',
        'CONTENT_LENGTH' => '',
        'CONTENT_TYPE' => '',
        'slim.url_scheme' => 'https',
        'slim.input' => '',
        'slim.request.query_hash' =>
          [
            'clientId' => 'quantimodo',
            'platform' => 'web',
            'limit' => '10',
          ],
        'responseStatusCode' => 200,
        'unixtime' => 1556757394,
        'requestDuration' => 18.813420057296753,
    ];
}
