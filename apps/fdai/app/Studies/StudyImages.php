<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Studies;
use App\Buttons\States\PredictorSearchStateButton;
use App\Computers\ThisComputer;
use App\VariableRelationships\QMVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Repos\ImagesRepo;
use App\Traits\HasCauseAndEffect;
use App\Types\QMStr;
use App\UI\CssHelper;
use App\UI\HtmlHelper;
use App\UI\ImageHelper;
use App\Utils\AppMode;
use App\Variables\QMVariableCategory;
use Illuminate\Support\Collection;
class StudyImages {
	public const ROBOT_PUZZLED_PNG = 'https://static.quantimo.do/img/robots/quantimodo-robot-puzzled-213-300.png'; // Need PNG instead of SVG so it works in emails
	private $hasCorrelationCoefficient;
	private $hasCauseAndEffect;
	public $causeVariableImageUrl;
	public $causeVariableSvgUrl;
	public $causeVariablePngUrl;
	public $causeVariableIonIcon;
	public $effectVariableImageUrl;
	public $effectVariableSvgUrl;
	public $effectVariablePngUrl;
	public $effectVariableIonIcon;
	public $gaugeImage;
	public $gaugeImageSquare;
	public $gaugeSharingImageUrl;
	public $imageUrl;
	public $robotSharingImageUrl;
	public $avatar;
	/**
	 * StudyImages constructor.
	 * @param GlobalVariableRelationship|UserVariableRelationship|QMVariableRelationship|null $hasCorrelationCoefficient
	 * @param HasCauseAndEffect|QMStudy|null $hasCauseAndEffect
	 */
	public function __construct($hasCorrelationCoefficient = null, $hasCauseAndEffect = null){
		if($hasCorrelationCoefficient){
			$this->hasCorrelationCoefficient = $hasCorrelationCoefficient;
		}
		if($hasCauseAndEffect){
			$this->hasCauseAndEffect = $hasCauseAndEffect;
		}
		if(!$hasCauseAndEffect && !$hasCorrelationCoefficient){
			return;
		}
		$this->setVariableIconsAndImages();
		$this->gaugeSharingImageUrl = $this->setRobotSharingImageUrl();
		$this->gaugeImage = $this->gaugeImageSquare = ImageHelper::getRobotPuzzledUrl();
		if($hasCorrelationCoefficient && $hasCorrelationCoefficient->getCorrelationCoefficient()){
			$this->setGaugeImage();
			$this->setGaugeImageSquare();
			$this->setGaugeSharingImageUrl();
		}
		$this->getImage();
	}
	/**
	 * @return QMStudy|HasCauseAndEffect
	 */
	public function getHasCauseAndEffect(){
		return $this->hasCauseAndEffect ?? $this->hasCorrelationCoefficient;
	}
	/**
	 * @return QMVariableCategory
	 */
	private function getCauseVariableCategory(): QMVariableCategory{
		if($this->hasCorrelationCoefficient && $this->getCorrelation()->getCauseQMVariableCategory()){
			return $this->getCorrelation()->getCauseQMVariableCategory();
		}
		$study = $this->getHasCauseAndEffect();
		if(!$study){
			le("No Study!");
		}
		return $this->getHasCauseAndEffect()->getCauseQMVariableCategory();
		//return $study->getOrSetCauseQMVariable()->getQMVariableCategory();
	}
	/**
	 * @return QMVariableCategory
	 */
	private function getEffectVariableCategory(): QMVariableCategory{
		if($this->hasCorrelationCoefficient && $this->getCorrelation()->getEffectQMVariableCategory()){
			return $this->getCorrelation()->getEffectQMVariableCategory();
		}
		return $this->getHasCauseAndEffect()->getOrSetEffectQMVariable()->getQMVariableCategory();
	}
	private function setVariableIconsAndImages(){
		if(!isset($this->causeVariableImageUrl) || !$this->causeVariableImageUrl){
			$this->causeVariableImageUrl = $this->causeVariableSvgUrl = $this->getCauseVariableCategory()->getSvgUrl();
			$this->causeVariablePngUrl = $this->getCauseVariableCategory()->getPngUrl();
		}
		if(!isset($this->causeVariableIonIcon) || !$this->causeVariableIonIcon){
			$this->causeVariableIonIcon = $this->getCauseVariableCategory()->getIonIcon();
		}
		if(!isset($this->effectVariableImageUrl) || !$this->effectVariableImageUrl){
			$this->effectVariableImageUrl = $this->effectVariableSvgUrl = $this->getEffectVariableCategory()->getSvgUrl();
			$this->effectVariablePngUrl = $this->getEffectVariableCategory()->getPngUrl();
		}
		if(!isset($this->effectVariableIonIcon) || !$this->effectVariableIonIcon){
			$this->effectVariableIonIcon = $this->getEffectVariableCategory()->getIonIcon();
		}
	}
	/**
	 * @return string
	 */
	private function setGaugeImage(): string {
		return $this->gaugeImage = self::generateGaugeUrl($this->getCorrelation()->getEffectSize());
	}
	/**
	 * @return void
	 */
	private function setGaugeImageSquare(): void{
		$this->gaugeImageSquare = ImageHelper::getImageUrl('gauges/200-200/' .
			$this->getGaugeFilename($this->getCorrelation()->getEffectSize()) . '-200-200.png');
	}
	/**
	 * @param string $effectSize
	 * @return string
	 */
	public static function getGaugeFilename(string $effectSize): string{
		$fileName = 'gauge-'.str_replace(" ", "-", $effectSize).'-relationship';
		$fileName = str_replace('very-', '', $fileName); // TODO:  Make more gauge images
		return $fileName;
	}
	/**
	 * @param string $effectSize
	 * @return string
	 */
	public static function generateGaugeUrl(string $effectSize): string{
		return ImageHelper::getImageUrl('gauges/246-120/'.self::getGaugeFilename($effectSize).'.png');
	}
	/**
	 * @return string
	 */
	private function setGaugeSharingImageUrl(): string {
		return $this->gaugeSharingImageUrl = self::generateGaugeSharingImageUrl($this->getCorrelation()->getEffectSize(),
		                                                                        $this->getCauseVariableCategory(), $this->getEffectVariableCategory());
	}
	/**
	 * @param string $effectSize
	 * @param string|int|QMVariableCategory $causeCategory
	 * @param string|int|QMVariableCategory $effectCategory
	 * @return string
	 */
	public static function generateGaugeSharingImageUrl(string $effectSize, $causeCategory, $effectCategory): string{
		return QMVariableRelationship::S3_IMAGE_PATH.QMVariableRelationship::GAUGE_IMAGE_PATH . self::getGaugeFilename($effectSize) .
		       '_' . self::getCauseEffectFileName($causeCategory, $effectCategory) . '_background.png';
	}
	/**
	 * @param QMVariableCategory|int|string $causeCategory
	 * @param QMVariableCategory|int|string $effectCategory
	 * @return string
	 */
	private static function getCauseEffectFileName($causeCategory, $effectCategory): string{
		if(!$causeCategory instanceof QMVariableCategory){
			$causeCategory = QMVariableCategory::find($causeCategory);
		}
		if(!$effectCategory instanceof QMVariableCategory){
			$effectCategory = QMVariableCategory::find($effectCategory);
		}
		return $causeCategory->getStudyImageFileName().'_'.$effectCategory->getStudyImageFileName();
	}
	/**
	 * @return string
	 */
	private function setRobotSharingImageUrl(): string{
		return $this->robotSharingImageUrl = self::generateVariableCategoriesRobotSharingImageWithBackgroundUrl($this->getCauseVariableCategory(), $this->getEffectVariableCategory());
	}
	/**
	 * @param QMVariableCategory|int|string $causeCategory
	 * @param QMVariableCategory|int|string $effectCategory
	 * @return string
	 */
	public static function generateVariableCategoriesRobotSharingImageWithBackgroundUrl($causeCategory, $effectCategory): string{
		return ImageHelper::STATIC_BASE_URL.QMVariableRelationship::VARIABLE_CATEGORIES_COMBINED_ROBOT_BACKGROUND .
		       self::getCauseEffectFileName($causeCategory, $effectCategory) . '_robot_background.png';
	}
	/**
	 * @param QMVariableCategory|int|string $causeCategory
	 * @param QMVariableCategory|int|string $effectCategory
	 * @return string
	 */
	public static function generateCategoriesWithoutBackgroundRobotImageUrl($causeCategory, $effectCategory): string{
		return QMVariableRelationship::S3_IMAGE_PATH.QMVariableRelationship::VARIABLE_CATEGORIES_COMBINED_ROBOT .
		       self::getCauseEffectFileName($causeCategory, $effectCategory) . '_robot.png';
	}
	/**
	 * @param string $gauge
	 * @param string $tagLine
	 * @return string
	 */
	public static function generateGaugeImagesTagLine(string $gauge, string $tagLine): string{
		$html = "
            <div style=\"text-align: center; max-width: 95%; margin: auto\">
                <div> $gauge </div>
                <div> $tagLine </div>
            </div>
        ";
		if(AppMode::isTestingOrStaging()){
			$html = HtmlHelper::checkForMissingHtmlClosingTags($html, __FUNCTION__);
		}
		if(str_contains($html, "/public/img/")){
			le("getGaugeAndImagesWithTagLine should not be $html");
		}
		QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 100);
		return $html;
	}
	/**
	 * @param QMVariableCategory|int|string $causeCategory
	 * @param QMVariableCategory|int|string $effectCategory
	 * @return string
	 */
	public static function generateSmallCategoriesRobotImageUrl($causeCategory, $effectCategory): string{
		return QMVariableRelationship::S3_IMAGE_PATH.QMVariableRelationship::VARIABLE_CATEGORIES_COMBINED_ROBOT_SMALL .
		       self::getCauseEffectFileName($causeCategory, $effectCategory) . '_robot-600-315.png';
	}
	/**
	 * @return QMVariableRelationship|GlobalVariableRelationship|UserVariableRelationship
	 */
	public function getCorrelation() {
		return $this->hasCorrelationCoefficient;
	}
	/**
	 * @param GlobalVariableRelationship[]|Collection $correlations
	 * @param string|null $title
	 * @param string|null $description
	 * @param bool $addSharingButtons
	 * @return string
	 */
	public static function getStudiesListWithGauges($correlations, ?string $title, ?string $description,
		bool $addSharingButtons = false): string{
		if(!$correlations){
			le("No user_variable_relationships provided to " . __FUNCTION__);
		}
		$html = '';
		if($title){
			$html .= CssHelper::addTitleCss($title);
		}
		if($description){
			$html .= CssHelper::addBodyCss($description);
		}
		$c = QMVariableRelationship::getFirst($correlations);
		$html .= '
                <div style="display: inline-block;">
                    <img style="max-width: 100%;"
                        src="' . $c->getStudyImages()->getRobotSharingImageUrl() . '"
                        alt="robot image">
                </div>
            ';
		foreach($correlations as $c){
			$html .= $c->getTitleGaugesTagLineHeader(true, true);
			if($addSharingButtons){ // TODO: Make sharing buttons safe for email so we can always add them
				$html = $c->getOrAddSocialSharingButtons($html);
			}
			$html .= $c->getButton()->getRoundOutlineWithIcon();
		}
		$html .= PredictorSearchStateButton::getMoreDiscoveriesButtonHtml();
		//$html .= HtmlHelper::getStartTrackingHtml();
		//$html .= HtmlHelper::getBrownSocialLinksHtml();
		//$html .= DownloadButtonsQMCard::getDownloadButtonsHtml();
		$html = "
            <div class=\"page-wrapper\" style=\"font-family: sans-serif; text-align: center;\">
                $html
            </div>
        ";
		QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 30);
		return $html;
	}
	/**
	 * @return string
	 */
	public function getGaugeImage(): string{
		return $this->gaugeImage ?: $this->setGaugeImage();
	}
	/**
	 * @return string
	 */
	public function getRobotSharingImageUrl(): string{
		return $this->robotSharingImageUrl ?: $this->setRobotSharingImageUrl();
	}
	/**
	 * @return string
	 */
	public function getCauseVariableImageUrl(): string{
		return $this->causeVariableImageUrl;
	}
	/**
	 * @return string
	 */
	public function getEffectVariableImageUrl(): string{
		return $this->effectVariableImageUrl;
	}
	/**
	 * @return string
	 */
	public function getCauseVariablePngUrl(): string {
		$url = $this->causeVariablePngUrl;
		if(strpos($url, "/public/img/") !== false){
			le("causeVariablePngUrl should not be $url");
		}
		return $url;
	}
	/**
	 * @return string
	 */
	public function getCauseVariableSvgUrl(): string{
		$url = $this->causeVariableSvgUrl;
		if(strpos($url, "/public/img/") !== false){
			le("causeVariableSvgUrl should not be $url");
		}
		return $this->causeVariableSvgUrl;
	}
	/**
	 * @return string
	 */
	public function getEffectVariablePngUrl(): string{
		return $this->effectVariablePngUrl;
	}
	/**
	 * @return string
	 */
	public function getEffectVariableSvgUrl(): string{
		return $this->effectVariableSvgUrl;
	}
	/**
	 * @return string
	 */
	public function getAvatar(): string {
		if($this->hasCauseAndEffect && $this->getHasCauseAndEffect()->principalInvestigator && $this->getHasCauseAndEffect()->getPrincipalInvestigator()
		                                                                                    ->hasNonGravatarOrNonDefaultAvatar()){
			$this->avatar = $this->getHasCauseAndEffect()->getPrincipalInvestigator()->getAvatar();
		}else if($this->getGaugeImage()){
			$this->avatar = $this->getGaugeImage();
		}
		return $this->avatar;
	}
	/**
	 * @return string
	 */
	public function getImage(): string {
		$c = $this->getCorrelation();
		if($c && $c->getCorrelationCoefficient()){
			$this->imageUrl = $this->setGaugeSharingImageUrl();
		}
		if (!$this->imageUrl) {
			$this->imageUrl = $this->setRobotSharingImageUrl();
		}
		return $this->imageUrl;
	}
	/**
	 * @return string
	 */
	public static function getRobotPuzzledHtml(): string {
		return ImageHelper::getRobotPuzzledHtml();
	}
	public static function generateCategoryImages(){
		ThisComputer::exec("bash scripts/generate_study_images.sh");
	}
	/**
	 * @noinspection PhpUnused
	 */
	public static function uploadStudyImages(){
		ImagesRepo::convertLargePngsToJpgs();
		ImagesRepo::uploadToS3Public();
	}
}
