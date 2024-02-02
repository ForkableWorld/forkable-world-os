<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */ /** @noinspection DisconnectedForeachInstructionInspection */
namespace App\Reports;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\ModelValidationException;
use App\Mail\QMSendgrid;
use App\Mail\TooManyEmailsException;
use App\Models\User;
use App\Models\Variable;
use App\Models\Vote;
use App\Models\WpPost;
use App\Properties\User\UserIdProperty;
use App\Properties\Variable\VariableIdProperty;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\NoEmailAddressException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UserVariableNotFoundException;
use App\Utils\AppMode;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Mail\RootCauseAnalysisEmail;
use App\Types\TimeHelper;
use App\UI\CssHelper;
use App\UI\ImageUrls;
use App\Logging\QMLog;
use App\Studies\QMStudy;
use App\Slim\Model\User\QMUser;
use App\VariableCategories\EmotionsVariableCategory;
use App\VariableCategories\EnvironmentVariableCategory;
use App\VariableCategories\FoodsVariableCategory;
use App\VariableCategories\NutrientsVariableCategory;
use App\VariableCategories\PhysicalActivityVariableCategory;
use App\VariableCategories\PhysiqueVariableCategory;
use App\VariableCategories\SleepVariableCategory;
use App\VariableCategories\SymptomsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
use App\VariableCategories\VitalSignsVariableCategory;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\QMUserVariable;
use SendGrid\Mail\TypeException;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
class RootCauseAnalysis extends AnalyticalReport {
    public const TITLE = null;
    protected $abbreviatedFactorList;
    protected $correlations;
    protected $demoReport;
    protected $outcomeVariable;
    protected $outcomeVariableId;
    protected $maximumCorrelations = 0;
    protected $predictorVariableCategoryName;
    protected $rows;
    private $upVoted;
    private $nonVoted;
    public $includeFlagged = true;
    public const MAX_CORRELATIONS_FOR_EMAIL = 10;
    protected const DISABLED_FILE_TYPES = [self::FILE_TYPE_XLS];
    public const FORMAT_LAST_MODIFIED_AT = "2019-10-16T22:38:51+0000";
    protected static $variableCategoryNames = [
        TreatmentsVariableCategory::NAME,
        FoodsVariableCategory::NAME,
        EnvironmentVariableCategory::NAME,
        NutrientsVariableCategory::NAME,
        PhysicalActivityVariableCategory::NAME,
        SleepVariableCategory::NAME,
        EmotionsVariableCategory::NAME,
        SymptomsVariableCategory::NAME,
        VitalSignsVariableCategory::NAME,
        //ActivitiesVariableCategory::NAME,
        PhysiqueVariableCategory::NAME,
        //PaymentsVariableCategory::NAME,
        //LocationsVariableCategory::NAME
    ];
	/**
	 * FailureAnalysis constructor.
	 * @param $outcomeVariableNameOrId
	 * @param int $userId
	 */
    public function __construct($outcomeVariableNameOrId, int $userId){
        $this->setUserId($userId);
        if(is_string($outcomeVariableNameOrId)){
            $outcomeVariableNameOrId = VariableIdProperty::fromName($outcomeVariableNameOrId);
        }
        $this->setOutcomeVariableId($outcomeVariableNameOrId);
    }
    public static function generateDemoRootCauseReportsForAllOutcomes(): void{
        $u = QMUser::mike();
        $outcomes = $u->getOutcomeVariablesWithData();
        foreach($outcomes as $outcome){
            /** @noinspection PhpUnhandledExceptionInspection */
            $outcome->email();
        }
    }
    /**
     * @param string $html
     * @throws InvalidStringException
     */
    public static function validateFactorsList(string $html): void{
        $needle = "Want personalized results?";
        $numberOfOccurrences = substr_count($html, $needle);
        if($numberOfOccurrences > 1){
            throw new InvalidStringException("Says $needle $numberOfOccurrences times!", $html,
                __FUNCTION__);
        }
    }
    /**
     * @return QMUserVariableRelationship[]
     */
    public function recalculate(): array {
        $v = $this->getOutcomeQMUserVariable();
        try {
            return $v->correlate();
        } catch (TooSlowToAnalyzeException $e) {
            le($e);
        }
    }
    /**
     * @return int
     */
    protected function getOutcomeVariableId(): int {
        return $this->outcomeVariableId;
    }
    /**
     * @param int $outcomeVariableId
     */
    private function setOutcomeVariableId(int $outcomeVariableId): void{
        $this->outcomeVariableId = $outcomeVariableId;
    }
    /**
     * @return QMUserVariable
     */
    public function getOutcomeQMUserVariable(): QMUserVariable {
        if(!$this->outcomeVariable){
            $outcomeVariable = QMUserVariable::findUserVariableByNameIdOrSynonym($this->getUserId(), $this->getOutcomeVariableId());
            $this->outcomeVariable = $outcomeVariable;
        }
        return $this->outcomeVariable;
    }
    /**
     * @param string $variableCategoryName
     * @return RootCauseAnalysisSection
     */
    public function getCategorySection(string $variableCategoryName): RootCauseAnalysisSection{
        // Can't use static here or it messes up sequential reports
        //if(isset(self::$sections[$variableCategoryName])){return self::$sections[$variableCategoryName];}
        if(isset($this->sections[$variableCategoryName])){return $this->sections[$variableCategoryName];}
        $className = "App\Reports\RootCauseAnalysis\Sections\\".str_replace(" ", "", $variableCategoryName).'Section';
        if(class_exists($className)){
            /** @var RootCauseAnalysisSection $section */
            $section = new $className($this);
        } else {
            $section = new RootCauseAnalysisSection($this, $variableCategoryName);
        }
        return $this->sections[$variableCategoryName] = $section;
    }
    /**
     * @return string
     */
    public function generateBodyHtml(): string {
        $this->setWriteToPdf(false);
        $this->bodyHtml = null;
        $this->addCoverPage();
        $this->addTableOfContents();
        $this->addIntroduction();
        $this->addYourDataSection();
        $this->addOutcomeOverview();
        $this->addDefinitionsSection();
        $this->addCategorySections();
        if ($this->includeFlagged) {
            $this->addFlaggedSections();
        }
        return $this->bodyHtml;
    }
    /**
     * @return void
     */
    public function generatePDF(): void {
        $this->setWriteToPdf(true);
        $this->logInfo(__FUNCTION__);
        $this->addCoverPage();
        $this->addTableOfContents();
        $this->addIntroduction();
        $this->addYourDataSection();
        $this->addOutcomeOverview();
        $this->addDefinitionsSection();
        $this->addCategorySections();
        if($this->includeFlagged){$this->addFlaggedSections();}
        $this->appendExistingStudyPDFS();
        $this->addIndex();
    }
    /**
     * @return string
     */
    public function getTitleAttribute(): string {
        // Don't use name to reduce risk of HIPAA violation or in case it's being used as a demo
        return $this->getOutcomeQMUserVariable()->getOrSetVariableDisplayName()." Factor Analysis";
    }
    /**
     * @return RootCauseAnalysisEmail
     */
    public function email(): QMSendgrid{
        $email = new RootCauseAnalysisEmail($this);
        try {
            $email->send();
        } catch (TooManyEmailsException | TypeException $e) {
            le($e);
        }
        return $email;
    }
    /**
     * @return string
     */
    public function getLogMetaDataString(): string {
        return "root-cause-analysis-".$this->getOutcomeQMUserVariable()->getLogMetaDataSlug();
    }
    /**
     * @return string
     */
    public function getSubtitleAttribute(): string {
        return "This report is intended to help you and your physician to gain insight into the root causes and ".
            "effective solutions to help you optimize your ".$this->getOutcomeQMUserVariable()->getOrSetVariableDisplayName().". ";
    }
    /**
     * @inheritDoc
     */
    public function getCategoryDescription(): string{
        return "Analysis of the factors most likely to improve or worsen an outcome of interest";
    }
    /**
     * @return string
     */
    public function getOutcomeVariableName(): string {
        return $this->getOutcomeQMUserVariable()->getOrSetVariableDisplayName();
    }
    protected function addFlaggedSections(){
        $this->addHeaderNewPageTOCIndexEntry("Flagged or Erroneous Studies", 1, Vote::THUMB_DOWN_BLACK_IMAGE_16);
        foreach (self::$variableCategoryNames as $categoryName) {
            $section = $this->getCategorySection($categoryName);
            $this->addFlaggedTablesSectionsToPDF($section);
        }
    }
    /**
     * @param RootCauseAnalysisSection $section
     */
    public function addSectionHeaderAndImage(RootCauseAnalysisSection $section): void {
        $this->addHeaderNewPageTOCIndexEntry($section->getTitleAttribute(), 1);
        $introductorySentenceHTML = $section->getIntroductorySentenceHTML();
        if(empty($introductorySentenceHTML)){
            $section->logError("No introductory sentence HTML!");
        } else {
            $this->addHtml($introductorySentenceHTML);
        }
        $imgHtml = $section->getImageHTML();
        if(empty($imgHtml)){
            $section->logError("No image HTML!");
        } else {
            $this->addHtml($imgHtml);
        }
    }
    protected function addCategorySections() {
        foreach (self::$variableCategoryNames as $categoryName) {
            $section = $this->getCategorySection($categoryName);
            $this->addSectionHeaderAndImage($section);
            $this->addPositiveUpVotedSection($section);
            $this->addNegativeUpVotedSection($section);
            $this->addNeedsReviewSections($section);
        }
    }
    protected function addOutcomeOverview(){
        $this->addVariableOverview($this->getOutcomeQMUserVariable());
    }
    protected function appendExistingStudyPDFS(): void{
        $titlePaths = QMStudy::getExistingStudyPDFsForUser($this->getUserId(), $this->getOutcomeVariableName());
        if(!$titlePaths){return;}
        $mpdf = $this->getMpdf();
        $mpdf->Header("");
        $mpdf->TOC_Entry("Some Full Studies", 0);
        foreach($titlePaths as $title => $path){
            $mpdf->IndexEntry($title);
            $mpdf->TOC_Entry($title, 1);
            try {
                $pageCount = $mpdf->SetSourceFile($path);
            } catch (PdfParserException $e) {
                le($e);
            }
            for ($i=1; $i<= $pageCount; $i++) {
                $mpdf->AddPage();
                $mpdf->Header("");
                try {
                    $import_page = $mpdf->ImportPage($i);
                } catch (CrossReferenceException | PdfTypeException $e) {
                    le($e);
                }
                $mpdf->UseTemplate($import_page);
            }
        }
    }
    /**
     * @return RootCauseAnalysis
     */
    public function getDemoRootCauseAnalysis(): RootCauseAnalysis{
        if($this->demoReport){return $this->demoReport;}
        $u = $this->getDemoUser();
        $v = $u->findOrCreateQMUserVariable($this->getOutcomeQMUserVariable()->getVariableIdAttribute());
        return $this->demoReport = $v->getRootCauseAnalysis();
    }
    public function uploadDynamicHtml(){
        $html = $this->getShowContent();
        $this->generateAndUploadHtmlAndPost();
    }
    public function getShowContent(bool $inlineJs = false): string{
        return $this->getBody($inlineJs);
    }
    /**
     * @return array
     */
    protected function getSpreadsheetRows(): array{
        $allSections = [];
        $sections = $this->getSections();
        foreach($sections as $section){
            $rows = $section->getSpreadsheetRows();
            $allSections = array_merge($allSections, $rows);
        }
        return $allSections;
    }
    /**
     * @return array
     */
    public function getSections(): array {
        $sections = [];
        foreach(self::$variableCategoryNames as $name){
            $sections[] = $this->getCategorySection($name);
        }
        return $sections;
    }
    /**
     * @return int
     */
    public static function generateForPrimaryOutcomeForAllUsers(): int {
        QMLog::infoWithoutObfuscation(__FUNCTION__);
        $users = QMUser::getAll();
        $count = 0;
        foreach($users as $u){
            if($u->isTestUser()){continue;}
            if($u->getId() < 4){continue;}
            //if($u->getId() < 3183){continue;}  // UNCOMMENT FOR DEBUGGING
            try {
                $u->getEmail();
            } catch (NoEmailAddressException $e){
                QMLog::info(__METHOD__.": ".$e->getMessage());
                continue;
            } catch (InvalidEmailException $e) {
                QMLog::error(__METHOD__.": ".$e->getMessage());
                continue;
            }
            $lastRootCauseEmail = $u->getLastEmailedAt(RootCauseAnalysisEmail::getType());
            if(strtotime($lastRootCauseEmail) > time() - 7 * 86400){
                $u->logInfo("Skipping because last root cause email was ".
                    TimeHelper::timeSinceHumanString($lastRootCauseEmail).". ");
                continue;
            }
            $v = $u->getPrimaryOutcomeQMUserVariable();
            try {
                $v->email();
            } catch (NoEmailAddressException $e) {
                QMLog::infoWithoutObfuscation(__METHOD__.": ".$e->getMessage());
            } catch (InvalidEmailException $e) {
                le($e);
            }
            $a = $v->getRootCauseAnalysis();
            if ($a->existsAndNotExpired(self::FILE_TYPE_PDF)) {
                $a->logInfo("Already exists on S3 and not expired.");
            } else {
//                $reminders = $v->getTrackingRemindersForVariable();
//                if(!$reminders){
//                    $measurements = $v->getAllRawMeasurementsInCommonUnitInChronologicalOrder();
//                    if(!$measurements){$v->createTrackingReminder();}
//                }
                $a->getDownloadOrCreateFile(AnalyticalReport::FILE_TYPE_PDF);
            }
            $count++;
        }
        return $count;
    }
    /**
     * @return string
     */
    public function getDemoOrUserFactorListForEmailHtml(): string {
        if($this->abbreviatedFactorList){return $this->abbreviatedFactorList;}
        if($this->userHasCorrelations()){
            $list = $this->getUserFactorListEmailHtmlWithBestOfAllCategories();
        } else {
            $list = $this->getDemoFactorListEmailHtmlWithBestOfAllCategories();
        }
        $button = $this->getStaticHtmlButton("SEE COMPLETE LIST OF FACTORS  &rarr;");
        $maxWidth = CssHelper::GLOBAL_MAX_POST_CONTENT_WIDTH;
        $list = "
            <div style=\"max-width: ".$maxWidth."px; font-family: 'Source Sans Pro', sans-serif; margin: auto;\">
                    $list
                <div style=\"text-align: center;\">
                    $button
                </div>
            </div>
        ";
        try {
            self::validateFactorsList($list);
        } catch (InvalidStringException $e) {
            le($e);
        }
        return $this->abbreviatedFactorList = $list;
    }
    /**
     * @return string
     */
    public static function getUpVotedDescription(): string {
        $description =
            "These studies have been up-voted or suggesting the data has been reviewed and appears valid. " .
            "If any of these findings seem questionable, click the bar to see the full analysis where you can modify the " .
            "analysis settings or flag the study as flawed by down-voting it. ";
        return $description;
    }
    /**
     * @param RootCauseAnalysisSection $section
     */
    public function addFlaggedTablesSectionsToPDF(RootCauseAnalysisSection $section){
        $title = "Flagged or Flawed Studies of ".$section->getTitleAttribute();
        $this->addHeaderNewPageTOCIndexEntry($title, 3, Vote::THUMB_DOWN_BLACK_IMAGE);
        $this->addHtml($section->getFlaggedHtml());
    }
    /**
     * @param RootCauseAnalysisSection $section
     * @return void
     */
    public function addNeedsReviewSections(RootCauseAnalysisSection $section): void {
        $title = "Un-Reviewed Studies of " . $section->getTitleAttribute();
        $this->addHeaderNewPageTOCIndexEntry($title, 3, Vote::NO_VOTE_IMAGE);
        $this->addHtml($section->getNeedReviewHtml());
    }
    /**
     * @param RootCauseAnalysisSection $section
     * @param string|null $subSectionTitle
     * @param string|null $explanation
     * @return void
     */
    public function addPositiveUpVotedSection(RootCauseAnalysisSection $section,
                                              string $subSectionTitle = null,
                                              string $explanation = null): void {
        if(!$subSectionTitle){
            $subSectionTitle = $section->getPredictorVariableCategory()->getNameSingular() .
                "-Related Factors Predictive of <b>Higher</b> " .$this->getOutcomeVariableName();
        }
        $this->addHeaderNewPageTOCIndexEntry($subSectionTitle, 2, Vote::THUMB_UP_BLACK_IMAGE_16);
        $this->addHtml($section->getPositiveUpVotedSectionHtml($subSectionTitle, $explanation));
    }
    /**
     * @param RootCauseAnalysisSection $section
     * @param string|null $subSectionTitle
     * @return void
     */
    protected function addUncorrelatedSection(RootCauseAnalysisSection $section,
                                              string $subSectionTitle = null): void {
        $this->addHtml($section->getUncorrelatedSectionHtml($subSectionTitle));
    }
    /**
     * @param RootCauseAnalysisSection $section
     * @param string|null $subSectionTitle
     * @param string|null $explanation
     * @return void
     */
    public function addNegativeUpVotedSection(RootCauseAnalysisSection $section,
                                              string $subSectionTitle = null,
                                              string $explanation = null): void {
        if(!$subSectionTitle){
            $subSectionTitle = $section->getPredictorVariableCategory()->getNameSingular() .
                "-Related Factors Predictive of <b>Lower</b> " .$this->getOutcomeVariableName();
        }
        $this->addHeaderNewPageTOCIndexEntry($subSectionTitle, 2);
        $this->addHtml($section->getNegativeUpVotedSectionHtml($subSectionTitle, $explanation));
    }
    /**
     * @return QMUserVariable
     */
    public function getSourceObject(): QMUserVariable{
        return $this->getOutcomeQMUserVariable();
    }
    /**
     * @return string
     */
    public function getImage(): string{
        return ImageUrls::FACTORS_SLIDE;
        return abs_path('public/img/factors-slide.png');
    }
    /**
     * @return string
     */
    public function getCoverImage(): string{
        return $this->getImage();
    }
    /**
     * @return string
     */
    public function generateEmailBody(): string{
        $v = $this->getOutcomeQMUserVariable();
        return $v->getRootCauseEmailBody($this);
    }
    /**
     * @return bool
     */
    public function userHasCorrelations(): bool {
        $v = $this->getOutcomeQMUserVariable();
        $number = $v->getOrCalculateNumberOfUserVariableRelationshipsAsEffect();
        return (bool)$number;
    }
    /**
     * @return User
     */
    private function getDemoUser(): User{
        return User::mike();
    }
    /**
     * @inheritDoc
     */
    public function getCategoryName(): string{
        return WpPost::CATEGORY_ROOT_CAUSE_ANALYSES_REPORTS;
    }
    /**
     * @param int $maxCorrelations
     * @return string
     * Better to use best all categories so we show the best instead of limiting to categories that might not have good studies
     * I tried using Treatments category first and had a bunch of questionable studies so it's better to use all
     * and we see that Sleep is the top correlation for Mood, etc.
     */
    protected function getDemoFactorListEmailHtmlWithBestOfAllCategories(int $maxCorrelations = self::MAX_CORRELATIONS_FOR_EMAIL): string{
        $v = $this->getOutcomeQMUserVariable();
        $html = $v->getDemoUserDisclaimerNotEnoughDataStartTrackingHtml();
        try {
            self::validateFactorsList($html);
        } catch (InvalidStringException $e) {
            le($e);
        }
        try {
            $demo = $this->getDemoRootCauseAnalysis();
            $demo->setMaximumCorrelations($maxCorrelations);
            $correlations = $demo->getUpVotedCorrelations();
            if(!$correlations){$correlations = $demo->getNonVotedCorrelations();}
            $html .= RootCauseAnalysisSection::generateListSectionHtml("Top $v->displayName Factors for Others",
                RootCauseAnalysis::getUpVotedDescription(),
                $correlations,
                $this->getOutcomeVariableName());
            try {
                self::validateFactorsList($html);
            } catch (InvalidStringException $e) {
                le($e);
            }
        } catch (UserVariableNotFoundException $e) {
            $this->logError("Could not get demo factor list because: ".$e->getMessage());
        }
        $this->validateDemoFactorList($html);
        try {
            self::validateFactorsList($html);
        } catch (InvalidStringException $e) {
            le($e);
        }
        return $html;
    }
    /**
     * @return string
     */
    public function getDefinitionsHtml():string {
        return static::columnDefinitionsHTML($this->getOutcomeVariableName());
    }
    /**
     * @param int $maxCorrelations
     * @return string
     */
    protected function getUserFactorListEmailHtmlWithBestOfAllCategories(int $maxCorrelations = self::MAX_CORRELATIONS_FOR_EMAIL): string{
        $list = $this->getTitleDescriptionHeaderHtml();
        $this->setMaximumCorrelations($maxCorrelations);
        $correlations = $this->getUpVotedCorrelations();
        if($correlations){
            $list .= RootCauseAnalysisSection::generateListSectionHtml("Confirmed Studies from Your Data",
                RootCauseAnalysis::getUpVotedDescription(),
                $correlations,
                $this->getOutcomeVariableName());
        }
        $correlations = $this->getNonVotedCorrelations();
        if($correlations){
            $list .= RootCauseAnalysisSection::generateListSectionHtml("Studies from Your Data That Need Review",
                RootCauseAnalysisSection::UNVERIFIED_DESCRIPTION,
                $correlations,
                $this->getOutcomeVariableName());
        }
        return $list;
    }
    /**
     * @param int $maxCorrelations
     * @return string
     * Better to use getUserFactorListEmailHtmlWithBestOfAllCategories so we show the best instead of limiting to
     * categories that might not have good studies I tried using Treatments category first and had a bunch of
     * questionable studies so it's better to use all and we see that Sleep is the top correlation for Mood, etc.
     */
    protected function getUserFactorListEmailHtmlByCategory(int $maxCorrelations = self::MAX_CORRELATIONS_FOR_EMAIL): string{
        $totalCorrelations = 0;
        $list = '';
        foreach(self::$variableCategoryNames as $categoryName){
            $section = $this->getCategorySection($categoryName);
            $list .= $section->getTitleDescriptionHeaderHtml();
            $section->setMaximumCorrelations($maxCorrelations);
            $correlations = $section->getUpVotedCorrelations();
            $totalCorrelations += count($correlations);
            if($correlations){
                $list .= RootCauseAnalysisSection::generateListSectionHtml("Confirmed Studies from Your Data",
                    RootCauseAnalysis::getUpVotedDescription(),
                    $correlations,
                    $this->getOutcomeVariableName());
            }
            if($maxCorrelations && $totalCorrelations >= $maxCorrelations){
                break;
            }
            $correlations = $section->getNonVotedCorrelations();
            $totalCorrelations += count($correlations);
            if($correlations){
                $list .= RootCauseAnalysisSection::generateListSectionHtml("Studies from Your Data That Need Review",
                    RootCauseAnalysisSection::UNVERIFIED_DESCRIPTION,
                    $correlations,
                    $this->getOutcomeVariableName());
            }
            if($maxCorrelations && $totalCorrelations >= $maxCorrelations){
                break;
            }
        }
        return $list;
    }
    /**
     * @return int
     */
    public function getMaximumCorrelations(): int {
        if (!$this->maximumCorrelations && AppMode::isApiRequest()) {
            return 100;
        }
        return $this->maximumCorrelations;
    }
    /**
     * @param int $maximumCorrelations
     */
    public function setMaximumCorrelations(int $maximumCorrelations): void {
        $this->maximumCorrelations = $maximumCorrelations;
    }
    /**
     * @return QMUserVariableRelationship[]
     */
    public function getUpVotedCorrelations(): array {
        if($this->upVoted !== null){return $this->upVoted;}
        $limit = $this->getMaximumCorrelations();
        if ($limit && !$this->correlations) {
            $correlations = QMUserVariableRelationship::getUpVotedCorrelationsByEffect($this->getUserId(),
                $this->getOutcomeVariableId(),
                $limit);
            return $this->upVoted = $correlations;
        }
        $correlations = $this->getOrSetCorrelations();
        $upVoted = Arr::where($correlations, static function ($correlation) {
            /** @var QMUserVariableRelationship $correlation */
            return $correlation->userUpVoted();
        });
        return $this->upVoted = $upVoted;
    }
    /**
     * @return QMUserVariableRelationship[]
     */
    public function getOrSetCorrelations(): array {
        $correlations = $this->correlations;
        if ($correlations === null) {$correlations = $this->setCorrelations();}
        if ($max = $this->getMaximumCorrelations()) {return collect($correlations)->take($max)->all();}
        return $correlations;
    }
    /**
     * @return QMUserVariableRelationship[]
     */
    protected function setCorrelations(): array {
        $v = $this->getOutcomeQMUserVariable();
        if(!AppMode::isApiRequest()){
            try {
                $v->calculateCorrelationsIfNecessary();
            } catch (TooSlowToAnalyzeException | InvalidAttributeException | ModelValidationException $e) {
                le($e);
            }
        }
        $limit = $this->getMaximumCorrelations();
        $correlations = $v->getUserOrGlobalVariableRelationshipsAsEffect($limit);
        return $this->correlations = $correlations;
    }
    /**
     * @return QMUserVariableRelationship[]
     */
    public function getNonVotedCorrelations(): array {
        if($this->nonVoted !== null){return $this->nonVoted;}
        $limit = $this->getMaximumCorrelations();
        if ($limit && !$this->correlations) {
            $correlations = QMUserVariableRelationship::getNonVotedCorrelationsByEffect($this->getUserId(),
                $this->getOutcomeVariableId(), $limit);
            return $this->nonVoted = $correlations;
        }
        $correlations = $this->getOrSetCorrelations();
        $nonVoted = Arr::where($correlations, static function ($correlation) {
            /** @var QMUserVariableRelationship $correlation */
            return $correlation->userDidNotVote();
        });
        return $this->nonVoted = $nonVoted;
    }
    static public function getDemoReport(): AnalyticalReport {
        try {
            $v = QMUserVariable::getByNameOrId(UserIdProperty::USER_ID_MIKE, OverallMoodCommonVariable::ID);
        } catch (UserVariableNotFoundException $e) {
            le($e);
        }
        $a = new self($v->getVariableIdAttribute(), $v->getUserId());
        return $a;
    }
    /**
     * @param string $html
     */
    protected function validateDemoFactorList(string $html): void{
        if(stripos($html, "from Your Data") !== false){
            le("from Your Data");
        }
    }
    public function getEmailBody(): string{
        $uv = $this->getOutcomeQMUserVariable();
        return $uv->getEmailBody();
    }
    public function getKeyWords(): array{
        $keywords = $this->getOutcomeQMUserVariable()->getKeyWords();
        $keywords[] = $this->getTitleAttribute();
        return array_unique($keywords);
    }
    public function getShowContentView(array $params = []): View{
        return $this->getOutcomeQMUserVariable()->getShowContentView($params);
    }
    protected function getShowPageView(array $params = []): View{
        return $this->getOutcomeQMUserVariable()->getShowPageView($params);
    }
    /**
     * @return string
     */
    public function getDemoUrl(): string {
        return qm_url("demo/".
            static::getSlugifiedClassName()
            ."/".
            $this->getVariable()->getSlug()
        );
    }
    private function getVariable(): Variable {
        return $this->getOutcomeQMUserVariable()->getVariable();
    }
    public function getShowUrl(array $params = []): string{
        return qm_url($this->getShowFolderPath()."/".$this->getVariable()->getSlug(), $params);
    }
}
