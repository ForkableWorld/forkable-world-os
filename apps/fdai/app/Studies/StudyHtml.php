<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Studies;
use App\Buttons\QMButton;
use App\Buttons\States\StudyJoinStateButton;
use App\Cards\DownloadButtonsQMCard;
use App\Charts\HighchartExport;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\BaseException;
use App\Exceptions\DuplicateFailedAnalysisException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\InsufficientMemoryException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Properties\Base\BaseUserStudyTextProperty;
use App\Slim\Model\StaticModel;
use App\Slim\Model\User\PublicUser;
use App\Slim\Model\User\QMUser;
use App\Traits\HasCauseAndEffect;
use App\Traits\HasCorrelationCoefficient;
use App\Types\QMStr;
use App\UI\CssHelper;
use App\UI\HtmlHelper;
use App\UI\ImageUrls;
use App\UI\MetaHtml;
use App\Utils\AppMode;
use App\Utils\EnvOverride;
use App\Utils\WikiHelper;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use LogicException;
use Pelago\Emogrifier\HtmlProcessor\HtmlNormalizer;
class StudyHtml extends StaticModel {
    private $hasCorrelationsCoefficient;
    public $chartHtmlWithEmbeddedImages;
    public $chartHtmlWithLinkedImages;
    public $downloadButtonsHtml;
    public $fullStudyHtml;
    public $participantInstructionsHtml;
    public $socialSharingButtonHtml;
    public $statisticsTableHtml;
    public $studyAbstractHtml;
    public $studyHeaderHtml;
    public $studyImageHtml;
    public $studyMetaHtml;
    public $studyTextHtml;
    public $studyTitleHtml;
    public $tagLineHtml;
    /**
     * StudyImages constructor.
     * @param HasCorrelationCoefficient|null $hasCorrelationsCoefficient
     */
    public function __construct($hasCorrelationsCoefficient = null){
        if(!$hasCorrelationsCoefficient){
            return;
        }
        $this->setHasCorrelationsCoefficient($hasCorrelationsCoefficient);
        $this->getTitleGaugesTagLineHeader(true, true);
    }
    /**
     * @return string
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     * @throws DuplicateFailedAnalysisException
     */
    public function setEmbeddedCharts(): ?string {
        $type = HighchartExport::DEFAULT_IMAGE_FORMAT;
        $html = $this->getCorrelationChartHtmlWithEmbeddedImageOrReasonForFailure($type);
        $fromUrl = $this->getStudyLinks()->getRecalculateStudyUrl();
        //$embedImages = true; // Let's embed images so studies without enough data for correlation charts at least have something
        $linkToVariablePosts = true; // Let's link to variable posts instead of including charts to save DB space in wp_posts table
        $cause = $this->getCauseVariable();
        $effect = $this->getEffectVariable();
        if($linkToVariablePosts){
            $html .= $cause->getChartsPostAndSettingsButton($type, false, $fromUrl);
            $html .= $effect->getChartsPostAndSettingsButton($type, false, $fromUrl);
        } else {
            $html .= $cause->getChartsPostAndSettingsButton($type, true, $fromUrl);
            $html .= $effect->getChartsPostAndSettingsButton($type, true, $fromUrl);
        }
        $this->chartHtmlWithEmbeddedImages = $html;
        $this->exceptionIfStudyDecoupled('chartHtmlWithEmbeddedImages');
        QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 500);
        return $html;
    }
    /**
     * @param bool $wiki
     * @return string
     */
    public function getEmbeddedCharts(bool $wiki = false): ?string {
        if(!$this->chartHtmlWithEmbeddedImages){
            $this->setEmbeddedCharts();
        }
        if($wiki){
            return WikiHelper::convertHtmlToWiki($this->chartHtmlWithEmbeddedImages);
        }
        return $this->chartHtmlWithEmbeddedImages;
    }
    /**
     * @return string
     */
    public function getEmbeddedOrLinkedChartHtml(): ?string{
        $correlationOrStudy = $this->getHasCauseAndEffect();
        $useLinked = false;  // Constantly checking S3 is slow and dealing with all this complexity of using linked images causes lots of bugs
        if ($useLinked && $correlationOrStudy->getIsPublic()) {
            if($this->chartHtmlWithLinkedImages){  // TODO: Remove this field because we use PNG now
                return $this->chartHtmlWithLinkedImages;
            }
            //$html = $this->getChartHtmlWithLinkedSvgs();
            $html = $this->getChartHtmlWithLinkedImages();
            $this->chartHtmlWithLinkedImages = $html; // TODO: Remove this field because we use PNG now
        } else {
            //$html = $this->getChartHtmlWithEmbeddedImages();
            if($this->chartHtmlWithEmbeddedImages){  // TODO: Remove this field because we use PNG now
                return $this->chartHtmlWithEmbeddedImages;
            }
            $html = $this->getEmbeddedCharts();
            $this->chartHtmlWithEmbeddedImages = $html; // TODO: Remove this field because we use PNG now
        }
        QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 100);
        return $html;
    }
    /**
     * @param bool $wiki
     * @return string
     */
    public function getChartHtmlWithLinkedImages(bool $wiki = false): ?string {
        if(!$this->chartHtmlWithLinkedImages){
            $this->setChartHtmlWithLinkedImages();
        }
        if($wiki){
            return WikiHelper::convertHtmlToWiki($this->chartHtmlWithLinkedImages);
        }
        return $this->chartHtmlWithLinkedImages;
    }
    /**
     * @param string $imageType
     * @return string |null
     */
    public function setChartHtmlWithLinkedImages(string $imageType = HighchartExport::DEFAULT_IMAGE_FORMAT): ?string {
        $study = $this->getHasCauseAndEffect();
        $html = '';
        try {
            $charts = $study->getOrSetCharts();
            $charts->setImageUrlsAndGenerateIfNecessary($imageType);
            $html .= $charts->getChartHtmlWithLinkedImages($imageType);
        } catch (TooSlowToAnalyzeException $e) {
            if(AppMode::isApiRequest()){
                $html .= "
                    <p style=\"text-align: center;\">
                        Relationship charts are being generated.  Check back soon or contact help@curedao.org.
                    </p>
                ";
                $this->getHasCauseAndEffect()->queue($e->getMessage());
            } else {
                /** @var LogicException $e */
                le("TooSlowException should only be thrown during API requests!");
            }
        } catch (NotEnoughDataException $e) {
            // Make sure we catch exceptions here so variable charts are returned to give an idea of what data we have
            $html .= ExceptionHandler::renderHtml($e);
        }
        $html .= $study->getCauseQMVariable()->getChartGroup()->getChartHtmlWithLinkedImages($imageType) .
            $study->getEffectQMVariable()->getChartGroup()->getChartHtmlWithLinkedImages($imageType);
        return $this->chartHtmlWithLinkedImages = $html;
    }
    /**
     * @return QMStudy|HasCauseAndEffect
     */
    public function getHasCauseAndEffect(){
		if($this->hasCorrelationsCoefficient instanceof QMStudy){
			$this->hasCorrelationsCoefficient->studyHtml = $this;
		}
        return $this->hasCorrelationsCoefficient;
    }
    /**
     * @return StudyImages
     */
    private function getStudyImages(): StudyImages{
        return $this->getHasCauseAndEffect()->getStudyImages();
    }
    /**
     * @return StudyLinks
     */
    private function getStudyLinks(): StudyLinks{
        return $this->getHasCauseAndEffect()->getStudyLinks();
    }
    /**
     * @return StudyText
     */
    public function getStudyText(): StudyText{
        $text = $this->getHasCauseAndEffect()->getStudyText();
        if($this->hasCorrelationsCoefficient){$text->setHasCauseAndEffect($this->hasCorrelationsCoefficient);}
        return $text;
    }
    /**
     * @return string
     */
    public function generateFullPageWithHead(): string{
        $study = $this->getHasCauseAndEffect();
        try {
            $study->getHasCorrelationCoefficient();
        } catch (NotEnoughDataException $e) {
            $study->logError($e->getMessage());
			return $this->fullStudyHtml = HtmlHelper::renderReportWithTailwind($e->getHtml(), $study, ['report' => $study]);
        }
	    $content = $study->getShowContent();
		if(AppMode::isTestingOrStaging()){HtmlHelper::validateHtml($content, 'study->getShowContent');}
	    $html = HtmlHelper::renderReportWithTailwind($content, $study, ['report' => $study]);
	    if(AppMode::isTestingOrStaging()){HtmlHelper::validateHtml($content, 'HtmlHelper::renderReport(study->getShowContent)');}
		$this->fullStudyHtml = $html;
		return $html;
    }
    /**
     * @param bool $arrows
     * @param bool $hyperLinkNames
     * @return string
     */
    public function getTitleGaugesTagLineHeader(bool $arrows = false, bool $hyperLinkNames = false): string {
        return $this->studyHeaderHtml = $this->getHasCauseAndEffect()->getTitleGaugesTagLineHeader($arrows, $hyperLinkNames);
    }
    /**
     * @return string
     */
    public function getGaugeAndImagesWithTagLine(): string {
        $gauge = $this->getGaugeAndVariableImages();
        $tagLine = $this->getTagLineHtml();
        return StudyImages::generateGaugeImagesTagLine($gauge, $tagLine);
    }
    /**
     * @return string
     */
    public function getGaugeAndVariableImages(): string {
        return '<div class="gauge-and-images" style="justify-content:space-around;">
            <span style="display: inline-block; max-width: 10%;">
                <img style="max-width: 100%; max-height: 150px;" src="'.$this->getStudyImages()->getCauseVariablePngUrl().'" alt="cause image">
            </span>
            <span style="display: inline-block; max-width: 65%;">
                <img style="max-width: 100%; max-height: 200px;" src="'.$this->getStudyImages()->getGaugeImage().'" alt="gauge image">
            </span>
            <span style="display: inline-block; max-width: 10%;">
                <img style="max-width: 100%; max-height: 150px;" src="'.$this->getStudyImages()->getEffectVariablePngUrl().'" alt="effect image">
            </span>
        </div>';
    }
    /**
     * @param bool $wiki
     * @return string
     */
    public function getDownloadButtons(bool $wiki = false): string{
        $this->downloadButtonsHtml = DownloadButtonsQMCard::getDownloadButtonsHtml();
        if($wiki){
            return WikiHelper::convertHtmlToWiki($this->downloadButtonsHtml);
        }
        return $this->downloadButtonsHtml;
    }
    /**
     * @param bool $cleanHtml
     * @return string
     * @throws TooSlowToAnalyzeException
     */
    public function setWithEmbeddedCharts(bool $cleanHtml = false): string{
        $this->getStudyAbstractHtml();
        $this->getTitleHtml();
        $html = $this->getSocialMetaHtml();
        $html .= $this->getStudyImageHtml();
        $html .= $this->getTitleGaugesTagLineHeader(true, true);
        $html .= $this->getOrAddSocialSharingButtons();
        $html .= $this->getJoinStudyButtonHTML();
        //$html .= $this->getInteractiveStudyButton(); // Don't want to overload servers and plenty of other buttons lead to app
        $html .= $this->setEmbeddedCharts();
        $html .= $this->getStudyTextHtml();
        $html .= $this->setStatisticsTable(); // We keep having a stale table
        $html .= $this->getOrAddSocialSharingButtons();
        //$html .= $this->getParticipantInstructionsHtml();
        $html .= $this->getPrincipalInvestigatorHtml();
        //$html .= $this->getDownloadButtons(); // DownloadButtons should be in footer and participant instructions. Also, we don't want to promote unmaintainable apps
        if ($cleanHtml) { // TODO: Why is cleaning necessary?
            $html = HtmlNormalizer::fromHtml($html)->render();  // Need to clean for WP post or gutenberg breaks sometimes
        }
        $html = HtmlHelper::globalWrapper($html);
        $this->setFullStudyHtml($html);
        $this->exceptionIfStudyDecoupled('fullStudyHtml');
        QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 300);
        return $html;
    }
    /**
     * @return string
     */
    public function generatePostContent(): string {
        $html = '';
        $html .= $this->getJoinStudyButtonHTML();
        //$html .= $this->getInteractiveStudyButton(); // Don't want to overload servers and plenty of other buttons lead to app
        try {
            $html .= $this->setEmbeddedCharts();
        } catch (DuplicateFailedAnalysisException $e) {
            le($e);
        } catch (NotEnoughDataException $e) {
            $this->addNotEnoughDataForCorrelationMessage($e, $html);
        } catch (TooSlowToAnalyzeException $e) {
            $this->getHasCauseAndEffect()->queue($e->getMessage());
        } catch (\Throwable $e){
            QMLog::error("Couldn't get chart images so using dynamic because: ".$e->getMessage());
            try {
                $html .= $this->getHasCauseAndEffect()->getOrSetCharts()->getHtmlWithDynamicCharts(true);
            } catch (NotEnoughDataException $e) {
                $this->addNotEnoughDataForCorrelationMessage($e, $html);
            }
        }
        $html .= $this->getStudyTextHtml();
        $html .= $this->setStatisticsTable(); // We keep having a stale table
        $html .= $this->getParticipantInstructionsHtml();
        $html .= $this->getOrAddSocialSharingButtons();
        QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 300);
        return $html;
    }
    /**
     * @return string
     */
    public function getJekyllStudyHtml(): string{
        $html = // Don't include these because google indexes individual category images
            //'<div style="text-align: center;">'.$this->getGaugeAndVariableImages().'</div><br>'.
            $this->getTagLineHtml().
            $this->getJoinStudyButtonHTML().
            //$this->getInteractiveStudyButton(). // Don't want to overload servers and plenty of other buttons lead to app
            $this->getEmbeddedOrLinkedChartHtml().
            $this->getStudyTextHtml().
            $this->getStatisticsTable().
            $this->getParticipantInstructionsHtml().
            "<br>".$this->getJoinStudyButtonHTML()."<br>".
            $this->getPrincipalInvestigatorHtml();
        //return $html;
        $cleanHtml = HtmlNormalizer::fromHtml($html)->render();
        $cleanHtml = str_replace('<!DOCTYPE html>', '', $cleanHtml);
        QMStr::errorIfLengthGreaterThan($cleanHtml, __FUNCTION__, 100);
        return $cleanHtml;
    }
    /**
     * @return string
     */
    public function getWiki(): string{
        $txt = WikiHelper::convertHtmlToWiki($this->getStudyTextHtml());
        return //$this->setStudyMetaHtml(true) .
            //$this->getStudySummaryBoxHtml(true) .
            //$this->getStudyImageHtml(true) .
            $txt.//this->getSocialSharingButtonHtml(true) .
            $this->getEmbeddedOrLinkedChartHtml().$this->getStatisticsTable(true)
            //. $this->getParticipantInstructionsHtml(true)
            //. $this->getDownloadButtonsHtml(true)
            ;
    }
    /**
     * @return string
     */
    public function getFullStudyHtml(): string{
        $this->getStudyAbstractHtml();
		if(!$this->studyAbstractHtml){le('!$this->studyAbstractHtml');}
        return $this->fullStudyHtml ?: $this->generateFullPageWithHead();
    }
    public function getKeyWordString(): string {
        return $this->getHasCauseAndEffect()->getKeyWordString();
    }
    /**
     * @return string
     */
    public function generateFullStudyHtml(): string{
        $this->setBasicHtmlProperties();
        $html = $this->getShowContent();
		if(!$this->studyAbstractHtml){le('!$this->studyAbstractHtml');}
        return $this->setFullStudyHtml($html);
    }
    /**
     * @param bool $wiki
     * @return string
     */
    public function getParticipantInstructionsHtml(bool $wiki = false): string{
        if(!$this->participantInstructionsHtml){
            $this->setParticipantInstructionsHtml();
        }
        if($wiki){
            return WikiHelper::convertHtmlToWiki($this->participantInstructionsHtml);
        }
        return $this->participantInstructionsHtml;
    }
    /**
     * @return string
     */
    public function setParticipantInstructionsHtml(): string{
        //$templateParameters = $this->getParticipantInstructionTemplateParameters();
        //$emailHtml = HtmlHelper::renderBlade('email.default-email', $templateParameters);
        //return $this->participantInstructionsHtml = $emailHtml;
        $causeVariable = $this->getCauseVariable();
        $effectVariable = $this->getEffectVariable();
        $cause = $causeVariable->setTrackingInstructionsHtml();
        $effect = $effectVariable->setTrackingInstructionsHtml();
        $html = "<h3>Tracking ".$causeVariable->getOrSetVariableDisplayName()."</h3> ".$cause.
            " <br> <h3>Tracking ".$effectVariable->getOrSetVariableDisplayName()."</h3> ".$effect;
        //$html .= "<br>".$this->getJoinStudyButton(false);
        $this->participantInstructionsHtml = $html;
        $this->exceptionIfStudyDecoupled('participantInstructionsHtml');
        QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 100);
        return $html;
    }
    /**
     * @param bool $arrows
     * @param bool $hyperLinkNames
     * @return string
     */
    public function getTitleHtml(bool $arrows = false, bool $hyperLinkNames = false): string{
        $t = $this->getStudyText();
		if($hyperLinkNames){
			$title = $t->getStudyTitleWithLinks($arrows);
		} else {
			$title = $t->getStudyTitle($arrows);
		}
        $html = $this->studyTitleHtml = "
            <h1 style=\"text-align: center; margin: 0.67em 0;\" class=\"study-title text-3xl\">
                $title
            </h1>
        ";
        QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 100);
        return $html;
    }
    /**
     * @return string
     */
    public function getSocialMetaHtml(): string{
        $txt = $this->getStudyText();
        $metaHtml =
            new MetaHtml($txt->getStudyTitle(), $txt->getSubtitleAttribute(), $this->getStudyImages()->getImage(),
                $this->getStudyLinks()->getStudyLinkStatic());
        $socialMetaHtml = $metaHtml->getSocialMetaHtml();
        $this->studyMetaHtml = $socialMetaHtml;
        $this->exceptionIfStudyDecoupled('studyMetaHtml');
        QMStr::errorIfLengthGreaterThan($socialMetaHtml, __FUNCTION__, 100);
        return $socialMetaHtml;
    }
    /**
     * @return string
     */
    public function getStudyImageHtml(): string {
        $sImages = $this->getStudyImages();
        $url = $sImages->getImage();
        $title = $this->getStudyText()->getStudyTitle();
        $maxWidth = CssHelper::GLOBAL_MAX_POST_CONTENT_WIDTH;
        $html = "
            <div id='study-image-container' style='text-align: center; margin: auto;'>
                <img id='study-image' style=\"width: 100%; text-align: center; margin: auto; max-width: ".$maxWidth."px;\"
                 src=\"$url\"
                 alt=\"$title\">
            </div>
        ";
        $this->studyImageHtml = $html;
        $this->exceptionIfStudyDecoupled('studyImageHtml');
        QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 100);
        return $html;
    }
    /**
     * @return string
     */
    public function getStudyTextHtml(): string {
        $html = "<div id=\"study-text\">";
        $sections = $this->getHasCauseAndEffect()->getStudySectionsArray();
        foreach($sections as $section){$html .= $section->getHtml();}
        $html .= '</div>';
        if(AppMode::isTestingOrStaging()){$html = HtmlHelper::checkForMissingHtmlClosingTags($html, __FUNCTION__);}
        $html = BaseUserStudyTextProperty::humanizeStudyText($html);
        QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 100);
        return $this->studyTextHtml = $html;
    }
    /**
     * @param HasCauseAndEffect|QMStudy $hasCorrelationsCoefficient
     */
    public function setHasCorrelationsCoefficient($hasCorrelationsCoefficient): void{
		//if($study->studyHtml && $study->studyHtml->fullStudyHtml && !$this->fullStudyHtml){le('$study->studyHtml &&
	    // $study->studyHtml->fullStudyHtml && !$this->fullStudyHtml');}
        $hasCorrelationsCoefficient->studyHtml = $this;
        $this->hasCorrelationsCoefficient = $hasCorrelationsCoefficient;
    }
    /**
     * @return QMCommonVariable|QMUserVariable
     */
    private function getCauseVariable(){
        return $this->getHasCauseAndEffect()->getOrSetCauseQMVariable();
    }
    /**
     * @return QMCommonVariable|QMUserVariable
     */
    private function getEffectVariable(){
        return $this->getHasCauseAndEffect()->getOrSetEffectQMVariable();
    }
    /**
     * @param bool $wiki
     * @return string
     *
     */
    public function getSocialShareTextHtml(bool $wiki = false): string{
        $html = '<br><a href="'.$this->getStudyLinks()->getStudyLinkFacebook().'">Share to Facebook</a> |
            <a href="'.$this->getStudyLinks()->getStudyLinkTwitter().'">Tweet</a>';
        //<a href="'.$this->getStudyLinks()->getStudyLinkGoogle().'" target="_blank">Share on G+</a><br>';
        if($wiki){
            return WikiHelper::convertHtmlToWiki($html);
        }
        QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 100);
        return $html;
    }
    /**
     * @return string
     */
    public function getStudyAbstractHtml(): string {
		if($this->hasCorrelationsCoefficient){
			$abs = $this->hasCorrelationsCoefficient->getStudyAbstract();
		} else {
			$abs = $this->getHasCauseAndEffect()->getStudyQuestion();
		}
        $html = '<div class="study-section-body">'.$abs.'</div>';
        $this->studyAbstractHtml = $html;
		if(!$this->studyAbstractHtml){le('!$this->studyAbstractHtml');}
		$this->getHasCauseAndEffect()->studyHtml = $this;
        return $html;
    }
    /**
     * @return string
     */
    public function getTagLineHtml(): string{
        $line = $this->getStudyText()->getTagLine();
        $html = "
            <div class=\"study-tag-line text-2xl\"
             style=\"padding: 10px; text-align: center;\">
                $line
            </div>
        ";
        return $this->tagLineHtml = $html;
    }
    /**
     * @return QMGlobalVariableRelationship|QMUserVariableRelationship
     * @throws NotEnoughDataException
     */
    private function getHasCorrelationCoefficient(){
        return $this->getHasCauseAndEffect()->getHasCorrelationCoefficient();
    }
    /**
     * @param bool $wiki
     * @return string
     */
    public function getStatisticsTable($wiki = false): string{
        if(!$this->statisticsTableHtml){
            $this->setStatisticsTable();
        }
        if($wiki && $this->statisticsTableHtml){
            return WikiHelper::convertHtmlToWiki($this->statisticsTableHtml);
        }
        return $this->statisticsTableHtml;
    }
    /**
     * @return string
     */
    public function setStatisticsTable(): string{
        $html = '';
        try {
            $html .= $this->getHasCorrelationCoefficient()->getStatisticsTableHtml();
        } catch (NotEnoughDataException $e) {
            $html = $this->addNotEnoughDataForCorrelationMessage($e, $html);
        }
        $html .= $this->getCauseVariable()->getStatisticsTableHtml();
        $html .= $this->getEffectVariable()->getStatisticsTableHtml();
        $this->statisticsTableHtml = $html;
        $this->exceptionIfStudyDecoupled('statisticsTableHtml');
        $html = "
            <div id=\"statistics-tables-container\"
            style=\"margin: auto;\">
                $html
            </div>
        ";
        QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 100);
        return $html;
    }
    /**
     * @return string
     */
    public function getStaticStudyButton(): string {
        $links = $this->getStudyLinks();
        $link = $links->getStudyLinkStatic();
        $button = $this->getButton();
        $button->setTextAndTitle("Go To Full Study");
        $button->setUrl($link);
        return $button->getTailwindCenteredRoundOutlineWithIcon();
    }
    /**
     * @return string
     */
    public function getJoinStudyButtonHTML(): string {
        return (new StudyJoinStateButton($this->getHasCauseAndEffect()))->getCenteredRoundOutlineWithIcon();
    }
    /**
     * @return string
     */
    public function getFullStudyLinkButtonHTML(): string {
        return $this->getHasCauseAndEffect()->getStudyStateButton()->getCenteredRoundOutlineWithIcon();
    }
    /**
     * @return string
     */
    public function getImage(): string{
        return $this->getHasCauseAndEffect()->getImage();
    }
    /**
     * @param array $params
     * @return string
     */
    public function getUrl(array $params = []): string{
        $s = $this->getHasCauseAndEffect();
        return $s->getUrl();
    }
    /**
     * @return string
     */
    public function getTitleAttribute(): string{
        return $this->getHasCauseAndEffect()->getTitleAttribute();
    }
    /**
     * @return string
     */
    public function getSubtitleAttribute(): string{
        return $this->getHasCauseAndEffect()->getSubtitleAttribute();
    }
    /**
     * @param string|null $html
     * @return string
     */
    public function getOrAddSocialSharingButtons(string $html = ''): string {
        $imagePreview = $this->getImage();
        $url = $this->getUrl();
        $shortTitle = $this->getTitleAttribute();
        $briefDescription = $this->getSubtitleAttribute();
        $html = HtmlHelper::getSocialSharingButtonsHtmlNonEmail($url, $shortTitle,
            $imagePreview, $briefDescription, $html);
        return $this->socialSharingButtonHtml = $html;
    }
    /**
     * @param bool $wiki
     * @return string
     */
    public function getOldSocialSharingButtons($wiki = false): string{
        $html = '
            <div style="text-align: center; margin: auto; padding: 20px;">
                <a href="'.$this->getStudyLinks()->getStudyLinkEmail().'" target="_blank">
                    <img class="email-sharing-img"
                        style="display: inline;"
                        src="https://cache.addthiscdn.com/icons/v3/thumbs/32x32/email.png"
                        border="0" alt="Email"/>
                </a>
                <a href="'. $this->getStudyLinks()->getStudyLinkFacebook(). '" target="_blank">
                    <img class="email-sharing-img"
                        style="display: inline;"
                        src="https://cache.addthiscdn.com/icons/v3/thumbs/32x32/facebook.png" border="0" alt="Facebook"/>
                </a>'.
                //<a href="'.$this->getStudyLinks()->getStudyLinkGoogle().'" target="_blank">
                //<img src="https://cache.addthiscdn.com/icons/v3/thumbs/32x32/google_plusone_share.png" border="0" alt="Google+"/>
                //</a>
                '<a href="'. $this->getStudyLinks()->getStudyLinkTwitter(). '" target="_blank">
                    <img class="email-sharing-img"
                        style="display: inline;"
                        src="https://cache.addthiscdn.com/icons/v3/thumbs/32x32/twitter.png" border="0" alt="Twitter"/>
                </a>
                <a href="'.$this->getStudyLinks()->getStudyLinkStatic().'" target="_blank">
                    <img class="email-sharing-img"
                        style="max-height: 32px;"
                    src="http://free-icon-rainbow.com/i/icon_00983/icon_009830_256.jpg" border="0" alt="Study"/>
                </a>
            </div>
        ';
        if($wiki){
            return WikiHelper::convertHtmlToWiki($html);
        }
        $this->socialSharingButtonHtml = $html;
        $this->exceptionIfStudyDecoupled('socialSharingButtonHtml');
        QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 100);
        return $html;
    }
    /**
     * @return PublicUser
     */
    private function getPrincipalInvestigator(): PublicUser{
        $study = $this->getHasCauseAndEffect();
        if($study instanceof QMGlobalVariableRelationship || $study instanceof QMPopulationStudy){
            return QMUser::getDefaultPrincipalInvestigator();
        }
        return $study->getPublicUser();
    }
    /**
     * @return string
     */
    public function getPrincipalInvestigatorHtml(): string{
        $user = $this->getPrincipalInvestigator();
        //return $user->getPrincipalInvestigatorProfileHtml();
        return $user->getBioHtml("Principal Investigator");
    }
    /**
     * @return string
     */
    public static function getTermsAndDefinitions(): string {
        return '
            <section>
                <header><h1 class="entry-title">Key Terms and Definitions</h1></header>
                <p style="padding-left: 1em; text-indent: -1em;"><strong><em>dependent variable: </em></strong>
                    the “outcome” variable in intervention research, sometimes referred to as the “<em>y</em>” variable, hypothesized to be influenced by the independent variable.</p>
                <p style="padding-left: 1em; text-indent: -1em;"><strong><em>independent variable: </em></strong>
                    the “input” variable in intervention research, sometimes referred to as the “<em>x</em>” variable, hypothesized to influence the outcomes (dependent variable).</p>
                <p style="padding-left: 1em; text-indent: -1em;"><strong><em>latency:&nbsp;</em></strong>
                    the stretch of time before something occurs, such as an event or onset of change.</p>
                <p style="padding-left: 1em; text-indent: -1em;"><strong><em>non-overlap of all pair (NAP) analysis: </em></strong>
                    a form of analysis based on all possible pairwise combinations of data points, comparing two phases of a single system design, resulting in a <em>Z</em>-statistic with a <em>p</em>-value that informs the decision to reject or fail to reject null hypothesis of no meaningful change/difference.</p>
                <p style="padding-left: 1em; text-indent: -1em;"><strong><em>nonparametric analysis: </em></strong>
                    statistical analyses not based on the same set of assumptions about the data that parametric analyses require.</p>
                <p style="padding-left: 1em; text-indent: -1em;"><strong><em>omnibus test of significance: </em></strong>
                    a statistical test that measures overall significance of a set of explanatory (independent) variables without distinguishing which one or ones contribute to the solution—additional post hoc analysis are needed to make the distinctions by individual variables.</p>
                <p style="padding-left: 1em; text-indent: -1em;"><strong><em>paired t-test: </em></strong>
                    a parametric statistical analysis where the dependent variable data were collected longitudinally at two points in time (or otherwise nonindependent on the independent variable), using the <em>t</em>-distribution and calculation of a <em>t</em>-statistic based on shared variance.</p>
                <p style="padding-left: 1em; text-indent: -1em;"><strong><em>two-standard deviation band method: </em></strong>
                    an approach to statistical analysis of single system design data comparing the baseline phase band of values two standard deviations above and below the mean to the intervention phase where two or more consecutive data points falling outside the band indicate meaningful change.</p>
                <p style="padding-left: 1em; text-indent: -1em;"><strong><em>x axis: </em></strong>
                    horizontal dimension on a line graph (and some other forms of bi-variate graphs)</p>
                <p style="padding-left: 1em; text-indent: -1em;"><strong><em>y axis: </em></strong>
                    vertical dimension on a line graph (and some other forms of bi-variate graphs)</p>
            </section>
        ';
    }
    /**
     * @param BaseException $e
     * @param string $html
     * @return string
     */
    private function addNotEnoughDataForCorrelationMessage(BaseException $e, string $html): string{
		$section = new StudySection("Not Enough Data for Statistical Analysis",  
		                            ExceptionHandler::renderHtml($e), 
		                            ImageUrls::ERROR_MESSAGE);
		$solutionButtons = ExceptionHandler::getDocumentationLinkButtons($e);
		if($solutionButtons){
			$section->setButtons($solutionButtons);
		}
		$html .= $section->getHtml();
        QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 100);
        return $html;
    }
    /**
     * @param string $property
     */
    private function exceptionIfStudyDecoupled(string $property): void{
        $study = $this->hasCorrelationsCoefficient;
        if(!$study){
            le("Study not set!");
        }
        if(!is_object($study)){
            le("Study not an object!");
        }
        if(!$study->studyHtml){
            $study->studyHtml = $this;
            return;
        }
        if($study->studyHtml->$property !== $this->$property){
            le("studyHtml is decoupled from study! Property $property is
                ". $study->studyHtml->$property." for studyHtml and
                $this->$property for study.");
        }
    }
    /**
     * @param string $type
     * @return string
     */
    public function getCorrelationChartHtmlWithEmbeddedImageOrReasonForFailure(
        string $type = HighchartExport::DEFAULT_IMAGE_FORMAT): string {
        $html = '';
        $study = $this->getHasCauseAndEffect();
        try {
            $charts = $study->getOrSetCharts();
            $html .= $charts->getChartHtmlWithEmbeddedImages($type);
        } catch (NotEnoughDataException $e) {
            $html .= ExceptionHandler::renderHtml($e);
        } catch (InsufficientMemoryException $e) {
            if(AppMode::isApiRequest()){
                $this->getHasCauseAndEffect()->queue("Could not generate charts during API request because ".$e->getMessage());
            }
            $html .= ExceptionHandler::renderHtml($e);
        }
        QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 100);
        return $html;
    }
    /**
     * @param string $type
     * @param bool $embedImages
     * @return string
     * @throws DuplicateFailedAnalysisException
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getEmbeddedEffectChartsOrButton(string $type, bool $embedImages = false): string{
        $cause = $this->getHasCauseAndEffect()->getCauseQMVariable();
        $html = $cause->getChartsPostAndSettingsButton($type, $embedImages,
            $this->getStudyLinks()->getRecalculateStudyUrl());
        QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 100);
        return $html;
    }
    /**
     * @param string $type
     * @param bool $embedImages
     * @return string
     */
    public function getEmbeddedCauseChartsOrButton(string $type, bool $embedImages = false): string{
        $cause = $this->getHasCauseAndEffect()->getCauseQMVariable();
        $html = $cause->getChartsPostAndSettingsButton($type, $embedImages,
            $this->getStudyLinks()->getRecalculateStudyUrl());
        QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 100);
        return $html;
    }
    public function setBasicHtmlProperties(): void{
        $this->getTitleHtml();
        $this->getTagLineHtml();
        $this->getTitleGaugesTagLineHeader(true, true);
        $this->getSocialMetaHtml();
        $this->getStudyAbstractHtml();
		if(!$this->studyAbstractHtml){le('!$this->studyAbstractHtml');}
        $this->getOrAddSocialSharingButtons();
        $this->getStudyImageHtml();
        $this->getDownloadButtons();
    }
    /**
     * @return string
     * @throws TooSlowToAnalyzeException
     */
    public function getBodyHtml(): string {
        return $this->setWithEmbeddedCharts();
    }
    public function getErrorsHtml(): string {
        $str = "";
        try {
            $c = $this->getHasCorrelationCoefficient();
        } catch (NotEnoughDataException $e) {
            return ExceptionHandler::renderHtml($e);
        }
        $str .= $c->getErrorsHtml();
        return $str;
    }
    public function getButton(array $params = []): QMButton{
        return $this->getHasCauseAndEffect()->getButton();
    }
    public function getHtmlWithoutCharts(): string {
        $study = $this->getHasCauseAndEffect();
        $c = $study->getHasCorrelationCoefficientFromDatabase();
        $html = $this->getStudyImageHtml();
        $html .= $this->getTitleGaugesTagLineHeader(true, true);
        if($c){
            $html .= $study->getStaticStudyButtonHtml();
            $html .= $c->getDataLabInterestingRelationshipsMenu()->getMaterialStatCards();
        } else {
            $html .= $study->getDataLabInterestingRelationshipsMenu()->getMaterialStatCards();
        }
        $html .= $this->getOrAddSocialSharingButtons();
        $html .= $this->getJoinStudyButtonHTML();
        $html .= $this->getStudyTextHtml();
        if($c){
            $html .= $c->getErrorsHtml();
            $html .= $this->setStatisticsTable(); // We keep having a stale table
        }
        $html .= $this->getOrAddSocialSharingButtons();
        $html .= $this->getPrincipalInvestigatorHtml();
        return "
<div style=\"max-width: 600px; margin: auto;\">
    $html
</div>";
    }
    public function getShowContent(bool $includeCharts = true): string {
        $html = "";
        try {
            $correlation = $this->getHasCorrelationCoefficient();
        } catch (NotEnoughDataException $e) {
            $this->logError($e->getMessage());
            $correlation = false;
        }
        $study = $this->getHasCauseAndEffect();
        $html .= $this->getStudyImageHtml();
	    $testing = AppMode::isTestingOrStaging();
	    if($testing && str_contains($html, '</html>')){le("Shouldn't have </html> tag yet");}
        $html .= $this->getTitleGaugesTagLineHeader(true, true);
	    if($testing && str_contains($html, '</html>')){le("Shouldn't have </html> tag yet");}
        if($correlation){
	        $dataLabRelationshipMenu = $correlation->getDataLabInterestingRelationshipsMenu();
	        $html .= $dataLabRelationshipMenu->getMaterialStatCards();
        } else {
	        $dataLabInterestingRelationshipsMenu = $study->getDataLabInterestingRelationshipsMenu();
	        $html .= $dataLabInterestingRelationshipsMenu->getMaterialStatCards();
        }
	    if($testing && str_contains($html, '</html>')){le("Shouldn't have </html> tag yet");}
        $html .= $this->getOrAddSocialSharingButtons();
	    if($testing && str_contains($html, '</html>')){le("Shouldn't have </html> tag yet");}
        $html .= $this->getJoinStudyButtonHTML();
	    if($testing && str_contains($html, '</html>')){le("Shouldn't have </html> tag yet");}
        if($correlation && $includeCharts){
	        $chartGroup = $correlation->getChartGroup();
	        $html .= $chartGroup->getHtmlWithDynamicCharts(false);
        }
	    if($testing && str_contains($html, '</html>')){le("Shouldn't have </html> tag yet");}
        $html .= $this->getStudyTextHtml();
	    if($testing && str_contains($html, '</html>')){le("Shouldn't have </html> tag yet");}
        if($correlation){
            $html .= $correlation->getErrorsHtml();
	        if($testing && str_contains($html, '</html>')){le("Shouldn't have </html> tag yet");}
			$html .= $this->setStatisticsTable(); // We keep having a stale table
	        if($testing && str_contains($html, '</html>')){le("Shouldn't have </html> tag yet");}
        }
        $html .= $this->getOrAddSocialSharingButtons();
	    if($testing && str_contains($html, '</html>')){le("Shouldn't have </html> tag yet");}
        $html .= $this->getPrincipalInvestigatorHtml();
	    if($testing && str_contains($html, '</html>')){le("Shouldn't have </html> tag yet");}
        return "
<div style=\"max-width: 600px; margin: auto;\">
    $html
</div>";
    }
    public function getHtml(): string {
        return HtmlHelper::renderView(view('html-layout', [
            'title' => $this->getHasCauseAndEffect()->getTitleAttribute(),
            'html'  => $this->getShowContent()
        ]));
    }
    /**
     * @param string $fullStudyHtml
     * @return string
     */
    public function setFullStudyHtml(string $fullStudyHtml): string{
        if(stripos($fullStudyHtml, $this->getGaugeAndImagesWithTagLine()) === false){
            if(EnvOverride::isLocal()){
	            FileHelper::writeHtmlFile(__FUNCTION__, $fullStudyHtml);
            }
            le("fullStudyHtml does not contain getGaugeAndImagesWithTagLine");
        }
        QMStudy::validateStudyHtml($fullStudyHtml);
        $fullStudyHtml = HtmlHelper::trimWhitespace($fullStudyHtml);
        return $this->fullStudyHtml = $fullStudyHtml;
    }
}
