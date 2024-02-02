<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Reports;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NotEnoughOverlappingDataException;
use App\Models\UserVariableRelationship;
use App\Models\User;
use App\Models\WpPost;
use App\Tables\BaseTable;
use App\Tables\VariableRelationshipsTable;
use App\Traits\HasCorrelationCoefficient;
use App\Traits\HasTable;
use App\Traits\HasModel\HasVariable;
use App\UI\HtmlHelper;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\InsufficientVarianceException;
use App\Exceptions\NotEnoughMeasurementsForCorrelationException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UserVariableNotFoundException;
use App\Exceptions\VariableCategoryNotFoundException;
use App\Utils\AppMode;
use App\Charts\BarChartButton;
use App\VariableRelationships\QMVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Models\Vote;
use App\Utils\QMProfile;
use App\UI\CssHelper;
use App\Logging\QMLog;
use App\Types\QMStr;
use App\Studies\StudyImages;
use App\Tables\QMTable;
use App\Slim\Model\User\QMUser;
use App\Variables\QMUserVariable;
use App\Variables\QMVariableCategory;
class RootCauseAnalysisSection extends AnalyticalReport {
    use HasTable, HasVariable;
    public const TITLE = null;
    protected $abbreviatedFactorList;
    protected $correlations;
    protected $downVoted;
    protected $factor = true;
    protected $includeFlagged;
    protected $maximumCorrelations = 0;
    protected $nonVoted;
    protected $outcomeVariableId;
    protected $predictorVariableCategory;
    protected $rootCauseAnalysis;
    protected $rows;
    protected $upVoted;
    protected const CORRELATION_THRESHOLD = 0;
    public $introductorySentence;
    public $predictorVariableCategoryName;
    public $title;
    public const MAXIMUM_CORRELATIONS = 100; // Need to limit number to avoid memory issues
    public const UNVERIFIED_DESCRIPTION = "Click to see the full study and click thumbs up to verify it if everything ".
    "looks good. If you see issues, you can fix it by modifying the analysis settings.";
    public const FLAGGED_DESCRIPTION = "These studies have been flagged as possibly erroneous due to missing data or other issues. " .
    "Click to see the full study, fix it by modifying the analysis settings, and un-flag it.";
    protected static $unnecessaryTableColumns = [
        'Outcome',
        'Association',
        'Correlation Coefficient'
    ];
    /**
     * RootCauseAnalysisSection constructor.
     * @param RootCauseAnalysis $r
     * @param string|null $predictorCategory
     */
    public function __construct(RootCauseAnalysis $r, string $predictorCategory = null){
        $this->setRootCauseAnalysis($r);
        if($predictorCategory){
            $this->predictorVariableCategoryName = $predictorCategory;
            $cat = $this->getPredictorVariableCategory();
            $this->title = $cat->getNameSingular() . "-Related Factors";
        }
        $this->outcomeVariableId = $r->getOutcomeQMUserVariable()->getVariableIdAttribute();
        $this->setUserId($r->getUserId());
    }
    /**
     * @return string
     */
    public function getTitleAttribute(): string {
        return $this->title = $this->getPredictorVariableCategoryName()." Most Likely to Impact ".$this->getOutcomeVariableName();
    }
    /**
     * @return RootCauseAnalysis
     */
    public function getRootCauseAnalysis(): RootCauseAnalysis {
        return $this->rootCauseAnalysis;
    }
    /**
     * @param RootCauseAnalysis $rootCauseAnalysis
     */
    public function setRootCauseAnalysis(RootCauseAnalysis $rootCauseAnalysis): void{
        $this->rootCauseAnalysis = $rootCauseAnalysis;
    }
    /**
     * @param bool $lowercase
     * @return string
     */
    public function getPredictorVariableCategoryName(bool $lowercase = false): string {
        if(!$this->predictorVariableCategoryName){
            le("No category name!");
        }
        if($lowercase){
            return strtolower($this->predictorVariableCategoryName);
        }
        return $this->predictorVariableCategoryName;
    }
    /**
     * @return QMVariableCategory
     */
    public function getPredictorVariableCategory(): QMVariableCategory {
        if($this->predictorVariableCategory){return $this->predictorVariableCategory;}
        try {
            $cat = QMVariableCategory::find($this->predictorVariableCategoryName);
        } catch (VariableCategoryNotFoundException $e) {
            le($e);
        }
        return $this->predictorVariableCategory = $cat;
    }
    /**
     * @return string
     */
    public function getIntroductorySentenceHTML(): string {
        $str = $this->introductorySentence;
        if(empty($str)){$str = $this->getSubtitleAttribute();}
        return "
        <p>
            $str
        </p>
        ";
    }
    /**
     * @return array
     */
    public function getPositiveUpVotedCorrelations(): array {
        $correlations = $this->getCorrelations();
        $filtered = Arr::where($correlations, static function($correlation){
            /** @var QMUserVariableRelationship $correlation */
            $upVoted = $correlation->userUpVoted();
            $coefficient = $correlation->getCorrelationCoefficient();
            $result = $coefficient > self::CORRELATION_THRESHOLD
                //&& !$correlation->userDownVoted()
                && $upVoted
                ;
            return $result;
        });
        return $filtered;
    }
    /**
     * @return QMUserVariableRelationship[]
     */
    public function getNegativeUpVotedCorrelations(): array {
        $correlations = $this->getUpVotedCorrelations();
        $correlations = Arr::where($correlations, static function ($correlation) {
            /** @var QMUserVariableRelationship $correlation */
            return $correlation->getCorrelationCoefficient() < -1 * self::CORRELATION_THRESHOLD;
        });
        return $correlations;
    }
    /**
     * @return QMUserVariableRelationship[]
     */
    public function getNeutralCorrelations(): array {
        $correlations = $this->getCorrelations();
        $correlations = Arr::where($correlations, static function($correlation){
            /** @var QMUserVariableRelationship $correlation */
            return $correlation->getCorrelationCoefficient() > -1 * self::CORRELATION_THRESHOLD &&
                $correlation->getCorrelationCoefficient() < self::CORRELATION_THRESHOLD
                && !$correlation->userDownVoted()
                //&& $correlation->userUpVoted()
                ;
        });
        return $correlations;
    }
    /**
     * @return QMUserVariableRelationship[]
     */
    public function getNeedsReviewCorrelations(): array {
        $correlations = $this->getCorrelations();
        $needReview = Arr::where($correlations, static function($correlation){
            /** @var QMUserVariableRelationship $correlation */
            $didNotVote = $correlation->userDidNotVote();
            return $didNotVote;
        });
        return $needReview;
    }
    /**
     * @return QMUserVariableRelationship[]
     */
    public function getFlaggedCorrelations(): array {
        $correlations = $this->getCorrelations();
        $flagged = Arr::where($correlations, static function($correlation){
            /** @var QMUserVariableRelationship $correlation */
            return $correlation->userDownVoted();
        });
        return $flagged;
    }
    /**
     * @return string
     */
    public function getOutcomeVariableName(): string {
        return $this->getRootCauseAnalysis()->getOutcomeQMUserVariable()->getOrSetVariableDisplayName();
    }
    /**
     * @return QMUserVariable
     */
    public function getOutcomeVariable(): QMUserVariable {
        return $this->getRootCauseAnalysis()->getOutcomeQMUserVariable();
    }
    /**
     * @return QMUser
     */
    public function getQMUser(): QMUser {
        return $this->getOutcomeVariable()->getQMUser();
    }
    /**
     * @param string $relationshipFragment
     * @return string
     */
    public function getNotEnoughDataSentence(string $relationshipFragment):string {
        return HtmlHelper::renderView(view('not-enough-data-for-section', [
            'section'              => $this,
            'relationshipFragment' => $relationshipFragment
        ]));
    }
    /**
     * @param bool $lowercase
     * @return string
     */
    public function getPluralPredictorCategoryName(bool $lowercase = false): string {
        $name = $this->getPredictorVariableCategory()->getNamePlural();
        if($lowercase){
            return strtolower($name);
        }
        return $name;
    }
    /**
     * @return string
     */
    public function getImage(): string{
        return StudyImages::generateSmallCategoriesRobotImageUrl($this->getPredictorVariableCategory(),
            $this->getOutcomeVariable()->getQMVariableCategory());
    }
    /**
     * @return string
     */
    public function getLogMetaDataString(): string {
        return $this->getPredictorVariableCategoryName()."-".$this->getRootCauseAnalysis()->getUniqueIndexIdsSlug();
    }
    /**
     * @return QMVariableRelationship[]
     */
    protected function getCorrelations(): array {
        if ($this->correlations !== null) {
            return $this->correlations;
        }
        $v = $this->getOutcomeVariable();
        $predictorVariableCategoryName = $this->getPredictorVariableCategoryName();
        $correlations = $v->getCorrelationsForPredictorCategory($predictorVariableCategoryName,
            self::MAXIMUM_CORRELATIONS);
        return $this->correlations = $correlations->all();
    }
    /**
     * @return bool
     */
    public function userHasData(): bool{
        $userVariableRelationships = $this->getCorrelations();
        return (bool)count($userVariableRelationships);
    }
    /**
     * @param string|null $subSectionTitle
     * @param string|null $explanation
     * @return void
     */
    protected function addNegativeUpVotedSection(string $subSectionTitle = null,
                                              string $explanation = null): void {
        if(!$subSectionTitle){
            $subSectionTitle = $this->getPredictorVariableCategory()->getNameSingular() .
                "-Related Factors Predictive of <b>Lower</b> " .$this->getOutcomeVariableName();
        }
        $this->addHeaderNewPageTOCIndexEntry($subSectionTitle, 2);
        $this->addHtml($this->getNegativeUpVotedSectionHtml($subSectionTitle, $explanation));
    }
    /**
     * @param string|null $subSectionTitle
     * @param string|null $explanation
     * @return string
     */
    public function getNegativeUpVotedSectionHtml(string $subSectionTitle = null,
                                                  string $explanation = null): string {
        if(!$explanation){
            $explanation = "Above average values of these ".$this->getPluralPredictorCategoryName(true).
                " are usually followed by <b>below average</b> ".$this->getOutcomeVariableName();
        }
        $html = "<p>
            $explanation
        </p>";
        $correlations = $this->getNegativeUpVotedCorrelations();
        if ($correlations) {
            $html .= $this->correlationsToListOrTableHtml($correlations, $subSectionTitle);
        } else {
            $html .= $this->getNotEnoughDataSentence("predictive of lower");
        }
        return $html;
    }
    /**
     * @param QMUserVariableRelationship[] $correlations
     * @param string $subSectionTitle
     * @return string
     */
    protected function correlationsToListOrTableHtml(array $correlations, string $subSectionTitle): string {
        if (!$correlations) {le("No user_variable_relationships for $subSectionTitle");}
        if (!$this->isWriteToPdf()) {return RootCauseAnalysisSection::getBarChartListHtml($correlations,
            $this::getOutcomeVariableName());}
        return RootCauseAnalysisSection::correlationsToTableHtml($correlations, $subSectionTitle);
    }
    /**
     * @param QMUserVariableRelationship[] $correlations
     * @param string $subSectionTitle
     * @return string
     */
    public static function correlationsToTableHtml(array $correlations, string $subSectionTitle): string {
        $rows = [];
        foreach ($correlations as $correlation) {
            $row = $correlation->getTableRow();
            foreach (static::$unnecessaryTableColumns as $column) {
                unset($row[$column]);
            }
            $rows[] = $row;
        }
        if (!$rows) {
            QMLog::error("No valid user_variable_relationships for $subSectionTitle table");
            return '';
        }
        $html = QMTable::arrayToHtmlTable($rows);
        return $html;
    }
    protected function addFlaggedSection(){
        $title = "Flagged or Flawed Studies of ".$this->getTitleAttribute();
        $this->addHeaderNewPageTOCIndexEntry($title, 2, Vote::THUMB_DOWN_BLACK_IMAGE);
        $this->addHtml($this->getFlaggedHtml());
    }
    /**
     * @return string
     */
    public function getFlaggedHtml(): string {
        $title = "Flagged or Flawed Studies of ".$this->getTitleAttribute();
        $correlations = $this->getFlaggedCorrelations();
        $str = "studies have been flagged as erroneous by clicking thumbs down in the interactive study.";
        $html = "<p>No $str</p>";
        if ($correlations) {
            $html = "<p>These $str</p>";
            $html .= $this->correlationsToListOrTableHtml($correlations, $title);
        }
        return $html;
    }
    /**
     * @return void
     */
    protected function addNeedsReviewSection(): void {
        $title = "Un-Reviewed Studies of " . $this->getTitleAttribute();
        $this->addHeaderNewPageTOCIndexEntry($title, 2, Vote::NO_VOTE_IMAGE);
        $this->addHtml($this->getNeedReviewHtml());
    }
    /**
     * @return string
     */
    public function getNeedReviewHtml(): string {
        $title = "Un-Reviewed Studies of " . $this->getTitleAttribute();
        $correlations = $this->getNeedsReviewCorrelations();
        $html = '';
        if ($correlations) {
            $html .= "
                <p>Please click the Unverified link to view these studies to view and then click: </p>
                <ul style='list-style: none;'>
                    <li>" . Vote::getThumbsUpImageHtml("float: left;") . " thumbs up to indicate the ones that you feel are valid</li>
                    <li>" . Vote::getThumbsDownImageHtml("float: left;") . " thumbs down to indicate the ones that you feel are flawed in some way</li>
                </ul>
             ";
            $html .= $this->correlationsToListOrTableHtml($correlations, $title);
        } else {
            $html .= "<p>All available " .
                $this->getPluralPredictorCategoryName(true) .
                " studies have been reviewed.</p>";
        }
        return $html;
    }
    /**
     * @param string|null $subSectionTitle
     * @param string|null $explanation
     * @return string
     */
    public function getPositiveUpVotedSectionHtml(string $subSectionTitle = null,
                                              string $explanation = null): string {
        if(!$subSectionTitle){
            $subSectionTitle =
                $this->getPredictorVariableCategory()->getNameSingular() .
                "-Related Factors Predictive of <b>Higher</b> " .
                $this->getOutcomeVariableName();
        }
        if(!$explanation){
            $explanation = "Above average values of these ".$this->getPluralPredictorCategoryName(true).
                " are usually followed by <b>above average</b> ".$this->getOutcomeVariableName().". ";
        }
        $html = "<p>
            $explanation
        </p>";
        $correlations = $this->getPositiveUpVotedCorrelations();
        if ($correlations) {
            $html .= $this->correlationsToListOrTableHtml($correlations, $subSectionTitle);
        } else {
            $html .= $this->getNotEnoughDataSentence("predictive of higher");
        }
        return $html;
    }
    /**
     * @param string|null $subSectionTitle
     * @return void
     */
    protected function addUncorrelatedSection(string $subSectionTitle = null): void {
        $this->addHtml($this->getUncorrelatedSectionHtml($subSectionTitle));
    }
    /**
     * @param string|null $subSectionTitle
     * @return string
     */
    public function getUncorrelatedSectionHtml(string $subSectionTitle = null): string {
        if(!$subSectionTitle){$subSectionTitle = $this->getPredictorVariableCategoryName(). " Uncorrelated with ".
            $this->getOutcomeVariableName();}
        $html = "<h3>$subSectionTitle</h3> \n ";
        $explanation = "Based on the available data it appears that these ". $this->getPredictorVariableCategoryName().
            " are likely unrelated to ".$this->getOutcomeVariableName().".";
        $html .=  "<p>$explanation</p> \n";
        $correlations = $this->getNeutralCorrelations();
        if ($correlations) {
            $html .= $this->correlationsToListOrTableHtml($correlations, $subSectionTitle);
        } else {
            $html .= "<p>No studies found which appear to lack a relationship</p>";
        }
        return $html;
    }
    /**
     * @param string $title
     * @param string $description
     * @param QMVariableRelationship[] $correlations
     * @param string $outcomeName
     * @return string
     */
    public static function generateListSectionHtml(string $title, string $description, array $correlations, string $outcomeName): string {
        $list = RootCauseAnalysisSection::getBarChartListHtml($correlations, $outcomeName);
        return "
                <div>
                    <h2>$title</h2>
                    <p>$description</p>
                    $list
                </div>
            ";
    }
    /**
     * @param HasCorrelationCoefficient $c
     * @param array $correlations
     * @return string
     */
    protected static function getTableRowBlock($c, array $correlations): string {
        $url = $c->getUrl();
        $tagLine = $c->generateStudyTitle();
        $color = $c->getColor();
        $width = static::getBarWidth($c, $correlations);
        $percent = $c->getOrCalculatePercentChangeFragment(true, true);
        $causeName = static::getTruncatedCauseName($c, $correlations);
        $img = $c->getCauseVariableImage();
        $html = BarChartButton::getHtmlWithRightText($causeName, $width, $url, $color, $img, $percent, $tagLine);
        return $html;
    }
    /**
     * @param UserVariableRelationship[]|QMUserVariableRelationship[] $correlations
     * @return int
     */
    protected static function getMaxChange(array $correlations): int {
        $maxChange = 0;
        foreach ($correlations as $c) {
            $change = $c->getChangeFromBaseline();
            if($change > $maxChange){$maxChange = $change;}
        }
        return $maxChange;
    }
    /**
     * @param QMVariableRelationship|UserVariableRelationship $c
     * @param UserVariableRelationship[]|QMUserVariableRelationship[] $correlations
     * @return float|int
     */
    public static function getBarWidth($c, array $correlations) {
        $minWidth = 70;
        $maxChange = static::getMaxChange($correlations);
        if (!$maxChange) {return $minWidth;
        }
        $factor = 30 / $maxChange;
        $percent = $c->getChangeFromBaseline();
        $width = $percent * $factor;
        if ($width < 0) {
            $width = $width * -1;
        }
        $width = $width + $minWidth;
        if ($width > 99) {
            $width = 99;
        }
        return $width;
    }
    /**
     * @return string
     */
    public function generateHtmlWithTable(): string {
        $max = $this->getMaximumCorrelations();
        $body = '';
        $correlations = $this->getUpVotedCorrelations();
        $count = count($correlations);
        if ($correlations) {
            $body .= RootCauseAnalysisSection::generateListSectionHtml("Confirmed Studies from Your Data",
                RootCauseAnalysis::getUpVotedDescription(),
                $correlations, $this->getOutcomeVariableName());
        }
        if ($max && $count >= $max) {
            return $body;
        }
        $correlations = $this->getNonVotedCorrelations();
        $count = $count + count($correlations);
        if ($correlations) {
            $body .= RootCauseAnalysisSection::generateListSectionHtml("Studies from Your Data That Need Review",
                self::UNVERIFIED_DESCRIPTION,
                $correlations, $this->getOutcomeVariableName());
        }
        if ($max && $count >= $max) {
            return $body;
        }
        $correlations = $this->getDownVotedUserVariableRelationships();
        if ($correlations) {
            $body .= RootCauseAnalysisSection::generateListSectionHtml("Flagged or Flawed Studies from Your Data",
                self::FLAGGED_DESCRIPTION,
                $correlations, $this->getOutcomeVariableName());
        }
        return $this->generateHtmlWithHeader($body);
    }
    /**
     * @param string $body
     * @return string
     */
    protected function generateHtmlWithHeader(string $body): string{
        $header = $this->getTitleDescriptionHeaderHtml();
        return "
            <div style=\"font-family: 'Source Sans Pro', sans-serif;\">
                $header
                $body
            </div>
        ";
    }
    /**
     * @return string
     */
    public function generateHtmlBodyWithInlineCss(): string {
        return $this->generateHtmlWithTable();
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
     * @param QMVariableRelationship[] $correlations
     * @param string $outcomeVariableName
     * @return string
     */
    public static function getBarChartListHtml(array $correlations, string $outcomeVariableName): string {
        if(!$correlations){return '';}
        $list = "
            <p>
                Each bar represents the amount of change seen in $outcomeVariableName following above average values for that factor.
                Click to see the full study.
            </p>
        ";
        foreach ($correlations as $c) {
            $list .= static::getTableRowBlock($c, $correlations);
        }
        return $list;
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
                $limit, $this->getPredictorVariableCategoryName());
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
     * @return int
     */
    protected function getOutcomeVariableId(): int {
        return $this->outcomeVariableId;
    }
    /**
     * @return QMUserVariableRelationship[]
     */
    public function getDownVotedUserVariableRelationships(): array {
        if($this->downVoted !== null){return $this->downVoted;}
        $correlations = $this->getOrSetCorrelations();
        $downVoted = Arr::where($correlations, static function ($correlation) {
            /** @var QMUserVariableRelationship $correlation */
            return $correlation->userDownVoted();
        });
        return $this->downVoted = $downVoted;
    }
    /**
     * @return QMUserVariableRelationship[]
     */
    public function getNonVotedCorrelations(): array {
        if($this->nonVoted !== null){return $this->nonVoted;}
        $limit = $this->getMaximumCorrelations();
        if ($limit && !$this->correlations) {
            $correlations = QMUserVariableRelationship::getNonVotedCorrelationsByEffect($this->getUserId(),
                $this->getOutcomeVariableId(),
                $limit, $this->getPredictorVariableCategoryName());
            return $this->nonVoted = $correlations;
        }
        $correlations = $this->getOrSetCorrelations();
        $nonVoted = Arr::where($correlations, static function ($correlation) {
            /** @var QMUserVariableRelationship $correlation */
            return $correlation->userDidNotVote();
        });
        return $this->nonVoted = $nonVoted;
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
        $v = $this->getOutcomeVariable();
        if(!AppMode::isApiRequest()){
            try {
                $v->calculateCorrelationsIfNecessary();
            } catch (TooSlowToAnalyzeException $e) {
                le($e);
            }
        }
        $limit = $this->getMaximumCorrelations();
        $correlations = $v->getCorrelationsForPredictorCategory($this->getPredictorVariableCategoryName(), $limit);
        return $this->correlations = $correlations;
    }
    /**
     * @param QMVariableRelationship|UserVariableRelationship $c
     * @param array $correlations
     * @param int $absoluteMax
     * @return string
     */
    protected static function getTruncatedCauseName($c, array $correlations, int $absoluteMax = 45): string {
        $width = static::getBarWidth($c, $correlations);
        $maxChars = $absoluteMax * $width / 100;
        $name = QMStr::truncate($c->getCauseNameWithoutCategoryOrUnit(), $maxChars);
        return $name;
    }
    /**
     * @return User
     */
    protected function getDemoUser(): User {
        return User::mike();
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
        $this->addPositiveUpVotedSection();
        $this->addNegativeUpVotedSection();
        $this->addNeedsReviewSection();
        $this->addUncorrelatedSection();
        if ($this->includeFlagged) {
            $this->addFlaggedSection();
        }
        $this->addIndex();
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
        $this->addPositiveUpVotedSection();
        $this->addNegativeUpVotedSection();
        $this->addNeedsReviewSection();
        $this->addUncorrelatedSection();
        if ($this->includeFlagged) {
            $this->addFlaggedSection();
        }
        return $this->bodyHtml;
    }
    protected function addOutcomeOverview(){
        $this->addVariableOverview($this->getOutcomeVariable());
    }
	/**
	 * @return array
	 * @throws \App\Exceptions\InvalidVariableValueException
	 */
    public function getSpreadsheetRows(): array{
        if($this->rows !== null){return $this->rows;}
        $rows = [];
        QMLog::infoWithoutContext("=== ".__FUNCTION__." ===");
        $correlations = $this->getOrSetCorrelations();
        $total = count($correlations);
        $i = 0;
        QMLog::infoWithoutContext("Got ".count($correlations)." for spreadsheet");
        $profile = false;
        foreach($correlations as $c){
            $i++;
            if($i % 100 === 0){$c->logInfo("getSpreadsheetRow ($i of $total completed)");}
	        QMProfile::profileIfEnvSet(true, false, __METHOD__);
            try {
                $c->getOrCalculateFollowUpPercentChangeFromBaseline();
            } catch (AlreadyAnalyzingException | TooSlowToAnalyzeException | NotEnoughMeasurementsForCorrelationException | InsufficientVarianceException | AlreadyAnalyzedException | NotEnoughOverlappingDataException | NotEnoughDataException $e) {
                $this->logError(__METHOD__.": ".$e->getMessage());
            }
	        $row = $c->getSpreadsheetRow();
	        QMProfile::endProfile();
            unset($row['Outcome']);
            $rows[] = $row;
        }
        if(!$rows){
            $this->logError("No rows for spreadsheet!");
            $rows = $this->getOrSetCorrelations();
        }
        return $this->rows = $rows;
    }
    /**
     * @inheritDoc
     */
    public function getSourceObject(){
        return $this->getOutcomeVariable();
    }
    /**
     * @inheritDoc
     */
    public function generateEmailBody(): string{
       return $this->getAbbreviatedFactorListForEmail();
    }
    /**
     * @inheritDoc
     */
    public function getCoverImage(): string{
        return StudyImages::generateCategoriesWithoutBackgroundRobotImageUrl($this->getPredictorVariableCategory(),
            $this->getOutcomeVariable()->getQMVariableCategory());
    }
    /**
     * @inheritDoc
     */
    public function getCategoryName(): string{
        return WpPost::CATEGORY_ROOT_CAUSE_ANALYSES_REPORTS;
    }
    /**
     * @return string
     */
    public function getSubtitleAttribute(): string {
        $cat = $this->getPredictorVariableCategoryName(true);
        return "This report is intended to help you and your physician to gain insight into which $cat most affect your ".
            $this->getOutcomeVariable()->getOrSetVariableDisplayName().". ";
    }
    /**
     * @return string
     */
    public function getAbbreviatedFactorListForEmail(): string {
        if($this->abbreviatedFactorList){return $this->abbreviatedFactorList;}
        if(!$this->userHasCorrelations()){return $this->getDemoFactorListEmailHtml();}
	    $list = $this->getTitleDescriptionHeaderHtml();
        $this->setMaximumCorrelations(10);
        $correlations = $this->getUpVotedCorrelations();
        if ($correlations) {
            $list .= RootCauseAnalysisSection::generateListSectionHtml("Confirmed Studies from Your Data",
                RootCauseAnalysis::getUpVotedDescription(),
                $correlations, $this->getOutcomeVariableName());
        }
        $correlations = $this->getNonVotedCorrelations();
        if ($correlations) {
            $list .= RootCauseAnalysisSection::generateListSectionHtml("Studies from Your Data That Need Review",
                RootCauseAnalysisSection::UNVERIFIED_DESCRIPTION,
                $correlations, $this->getOutcomeVariableName());
        }
        $button = $this->getStaticHtmlButton("SEE COMPLETE LIST OF FACTORS  &rarr;");
        $maxWidth = CssHelper::GLOBAL_MAX_POST_CONTENT_WIDTH;
        $list = "
            <div style=\"max-width: ".$maxWidth."px; font-family: 'Source Sans Pro', sans-serif; margin: auto;\">
                    $list
                <div>
                    $button
                </div>
            </div>
        ";
        return $this->abbreviatedFactorList = $list;
    }
    /**
     * @return string
     */
    protected function getDemoFactorListEmailHtml(): string{
        $v = $this->getOutcomeVariable();
        $cat = $this->getPredictorVariableCategoryName();
        $html = $v->getDemoUserDisclaimerNotEnoughDataStartTrackingHtml($cat);
        try {
            $demo = $this->getDemoRootCauseAnalysisSection();
            $html .= "<h6>Here are the top $this->predictorVariableCategoryName factors for other users</h6>";
            $html .= $demo->getAbbreviatedFactorListForEmail();
        } catch (UserVariableNotFoundException $e) {
            $this->logError("Could not get demo factor list because: ".$e->getMessage());
        }
        return $html;
    }
    /**
     * @return RootCauseAnalysis
     */
    public function getDemoRootCauseAnalysis(): RootCauseAnalysis{
        $u = $this->getDemoUser();
        $v = $u->findOrCreateQMUserVariable($this->getOutcomeVariable()->getVariableIdAttribute());
        return $v->getRootCauseAnalysis();
    }
	/**
	 * @return RootCauseAnalysisSection
	 */
    public function getDemoRootCauseAnalysisSection(): RootCauseAnalysisSection {
        $a = $this->getDemoRootCauseAnalysis();
        return $a->getCategorySection($this->getPredictorVariableCategoryName());
    }
    /**
     * @return bool
     */
    public function userHasCorrelations(): bool {
        $correlations = $this->getCorrelations();
        return count($correlations) > 0;
    }
    /**
     * @return string
     */
    public function getDefinitionsHtml():string {
        return static::columnDefinitionsHTML($this->getOutcomeVariableName());
    }
    /**
     * @param string|null $subSectionTitle
     * @param string|null $explanation
     * @return void
     */
    public function addPositiveUpVotedSection(string $subSectionTitle = null,
                                              string $explanation = null): void {
        if(!$subSectionTitle){
            $subSectionTitle = $this->getPredictorVariableCategory()->getNameSingular() .
                "-Related Factors Predictive of <b>Higher</b> " .$this->getOutcomeVariableName();
        }
        $this->addHeaderNewPageTOCIndexEntry($subSectionTitle, 2, Vote::THUMB_UP_BLACK_IMAGE_16);
        $html = $this->getPositiveUpVotedSectionHtml($subSectionTitle, $explanation);
        $this->addHtml($html);
    }
    /**
     * @return AnalyticalReport
     */
    static public function getDemoReport(): AnalyticalReport {
        $u = User::mike();
        $v = $u->getPrimaryOutcomeQMUserVariable();
        $a = new self($v->getRootCauseAnalysis());
        return $a;
    }
    public function getBaseTable(): BaseTable{
        $t = new VariableRelationshipsTable($this->getCorrelations());
        return $t;
    }
    public function getVariableIdAttribute(): ?int{
        return $this->getOutcomeVariableId();
    }
    public function getVariableCategoryId(): int{
        return $this->getPredictorVariableCategory()->getId();
    }
    public function getShowContentView(array $params = []): View{
        // TODO: Implement getShowContentView() method.
    }
    protected function getShowPageView(array $params = []): View{
        // TODO: Implement getShowPageView() method.
    }
}
