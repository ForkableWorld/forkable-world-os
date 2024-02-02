<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Buttons\QMButton;
use App\Buttons\StudyButton;
use App\Charts\BarChartButton;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\VariableRelationships\QMVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\DataSources\QMClient;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InsufficientVarianceException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NotEnoughMeasurementsForCorrelationException;
use App\Exceptions\NotEnoughOverlappingDataException;
use App\Exceptions\StupidVariableException;
use App\Exceptions\StupidVariableNameException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Fields\Avatar;
use App\Http\Parameters\IncludeChartsParam;
use App\Logging\QMLog;
use App\Models\UserVariableRelationship;
use App\Models\Study;
use App\Models\Variable;
use App\Models\WpPost;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipDataSourceNameProperty;
use App\Properties\Base\BaseConfidenceLevelProperty;
use App\Properties\Base\BaseEffectFollowUpPercentChangeFromBaselineProperty;
use App\Properties\Base\BaseFontAwesomeProperty;
use App\Properties\Base\BaseForwardPearsonCorrelationCoefficientProperty;
use App\Properties\Base\BaseNumberOfPairsProperty;
use App\Properties\Base\BaseNumberOfUsersProperty;
use App\Properties\Base\BasePValueProperty;
use App\Properties\Base\BaseStrengthLevelProperty;
use App\Properties\Base\BaseUserStudyTextProperty;
use App\Properties\Base\BaseValenceProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Studies\QMStudy;
use App\Studies\StudyImages;
use App\Studies\StudyLinks;
use App\Studies\StudySection;
use App\Studies\StudyText;
use App\Tables\TableCell;
use App\Traits\HasProperty\HasOnsetAndDuration;
use App\Types\QMStr;
use App\UI\CssHelper;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use App\UI\ImageHelper;
use App\UI\IonIcon;
use App\Utils\AppMode;
use App\Utils\Stats;
use App\Utils\UrlHelper;
use App\VariableCategories\SoftwareVariableCategory;
use Illuminate\View\View;
trait HasCorrelationCoefficient {
	use HasCauseAndEffect;
	use HasOnsetAndDuration;
	public function getNumberOfPairs(): int {
		$l = $this->l();
		$val = $l->getAttribute(BaseNumberOfPairsProperty::NAME);
		if($val === null){
			le("val === null");
		}
		$val = intval($val);
		return $val;
	}
	public function getPValue(int $sigFigs = null): ?float{
		$val = $this->getAttribute(BasePValueProperty::NAME);
		if($sigFigs){
			return Stats::roundByNumberOfSignificantDigits($val, $sigFigs);
		}
		return $val;
	}
	/**
	 * @return int
	 */
	public function getNumberOfUsers(): ?int{ return $this->getAttribute(BaseNumberOfUsersProperty::NAME); }
	/**
	 * @return string
	 */
	public function getEffectSize(): string{
		return $this->generateEffectSize();
	}
	public function getGaugeLink(array $params = [], int $maxLength = 50,
		string $style = CssHelper::SMALL_IMAGE_STYLE): string{
		$url = $this->getUrl($params);
		$name = $this->getTitleAttribute();
		$name = QMStr::truncate($name, $maxLength);
		$img = $this->getGaugeImageUrl();
		return "
            <a href=\"$url\" target='_self' title=\"See $name Details\"'>
                <img src=\"$img\"
                    alt=\"$name\"
                    style=\"$style\"/>
            </a>
        ";
	}
	public function getImageDropDown(): string{
		return $this->getGaugeDropDown();
	}
	public function getGaugeImageUrl(): string{
		return StudyImages::generateGaugeUrl($this->getEffectSize());
	}
	public function getGaugeDropDown(): string{
		$buttons = $this->getDataLabModelButtons();
		if(!$buttons){
			return "";
		}
		$html =
			HtmlHelper::generateImageNameDropDown($this->getGaugeImageUrl(), $buttons, $this->getNameAttribute() . " Options",
				$this->getTitleAttribute(), CssHelper::SMALL_IMAGE_STYLE);
		return $html;
	}
	public function getGaugeNameDropDown(): string{
		$buttons = $this->getDataLabModelButtons();
		if(!$buttons){
			return "";
		}
		$html =
			HtmlHelper::generateImageNameDropDown($this->getGaugeImageUrl(), $buttons, $this->getNameAttribute() . " Options",
				$this->getTitleAttribute(), CssHelper::SMALL_IMAGE_STYLE);
		return $html;
	}
	public function getImage(): string{
		if(!$this->hasId()){
			return static::DEFAULT_IMAGE; // This is an empty model
		}
		$causeCatId = $this->getCauseVariableCategoryId();
		$effectCatId = $this->getEffectVariableCategoryId();
		if(!$this->getCorrelationCoefficient()){
			return StudyImages::generateVariableCategoriesRobotSharingImageWithBackgroundUrl($causeCatId, $effectCatId);
		}
		$size = $this->getEffectSize();
		if($causeCatId && $effectCatId){
			return StudyImages::generateGaugeSharingImageUrl($size, $causeCatId, $effectCatId);
		}
		return StudyImages::generateGaugeUrl($size);
	}
	public function getIcon(): string{
		if(!$this->getCorrelationCoefficient()){
			return ImageHelper::getRobotPuzzledUrl();
		}
		return ImageHelper::getImageUrl('gauges/200-200/' . StudyImages::getGaugeFilename($this->getEffectSize()) .
			'-200-200.png');
	}
	public function getNameAttribute(): string{
		if(!$this->getAttribute(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID)){
			return static::getClassNameTitle();
		}
		return "Relationship Between " . $this->getCauseVariableName() . " and " . $this->getEffectVariableName();
	}
	/**
	 * @return string
	 */
	public function optimalValueSentence(): ?string{
		$change = $this->changeFragment();
		if($change === null){
			return null;
		}
		return $this->getEffectVariableDisplayName() . " is generally " . $change .//" than baseline ".
			" after " .//"an average of ".
			$this->causeValueUnitVariableName($this->getCauseTreatmentAveragePerDurationOfAction()) .
			" over the previous " . $this->getDurationOfActionHumanString() . ". ";
	}
	public function getCauseTreatmentAveragePerDurationOfAction(): ?float{
		return $this->getAttribute(UserVariableRelationship::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DURATION_OF_ACTION);
	}
	public function getColor(): string{
		$change = $this->getChangeFromBaseline();
		if($change === null){
			return static::COLOR;
		}
		return BaseEffectFollowUpPercentChangeFromBaselineProperty::generateColor($change,
			$this->getEffectVariableValence());
	}
	/**
	 * @return string
	 */
	public function aboveAverageSentence(): ?string{
		$sentence = $this->getEffectVariable()->getTitleAttribute() . " was generally ";
		$change = $this->changeFragment();
		if($change === null){
			$change = $this->changeFragment();
			QMLog::error("change === null");
			return null;
		}
		$sentence .= $change . " following above average " . $this->getCauseVariable()->getTitleAttribute() .
			" over the previous " . $this->getDurationOfActionHumanString();
		return $sentence . ". ";
	}
	public function changeFragment(): ?string{
		$percentChange = $this->getChangeFromBaseline();
		if($percentChange === null){
			return null;
		}
		if($percentChange > 100 || $percentChange < -100){
			$absChange = $this->getAbsoluteFollowupChange();
			$variable = $this->getEffectVariable();
			$QMUnit = $variable->getCommonUnit();
			return $QMUnit->getHigherLowerValueUnitString($absChange);
		}
		$str = BaseEffectFollowUpPercentChangeFromBaselineProperty::percentToHigherLowerString(false, $percentChange);
		return $str;
	}
	public function getSubtitleAttribute(): string{
		if(!$this->getAttribute(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID)){
			return static::getClassDescription();
		}
		$str =
			$this->getChangeFromBaselineSentence() . " Based on " . $this->getNumberOfDays() . " days of data from " .
			($this->getNumberOfUsers() + BaseNumberOfUsersProperty::NUMBER_OF_FAKE_USERS) . " participants.";
		if(!$str){
			$str = "Analysis still in progress";
		}
		return $str;
	}
	public function findInMemoryOrNewQMStudy(): QMStudy{
		return QMStudy::findInMemoryOrDB($this->getStudyId());
	}
	/**
	 * @return \App\Studies\QMStudy|null
	 */
	protected function findQMStudyInMemory(): ?QMStudy{
		return QMStudy::findInMemoryOrDB($this->getStudyId());
	}
	/**
	 * @return \App\VariableRelationships\QMVariableRelationship|\App\Models\GlobalVariableRelationship|\App\Models\UserVariableRelationship|\App\Traits\HasCorrelationCoefficient
	 */
	public function getHasCorrelationCoefficient(){
		return $this;
	}
	public function getGaugeImage(): string{
		return StudyImages::generateGaugeUrl($this->getEffectSize());
	}
	public function getGaugeImageHtml(string $style = null): string{
		return HtmlHelper::getImageHtml($this->getGaugeImageUrl(), $this->getEffectSize(), $style);
	}
	public function getLogMetaDataString(): string{
		return $this->getNameAttribute();
	}
	public function getHtmlPage(bool $inlineJs = false): string{
		return $this->getStudyHtml()->getFullStudyHtml($inlineJs);
	}
	/**
	 * @return Avatar
	 */
	public function gaugeField(): Avatar{
		return Avatar::make('', function(){
			return $this->getGaugeImageUrl();
		})->disk('public')->squared()->disableDownload()->thumbnail(function(){
			return $this->getGaugeImageUrl();
		})->preview(function(){
			return $this->getGaugeImageUrl();
		});
	}
	public function getBarChartButton(float $maxChange): BarChartButton{
		return new BarChartButton($this->getTitleAttribute(), $this->getBarWidth($maxChange), $this->getUrl(), $this->getColor(),
			$this->getIcon(), $this->getChangeFromBaselineString(), $this->getSubtitleAttribute());
	}
	public function getChangeFromBaselineString(): string{
		$change = $this->getChangeFromBaseline();
		return BaseEffectFollowUpPercentChangeFromBaselineProperty::generateString($change);
	}
	public function getChangeFromBaselineSentence(): string{
		return BaseEffectFollowUpPercentChangeFromBaselineProperty::generateSentence($this->getCauseVariable(),
			$this->getEffectVariable(), $this->getChangeFromBaseline());
	}
	public function getChangeFromBaselineFragment(): string{
		$change = $this->getChangeFromBaseline();
		return BaseEffectFollowUpPercentChangeFromBaselineProperty::generateFragment($this->getCauseVariableName(),
			$change);
	}
	public function getChangeLink(): string{
		return BaseEffectFollowUpPercentChangeFromBaselineProperty::generateFragmentLink($this->getCauseVariableName(),
			$this->getChangeFromBaseline(), $this->getEffectVariableValence(), $this->getUrl());
	}
	public function getChangeFromBaselineFragmentHtml(): string{
		$change = $this->getChangeFromBaseline();
		return BaseEffectFollowUpPercentChangeFromBaselineProperty::generateFragmentHtml($this->getCauseVariableName(),
			$change, $this->getEffectVariableValence());
	}
	/**
	 * @return float
	 */
	public function getAverageEffect(): ?float{
		if(property_exists($this, 'attributes') && $this->attributes){
			$val = $this->attributes[UserVariableRelationship::FIELD_AVERAGE_EFFECT];
		} else{
			if(!isset($this->averageEffect)){
				$val = null;
			} else {
				$val = $this->averageEffect;
			}
		}
		return $val;
	}
	public function getChartTooltip(): string{
		$change = $this->getChangeFromBaseline();
		$rounded = round($change);
		$effect = $this->getEffectVariable();
		$valence = $effect->getValenceAttribute();
		if(!$valence){
			$valence = $this->getEffectQMVariableCategory()->valence;
		}
		if(!$valence){
			$valence = BaseValenceProperty::VALENCE_NEUTRAL;
		}
		$color = BaseEffectFollowUpPercentChangeFromBaselineProperty::generateColor($rounded, $valence);
		$changeStr = BaseEffectFollowUpPercentChangeFromBaselineProperty::generateString($change, 2);
		$url = $this->getUrl();
		$causeUrl = $this->getCauseUrl();
		$effectUrl = $this->getEffectUrl();
		$effectName = $effect->getDisplayNameWithCategoryOrUnitSuffix();
		if(empty($effectName)){
			le("no effect name");
		}
		$tt = "<a style=\"color: $color; font-size: 20px; text-align: center;\" href=\"$effectUrl\"'>" . $effectName .
			"</a><br>" . "<span style=\"color: $color; font-size: 20px; text-align: center;\">" . $changeStr .
			"</span>" . "<br>following above average" .
			"<br><a style=\"color: $color; font-size: 20px; text-align: center;\" href=\"$causeUrl\">" .
			$this->getCauseVariableName() . "</a>";
		$tt .= $this->getBasedOnString();
		$tt .= "<br><a style=\"color: blue; text-align: center;\" href=\"$url\" target=\"_blank\">Click to See Full Study</a>";
		return $tt;
	}
	/**
	 * @return bool
	 */
	public function typeIsIndividual(): bool{
		return $this instanceof UserVariableRelationship || $this instanceof QMUserVariableRelationship;
	}
	/**
	 * @return string
	 */
	public function getDirection(): string{
		$fromCorrelation = $this->getDirectionFromCorrelationCoefficient();
		$fromChange = $this->getDirectionFromChange();
		if($fromCorrelation){
			$direction = $fromCorrelation;
		} else{
			$this->logDebug("Could not get direction from CorrelationCoefficient.  " .
				$this->getPredictorValuesErrorString());
			$direction = $fromChange;
		}
		$coefficient = $this->getCorrelationCoefficient();
		if($coefficient === null){
			$coefficient = $this->getCorrelationCoefficient();
			$this->logError("Could not get direction from CorrelationCoefficient.  ", [
				'data' => $this->toArray()
			]);
			return $fromChange;
		}
		if($fromCorrelation !== $fromChange && abs($coefficient) > 0.1){
			$this->logDebug("getDirectionFromCorrelationCoefficient is $fromCorrelation " .
				"but getDirectionFromPredictorValues is $fromChange");
		}
		if(property_exists($this, 'direction')){
			$this->direction = $direction;
		}
		return $direction;
	}
	/**
	 * @return string
	 */
	protected function getPredictorValuesErrorString(): string{
		return "valuePredictingHighOutcome is {$this->getAvgDailyValuePredictingHighOutcome()} " .
			"and valuePredictingLowOutcome is {$this->getAvgDailyValuePredictingLowOutcome()}";
	}
	/**
	 * @return bool|string
	 */
	private function getDirectionFromCorrelationCoefficient(): string{
		$cc = $this->getCorrelationCoefficient();
		return ($cc >= 0) ? QMVariableRelationship::DIRECTION_HIGHER : QMVariableRelationship::DIRECTION_LOWER;
	}
	/**
	 * @return string
	 */
	private function getDirectionFromChange(): string{
		$change = $this->getChangeFromBaseline();
		return ($change > 0) ? QMVariableRelationship::DIRECTION_HIGHER : QMVariableRelationship::DIRECTION_LOWER;
	}
	/**
	 * @return string
	 */
	public function getAbsoluteFollowupChangeString(): string{
		$absFollowupChange = $this->getAbsoluteFollowupChange();
		$absFollowupChange = Stats::roundByNumberOfSignificantDigits($absFollowupChange, QMVariableRelationship::SIG_FIGS);
		try {
			return $this->effectValueUserUnit($absFollowupChange, QMVariableRelationship::SIG_FIGS, false);
		} catch (IncompatibleUnitException|InvalidVariableValueException $e) {
			le($e);
		} // Can't validate because sometimes Body Weight has a negative change from baseline, for instance
	}
	/**
	 * @return string
	 */
	public function getMeanBaselineString(): string{
		$val = $this->getAttribute(UserVariableRelationship::FIELD_EFFECT_BASELINE_AVERAGE);
		return $this->effectValueUserUnit($val);
	}
	/**
	 * @return float
	 */
	public function getAbsoluteFollowupChange(): float{
		$baseline = $this->getAttribute(UserVariableRelationship::FIELD_EFFECT_BASELINE_AVERAGE);
		$followUp = $this->getAttribute(UserVariableRelationship::FIELD_EFFECT_FOLLOW_UP_AVERAGE);
		return $followUp - $baseline;
	}
	/**
	 * @param int|null $precision
	 * @return float
	 */
	public function getOrCalculateZScore(?int $precision = null): float{
		$z = $this->getAttribute(UserVariableRelationship::FIELD_Z_SCORE);
		if($z === null){
			$this->calculateOutcomeBaselineStatistics();
			$z = $this->getAttribute(UserVariableRelationship::FIELD_Z_SCORE);
		}
		if($precision){
			return Stats::roundByNumberOfSignificantDigits($z, $precision);
		}
		return $z;
	}
	/**
	 * @param int|null $precision
	 * @return float
	 */
	public function getZScore(?int $precision = null): ?float{
		$z = $this->getAttribute(UserVariableRelationship::FIELD_Z_SCORE);
		if($precision && $z){
			return Stats::roundByNumberOfSignificantDigits($z, $precision);
		}
		return $z;
	}
	/**
	 * @param bool $numeric
	 * @return TableCell
	 */
	protected function getChangeCell(bool $numeric): TableCell{
		$value = $this->getChangeFromBaseline();
		if($value === null){
			return new TableCell("Unknown");
		}
		$increaseDecrease =
			BaseEffectFollowUpPercentChangeFromBaselineProperty::percentToIncreaseDecreaseString(true, $value);
		$higherLower = BaseEffectFollowUpPercentChangeFromBaselineProperty::percentToHigherLowerString(true, $value);
		if($numeric){
			$cell = new TableCell($value . "%"); // Use this instead of arrows so table is sortable
		} else{
			$cell = new TableCell($increaseDecrease);
		}
		$tooltip = $this->getEffectNameWithoutCategoryOrUnit() . " is about $higherLower following above average " .
			$this->getCauseNameWithoutCategoryOrUnit() . " than it is after below average " .
			$this->getCauseNameWithoutCategoryOrUnit();
		$cell->setTooltip($tooltip);
		return $cell;
	}
	/**
	 * @param bool $arrows
	 * @return string
	 */
	public function getPercentHigherLowerFragment(bool $arrows = true): string{
		$change = $this->getOrCalculatePercentEffectChangeFromLowCauseToHighCause(1);
		if(!$change){
			return "Unknown";
		}
		$changeString =
			BaseEffectFollowUpPercentChangeFromBaselineProperty::percentToHigherLowerString($arrows, $change);
		return $changeString;
	}
	/**
	 * @param bool $addressingUser
	 * @return string
	 */
	public function changeFromBaselineSentence(bool $addressingUser = null): string{
		//$sentence = $this->getForMostYourOrThisIndividual($addressingUser);
		return $this->effectNameWithSuffix() . " was " . $this->getOrCalculateChangeFromBaselineFragment() .
			" following above average" . " " . $this->getCauseNameWithoutCategoryOrUnit() . " over the previous " .
			$this->getDurationOfActionHumanString() . ". ";
	}
	/**
	 * @return string
	 */
	protected function getOrCalculateChangeFromBaselineFragment(): string{
		$change = $this->getOrCalculateFollowUpPercentChangeFromBaseline();
		if($change > 100 || $change < -100){
			$absChange = $this->getAbsoluteFollowupChange();
			return $this->getEffectVariableCommonUnit()->getHigherLowerValueUnitString($absChange);
		}
		return BaseEffectFollowUpPercentChangeFromBaselineProperty::percentToHigherLowerString(false, $change);
	}
	/**
	 * @return TableCell
	 */
	public function getConfidenceLevelCell(): TableCell{
		$level = $this->getConfidenceLevel();
		$color = BaseConfidenceLevelProperty::toColor($level);
		$cell = new TableCell($level, $color);
		$cell->setTooltip("pValue $this->pValue; Number of Pairs: $this->numberOfPairs");
		return $cell;
	}
	/**
	 * @param float $maxChange
	 * @param int $absoluteMax
	 * @return string
	 */
	protected function getTruncatedCauseName(float $maxChange, int $absoluteMax = 45): string{
		$width = $this->getBarWidth($maxChange);
		$maxChars = $absoluteMax * $width / 100;
		$name = QMStr::truncate($this->getCauseNameWithoutCategoryOrUnit(), $maxChars);
		return $name;
	}
	/**
	 * @param float|null $maxChange
	 * @return float|int
	 */
	public function getBarWidth(float $maxChange = null){
		$minWidth = 70;
		if($maxChange){
			$factor = 30 / $maxChange;
			$percent = $this->getChangeFromBaseline();
			$width = $percent * $factor;
			if($width < 0){
				$width = $width * -1;
			}
			$width = $width + $minWidth;
			if($width > 99){
				$width = 99;
			}
			return $width;
		} else{
			return $minWidth;
		}
	}
	/**
	 * @param HasCorrelationCoefficient[] $correlations
	 * @return int
	 */
	public static function getMaxChange($correlations): float {
		$maxChange = 0;
		foreach($correlations as $c){
			$change = $c->getChangeFromBaseline();
			if($change > $maxChange){
				$maxChange = $change;
			}
		}
		return $maxChange;
	}
	/**
	 * @param float $maxChange
	 * @return string
	 */
	public function getTableRowBlock(float $maxChange): string{
		$url = $this->getUrl();
		$tagLine = $this->generateStudyTitle();
		$color = $this->getColor();
		$width = $this->getBarWidth($maxChange);
		$percent = $this->getOrCalculatePercentChangeFragment(true, true);
		$causeName = $this->getTruncatedCauseName($maxChange);
		$img = $this->getCauseQMVariableCategory()->getImageUrl();
		$html = BarChartButton::getHtmlWithRightText($causeName, $width, $url, $color, $img, $percent, $tagLine);
		return $html;
	}
	/**
	 * @param HasCorrelationCoefficient[] $correlations
	 * @param string $outcomeVariableName
	 * @return string
	 */
	public static function getBarChartListHtml(array $correlations, string $outcomeVariableName): string{
		if(!$correlations){
			return '';
		}
		$list = "
            <p>
                Each bar represents the amount of change seen in $outcomeVariableName following above average values for that factor.
                Click to see the full study.
            </p>
        ";
		$maxChange = self::getMaxChange($correlations);
		foreach($correlations as $c){
			$list .= $c->getTableRowBlock($maxChange);
		}
		return $list;
	}
	public static function generateListSectionHtml(string $title, string $description, array $correlations,
		string $outcomeName): string{
		$list = UserVariableRelationship::getBarChartListHtml($correlations, $outcomeName);
		return "
                <div>
                    <h2>$title</h2>
                    <p>$description</p>
                    $list
                </div>
            ";
	}
	/**
	 * @param bool $arrows
	 * @param bool $hyperLinkNames
	 * @return string
	 */
	public function getTitleHtml(bool $arrows = false, $hyperLinkNames = false): string{
		$title = $this->generateStudyTitle($arrows, $hyperLinkNames);
		$html = "
            <h1 style=\"text-align: center; margin: 0.67em 0;\" class=\"study-title text-3xl\">
                $title
            </h1>
";
		QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 100);
		return $html;
	}
	/**
	 * @param bool $arrows
	 * @param bool $hyperLinkNames
	 * @return string
	 */
	public function generateStudyTitle(bool $arrows = false, bool $hyperLinkNames = false): string{
		try {
			if($this->getCorrelationCoefficient()){
				$title = $this->getPredictorExplanationTitle($arrows, $hyperLinkNames);
			} else{
				$title = $this->getStudyQuestion();
			}
		} catch (NotEnoughDataException $e) {
			$title = $this->getStudyQuestion();
		}
		if($this instanceof QMGlobalVariableRelationship && stripos($title, "Population") === false){
			$title .= " for Population";
		}
		$title = BaseUserStudyTextProperty::humanizeStudyText($title);
		if(!$arrows){
			$title = str_replace(["&darr;", "&uarr;"], '', $title);
		}
		try {
			QMStr::assertStringDoesNotContain($title, [
                //"(Systolic - Top Number)",
                "Population for Population",
            ], __FUNCTION__);
		} catch (InvalidStringException $e) {
			le($e);
		}
		// Don't do this here because it contains links $title = StringHelper::titleCase($title);
		return $title;
	}
	/**
	 * @return string
	 */
	public function getGaugeAndImagesWithTagLine(): string{
		return StudyImages::generateGaugeImagesTagLine($this->getGaugeAndVariableImages(), $this->getTagLineHtml());
	}
	/**
	 * @return string
	 */
	public function getTagLineHtml(): string{
		return "
            <div class=\"study-tag-line text-2xl\"
             style=\"padding: 10px; text-align: center;\">
                {$this->getTagLine()}
            </div>
";
	}
	/**
	 * @return string
	 */
	public function getGaugeAndVariableImages(): string{
		return '<div class="gauge-and-images" style="justify-content:space-around;">
            <span style="display: inline-block; max-width: 10%;">
                <img style="max-width: 100%; max-height: 150px;" src="' . $this->getCauseVariableImage() . '" alt="cause image">
            </span>
            <span style="display: inline-block; max-width: 65%;">
                <img style="max-width: 100%; max-height: 200px;" src="' . $this->getGaugeImage() . '" alt="gauge image">
            </span>
            <span style="display: inline-block; max-width: 10%;">
                <img style="max-width: 100%; max-height: 150px;" src="' . $this->getEffectVariableImage() . '" alt="effect image">
            </span>
        </div>';
	}
	/**
	 * @return QMButton
	 */
	public function getGoToStudyButton(): QMButton{
		$b = $this->getButton();
		$b->setTextAndTitle("Go to Study");
		$b->setUrl($this->getStudyUrl());
		$b->setFontAwesome(FontAwesome::STUDY);
		return $b;
	}
	/**
	 * @param bool $arrows
	 * @param bool $hyperLinkNames
	 * @return string
	 */
	public function getPredictorExplanationTitle(bool $arrows = false, bool $hyperLinkNames = false): string{
		$direction = $this->getDirection();
		if($hyperLinkNames){
			$causeName = $this->getCauseNameLink();
			$effectName = $this->getEffectNameLink();
		} else{
			$causeName = ucwords($this->causeNameWithSuffix());
			$effectName = ucwords($this->effectNameWithSuffix());
		}
		if($this->getEffectSize() === BaseForwardPearsonCorrelationCoefficientProperty::EFFECT_SIZE_non_existent){
			return $causeName . " does not appear to have a significant effect on " . $effectName;
		}
		if($arrows){
			$str = "&uarr;Higher ";
			$dir = ucwords($this->getDirectionWithArrow());
		} else{
			$str = "Higher ";
			$dir = ucwords($direction);
		}
		return $str . $causeName . $this->getPredictsStrengthPhrase() . $dir . " " . $effectName;
	}
	/**
	 * @return string
	 */
	private function getDirectionWithArrow(): string{
		$dir = $this->getDirection();
		if($dir === QMVariableRelationship::DIRECTION_HIGHER){
			return "&uarr;" . ucwords($dir);
		}
		return "&darr;" . ucwords($dir);
	}
	/**
	 * @return string
	 */
	private function getPredictsStrengthPhrase(): string{
		$map = [
			BaseForwardPearsonCorrelationCoefficientProperty::EFFECT_SIZE_moderately_negative => "Moderately",
			BaseForwardPearsonCorrelationCoefficientProperty::EFFECT_SIZE_moderately_positive => "Moderately",
			BaseForwardPearsonCorrelationCoefficientProperty::EFFECT_SIZE_strongly_negative => "Significantly",
			BaseForwardPearsonCorrelationCoefficientProperty::EFFECT_SIZE_strongly_positive => "Significantly",
			BaseForwardPearsonCorrelationCoefficientProperty::EFFECT_SIZE_very_weakly_negative => "Very Slightly",
			BaseForwardPearsonCorrelationCoefficientProperty::EFFECT_SIZE_very_weakly_positive => "Very Slightly",
			BaseForwardPearsonCorrelationCoefficientProperty::EFFECT_SIZE_weakly_negative => "Slightly",
			BaseForwardPearsonCorrelationCoefficientProperty::EFFECT_SIZE_weakly_positive => "Slightly",
		];
		if(isset($map[$this->getEffectSize()])){
			return " Predicts " . $map[$this->getEffectSize()] . " ";
		}
		return " Predicts ";
	}
	/**
	 * @return string
	 */
	public function generatePredictorExplanationSentence(): string{
		if($this->getEffectSize() === BaseForwardPearsonCorrelationCoefficientProperty::EFFECT_SIZE_non_existent){
			$sentence = ucwords($this->causeNameWithSuffix()) . " does not appear to have a significant effect on " .
				$this->effectNameWithSuffix() . ". ";
		} else{
			$sentence = "Higher " . $this->causeNameWithSuffix() . strtolower($this->getPredictsStrengthPhrase()) .
				$this->getDirection() . " " . $this->effectNameWithSuffix() . ". ";
			$this->validateDirectionAndEffectSizeContradiction();
		}
		return $sentence;
	}
	/**
	 * @return string
	 */
	public function getConfidenceLevel(): string{
		return BaseConfidenceLevelProperty::calculate($this);
	}
	/**
	 * @return string
	 */
	public function getStrengthTitleCase(): string{
		return ucfirst(strtolower($this->getStrengthLevel()));
	}
	/**
	 * @return string
	 */
	public function getStrengthLevel(): string{
		return BaseStrengthLevelProperty::calculate($this);
	}
	private function validateDirectionAndEffectSizeContradiction(){
		$dir = $this->getDirectionWithArrow();
		$effect = $this->getEffectSize();
		if(stripos($dir, 'lower') && stripos($effect, 'positive')){
			$this->logError("direction lower but effect size positive!");
		}
		if(stripos($dir, 'higher') && stripos($effect, 'negative')){
			$this->logError("direction lower but effect size positive!");
		}
	}
	/**
	 * @param int|null $precision
	 * @return float
	 */
	public function getOrCalculatePercentEffectChangeFromLowCauseToHighCause(int $precision = null): ?float{
		$low = $this->getAverageEffectFollowingLowCause();
		$high = $this->getAverageEffectFollowingHighCause();
		$average = $this->getAverageEffect();
		if($average === null){
			$average = $this->getAverageEffect();
			QMLog::error("No average effect! for: ", $this->toArray());
			return null;
		}
		$difference = $high - $low;
		$percentChangeFromAverageEffect = $difference / $average * 100;
		if($precision){
			return $this->round($percentChangeFromAverageEffect, $precision);
		}
		return $percentChangeFromAverageEffect;
	}
	/**
	 * @param int|null $precision
	 * @return float
	 */
	public function getReverseCorrelationCoefficient(int $precision = null): ?float{
		return $this->getFromAttributesOrCamelAndRound(UserVariableRelationship::FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT,
			$precision);
	}
	/**
	 * @param int|null $precision
	 * @return float
	 */
	public function getAverageEffectFollowingHighCause(int $precision = null): ?float{
		return $this->getFromAttributesOrCamelAndRound(UserVariableRelationship::FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE,
			$precision);
	}
	/**
	 * @param int|null $precision
	 * @return float
	 */
	public function getAverageEffectFollowingLowCause(int $precision = null): ?float{
		return $this->getFromAttributesOrCamelAndRound(UserVariableRelationship::FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE,
			$precision);
	}
	/**
	 * @param int|null $precision
	 * @return float
	 */
	public function getAverageDailyHighCause(int $precision = null): ?float{
		return $this->getFromAttributesOrCamelAndRound(UserVariableRelationship::FIELD_AVERAGE_DAILY_HIGH_CAUSE, $precision);
	}
	/**
	 * @param int|null $precision
	 * @return float
	 */
	public function getAverageDailyLowCause(int $precision = null): ?float{
		return $this->getFromAttributesOrCamelAndRound(UserVariableRelationship::FIELD_AVERAGE_DAILY_LOW_CAUSE, $precision);
	}
	/**
	 * @param string $key
	 * @param int|null $precision
	 * @return float
	 */
	public function getFromAttributesOrCamelAndRound(string $key, int $precision = null): ?float{
		if(property_exists($this, 'attributes') && $this->attributes){
			$val = $this->attributes[$key] ?? null;
		} else{
			$camel = QMStr::camelize($key);
			if(isset($this->$camel)){
				$val = $this->$camel;
			} else{
				$val = null;
			}
		}
		if($precision && $val !== null){
			return $this->round($val, $precision);
		}
		return $val;
	}
	/**
	 * @param float|string $value
	 * @param int $significantFigures
	 * @return float|int
	 */
	protected function round(float $value, int $significantFigures = 3): float{
		// $rounded = Stats::roundToSignificantFiguresIfGreater($value, $significantFigures);
		if(is_string($value)){
			$value = (float)$value;
		}
		$roundedString = $value;
		if(abs($value) > 0){
			$roundedString = number_format($value,
				$significantFigures); // PHP7 float precision issues are a nightmare!  This is the best solution I could find
			$roundedString = rtrim($roundedString, "0");
			$roundedString = rtrim($roundedString, ".");
		}
		$float = (float)$roundedString;
		return $float;
	}
	/**
	 * @param bool|null $addressingUser
	 * @return string
	 */
	public function getOptimalValueWithDurationOfActionSentence(bool $addressingUser = null): string{
		if($this instanceof QMUserVariableRelationship && $this->effectFollowUpPercentChangeFromBaseline !== null &&
			$this->zScore !== null){
			$message = $this->changeFromBaselineSentence($addressingUser);
			if(stripos($message, "above average.") !== false){
				le('stripos($message, "above average.") !== false');
			}
			return $message;
		}
		$prefix = $this->getForMostYourOrThisIndividual($addressingUser);
		$prefix .= $this->effectNameWithSuffix() . " was ";
		if($this->effectValenceIsNegative()){
			$suffix = $this->getOptimalValueForNegativeValenceString();
		} else{
			$suffix = $this->getOptimalValueForPositiveValenceString();
		}
		if(!empty($suffix)){
			$suffix = str_replace('total of', 'daily total of', $suffix);
		}
		return $prefix . $suffix . ". ";
	}
	/**
	 * @return string
	 */
	private function getOptimalValueForNegativeValenceString(){
		$value = $this->getCauseValueClosestToValuePredictingLowOutcomeGroupedOverDurationOfAction();
		if($value === null || $value === false){
			$this->logError("No CumulativeValueOverDurationOfActionPredictingHighOutcome and it's not even a third party correlation!");
			return false;
		}
		return "lowest after " . $this->causeValueUnitVariableName($value);
		//." over the previous ".$this->getDurationOfActionHumanString();
	}
	/**
	 * @return float
	 */
	public function getCauseValueClosestToValuePredictingLowOutcomeGroupedOverDurationOfAction(): ?float{
		$value = $this->getAttribute(UserVariableRelationship::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME);
		if($value === null && !AppMode::isApiRequest()){
			$this->logError("No groupedCauseValueClosestToValuePredictingLowOutcome so recalculating...");
			$value = $this->calculateGroupedCauseValueClosestToValuePredictingLowOutcome();
		}
		return $value;
	}
	/**
	 * @return string
	 */
	private function getOverThePreviousDurationOfAction(): string{
		return $this->causeNameWithSuffix() . " over the previous " . $this->getDurationOfActionHumanString();
	}
	/**
	 * @param string|float $inCommonUnit
	 * @return string
	 */
	protected function aggregatedCauseValueUnit(float $inCommonUnit): string{
		$combination = $this->getAggregationSentenceFragment();
		return $combination . " " . $this->causeValueUserUnit($inCommonUnit) . " of " .
			$this->getOverThePreviousDurationOfAction();
	}
	/**
	 * @return string
	 */
	protected function getAggregationSentenceFragment(): string{
		$combination = "an average of";
		if($this->getCauseVariable()->isSum()){
			$combination = "a total of";
		}
		return $combination;
	}
	/**
	 * @param string|float $inCommonUnit
	 * @return string
	 */
	public function causeValueUnitVariableName(float $inCommonUnit): string{
		return $this->causeValueUserUnit($inCommonUnit) . " of " . $this->getCauseVariableDisplayName();
	}
	/**
	 * @param bool|null $addressingUser
	 * @return string
	 */
	public function getOptimalValueSentenceWithPercentChange(): string{
		$sentence = $this->effectNameWithSuffix() . " was generally ";
		if($this->effectValenceIsNegative()){
			$sentence .= ($this->changeFragment()) . " than average after " .
				$this->causeValueUnitVariableName($this->getCauseValueClosestToValuePredictingLowOutcomeGroupedOverDurationOfAction()) .
				" per " . $this->getDurationOfActionHumanString() . ". ";
		} else{
			$sentence .= Stats::roundToSignificantFiguresIfGreater(abs($this->getPredictsHighEffectChange())) .
				"% higher than average after " . $this->getAggregationSentenceFragment() . " " .
				$this->causeValueUnitVariableName($this->getGroupedCauseValueClosestToValuePredictingHighOutcome()) .
				" over the previous " . $this->getDurationOfActionHumanString() . ". ";
		}
		if(!$this->typeIsIndividual()){
			$forMost = "Based on data from " . $this->getNumberOfUsers() . " participants,";
			$sentence = str_replace("Your", $forMost, $sentence);
		}
		return $sentence;
	}
	/**
	 * @return string
	 */
	public function valueOverDurationPredictingHighOutcomeSentence(): ?string{
		$val = $this->getDailyValuePredictingHighOutcome();
		if($val === null){
			return null;
		}
		return $this->effectNameWithSuffix() . " is " . $this->getPredictsHighEffectChange() . "%higher after " .
			$this->causeValueUnitVariableName($val) . ". ";
	}
	/**
	 * @return string
	 */
	public function getDailyValuePredictingHighOutcomeString(): string{
		$val = $this->getDailyValuePredictingHighOutcome();
		if($val === null){
			debugger("no val");
			$val = $this->getDailyValuePredictingHighOutcome();
			le("no val");
		}
		return $this->causeValueUserUnit($val);
	}
	/**
	 * @return string
	 */
	public function getDailyValuePredictingLowOutcomeString(): string{
		$val = $this->getDailyValuePredictingLowOutcome();
		return $this->causeValueUserUnit($val);
	}
	/**
	 * @return string
	 */
	public function getDailyValuePredictingLowOutcomeSentence(): string{
		$value = $this->getDailyValuePredictingLowOutcome();
		if($value === null){
			return "Could not determine Avg Daily Value Predicting Low Outcome";
		}
		if($this->getPredictsLowEffectChange() !== null){
			$change = abs($this->getPredictsLowEffectChange()) . '% ';
		} else{
			$change = '';
			$this->logError("No predictsLowEffectChange!");
		}
		return $this->effectNameWithSuffix() . " was " . $change . 'lower than normal after ' .
			$this->causeValueUnitVariableName($value) . $this->getPerDaySentenceFragment() . ". ";
	}
	/**
	 * @return string
	 */
	public function getHighestQuartileSentence(): string{
		return "The highest quartile of {$this->getEffectVariableDisplayName()} measurements were observed following an average " .
			$this->getDailyValuePredictingHighOutcomeString() . " {$this->getCauseVariableDisplayName()}" .
			$this->getPerDaySentenceFragment() . ". ";
	}
	public function getLowestQuartileSentence(): string{
		return "The lowest quartile of {$this->getEffectVariableDisplayName()} measurements were observed following " .
			'an average ' . $this->causeValueUnitVariableName($this->getDailyValuePredictingLowOutcome()) .
			$this->getPerDaySentenceFragment() . ".  ";
	}
	/**
	 * @return string
	 */
	public function getPerDaySentenceFragment(): string{
		if($this->getCauseVariable()->isSum()){
			return ' per day';
		}
		return '';
	}
	/**
	 * @return string
	 */
	public function getYouOrThisIndividual(): string{
		if(QMAuth::getQMUserIfSet() && QMAuth::getQMUserIfSet()->getId() === $this->getUserId()){
			$prefix = "You";
		} else{
			$prefix = "This individual";
		}
		return $prefix;
	}
	/**
	 * @return string
	 */
	public function getOptimalValueMessage(): string{
		return $this->getHigherPredictsAndOptimalValueSentenceWithDurationOfAction();
	}
	/**
	 * @return string
	 */
	private function getOptimalValueForPositiveValenceString(): ?string{
		$inCommonUnit = $this->getGroupedCauseValueClosestToValuePredictingHighOutcome();
		if($inCommonUnit === null){
			return null;
		}
		return "highest after " . $this->causeValueUnitVariableName($inCommonUnit);
		//." over the previous ".$this->getDurationOfActionHumanString();
	}
	public function getAvgDailyValuePredictingHighOutcome(): ?float{
		return $this->getAttribute(UserVariableRelationship::FIELD_VALUE_PREDICTING_HIGH_OUTCOME);
	}
	public function getAvgDailyValuePredictingLowOutcome(): ?float{
		return $this->getAttribute(UserVariableRelationship::FIELD_VALUE_PREDICTING_LOW_OUTCOME);
	}
	/**
	 * @return string
	 */
	public function getHigherPredictsAndOptimalValueSentenceWithDurationOfAction(): string{
		$prefix = $this->generatePredictorExplanationSentence();
		$suffix = $this->getOptimalValueWithDurationOfActionSentence();
		$causeName = $this->causeNameWithSuffix();
		if(stripos($prefix, $causeName) !== false){ // Don't repeat cause name
			$suffix = str_replace([
				' of ' . $causeName,
				' ' . $causeName,
			], '', $suffix);
		}
		return $prefix . " " . $suffix;
	}
	/**
	 * @param bool $arrows
	 * @param bool $round
	 * @return string
	 * @throws AlreadyAnalyzedException
	 * @throws InsufficientVarianceException
	 * @throws InvalidVariableValueException
	 * @throws NotEnoughDataException
	 * @throws NotEnoughMeasurementsForCorrelationException
	 * @throws NotEnoughOverlappingDataException
	 * @throws TooSlowToAnalyzeException
	 */
	public function getOrCalculatePercentChangeFragment(bool $arrows = true, bool $round = false): string{
		$change = $this->getOrCalculatePercentEffectChangeFromLowCauseToHighCause(1);
		if($change === null){
			$this->logError("Could not getOrCalculatePercentChangeFragment");
			return "Unknown";
		}
		if($round){
			$change = round($change);
		}
		$changeString =
			BaseEffectFollowUpPercentChangeFromBaselineProperty::percentToIncreaseDecreaseString($arrows, $change);
		return $changeString;
	}
	/**
	 * @return string
	 */
	public function generateEffectSize(): string{
		$c = $this->getCorrelationCoefficient();
		return BaseForwardPearsonCorrelationCoefficientProperty::generateEffectSize($c);
	}
	/**
	 * @return float
	 * @internal param bool $round
	 */
	public function getDailyValuePredictingHighOutcome(): ?float{
		$val = $this->getAttribute(UserVariableRelationship::FIELD_VALUE_PREDICTING_HIGH_OUTCOME);
		return $val;
	}
	/**
	 * @return float|null
	 */
	public function getDailyValuePredictingLowOutcome(): ?float{
		$val = $this->getAttribute(UserVariableRelationship::FIELD_VALUE_PREDICTING_LOW_OUTCOME);
		return $val;
	}
	/**
	 * @return string
	 */
	public function getPValueDataPointsOrNumberOfParticipantsFragment(): string{
		if($this->getPValue()){
			return '(' . $this->getPValueConfidenceIntervalString() . ") ";
		}
		if($pairs = $this->getNumberOfPairs()){
			return '(' . $pairs . " overlapping data points) ";
		}
		if($users = $this->getNumberOfUsers()){
			return '(' . $users . " study participants) ";
		}
		return '';
	}
	/**
	 * @return string
	 */
	public function getPValueConfidenceIntervalString(): string{
		return 'p=' . $this->getPValue(QMVariableRelationship::SIG_FIGS) . ', 95% CI ' .
			round($this->getCorrelationCoefficient() - $this->getConfidenceInterval(), QMVariableRelationship::SIG_FIGS) .
			' to ' .
			round($this->getCorrelationCoefficient() + $this->getConfidenceInterval(), QMVariableRelationship::SIG_FIGS);
	}
	/**
	 * @return float
	 */
	public function getConfidenceInterval(): float {
		return $this->getAttribute(UserVariableRelationship::FIELD_CONFIDENCE_INTERVAL);
	}
	/**
	 * @param bool|null $addressingUser
	 * @return string
	 * @throws InsufficientVarianceException
	 */
	public function getOneLineSummary(bool $addressingUser = null): string{
		$theseData = $this->getForMostYourOrThisIndividual($addressingUser) . " data";
		if(isset($this->numberOfUsers)){
			$theseData = "Aggregated data from {$this->getNumberOfUsers()} study participants";
		}
		return "$theseData suggests with a {$this->getConfidenceLevel()} degree of confidence " .
			$this->getPValueDataPointsOrNumberOfParticipantsFragment() .
			"that {$this->getCauseVariableDisplayName()} has a " .
			"{$this->getEffectSize()} predictive relationship (R=" .
			$this->getCorrelationCoefficient(QMVariableRelationship::SIG_FIGS) . ') with ' . $this->effectNameWithSuffix() .
			". ";
	}
	/**
	 * @return string
	 */
	public function getStudyAbstract(): string{
		if($this->getCorrelationCoefficient() === null){
			return $this->getParticipantInstructionsHtml();
		}
		$abstract = "";
		if($err = $this->getUserErrorMessage()){
			$abstract .= "
<p>
    $err
</p>
";
		}
		if($this->getEffectSize() && $this->getConfidenceLevel()){
			return $abstract .HtmlHelper::renderView(view('study-abstract', ['c' => $this]));
		}else{
			return $abstract .$this->getParticipantInstructionsHtml();
		}
	}
	/**
	 * @return int
	 */
	public function getPredictsLowEffectChange(): ?float {
		return $this->getAttribute(UserVariableRelationship::FIELD_PREDICTS_LOW_EFFECT_CHANGE);
	}
	/**
	 * @return string
	 */
	public function getPredictsLowEffectChangeSentenceFragment(): string{
		$change = $this->getPredictsLowEffectChange();
		if($change !== null){
			return ' generally ' . abs($change) . '% ';
		}
		return ' ';
	}
	/**
	 * @return float
	 */
	public function getGroupedCauseValueClosestToValuePredictingHighOutcome(): float{
		$val = $this->getAttribute(UserVariableRelationship::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME);
		return $val;
	}
	/**
	 * @return float
	 */
	public function getGroupedCauseValueClosestToValuePredictingLowOutcome(): float{
		return $this->getAttribute(UserVariableRelationship::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME);
	}
	/**
	 * @return float
	 */
	public function getGroupedValueOverDurationOfActionClosestToValuePredictingHighOutcome(): float{
		return $this->getAttribute(UserVariableRelationship::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME);
	}
	/**
	 * @return string
	 * @throws InsufficientVarianceException
	 */
	public function getReverseStatisticsAndAnalysisSettingsString(): string{
		$c = $this->getHasCorrelationCoefficient();
		$string = '(';
		$pValue = $c->getPValue();
		if($pValue){
			$string .= $pValue . ', 95% CI ' .
				round($c->getReverseCorrelationCoefficient() - $c->getConfidenceInterval(), QMVariableRelationship::SIG_FIGS) .
				' to ' .
				round($c->getReverseCorrelationCoefficient() + $c->getConfidenceInterval(), QMVariableRelationship::SIG_FIGS) .
				', ';
		}
		return $string . 'onset delay = -' . $c->getOnsetDelayHumanString() . ', duration of action = -' .
			$c->getDurationOfActionHumanString() . ')';
	}
	/**
	 * @param string $errorMessage
	 * @return string
	 */
	public function addErrorMessageToStudyAbstract(string $errorMessage): string{
		$html = "<p>I couldn't determine the relationship yet because $errorMessage</p>" .
			"<p>I generally need a month of data and 30 measurements in order to perform an analysis.</p>";
		if($this->typeIsIndividual() && $this->getCauseVariable() && $this->getEffectVariable()){
			$html .= $this->getCauseUserVariable()->getDataQuantityHTML() .
				$this->getEffectUserVariable()->getDataQuantityHTML();
		}
		$html .= "<p> If you haven't already, please add a reminder and start tracking. </p>" .
			"<p> Please create a ticket at http://help.quantimo.do if you need assistance.  I love you! </p>";
		return $html;
	}
	/**
	 * @return string
	 */
	private function getForwardStatisticsAndAnalysisSettingsString(): string{
		return '(' . $this->getHasCorrelationCoefficient()->getPValueDataPointsOrNumberOfParticipantsFragment() . ', ' .
			$this->getOnsetDelayDurationOfActionString() . ')';
	}
	/**
	 * @return string
	 * @throws InsufficientVarianceException
	 */
	public function getAnalysisSuggestsHigherCausePredictsEffectWithSignificanceSentence(): string{
		return "This analysis suggests that higher {$this->getCauseVariableDisplayName()} " .
			"({$this->getCauseVariableCategory()->name}) generally predicts " .
			"{$this->getDirection()} {$this->getEffectVariableDisplayName()} " .
			$this->getPValueDataPointsOrNumberOfParticipantsFragment();
	}
	/**
	 * @return string
	 */
	public function getForwardPearsonSentence(): string{
		$c = $this->getHasCorrelationCoefficient();
		return "<p>The Forward Pearson Predictive Coefficient was " .
			$c->getCorrelationCoefficient(QMVariableRelationship::SIG_FIGS) .
			$this->getForwardStatisticsAndAnalysisSettingsString() . '.
            </p>';
	}
	/**
	 * @return string
	 */
	public function getCumulativeValueOverDurationOfActionValuePredictingHighOutcomeSentence(): ?string{
		if($this->getDailyValuePredictingHighOutcome() === null){
			return null;
		}
		return "
            <p>{$this->getEffectVariableDisplayName()} is" . $this->getPredictsHighEffectChange() .
			"% higher after around " . $this->getDailyValuePredictingHighOutcomeString() . " {$this->getCauseVariableDisplayName()}.
            </p>";
	}
	/**
	 * @return string
	 */
	public function getCumulativeValueOverDurationOfActionValuePredictingLowOutcomeSentence(): ?string{
		if($this->getDailyValuePredictingLowOutcome() === null){
			return null;
		}
		return "
            <p>After an onset delay of " . $this->getOnsetDelayHumanString() .
			", {$this->getEffectVariableDisplayName()} is" . $this->getPredictsLowEffectChangeSentenceFragment() .
			" than its average over the " . $this->getDurationOfActionHumanString() . " following around " .
			$this->getDailyValuePredictingLowOutcomeString() . " {$this->getCauseVariableDisplayName()}.
            </p> ";
	}
	/**
	 * @return string
	 */
	public function getPredictsLowEffectChangeSentence(): ?string{
		if($this->getDailyValuePredictingLowOutcome() === null){
			return null;
		}
		return "<p>When the " . "{$this->getCauseVariableDisplayName()} value is closer to " .
			$this->getDailyValuePredictingLowOutcomeString() . " than " .
			$this->getDailyValuePredictingHighOutcomeString() . ", the " .
			"{$this->getEffectVariableDisplayName()} value which follows is " .
			$this->getPredictsLowEffectChangeSentenceFragment() . ' than its typical value.
            </p>';
	}
	/**
	 * @return string
	 */
	public function getTopQuartileSentence(): ?string{
		if($this->getDailyValuePredictingHighOutcome() === null){
			return null;
		}
		return '<p> The top quartile outcome values are preceded by an average ' .
			$this->getDailyValuePredictingHighOutcomeString() . " of {$this->getCauseVariableDisplayName()}.
            </p>";
	}
	/**
	 * @return string
	 */
	public function getBottomQuartileSentence(): ?string{
		if($this->getDailyValuePredictingLowOutcome() === null){
			return null;
		}
		return '<p>The bottom quartile outcome values are preceded by an average ' .
			$this->getDailyValuePredictingLowOutcomeString() . " of " . "{$this->getCauseVariableDisplayName()}.
            </p> ";
	}
	/**
	 * @return string
	 */
	public function getReversePearsonSentence(): string{
		return '<p>The Reverse Pearson Predictive Coefficient was ' .
			$this->getReverseCorrelationCoefficient(QMVariableRelationship::SIG_FIGS) .
			$this->getReverseStatisticsAndAnalysisSettingsString() . '.
            </p>';
	}
	/**
	 * @return string
	 */
	public function getPredictsHighEffectChangeSentence(): ?string{
		if($this->getDailyValuePredictingHighOutcome() === null){
			return null;
		}
		return '
            <p>When the ' . "{$this->getCauseVariableDisplayName()} value is closer to " .
			$this->getDailyValuePredictingHighOutcomeString() . " than " .
			$this->getDailyValuePredictingLowOutcomeString() . ", the " .
			"{$this->getEffectVariableDisplayName()} value which follows is " . $this->getPredictsHighEffectChange()
		       . " percent higher than its typical value.
            </p>";
	}
	/**
	 * @return string
	 */
	public function getStudyBackground(): string{
		return 'In order to reduce suffering through the advancement of ' . 'human knowledge, I have ' .
			"chosen to share my findings regarding the relationship between " . $this->getCauseVariableDisplayName() .
			" and " . $this->getEffectVariableDisplayName() . ".";
	}
	/**
	 * @param bool $arrows
	 * @param bool $hyperLinkNames
	 * @return string
	 */
	public function getStudyTitle(bool $arrows = false, bool $hyperLinkNames = false): string{
		$cc = $this->getCorrelationCoefficient();
		if($cc === null){
			return $this->getStudyQuestion();
		}
		$title = $this->getPredictorExplanationTitle($arrows, $hyperLinkNames);
		return StudyText::formatTitle($this, $title, $arrows);
	}
	/**
	 * @return null|string
	 */
	public function getAverageEffectFollowingLowCauseExplanation(): ?string{
		$followingLowCause = $this->getAverageEffectFollowingLowCause();
		$avg = $this->getOrCalculateAverageEffect();
		if(!$avg || $followingLowCause === null){
			return null;
		}
		$change = round(($followingLowCause - $avg) / $avg * 100, 0);
		if(!$change){
			return null;
		}
		if($change < 0){
			$percentChangeFromAverageEffectText = '(' . abs($change) . '% lower)';
		} else{
			$percentChangeFromAverageEffectText = '(' . $change . '% higher)';
		}
		$averageDailyLowCause = $this->getAverageDailyLowCause();
		if($averageDailyLowCause === null){
			return null;
		}
		return "{$this->getEffectVariableDisplayName()} is " .
			$this->getAverageEffectFollowingLowCause(QMVariableRelationship::SIG_FIGS) .
			"{$this->getEffectVariableCommonUnitAbbreviatedName()} $percentChangeFromAverageEffectText on average " .
			"after days with around " . $this->causeValueUnitVariableName($averageDailyLowCause) . ".";
	}
	/**
	 * @return string
	 */
	public function generatePostContent(): string{
		$s = $this->findInMemoryOrNewQMStudy();
		$html = $s->generatePostContent();
		return $html;
	}
	/**
	 * @return string|null
	 */
	public function getCategoryName(): string{
		return $this->findInMemoryOrNewQMStudy()->getCategoryName();
	}
	/**
	 * @return string
	 */
	public function getCategoryDescription(): string{
		return $this->findInMemoryOrNewQMStudy()->getCategoryDescription();
	}
	/**
	 * @return string|null
	 */
	abstract public function getParentCategoryName(): ?string;
	/**
	 * @param string $content
	 * @throws InvalidStringException
	 */
	public static function validatePostContent(string $content){
		QMStr::assertStringDoesNotContain($content, [//"&amp;effectVariableId=",
        ], WpPost::FIELD_POST_CONTENT);
		QMStr::assertStringContains($content, [
            "https://app.quantimo.do/api/v2/study?causeVariableId=",
            "join-study-button",
            "data:image/png;base64",
        ], WpPost::FIELD_POST_CONTENT, true);
	}
	/**
	 * @return string
	 */
	public function getTitleAttribute(): string{
		if(!$this->hasId()){
			return static::getClassNameTitle();
		}
		return $this->getStudyTitle();
	}
	public function exceptionIfWeShouldNotPost(): void{
		if($this->isStupidVariableCategoryPair()){
			le("Not posting stupid variable category pair $this");
		}
		if($this->getCauseQMVariable()->isStupidVariable()){
			le("Not posting stupid variable $this");
		}
		if($this->getEffectQMVariable()->isStupidVariable()){
			le("Not posting stupid variable $this");
		}
	}
	public function getShowContent(bool $inlineJs = false): string{
		return $this->getStudyHtml()->getShowContent();
	}
	/**
	 * @return bool
	 */
	public static function weShouldDoFullCalculationAndGenerateCharts(): bool{
		if(!AppMode::isApiRequest()){
			return true;
		}
		if(QMRequest::urlContains('/study')){
			return true;
		}
		if(IncludeChartsParam::includeCharts()){
			return true;
		}
		return false;
	}
	public function getSlugWithNames(): string{
		return $this->getStudyId();
	}
	public function getInteractiveStudyUrl(): string{
		$url = StudyLinks::generateStudyUrlDynamic($this->getCauseVariableName(), $this->getEffectVariableName(),
			$this->getUserId(), $this->getStudyId());
		return $url;
	}
	/**
	 * @return bool
	 */
	public function isBoring(): bool{
		if($this->getDataSourceName() === GlobalVariableRelationshipDataSourceNameProperty::DATA_SOURCE_NAME_MedDRA){
			$this->logError("Data source is MedDRA");
			return true;
		}
		if(VariableNameProperty::isStupid($this->getCauseVariableName())){
			$this->logError($this->getCauseVariableName() . " is stupid cause variable name");
			return true;
		}
		if(VariableNameProperty::isStupid($this->getEffectVariableName())){
			$this->logError($this->getEffectVariableName() . " is stupid effect variable name");
			return true;
		}
		return false;
	}
	/**
	 * @return bool
	 */
	protected function shouldUseChangeFromBaseline(): bool{
		return false;  // TODO: Implement for global variable relationship
	}
	/**
	 * @throws StupidVariableException
	 * @throws StupidVariableNameException
	 */
	protected function exceptionIfStupidVariable(){
		$cause = $this->getOrSetCauseQMVariable();
		if($cause->getVariableCategoryId() === SoftwareVariableCategory::ID){
			if($cause->getNumberOfUsers() < 20){
				throw new StupidVariableException("Not calculating SoftwareVariableCategory $cause correlation because it has less than 20 users",
					$cause);
			}
		}
		$cause->exceptionIfStupidVariable("Not calculating correlation.");
		$this->getOrSetEffectQMVariable()->exceptionIfStupidVariable("Not calculating correlation.");
	}
	/**
	 * @return string
	 */
	public function getDataQuantityOrTrackingInstructionsHTML(): string{
		return $this->getOrSetCauseQMVariable()->getDataQuantityOrTrackingInstructionsHTML() .
			$this->getOrSetEffectQMVariable()->getDataQuantityOrTrackingInstructionsHTML();
	}
	protected function getShowPageView(array $params = []): View{
		return $this->findInMemoryOrNewQMStudy()->getShowView($params);
	}
	public function getEffectSizeLinkToStudyWithExplanation(): string{
		return BaseEffectFollowUpPercentChangeFromBaselineProperty::generateHyperlink($this->causeNameWithSuffix(),
			$this->effectNameWithSuffix(), $this->getChangeFromBaseline(), $this->getInteractiveStudyUrl(),
			$this->getEffectVariableValence());
	}
	/**
	 * @param string $message
	 * @param array $parameters
	 * @return string
	 */
	public function logAndSetCorrelationError(string $message, array $parameters = []): string{
		$this->logError($message, $parameters);
		return $this->setInternalErrorMessage($message);
	}
	public function getHyperParametersSentence(): string{
		return "An onset delay of " . $this->getOnsetDelayHumanString() . " and duration of action " .
			$this->getDurationOfActionHumanString() . " were being used. ";
	}
	public function hyperParamSlug(): string{
		return UrlHelper::toQueryString($this->hyperParams());
	}
	public function hyperParams(): array{
		return [
			UserVariableRelationship::FIELD_ONSET_DELAY => $this->getOnsetDelay(),
			UserVariableRelationship::FIELD_DURATION_OF_ACTION => $this->getDurationOfAction(),
		];
	}
	/**
	 * @return string
	 * Need getPostNameSlug because we have to override and use study UniqueIndexIdsSlug in QMCorrelations to avoid
	 *     duplicate posts
	 */
	public function getSlugWithClassAndNames(): string{
		return $this->getStudyId();
	}
	public function getChartsUrl(): string{
		return $this->getStudyUrl();
	}
	public function getBadgeText(): ?string{
		return $this->getChangeFromBaselineString();
	}
	public function getBadgeHtml(): string{
		return $this->getChangeFromBaselineFragmentHtml();
	}
	public function getPredictorButtonData(float $maxChange): array{
		$v = $this->getCauseVariable();
		return $this->getVariableButtonData($v, $maxChange);
	}
	public function getOutcomeButtonData(float $maxChange): array{
		$v = $this->getEffectVariable();
		return $this->getVariableButtonData($v, $maxChange);
	}
	/**
	 * @param Variable $v
	 * @param float $maxChange
	 * @return array
	 */
	private function getVariableButtonData(Variable $v, float $maxChange): array{
		return [
			'image' => $v->getImage(),
			'avatar' => $v->getAvatar(),
			'badge_text' => $this->getBadgeText(),
			'url' => $this->getUrl(),
			'number_of_users' => $this->getNumberOfUsers() + BaseNumberOfUsersProperty::NUMBER_OF_FAKE_USERS,
			'number_of_days' => $this->getNumberOfDays(),
			'predictive_coefficient' => $this->getForwardPearsonCorrelationCoefficient(),
			'change' => $this->getChangeFromBaseline(),
			'z_score' => $this->getZScore(),
			BaseConfidenceLevelProperty::NAME => $this->getConfidenceLevel(),
			BaseStrengthLevelProperty::NAME => $this->getStrengthLevel(),
			'gauge' => $this->getGaugeImageUrl(),
			'title' => $v->getNameAttribute(),  // Get the full stupid name for disambiguation
			//'title'                => $v->getDisplayNameAttribute(),
			'sorting_score' => $this->getSortingScore(),
			'keywords' => $this->getKeyWordString(),
			//'description'          => $this->getSubtitleAttribute(),
			'tooltip' => $this->getSubtitleAttribute(),
			'color' => $this->getHexColor(),
			'width' => round($this->getBarWidth($maxChange)),
			BaseFontAwesomeProperty::NAME => $v->getFontAwesome(),
		];
	}
	public function getSharingUrl(array $params = []): string{
		return $this->getStudyLinkStatic($params);
	}
	/**
	 * @return string
	 */
	public function getStrengthSentence(): string {
		$r = $this->getForwardPearsonCorrelationCoefficient();
		return "There is a ". $this->getEffectSize() . " (R = $r) relationship between ".
			$this->causeNameWithSuffix() . ' and '.
			$this->effectNameWithSuffix().". ";
	}
	/**
	 * @return array
	 */
	public function getTags(): array{
		$tags = [];
		if($this->getConfidenceLevel() === BaseConfidenceLevelProperty::CONFIDENCE_LEVEL_HIGH){
			$tags[] = "High Confidence";
		}
		if($this->getConfidenceLevel() === BaseConfidenceLevelProperty::CONFIDENCE_LEVEL_MEDIUM){
			$tags[] = "Medium Confidence";
		}
		if($this->getConfidenceLevel() === BaseConfidenceLevelProperty::CONFIDENCE_LEVEL_LOW){
			$tags[] = "Low Confidence";
		}
		$strengthLevel = $this->getStrengthTitleCase();
		$tags[] = $strengthLevel . " Effect Size";
		//        if($strengthLevel === self::EFFECT_SIZE_moderately_negative){$tags[] = "Medium Effect Size";}
		//        if($strengthLevel === self::EFFECT_SIZE_moderately_positive){$tags[] = "Medium Effect Size";}
		//        if($strengthLevel === self::EFFECT_SIZE_non_existent){$tags[] = "No Effect";}
		//        if($strengthLevel === self::EFFECT_SIZE_strongly_negative){$tags[] = "Strong Effect Size";}
		//        if($strengthLevel === self::EFFECT_SIZE_strongly_positive){$tags[] = "Strong Effect Size";}
		//        if($strengthLevel === self::EFFECT_SIZE_very_weakly_negative){$tags[] = "Weak Effect Size";}
		//        if($strengthLevel === self::EFFECT_SIZE_weakly_positive){$tags[] = "Weak Effect Size";}
		if($this->setDirection() === QMVariableRelationship::DIRECTION_HIGHER){
			$tags[] = "Positive Relationship";
		}
		if($this->setDirection() === QMVariableRelationship::DIRECTION_LOWER){
			$tags[] = "Negative Relationship";
		}
		if($this->typeIsIndividual()){
			$tags[] = "Individual Case Study";
		}
		if(!$this->typeIsIndividual()){
			$tags[] = "Population Study";
		}
		//$tags[] = $this->causeNameWithSuffix();
		//$tags[] = $this->effectNameWithSuffix();
		//$tags[] = $this->getCauseQMVariableCategory()->getName();
		//$tags[] = $this->getEffectQMVariableCategory()->getName();
		$title = [];
		foreach($tags as $tag){
			$title[] = QMStr::titleCaseSlow($tag);
		}
		return $title;
	}
	public function getKeyWords(): array{
		return $this->getStudyKeywords();
	}
	/**
	 * @return string
	 */
	public function getSharingDescription(): string{
		if($this->highConfidence()){
			return $this->getStudyAbstract();
		}else{
			return $this->getStudyQuestion();
		}
	}
	/**
	 * @return bool
	 */
	public function highConfidence(): bool{
		if($this->getCorrelationCoefficient() === null){
			return false;
		}
		try {
			return $this->getConfidenceLevel() === BaseConfidenceLevelProperty::CONFIDENCE_LEVEL_HIGH;
		} catch (NotEnoughDataException $e) {
			return false;
		}
	}
	public function getTagLine(): string{
		return $this->aboveAverageSentence();
	}
	/**
	 * @param int|null $precision
	 * @return float
	 */
	abstract public function getCorrelationCoefficient(int $precision = null): ?float;
	/**
	 * @return StudyImages
	 */
	public function getStudyImages(): StudyImages{
		return new StudyImages($this,
			$this);
	}
	/**
	 * @return StudySection[]
	 */
	public function getStudySectionsArray(): array{
		return $this->getStudyText()->getStudySectionsArray();
	}
	/**
	 * @return string
	 */
	public function getSharingTitle(): string{
		$highConfidence = $this->highConfidence();
		if($highConfidence){
			try {
				return $this->getStudyTitle();
			} catch (\Throwable $e) {
				QMLog::info(__METHOD__.": ".$e->getMessage());
				return $this->getStudyTitle();
			}
		}else{
			return $this->getStudyQuestion();
		}
	}
	/**
	 * @return StudyButton
	 */
	public function getFullStudyLinkButton(){
		$studyLinks = $this->getStudyLinks();
		$link = $studyLinks->getStudyLinkStatic();
		if(QMAuth::getQMUserIfSet() && QMClient::frameworkIsIonicApp()){
			$link = $studyLinks->getStudyUrlDynamic();
		}
		$button = new StudyButton();
		$button->setUrl($link);
		return $button;
	}
}
