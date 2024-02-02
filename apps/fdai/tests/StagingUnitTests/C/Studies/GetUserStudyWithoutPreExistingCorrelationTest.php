<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\C\Studies;
use App\Computers\ThisComputer;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Files\TestArtifacts\TestQueryLogFile;
use App\Models\UserVariableRelationship;
use App\Models\User;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\User\UserIdProperty;
use App\Storage\Memory;
use App\Studies\QMUserStudy;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;
use Tests\Traits\TestsStudies;
class GetUserStudyWithoutPreExistingCorrelationTest extends SlimStagingTestCase {
	use TestsStudies;
    public const JOB_NAME = "Production-C-phpunit";
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testGetUserVariablesToCorrelateWith(){
        $cause = "Pickles, Cucumber, Dill Or Kosher Dill";
        $cause = UserVariable::findByNameOrId(UserIdProperty::USER_ID_MIKE, $cause);
        $effect = 'Overall Mood';
        $effect = UserVariable::findByNameOrId(UserIdProperty::USER_ID_MIKE, $effect);
        $c = QMUserVariableRelationship::findOrCreate(UserIdProperty::USER_ID_MIKE, $cause->name,
            $effect->name);
        $sentence = $c->getTagLine();
        $u = User::mike();
        $qb = $effect->userVariableIdsToCorrelateWithQB();
		$plucked = $qb->pluck(UserVariable::FIELD_VARIABLE_ID);
        $variableIds = $plucked->all();
        $this->assertContains($cause->variable_id, $variableIds,
            "getUserVariablesToCorrelateWith not working! ID for pickles not present. ");
        $names = Variable::whereIn(Variable::FIELD_ID, $variableIds)->pluck('name')->all();
        $this->assertContains($cause->name, $names,
            "getUserVariablesToCorrelateWith not working! NAME pickles not present. ");
    }
    public function TODOTestCorrelationTagLineForCountOutcome(){
        $cause = "Pickles, Cucumber, Dill Or Kosher Dill";
        $cause = QMUserVariable::getByNameOrId(UserIdProperty::USER_ID_MIKE, $cause);
        $effect = 'Number of Farts';
        $effect = QMUserVariable::getByNameOrId(UserIdProperty::USER_ID_MIKE, $effect);
        $c = QMUserVariableRelationship::findOrCreate(UserIdProperty::USER_ID_MIKE, $cause->name,
            $effect->name);
        $c->analyzeFully(__FUNCTION__);
        $this->assertLessThan(0, $c->effectFollowUpPercentChangeFromBaseline);
        $sentence = $c->getTagLine();
        $this->assertContains(" higher following ", $sentence);
        $this->compareHtmlPage("DynamicContent", $c->getShowContent());
    }
    public function testDeleteCorrelationAndGetStudy(): void{
        $causeSynonym = "BMI";
        $effectName = "Overall Mood";
        $cause = QMCommonVariable::findByNameIdOrSynonym($causeSynonym);
        $causeName = $cause->name;
        $userId = UserIdProperty::USER_ID_INACTIVE;
        $this->setQueryParam('causeVariableName', $causeName);
        $this->setQueryParam('effectVariableName', $effectName);
        $this->setQueryParam('userId', $userId);
        $this->setAccessTokenByUserId($userId);
        $this->assertNotNull($cause, "Could not find cause");
        QMUserStudy::deleteStudyAndCorrelation($causeName, $effectName, $userId);
        $cause = Variable::findByNameOrId($causeName);
        $effect = Variable::findByNameOrId($effectName);
        $correlations = UserVariableRelationship::whereCauseVariableId($cause->id)
            ->where(UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $effect->id)
            ->where(UserVariableRelationship::FIELD_USER_ID, $userId)
            ->withTrashed()
            ->get();
        $this->assertCount(0, $correlations);
        Memory::flush();
		$responseBody = $this->callAndCheckResponse($effectName);
        /** @var QMUserStudy $study */
        $study = $responseBody;
        $this->assertEquals($userId, $study->userId);
		$this->compareStudyHtml($study->studyHtml->fullStudyHtml, 'non-existent-study');
		$this->checkTestDuration(18);
		$this->checkQueryCount(51);
		TestQueryLogFile::resetQueryCount();
		$this->resetTestStartTime();
        /** @var QMUserStudy $existingResponse */
        $existingResponse = $this->callAndCheckResponse($effectName);
        $html = $existingResponse->studyHtml;
        $this->compareObjectFixture('participantInstructions', $existingResponse->participantInstructions);
	    $this->compareObjectFixture('studyLinks', $existingResponse->studyLinks);
        // Chart changes every day from new data.  Just compare HTML stuff in UnitTests
        //$this->compareStudyHtml($html->fullStudyHtml, 'existing-study');
        $this->checkTestDuration(10);
        $this->checkQueryCount(18);
	}
	public $expectedResponseSizes = [
        //'pairs'                   => 0.006,
        'userId'                  => 0.006,
        'causeVariable'           => 17,
        'causeVariableName'       => 0.05,
        'effectVariable'          => [
            'max' => 333,
            'min' => 17
        ],
        'effectVariableName'      => 0.02,
        //'errorMessage' => 0.002,
        'id'                      => 0.053,
        'joined'                  => 0.004,
        'participantInstructions' => 23,
        //'publishedAt' => 0.002,
        'principalInvestigator'   => 0.56,
        'statistics'              => ['min' => 14, 'max' => 400],
        'studyCard'               => 18.248,
        'studyCharts'             => 250,
        'studyHtml'               => ['min' => 250, 'max' => 1333],
        'studyImages'             => 1.413,
        'studyLinks'              => 2.833,
        'studySharing'            => 0.25,
        'studyVotes'              => 0.023,
        'title'                   => 0.1,
        //'trackingReminderNotifications' => 0.002,
        //'trackingReminders' => 0.002,
        'type'                    => 0.018,
        //'userVote' => 0.002,
        //'wpPostId' => 0.002,
        'isPublic'                => 0.004,
        'success'                 => 0.004,
        'status'                  => 0.009,
	];
	public $slimEnvironmentSettings = [
  'REQUEST_METHOD' => 'GET',
  'REMOTE_ADDR' => '192.168.10.1',
  'SCRIPT_NAME' => '',
  'PATH_INFO' => '/api/v4/study',
  'SERVER_NAME' => ThisComputer::LOCAL_HOST_NAME,
  'SERVER_PORT' => '443',
  'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
  'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
  'HTTP_REFERER' => 'https://dev-web.quantimo.do/',
  'HTTP_SEC_FETCH_SITE' => 'same-site',
  'HTTP_X_FRAMEWORK' => 'ionic',
  'HTTP_X_PLATFORM' => 'web',
  'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36',
  'HTTP_X_CLIENT_ID' => 'quantimodo',
  'HTTP_ACCEPT' => 'application/json',
  'HTTP_CONTENT_TYPE' => 'application/json',
  'HTTP_AUTHORIZATION' => null,
  'HTTP_X_APP_VERSION' => '2.9.902',
  'HTTP_ORIGIN' => 'https://dev-web.quantimo.do',
  'HTTP_X_TIMEZONE' => 'America/Chicago',
  'HTTP_SEC_FETCH_MODE' => 'cors',
  'HTTP_CACHE_CONTROL' => 'no-cache',
  'HTTP_PRAGMA' => 'no-cache',
  'HTTP_CONNECTION' => 'keep-alive',
  'CONTENT_LENGTH' => '',
  'CONTENT_TYPE' => 'application/json',
  'slim.url_scheme' => 'https',
  'slim.input' => '',
  'slim.request.query_hash' =>
  [
    'causeVariableName' =>  null,
    'effectVariableName' => null,
    'clientId' => 'quantimodo',
    'includeCharts' => 'true',
    'platform' => 'web',
    //'recalculate' => 'true',
    //'studyId' => 'cause-6054148-effect-1398-user-230-user-study',
  ],
  'responseStatusCode' => 200,
  'unixtime' => 1567446746,
  'requestDuration' => 8.888665199279785,
	];
}
