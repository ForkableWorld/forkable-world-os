<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\VariableRelationships;
use App\Buttons\Links\HelpButton;
use App\Cards\StartTrackingQMCard;
use App\Logging\QMLog;
use App\Models\GlobalVariableRelationship;
use App\Slim\Model\QMResponseBody;
use App\Slim\View\Request\QMRequest;
use App\Studies\QMStudy;
use App\Studies\StudyImages;
use App\Types\QMArr;
use App\UI\ImageHelper;
use App\UI\IonIcon;
use Illuminate\Support\Collection;
abstract class CorrelationListExplanationResponseBody extends QMResponseBody {
    const THE_MORE_DATA_I_HAVE_THE_MORE_ACCURATE_YOUR_RESULTS_WILL_BE_SO_TRACK_REGULARLY =
        "The more data I have the more accurate your results will be. So track regularly!";
    const START_TRACKING = 'Start Tracking';
    public $ionIcon;
    public $image;
    public $startTracking;
    public $description;
    public $title;
    public $html;
    protected $correlations;
    /**
     * CorrelationListExplanationResponseBody constructor.
     * @param GlobalVariableRelationship[]|Collection|null $correlations
     */
    public function __construct($correlations = null){
        parent::__construct();
        $this->setStartTrackingImproveAccuracy();
        $this->correlations = $correlations;
        $this->image = ImageHelper::getCorrelationsExplanationImage();
        $this->setIonIcon(IonIcon::ion_icon_discoveries);
        $this->setAvatar(ImageHelper::getStudyPngUrl());
        if($correlations && !QMRequest::urlContains('/studies')){
            $this->setListResponseHtml();
        } // This will be done in study response
    }
    protected function setStartTrackingImproveAccuracy(){
        $this->setStartTracking([
            'title'       => 'Improve Accuracy',
            'description' => self::THE_MORE_DATA_I_HAVE_THE_MORE_ACCURATE_YOUR_RESULTS_WILL_BE_SO_TRACK_REGULARLY,
            'button'      => [
                'text' => self::START_TRACKING,
                'link' => QMRequest::host()
            ]
        ]);
    }
    /**
     * @param $filters
     * @return string
     */
    public static function getIncreasingDecreasing($filters): string{
        if(isset($filters['correlationCoefficient'])){
            if(strpos($filters['correlationCoefficient'], 'gt') !== false){
                return 'increasing ';
            }
            if(strpos($filters['correlationCoefficient'], 'lt') !== false){
                return 'decreasing ';
            }
        }
        return '';
    }
    /**
     * @param array $startTracking
     */
    public function setStartTracking($startTracking){
        $this->startTracking = $startTracking;
    }
    /**
     * @return string
     */
    public function getIonIcon(): string{
        return $this->ionIcon;
    }
    /**
     * @param string $ionIcon
     */
    public function setIonIcon(string $ionIcon){
        $this->ionIcon = $ionIcon;
    }
    /**
     * @return string
     */
    public function getSubtitleAttribute(): string{
        return $this->description;
    }
	/**
	 * @param string $defaultDescription
	 * @param array $filters
	 * @param string $suffix
	 */
    public function setDescription(string $defaultDescription, array $filters, string $suffix){
        $this->description = $defaultDescription;
        if(isset($filters['effectVariableName']) && isset($filters['causeVariableName'])){
            $this->description = "This study explores the relationship between ".$filters['causeVariableName']." and ".
                $filters['effectVariableName'].$suffix;
        }else if(isset($filters['effectVariableName'])){
            $this->description = 'These factors are most predictive of '.self::getIncreasingDecreasing($filters).
                $filters['effectVariableName'].$suffix;
        }else if(isset($filters['causeVariableName'])){
            $this->description = 'These are the outcomes most likely to be influenced by '.
                self::getIncreasingDecreasing($filters).$filters['causeVariableName'].$suffix;
        }
    }
    /**
     * @return string
     */
    public function getTitleAttribute(): string {
        return $this->title;
    }
    /**
     * @param $defaultTitle
     * @param array $filters
     * @internal param string $title
     */
    public function setTitle(string $defaultTitle, array $filters = []){
        $this->title = $defaultTitle;
        if(isset($filters['effectVariableName']) && isset($filters['causeVariableName'])){
            $this->title = "Relationship between ".$filters['causeVariableName']." and ".$filters['effectVariableName'];
        }else if(isset($filters['effectVariableName'])){
            $this->title = "Top Predictors of ".$filters['effectVariableName'];
        }else if(isset($filters['causeVariableName'])){
            $this->title = "Top Outcomes of ".$filters['causeVariableName'];
        }
        if(!empty($this->title)){
            $this->summary = $this->title;
        }
    }
    /**
     * @return string
     */
    public function getImage(): string {
        return $this->image;
    }
    /**
     * @param string $image
     */
    public function setImage(string $image){
        $this->image = $image;
    }
    /**
     * @return string
     */
    public function getHtml(): string {
        if(!$this->html){
            $this->setListResponseHtml();
        }
        return $this->html;
    }
    /**
     * @return GlobalVariableRelationship[]|QMUserVariableRelationship[]
     */
    public function getCorrelations(): array {
        return QMArr::toArray($this->correlations);
    }
    /**
     * @return bool|QMVariableRelationship
     */
    protected function getFirstCorrelationOrStudy(){
        if(!isset($this->correlations[0])){
            return false;
        }
        return $this->correlations[0];
    }
    /**
     * @return string
     */
    public function setListResponseHtml(): string {
        $correlations = $this->getCorrelationsOrStudies();
        if(!$correlations){
            $robot = StudyImages::getRobotPuzzledHtml();
            $button = HelpButton::getHelpButtonHtml();
            $html = "
                <h2>
                    No Matching Studies Found
                </h2>
                $robot
                $button
            ".StartTrackingQMCard::getStartTrackingHtml();
            QMLog::error("No Matching Studies Found with params " . \App\Logging\QMLog::print_r(QMRequest::getQuery(), true));
            return $this->html = $html;
        }
        return $this->html = StudyImages::getStudiesListWithGauges($correlations, $this->title, $this->description);
    }
    /**
     * @return QMVariableRelationship[]|QMStudy[]
     */
    public function getCorrelationsOrStudies(){
        return $this->correlations;
    }
}
