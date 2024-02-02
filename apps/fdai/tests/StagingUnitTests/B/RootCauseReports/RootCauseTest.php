<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\B\RootCauseReports;
use App\Computers\ThisComputer;
use App\DevOps\XDebug;
use App\Exceptions\InvalidStringException;
use App\Properties\User\UserIdProperty;
use App\Reports\AnalyticalReport;
use App\Reports\RootCauseAnalysis;
use App\Slim\Model\User\QMUser;
use App\UI\CssHelper;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\QMUserVariable;
use Tests\QMBaseTestCase;
use Tests\SlimStagingTestCase;
class RootCauseTest extends SlimStagingTestCase{
    public function testRootCauseWithData(){
        $primaryOutcomeName = OverallMoodCommonVariable::NAME;
        $userId = UserIdProperty::USER_ID_INACTIVE;
        $user = QMUser::find($userId);
        $correlations = $user->getCorrelations();
        $this->assertGreaterThan(827, count($correlations));
//        $c = QMUserVariableRelationship::findByNamesOrIds($userId, "Sleep Quality", $primaryOutcomeName);
//        $c->analyzeFullyIfNecessaryAndSave(__FUNCTION__);
	    $user->primaryOutcomeVariableId = null;
        $primaryOutcome = $user->calculatePrimaryOutcomeVariable();
        $this->assertEquals($primaryOutcomeName, $primaryOutcome->name);
        $mikeAnalysis = $user->getRootCauseAnalysis();
        $emailHtml = $mikeAnalysis->generateEmailBody();
        $this->assertContains($primaryOutcomeName." Factor Analysis", $emailHtml);
        $this->assertContains("optimize your ".$primaryOutcomeName, $emailHtml);
        $this->assertContains("Studies from Your Data", $emailHtml);
        $this->assertStringContains($emailHtml, [
	        "Higher Sleep Duration Predicts ",
	        "Higher Outdoor Temperature Predicts ",
	        $primaryOutcomeName
        ],                                   QMBaseTestCase::getCurrentTestName() . "-" . "EXPECTED_STUDY_TITLES");
    }
    public function testRootCauseWithoutData(){
        $u = $this->getZain();
        $v = $u->getPrimaryOutcomeQMUserVariable();
        $mail = $v->email();
        $html = $mail->getHtmlContent();
        $this->checkEmailHtmlForUserWithoutData($html);
        $report = $v->getRootCauseAnalysis();
        $start = microtime(true);
        $this->checkEmailHtmlForUserWithoutData($emailHtml = $report->getOrGenerateEmailHtml());
        $duration = microtime(true) - $start;
        if(!XDebug::active()){
            $this->assertLessThan(60, $duration, "This should be cached and fast");
        }
        $this->checkFactorList($report, $v);
        //$this->checkMikeWpPost($report);
        $start = microtime(true);
		$report->deletePDF();
        $report->getDownloadOrCreateFile(AnalyticalReport::FILE_TYPE_PDF);
        $duration = microtime(true) - $start;
        //$this->assertLessThan(1, $duration, "This should be cached and fast");
        $this->checkSlackMessageAndAttachments($report);
    }
    /**
     * @param RootCauseAnalysis $attachment
     */
    protected function checkSlackMessageAndAttachments(RootCauseAnalysis $attachment): void{
        $attachments = $attachment->getSlackAttachments();
        $link = $attachments[0]->getTitleLink();
        $this->assertContains(AnalyticalReport::FILE_TYPE_EMAIL_HTML, $link);
        $buttons = $attachment->getButtons();
        $link = $buttons[0]->link;
        $this->assertContains(AnalyticalReport::FILE_TYPE_EMAIL_HTML, $link);
        $email = $attachment->email();
        $slackMessage = $email->slack(201);
        $host = (new ThisComputer)->getHost();
        $text = $slackMessage->getText();
        $this->assertContains($host, $text);
        $this->assertContains($attachment->getOutcomeVariableName(), $slackMessage->getText());
        //$this->assertContains($a->getSubtitleAttribute(), $slackMessage->getText());
        $attachments = $slackMessage->getAttachments();
        $titles = [];
        foreach($attachments as $attachment){
            $this->assertNotContains($attachment->getTitle(), $titles);
            $titles[] = $attachment->getTitle();
        }
    }
	/**
	 * @param string $emailHtml
	 * @throws InvalidStringException
	 */
    public function checkEmailHtmlForUserWithoutData(string $emailHtml): void{
        $this->assertStringContains($emailHtml, [
	        "Higher Sleep Quality Predicts ",
	        //"Higher Vegetable Skillet Consumption Predicts ",
	        "Overall Mood"
        ],                                   QMBaseTestCase::getCurrentTestName() . "-" . "EXPECTED_STUDY_TITLES");
        $this->assertNotContains("Confirmed Studies from Your Data", $emailHtml);
        $this->assertContains("<table ",
            $emailHtml,
            "There should be a table because we cannot use image buttons in email");
        $this->assertNotContains(CssHelper::CLASS_ROUNDED_BUTTON_WITH_IMAGE,
            $emailHtml,
            "There should be a table because we cannot use image buttons in email");
        $this->assertContains("SEE COMPLETE LIST OF FACTORS", $emailHtml);
        $this->assertContains('Below are the factors most significant for other users',
            $emailHtml,
            "Zain doesn't have any predictor measurements");
        $numberOfOccurrences = substr_count ( $emailHtml, "Want personalized results?" );
        $this->assertEquals(1, $numberOfOccurrences);
    }
    /**
     * @param RootCauseAnalysis $report
     * @param QMUserVariable $v
     */
    public function checkFactorList(RootCauseAnalysis $report, QMUserVariable $v): void{
        $factorList = $report->getDemoOrUserFactorListForEmailHtml();
        $this->assertContains($v->getDemoUserDisclaimerNotEnoughDataStartTrackingHtml(),
            $factorList,
            "Zain doesn't have any predictor measurements");
    }
    /**
     * @param RootCauseAnalysis $report
     */
    public function checkMikeWpPost(RootCauseAnalysis $report): void{
        $post = $report->firstOrCreateWpPost();
        //        $this->assertContains($report->getFullHtmlUrlLink(), $post->post_content);
        //        $this->assertContains($report->getPdfUrl(), $post->post_content);
        //        $this->assertContains($report->getEmailHtmlUrlLink(), $post->post_content);
        $vegetableSkillet = "https://app.quantimo.do/api/v2/study?causeVariableId=1668&effectVariableId=1398&userId=230&studyId=cause-1668-effect-1398-user-230-user-study";
        $html = $post->post_content;
        $this->assertNotContains("&amp;effectVariableId", $html, __FUNCTION__);
        $this->assertContains($vegetableSkillet, $html, 'post_content');
    }
    /**
     * @return QMUser
     */
    private function getZain(): QMUser{
        $u = QMUser::find(256);
        return $u;
    }
}
