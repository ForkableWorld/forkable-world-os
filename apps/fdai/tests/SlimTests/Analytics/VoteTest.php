<?php /** @noinspection PhpMissingReturnTypeInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Analytics;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\TreatmentsCommonVariables\BupropionSrCommonVariable;
use App\Models\Vote;
class VoteTest extends \Tests\SlimTests\SlimTestCase {
	protected function setUp(): void{
		parent::setUp();
		Vote::deleteAll();
	}
    private $userId = 1;
    // Users should be able to thumbs up a correlation which stores a user vote of 1.
    /**
     * @param $postData
     * @return void
     */
    private function postVoteAndCheckResponse($postData): void{
        $this->postApiV3('votes', json_encode($postData));
    }
    /**
     * Update Vote Test
     *
     * @group api
     */
    public function testUpdateVotes()
    {
        $this->setAuthenticatedUser($userId = 1);
	    $effect = OverallMoodCommonVariable::NAME;
	    $cause = BupropionSrCommonVariable::NAME;
	    $postData = ['effectVariableName' => $effect, 'causeVariableName' => $cause, 'vote' => 0,];
        $this->postVoteAndCheckResponse($postData);
	    $voteQB = Vote::whereUserId($this->userId)
	                  ->where('cause_variable_id', BupropionSrCommonVariable::ID)
	                  ->where('effect_variable_id', OverallMoodCommonVariable::ID);
	    $vote = $voteQB->first();
	    $this->assertEquals(0, $vote->value);
        $postData = ['effectVariableName' => $effect, 'causeVariableName' => $cause, 'vote' => 1,];
        $this->postVoteAndCheckResponse($postData);
	    $vote = $voteQB->first();
        $this->assertEquals(1, $vote->value);
    }

}
