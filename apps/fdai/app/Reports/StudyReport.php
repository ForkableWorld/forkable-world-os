<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Reports;
use App\Charts\HighchartExport;
use App\Models\Study;
use App\Models\User;
use App\Traits\HasCauseAndEffect;
use App\UI\CssHelper;
use App\Types\TimeHelper;
use Illuminate\View\View;
use App\Studies\QMStudy;
class StudyReport extends AnalyticalReport {
    public const DISABLED_FILE_TYPES = [
        self::FILE_TYPE_XLS,
        self::FILE_TYPE_CSV
    ];
    public const CSS_PATHS = [
        //"https://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.no-icons.min.css",
        //"https://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css",
        //"https://static.quantimo.do/lib/sortable/css/sortable-theme-minimal.css",
        CssHelper::MATERIAL_ICONS,
        CssHelper::FA_5,
        CssHelper::FA_4,
        CssHelper::CREATIVE_TIM,
        self::PRODUCTION_CSS_URL."material-card.css",
        self::PRODUCTION_CSS_URL."medium-study.css",
        self::PRODUCTION_CSS_URL."statistics-table.css",
    ];
    public const FORMAT_LAST_MODIFIED_AT = "2019-10-27";
    /** @var Study */
    private $study;
    /**
     * StudyReport constructor.
     * @param Study $study
     */
    public function __construct(Study $study) {
        $this->study = $study;
        $this->setUserId($study->getUserId());
    }
    /**
     * @return string
     */
    public function getCoverImage(): string{
        return $this->getStudy()->getImage();
    }
    /**
     * @return void
     */
    public function generatePDF(): void {
        $this->setWriteToPdf(true);
        $study = $this->getStudy();
        $title = $study->getTitleAttribute();
        $this->addHeaderNewPageTOCIndexEntry($title, 0);
        $h = $study->getStudyHtml();
        $this->addCoverPage();
        $this->addTableOfContents();
        $this->addHeaderNewPageTOCIndexEntry("Charts", 1);
        $this->addHeaderNewPageTOCIndexEntry("Relationship Charts", 2);
        $this->addHtml($h->getCorrelationChartHtmlWithEmbeddedImageOrReasonForFailure(HighchartExport::PNG));
        $this->addHeaderNewPageTOCIndexEntry($study->getCauseVariableName()." Charts", 2);
        $this->addHtml($h->getEmbeddedCauseChartsOrButton('png'));
        $this->addHeaderNewPageTOCIndexEntry($study->getEffectVariableName()." Charts", 2);
        $this->addHtml($h->getEmbeddedEffectChartsOrButton('png'));
        $this->addHtml($h->getStudyTextHtml());
        $sections = $this->getStudy()->getStudySectionsArray();
        foreach($sections as $section){
            $this->addHeaderNewPageTOCIndexEntry($section->title, 1);
            $this->addHtml('<div class="study-section-body">'.$section->body.'</div>');
        }
        $this->addHeaderNewPageTOCIndexEntry("Statistics", 1);
        $this->addHtml($h->getStatisticsTable());
        $this->addHeaderNewPageTOCIndexEntry("Participant Instructions", 1);
        $this->addHtml($h->getParticipantInstructionsHtml());
        $this->addHeaderNewPageTOCIndexEntry("Principal Investigator", 1);
        $this->addHtml($h->getPrincipalInvestigatorHtml());
        $this->addHtml($h->getOrAddSocialSharingButtons());
        $this->addHtml($h->getDownloadButtons());
        $date = TimeHelper::getIso8601UtcDateTimeString();
        $this->addHtml("<p>Publish Date: $date</p>");
        $this->addIndex();
    }
    /**
     * @return string
     */
    public function generateBodyHtml(): string {
        $this->setWriteToPdf(false);
        $study = $this->getStudy();
        $studyHtml = $study->getStudyHtml();
        $html = $studyHtml->getShowContent();
        return $html;
    }
    /**
     * @return string
     */
    public function getTitleAttribute(): string {
        $study = $this->getStudy();
        return $study->getTitleAttribute();
    }
    /**
     * @return string
     */
    public function getSubtitleAttribute(): string {
        $study = $this->getStudy();
        return $study->getStudyText()->getTagLine();
    }
    /**
     * @return Study
     */
    public function getStudy(): Study {
        return $this->study;
    }
    /**
     * @return array
     */
    protected function getSpreadsheetRows(): array {
        return $this->getSourceObject()->getSpreadsheetRows();
    }
    /**
     * @return QMStudy|HasCauseAndEffect
     */
    public function getSourceObject(): Study{
        return $this->getStudy();
    }
    protected function addCoverPage(): void{
        $study = $this->getStudy();
        $h = $study->getStudyHtml();
        $this->addHtml($h->getGaugeAndImagesWithTagLine());
        $this->addHtml($h->getOrAddSocialSharingButtons());
        $this->addHtml($h->getJoinStudyButtonHTML());
        //$this->addHtml($h->getInteractiveStudyButton()); // Don't want to overload servers and plenty of other buttons lead to app
    }
    /**
     * @return string
     */
    public function generateEmailBody(): string{
        return $this->getStudy()->getStudyHtml()->getTitleGaugesTagLineHeader(true, true);
    }
    /**
     * @return string
     */
    public function getPlainText(): string{
        return $this->getStudy()->getStudyText()->getPlainText();
    }
    /**
     * @param array $params
     * @return string
     */
    public function getUrl(array $params = []): string{
        return $this->getStudy()->getStudyLinks()->getStudyLinkStatic();
    }
    /**
     * @inheritDoc
     */
    public function getCategoryDescription(): string{
        return $this->getStudy()->getCategoryDescription();
    }
    /**
     * @inheritDoc
     */
    public function getCategoryName(): string{
        return $this->getStudy()->getCategoryName();
    }
    /**
     * @return string|null
     */
    public function getParentCategoryName(): ?string{
        return $this->getStudy()->getParentCategoryName();
    }
    static public function getDemoReport(): AnalyticalReport {
        return new self(User::mike()->getBestUserStudy());
    }
    public function getSlugWithNames(): string{
        return $this->getStudy()->getId();
    }
    public function getShowContentView(array $params = []): View{
        return $this->getStudy()->getShowContentView($params);
    }
    protected function getShowPageView(array $params = []): View{
        return $this->getStudy()->getShowPageView($params);
    }
    public static function getIndexPath(): string{
        return Study::getIndexPath();
    }
}
