<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\C\Studies\UserStudies;
use App\Properties\User\UserIdProperty;
use App\Utils\APIHelper;
use App\Files\FileHelper;
use App\Utils\QMProfile;
use App\Reports\StudyReport;
use App\Studies\QMStudy;
use App\Studies\QMUserStudy;
use App\Slim\Model\User\QMUser;
use Tests\SlimStagingTestCase;
class SendUserStudyEmailTest extends SlimStagingTestCase {
    public const JOB_NAME = "Production-C-phpunit";
    private const CAUSE_NAME = "Pickles, Cucumber, Dill Or Kosher Dill";
    private const EFFECT_NAME = 'Overall Mood';
    public function testSendUserStudyEmail(): void{
        if($this->skipIfQueued(static::JOB_NAME)){return;}
        $study = QMUserStudy::getStudyIfExists(self::CAUSE_NAME, self::EFFECT_NAME,
            UserIdProperty::USER_ID_MIKE);
        $r = $study->getReport();
        $this->assertEquals("cause-5438411-effect-1398-user-230-user-study-study-report.pdf",
            $r->getFileName(FileHelper::TYPE_PDF));
        $studyHtml = $study->getStudyHtml();
        $fullStudyHtml = $studyHtml->generateFullPageWithHead();
        $this->checkStudyHtmlMetaTags($fullStudyHtml, $study);
        $links = $study->getStudyLinks();
        $static = $links->getStudyLinkStatic();
        //$this->checkProductionStudyTags($static); // Uncomment after releasing fix
        //$this->profilePDF($r);
        $result = $study->email();
        $this->assertNotNull($result);
        $u = QMUser::findInDB(230);
        $this->assertGreaterThan(time() - 30, strtotime($u->lastEmailAt));
    }
    /**
     * @param string $fullStudyHtml
     * @param QMStudy $study
     */
    private function checkStudyHtmlMetaTags(string $fullStudyHtml, QMStudy $study): void{
        $this->assertHtmlHeadContains("<meta property=\"fb:app_id\" content=\"225078261031461\">", $fullStudyHtml);
        $tag = $study->getStudyText()->getTagLine();
        //$this->assertHtmlHeadContains("<meta property=\"og:description\" content=\"$tag\">", $fullStudyHtml);
        $url = $study->getStudyLinks()->getStudyLinkStatic();
        $this->assertHtmlHeadContains("<meta property=\"og:url\" content=\"$url\">", $fullStudyHtml);
    }
    /**
     * @param StudyReport $r
     */
    private function profilePDF(StudyReport $r): void{
        $path = $r->getDemoS3FilePath('pdf');
        $r->generateAndUploadPdf();
    }
    /**
     * @param string $url
     * @param QMStudy $study
     */
    private function checkProductionStudyTags(string $url, QMStudy $study): void{
        $staticHtml = APIHelper::getRequest($url);
        $this->checkStudyHtmlMetaTags($staticHtml, $study);
    }
}
