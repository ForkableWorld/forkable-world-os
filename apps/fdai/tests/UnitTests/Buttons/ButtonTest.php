<?php /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Buttons;
use App\Buttons\Analyzable\DataLabFailedAnalysesButton;
use App\Buttons\RelationshipButtons\User\UserMeasurementsButton;
use App\Buttons\States\StudyJoinStateButton;
use App\Charts\BarChartButton;
use App\Models\Connection;
use App\Models\TrackingReminder;
use App\Models\User;
use App\Slim\Model\User\QMUser;
use App\Storage\QueryBuilderHelper;
use App\Studies\QMCohortStudy;
use App\UI\FontAwesome;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\PhysiqueCommonVariables\BodyWeightCommonVariable;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests
 */
class ButtonTest extends UnitTestCase
{
	/**
	 * @throws \Throwable
	 * @covers \App\Charts\BarChartButton::getTestHtml
	 */
	public function testBarChartButton(){
        $html = BarChartButton::getTestHtml();
        $url = \App\Utils\Env::getAppUrl();
        $this->compareHtmlFragment('button', $html,
            "PDF button changed! Verify at
                $url/html
                $url/pdf
            and update this test with new PDF contents if it looks good
            Start Selection>$html<End Selection ");
        $this->assertTrue(true);
    }

    /**
     * @covers \App\Buttons\States\StudyJoinStateButton
     */
	public function testJoinStudyButton(){
        $s = QMCohortStudy::findOrNewQMStudy(BodyWeightCommonVariable::NAME, OverallMoodCommonVariable::NAME, 1);
        $b = new StudyJoinStateButton($s);
        $this->assertContains('join-study-button', $b->getRoundOutlineWithIcon());
    }
	/**
	 * @covers \App\Buttons\Analyzable\DataLabFailedAnalysesButton
	 */
	public function testFailedAnalysisButton(){
        $b = new DataLabFailedAnalysesButton(User::TABLE);
        $progressBox = $b->getProgressBoxWidget();
        $this->assertContains(QueryBuilderHelper::HAVE_AN_ERROR, $progressBox->getHumanizedWhereString());
        $html = $progressBox->getHtml();
        $this->assertContains(QueryBuilderHelper::HAVE_AN_ERROR, $html);
    }
	/**
	 * @covers \App\Buttons\RelationshipButtons\User\UserMeasurementsButton
	 */
	public function testUserMeasurementsButton(){
        $u = QMUser::testUser();
	    Connection::truncate();
        $measurements = $this->createMoodMeasurements($u->id);
        $u->analyzeFullyAndSave(__FUNCTION__);
        $b = new UserMeasurementsButton($u->l());
        $link = $b->getLink();
        $this->assertContains((string)count($measurements), $link);
    }
	public function testIndexButton(){
        $params[TrackingReminder::FIELD_USER_ID] = 1;
        $number = 5;
        $b = TrackingReminder::generateDataLabIndexButton($params, $number, "Reminders",
            FontAwesome::BELL, "See Variables for Which You Have Created Manual Tracking Reminders");
        $this->assertNotNull($b->fontAwesome, "No icon for $b->title");
        $box = $b->getStatBox();
        $this->assertContains(FontAwesome::BELL, $box);
    }
}
