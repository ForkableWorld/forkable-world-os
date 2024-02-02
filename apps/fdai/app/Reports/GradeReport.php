<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Reports;
use App\Charts\BarChartButton;
use App\DataSources\Connectors\QuantiModoConnector;
use App\DataSources\Connectors\TigerViewConnector;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoChangesException;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Models\WpPost;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\Measurement\AnonymousMeasurement;
use App\Slim\Model\Measurement\QMMeasurement;

use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\ImageHelper;
use App\UI\QMColor;
use App\Units\DollarsUnit;
use App\Units\GramsUnit;
use App\Units\HoursUnit;
use App\VariableCategories\BooksVariableCategory;
use App\VariableCategories\ElectronicsVariableCategory;
use App\VariableCategories\GoalsVariableCategory;
use App\VariableCategories\NutrientsVariableCategory;
use App\VariableCategories\PaymentsVariableCategory;
use App\Variables\CommonVariables\GoalsCommonVariables\DailyAverageGradeCommonVariable;
use App\Variables\QMUserVariable;
use Illuminate\View\View;
class GradeReport extends AnalyticalReport {

    public const TITLE = "Daily Report Card";
    public const DEMO_USER_ID = UserIdProperty::USER_ID_IVY;
    public const NAME = 'Daily Average Grade';
    protected const INCLUDE_MONETARY_ALLOWANCE = false;
    const CATEGORY_DESCRIPTION = "Overview recent academic performance and rewards earned";
    /**
     * @var QMMeasurement|null
     */
    private $lastDailyAverageMeasurement;
    public function __construct(int $userId = null){
        if($userId){
            $this->setUserId($userId);
        }
    }
    /**
     * @param string $leftText
     * @return mixed|string
     */
    protected static function getSubjectFromGradeVariableName(string $leftText): string{
        $leftText = str_ireplace(TigerViewConnector::CLASS_DAILY_AVERAGE_GRADE_SUFFIX, '', $leftText);
        $leftText = str_ireplace(TigerViewConnector::CURRENT_AVERAGE_GRADE_PREFIX, '', $leftText);
        return $leftText;
    }
    /**
     * @return string
     */
    public function getCoverImage(): string{
        return BooksVariableCategory::IMAGE_URL;
    }
    /**
     * @return string
     */
    public function generateBodyHtml(): string{
        $html = $this->getLastDailyAverageHtml();
        $html .= $this->getAllowanceHtml();
        $html .= $this->getQuarterlyAverageHtml();
        $html .= $this->getLastDailyGradesHtml();
        $html .= $this->getAllowanceExplanationsHtml();
        return $html;
    }
    /**
     * @return string
     */
    public function generateEmailBody(): string {
        $html = $this->generateBodyHtml();
        return $html;
    }
    /**
     * @return QMUserVariable
     */
    public function getSourceObject(): QMUserVariable{
        return $this->getDailyAverageQMUserVariable();
    }
    /**
     * @param QMMeasurement $lastDailyAvgMeasurementForSubject
     * @return string
     */
    protected function getDailyGradeForSubjectBarHtml(QMMeasurement $lastDailyAvgMeasurementForSubject): string {
        $v = $lastDailyAvgMeasurementForSubject->getQMUserVariable();
        $subject = self::getSubjectFromGradeVariableName($v->getOrSetVariableDisplayName());
        $meta = $lastDailyAvgMeasurementForSubject->getAdditionalMetaData();
        $assignment = $meta->getMessage();
        $leftText = "$assignment for $subject";
        if (strlen($leftText) > 45) {
            $leftText = $assignment;
        }
        $html = self::getGradeRowBlock($lastDailyAvgMeasurementForSubject, $leftText);
        return $html;
    }
    /**
     * @return string
     */
    public function getQuarterlyAverageHtml(): string {
        $html = '<h3>Quarter Averages</h3>';
        $arr = $this->getCurrentAverageGradeVariables();
        foreach ($arr as $v) {
            $m = $v->getLastDailyMeasurementWithTagsAndFilling();
            if (!$m) {
                $v->logError("No last daily measurement");
            } else {
                if($m->getOrSetStartTime() < time() - 30 * 86400){
                    $m->logInfo("Skipping Quarter Averages for $m->variableName because last measurement was ".
                        TimeHelper::timeSinceHumanString($m->getOrSetStartTime()));
                    continue;
                }
                $html .= self::getGradeRowBlock($m);
            }
        }
        return $html;
    }
    /**
     * @return string
     */
    public function getLastDailyGradesHtml(): string {
        $html = '<h3>Last Daily Grades</h3>';
        $arr = $this->getDailyAverageGradeVariables();
        foreach ($arr as $v) {
            $m = $v->getLastDailyMeasurementWithTagsAndFilling();
            if(!$m){
                $m = $v->getLastDailyMeasurementWithTagsAndFilling();
                $v->logError("No LastDailyMeasurement");
                continue;
                //le("No getLastDailyMeasurement");
            }
            if($m->getOrSetStartTime() < time() - 14 * 86400){
                $m->logInfo("Skipping this last daily grade because it's from ".
                    TimeHelper::timeSinceHumanString($m->getOrSetStartTime()));
                continue;
            }
            $html .= $this->getDailyGradeForSubjectBarHtml($m);
        }
        return $html;
    }
    /**
     * @return string
     */
    public function getLastDailyAverageHtml(): string {
        $v = $this->getDailyAverageQMUserVariable();
        $m = $this->getLastDailyAverageMeasurement();
        $lastDailyAvgMeasurementForSubjects = $m->getGroupedMeasurements();
        $date = date("l F j", $m->getOrSetStartTime());
        $html = '';
        $html .= "<h2>Grade for $date</h2>";
        $html .= self::getGradeRowBlock($m, $v->getOrSetVariableDisplayName());
        $html .= "<h3>Assignments for $date</h3>";
        foreach ($lastDailyAvgMeasurementForSubjects as $lastDailyAvgMeasurementForSubject) {
            $html .= $this->getDailyGradeForSubjectBarHtml($lastDailyAvgMeasurementForSubject);
        }
        return $html;
    }
    /**
     * @return string
     */
    public function getAllowanceHtml(): string {
        $html = "<h3>Rewards</h3>";
        if (self::INCLUDE_MONETARY_ALLOWANCE) {
            $html .= $this->getDailyMonetaryAllowanceHtml();
        }
        $html .= $this->getElectronicsAllowanceHtml();
        $html .= $this->getSugarAllowanceHtml();
        return $html;
    }
    /**
     * @return string
     */
    public function getAllowanceExplanationsHtml(): string {
        $html = '';
        if (self::INCLUDE_MONETARY_ALLOWANCE) {
            $html .= $this->monetaryExplanation();
        }
        $html .= $this->sugarExplanation();
        $html .= $this->electronicsExplanation();
        return $html;
    }
    /**
     * @return string
     */
    private function getDailyMonetaryAllowanceHtml(): string {
        $averageDailyForEveryone = round(30 / 7, 2);
        $m =
            $this->getRewardMeasurement("Daily Monetary Allowance", DollarsUnit::ID, $averageDailyForEveryone,
                PaymentsVariableCategory::IMAGE_URL);
        $html = $this->getRewardBar($m, "$" . $averageDailyForEveryone);
        return $html;
    }
    /**
     * @param QMMeasurement $m
     * @param string $averageDailyForEveryone
     * @return string
     */
    private function getRewardBar(QMMeasurement $m, string $averageDailyForEveryone): string {
        $lastDailyGrade = $this->getLastDailyGradePercent();
        $rewardPercent = $this->getRewardPercent();
        $html = BarChartButton::getHtmlWithRightText($m->getVariableName(), $lastDailyGrade,
                $m->getUrl(), QMColor::HEX_GOOGLE_GREEN, $m->getImage(), $m->getValueUnitString(),
                "$rewardPercent% of a possible $averageDailyForEveryone.");
        return $html;
    }
    /**
     * @return int
     */
    private function getLastDailyGradePercent(): int {
        $lastDailyGrade = $this->getLastDailyAverageMeasurement()->getValue();
        return round($lastDailyGrade);
    }
    /**
     * @return string
     */
    private function getElectronicsAllowanceHtml(): string {
        $averageDailyForEveryone = 7.3;
        $m =
            $this->getRewardMeasurement("Daily Screen Time Allowance", HoursUnit::ID, $averageDailyForEveryone,
                ElectronicsVariableCategory::IMAGE_URL);
        $html = $this->getRewardBar($m, $averageDailyForEveryone . " hours");
        return $html;
    }
    /**
     * @return string
     */
    private function getSugarAllowanceHtml(): string {
        $averageDailyForEveryone = 71;
        $m = $this->getRewardMeasurement("Daily Sugar Allowance", GramsUnit::ID, $averageDailyForEveryone,
                NutrientsVariableCategory::IMAGE_URL);
        $html = $this->getRewardBar($m, $averageDailyForEveryone . " grams");
        return $html;
    }
    /**
     * @return string
     */
    private function monetaryExplanation(): string{
        $html = '';
        $html .= "<h4 class=\"text-2xl font-semibold\">How Maximum Monetary Allowance was Determined</h4>";
        $html .= "<p>Two-thirds of children get an allowance. On average, kids in the U.S. get " .
            "<a href='https://www.marketwatch.com/story/5-mistakes-parents-make-when-giving-kids-an-allowance-2016-05-06'>
                $30</a> per week.</p>";
        return $html;
    }
    /**
     * @return string
     */
    private function sugarExplanation(): string{
        $html = '';
        $html .= "<h4 class=\"text-2xl font-semibold\">How Maximum Sugar Allowance was Determined</h4>";
        $html .= "<p>The average American consumes "."<a href='https://www.healthline.com/nutrition/how-much-sugar-per-day#section3'>
                17 teaspoons (71.14 grams)</a> of sugar every day.</p>";
        return $html;
    }
    /**
     * @return string
     */
    private function electronicsExplanation(): string{
        $html = '';
        $html .= "<h4 class=\"text-2xl font-semibold\">How Maximum Screen Time was Determined</h4>";
        $html .= "<p>On average, American 8-to-12-year-olds spent 5 hours on screen media each day. "."Teens average "."<a href='https://www.commonsensemedia.org/Media-use-by-tweens-and-teens-2019-infographic'>
                7 hours and 22 minutes</a> (not including time spent using screens for school or homework).</p>";
        return $html;
    }
    /**
     * @return QMUserVariable
     */
    public function getDailyAverageQMUserVariable(): QMUserVariable {
        $v = UserVariable::findOrCreateByNameOrVariableId($this->getUserId(),
            DailyAverageGradeCommonVariable::NAME);
        if($v->getVariableIdAttribute() !== DailyAverageGradeCommonVariable::ID){
            le("$v should have id ". DailyAverageGradeCommonVariable::ID);
        }
        return $v->getDBModel();
    }
    /**
     * @return QMMeasurement
     */
    public function getLastDailyAverageMeasurement(): QMMeasurement {
        // Outdated measurement gets cached
        //if($this->lastDailyAverageMeasurement){return $this->lastDailyAverageMeasurement;}
        $v = $this->getDailyAverageQMUserVariable();
        $m = $v->getLastDailyMeasurementWithTagsAndFilling();
        if(!$m){
            $tags = $v->getCommonTaggedVariables();
            if(!$tags){
                $tags = $v->getCommonTaggedVariables();
                le("$v should have class subject tags!");
            }
            foreach($tags as $tag){
                $tagMeasurements[$tag->name] = $tag->getValidDailyMeasurementsWithTags();
                $tag->logMeasurementTable();
            }
            $m = $v->getLastDailyMeasurementWithTagsAndFilling();
        }
        return $this->lastDailyAverageMeasurement = $m;
    }
    /**
     * @param AnonymousMeasurement $lastDailyAverageMeasurement
     */
    public function setLastDailyAverageMeasurement(AnonymousMeasurement $lastDailyAverageMeasurement): void{
        $this->lastDailyAverageMeasurement = $lastDailyAverageMeasurement;
    }
    /**
     * @return array
     */
    public function getSpreadsheetRows(): array{
        $rows = [];
        $c = $this->getCurrentAverageGradeVariables();
        foreach($c as $v){
            $rows = array_merge($rows, $v->getSpreadsheetRows());
        }
        $c = $this->getDailyAverageGradeVariables();
        foreach($c as $v){
            $rows = array_merge($rows, $v->getSpreadsheetRows());
        }
        return $rows;
    }
    /**
     * @return string
     */
    public function getTitleAttribute(): string{
        return self::TITLE;
    }
    /**
     * @return string
     */
    public function getSubtitleAttribute(): string{
        $m = $this->getLastDailyAverageMeasurement();
        $lastDailyGrade = round($m->getValue()) . "%";
        return "The Last Daily Average Grade was $lastDailyGrade";
    }
    /**
     * @return string
     */
    public function getImage(): string{
        return BooksVariableCategory::IMAGE_URL;
    }
    /**
     * @return QMUserVariable[]
     */
    public function getCurrentAverageGradeVariables(): array{
        return TigerViewConnector::getCurrentAverageGradeVariables($this->getUserId());
    }
    /**
     * @return QMUserVariable[]
     */
    public function getDailyAverageGradeVariables(): array{
        return TigerViewConnector::getDailyAverageGradeVariables($this->getUserId());
    }
    /**
     * @param QMMeasurement $m
     * @param string|null $leftText
     * @return string
     */
    protected static function getGradeRowBlock(QMMeasurement $m, string $leftText = null): string {
        $lastValue = $m->getValue();
        $lastValue = round($lastValue);
        $tagLine = $m->getSentence();
        $color = QMColor::gradeToColor($lastValue);
        $img = ImageHelper::gradeToFace($lastValue);
        if (!$leftText) {
            $leftText = $m->getVariableName();
            if ($leftText !== DailyAverageGradeCommonVariable::NAME) {
                $leftText = self::getSubjectFromGradeVariableName($leftText);
            }
        }
        $width = 50 + $lastValue / 2;
        $html = BarChartButton::getHtmlWithRightText($leftText, $width, $m->getUrl(),
                $color,
                $img,
            $lastValue."%",
                $tagLine);
        return $html;
    }
    /**
     * @param string $name
     * @param int $unitId
     * @param float $averageDailyForEveryone
     * @param string $image
     * @return QMMeasurement
     */
    private function getRewardMeasurement(string $name,
                                          int $unitId,
                                          float $averageDailyForEveryone,
                                          string $image): QMMeasurement {
        $v = QMUserVariable::findOrCreateByNameOrId($this->getUserId(), $name, [], [
            Variable::FIELD_VARIABLE_CATEGORY_ID => GoalsVariableCategory::ID,
            Variable::FIELD_DEFAULT_UNIT_ID      => $unitId,
            Variable::FIELD_IMAGE_URL            => $image
        ]);
        $dailyAverage = $this->getLastDailyAverageMeasurement();
        $rewardPercent = $this->getRewardPercent();
        $yourDailyAllowance = round($averageDailyForEveryone * $rewardPercent / 100, 2);
        if($m = $v->getMeasurementByStartAt($dailyAverage->getStartAt())){
            if($m->getValue() !== $yourDailyAllowance){
                $m->setValueInCommonUnit($yourDailyAllowance);
                try {
                    $m->save();
                } catch (IncompatibleUnitException | InvalidVariableValueException | InvalidAttributeException |
                ModelValidationException | NoChangesException $e) {
                    le($e);
                }
            }
            return $m;
        }
        $m = new QMMeasurement($dailyAverage->getStartAt(), $yourDailyAllowance);
        $m->setUnitId($unitId);
        $m->setVariableCategory(GoalsVariableCategory::ID);
        $m->setImageUrl($image);
        $m->setMessage("Based on a daily average grade of " . $dailyAverage->getValueUnitString() .
            " on " . $dailyAverage->getDate());
        $fromConnector = $this->getLastDailyAverageMeasurement();
	    $m->setSourceName($fromConnector->sourceName ?? QuantiModoConnector::NAME);
        $m->connectorId = $fromConnector->connectorId;
        if($m->connectorId){
            $m->clientId = $fromConnector->getConnector()->name;
        } else {
            $m->clientId = $fromConnector->clientId;
        }
        $m->connectionId = $fromConnector->connectionId;
        $v->saveMeasurementIfNoneExist($m);
        return $m;
    }
    /**
     * @return float|int
     */
    private function getRewardPercent(): int {
        $dailyAverage = $this->getLastDailyAverageMeasurement();
        $from100 = 100 - $dailyAverage->getValue();
        $twice = $from100 * 3;
        $rewardPercent = (100 - $twice);
        if ($rewardPercent < 0) {
            $rewardPercent = 0;
        }
        return round($rewardPercent);
    }
    /**
     * @inheritDoc
     */
    public function getCategoryDescription(): string{
        return self::CATEGORY_DESCRIPTION;
    }
    /**
     * @inheritDoc
     */
    public function getCategoryName(): string{
        return WpPost::CATEGORY_GRADE_REPORTS;
    }
    /**
     * @return AnalyticalReport
     */
    static public function getDemoReport(): AnalyticalReport {
        $a = new self();
        $a->setUserId(static::DEMO_USER_ID);
        $v = $a->getDailyAverageQMUserVariable();
        $a->setLastDailyAverageMeasurement($v->getLastDailyNonZeroQMMeasurement());
        return $a;
    }
    private function logConnectorMeasurements(){
        $c = TigerViewConnector::getByUserId($this->getUserId());
        $c->logVariables();
        $c->logMeasurements();
    }
    public function getSlugWithNames(): string{
        return QMStr::slugify(static::NAME);
    }
    public function getShowContentView(array $params = []): View{
        $params['obj'] = $this;
        return view('grade-report-content', $this->getShowParams($params));
    }
    public function getShowPageView(array $params = []): View{
        $params['obj'] = $this;
        return view('grade-report', $this->getShowParams($params));
    }
    public function getShowFolderPath():string{
        return $this->getUser()->getShowFolderPath()."/".static::getSlugifiedClassName();
    }
}
