<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\B\Reports;
use App\Files\FileHelper;
use App\Properties\User\UserIdProperty;
use App\Reports\GradeReport;
use App\Reports\RootCauseAnalysis;
use App\Slim\Model\User\PublicUser;
use App\Types\QMStr;
use App\Variables\CommonVariables\GoalsCommonVariables\DailyAverageGradeCommonVariable;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;
class DemoReportsTest extends SlimStagingTestCase {
    public const JOB_NAME = "Production-B-phpunit";
    public function testGenerateDemoGradeReport(){
		$u = PublicUser::find(UserIdProperty::USER_ID_IVY);
		$this->assertEquals("guski", $u->getDisplayNameAttribute());
        $v = QMUserVariable::findOrCreateByNameOrId(UserIdProperty::USER_ID_IVY,
            DailyAverageGradeCommonVariable::NAME);
        $this->assertEquals("user-variables/219174", $v->getShowFolderPath());
        $report = GradeReport::getDemoReport();
        $this->assertEquals("users/83110/grade-report", $report->getShowFolderPath());
        $variableName = DailyAverageGradeCommonVariable::NAME;
        $slug = QMStr::slugify($variableName);
        $this->assertEquals("$slug-for-guski-grade-report.pdf", $report->getFileName(FileHelper::TYPE_PDF));
        $this->assertEquals('https://staging.quantimo.do/users/83110/grade-report', $report->getUrl());
        $this->assertContains("The Last Daily Average Grade was ", $report->getSubtitleAttribute());
        $this->assertEquals(GradeReport::TITLE, $report->getTitleAttribute());
        $this->assertEquals(GradeReport::TITLE." for Guski",  $report->getTitleWithUserName());
        GradeReport::publishDemoReport();
        $this->assertEquals('https://staging.quantimo.do/demo/grade-report', $report->getDemoUrl());
    }
    public function testGenerateRootCauseDemoReport(){
        if($this->skipIfQueued(static::JOB_NAME)){return;}
        $report = RootCauseAnalysis::getDemoReport();
        $this->assertEquals("https://staging.quantimo.do/demo/root-cause-analysis/Overall_Mood", $report->getDemoUrl());
        $report = RootCauseAnalysis::publishDemoReport();
    }
}
