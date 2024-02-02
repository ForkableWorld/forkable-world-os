<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Studies;
use App\VariableRelationships\QMVariableRelationship;
use App\DataSources\QMDataSource;
use App\Exceptions\InvalidStringException;
use App\Exceptions\NotEnoughDataException;
use App\Models\UserVariableRelationship;
use App\Properties\Base\BaseUserStudyTextProperty;
use App\Slim\Model\QMUnit;
use App\Slim\Model\StaticModel;
use App\Traits\HasCauseAndEffect;
use App\Traits\HasCorrelationCoefficient;
use App\Traits\HasProperty\HasOnsetAndDuration;
use App\Types\QMStr;
use App\UI\HtmlHelper;
use App\UI\ImageUrls;
use App\Utils\AppMode;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use LogicException;
/** Class StudyText
 * @package app/Studies
 */
class StudyText extends StaticModel {
    use HasOnsetAndDuration;
	public const TEXT_THIS_INDIVIDUAL_S = "This individual's ";
	public const TEXT_YOUR              = "Your ";
	private $hasCorrelationCoefficient;
    private $hasCauseAndEffect;
    public string $averageEffectFollowingHighCauseExplanation;
    public string $averageEffectFollowingLowCauseExplanation;
    public string $avgDailyValuePredictingHighOutcomeExplanation;
    public string $avgDailyValuePredictingLowOutcomeExplanation;
    public $dataAnalysis;
    public $dataSources;
    public $dataSourcesParagraphForCause;
    public $dataSourcesParagraphForEffect;
    public $optimalDailyValueSentence;
    public $participantInstructions;
    public $sharingDescription;
    public $sharingTitle;
    public $significanceExplanation;
    public $studyAbstract = 'Please join our study and help us to advance citizen science!';
    public $studyBackground;
    public $studyDesign;
    public $studyInvitation;
    public $studyLimitations;
    public $studyObjective;
    public $studyQuestion;
    public $studyResults;
    public $studyTitle;
    public $tagLine;
    /**
     * Study constructor.
     * @param QMVariableRelationship|UserVariableRelationship|\App\Models\GlobalVariableRelationship|null $hasCorrelationCoefficient
     * @param HasCauseAndEffect|QMStudy null $hasCauseAndEffect
     * @param null $cached
     */
    public function __construct($hasCorrelationCoefficient = null, $hasCauseAndEffect = null, $cached = null){
        if($cached){
            foreach($cached as $key => $value){
                $this->$key = $value;
            }
            return;
        }
        $this->hasCauseAndEffect = $this->hasCorrelationCoefficient = $hasCorrelationCoefficient;
        if($hasCauseAndEffect){
            $this->setHasCauseAndEffect($hasCauseAndEffect);
        }
        if(!$hasCauseAndEffect && !$hasCorrelationCoefficient){
            return;
        }
        $this->setStudyQuestion();
        $this->setStudyInvitation();
        $this->getSharingTitle();
        $this->getSharingDescription();
        $this->setStudyAbstract();
        $this->getTagLine();
	    $this->getStudyTitle();
	    $this->getParticipantInstructions();
	    $this->setStudyObjective();
        if(!$this->getHasCorrelationCoefficient()){
            return;
        }
        $this->setAverageEffectFollowingHighCauseExplanation();
        $this->setAverageEffectFollowingLowCauseExplanation();
        $this->setStudyBackground();
        if(QMStudy::weShouldGenerateFullStudyWithChartsCssAndInstructions($hasCauseAndEffect ?: $hasCorrelationCoefficient)){
            $this->setDataSources();
            $this->setDataAnalysis();
            $this->setStudyLimitations(); // This makes votes query for plausibility section
        }
        $this->setStudyDesign();
        $this->setStudyResults();
        $this->setSignificanceExplanation();
        $this->setValueOverDurationPredictingHighOutcomeSentence();
        $this->getDailyValuePredictingLowOutcomeSentence();
    }
    /**
     * @param bool $allowDbQueries
     * @return QMCommonVariable|QMVariable|QMUserVariable
     */
    private function getCauseVariable(bool $allowDbQueries = true){
	    $hasCauseAndEffect = $this->hasCauseAndEffect;
	    if($hasCauseAndEffect && $hasCauseAndEffect->hasCauseVariable()){
            return $hasCauseAndEffect->getOrSetCauseQMVariable();
        } // Prefer from study
        $c = $this->hasCorrelationCoefficient;
        if($c){
            $v = $c->hasCauseVariable();
            if($v){
                return $v;
            }
            if($allowDbQueries){
                return $c->getOrSetCauseQMVariable();
            }
        }
        if($allowDbQueries && $hasCauseAndEffect){
            return $hasCauseAndEffect->getOrSetCauseQMVariable();
        }
        return null;
    }
    /**
     * @param bool $allowDbQueries
     * @return QMCommonVariable|QMVariable|QMUserVariable
     */
    private function getEffectVariable(bool $allowDbQueries = true){
	    $hasCauseAndEffect = $this->hasCauseAndEffect;
	    if($hasCauseAndEffect && $hasCauseAndEffect->hasEffectVariable()){
            return $hasCauseAndEffect->getOrSetEffectQMVariable();
        } // Prefer from study
        $c = $this->hasCorrelationCoefficient;
        if($c){
            $v = $c->hasEffectVariable();
            if($v){
                return $v;
            }
            if($allowDbQueries){
                return $c->getOrSetEffectQMVariable();
            }
        }
        if($allowDbQueries && $hasCauseAndEffect){
            return $hasCauseAndEffect->getOrSetEffectQMVariable();
        }
        return null;
    }
    /**
     * @return string
     */
    public function addWikipediaExtractToBackground(): string{
        $causeExtract = $this->getCauseVariable()->getWikipediaExtract();
        $effectExtract = $this->getEffectVariable()->getWikipediaExtract();
        $this->studyBackground = $this->studyBackground.' <br> '.$causeExtract.' <br> '.$effectExtract;
        return $this->studyBackground;
    }
    /**
     * @return string
     */
    private function setStudyResults(): ?string{
        $c = $this->getHasCorrelationCoefficient();
        $studyResults =
            $c->getAnalysisSuggestsHigherCausePredictsEffectWithSignificanceSentence().
            $c->getCumulativeValueOverDurationOfActionValuePredictingHighOutcomeSentence().
            $c->getCumulativeValueOverDurationOfActionValuePredictingLowOutcomeSentence().
            $c->getDataQuantitySentence().
            $c->getTopQuartileSentence().
            $c->getBottomQuartileSentence().
            $c->getForwardPearsonSentence().
            $c->getReversePearsonSentence().
            $c->getPredictsHighEffectChangeSentence().
            $c->getPredictsLowEffectChangeSentence();
        if(isset($this->averageEffectFollowingHighCauseExplanation)){$studyResults .= '  '.$this->averageEffectFollowingHighCauseExplanation;}
        if(isset($this->averageEffectFollowingLowCauseExplanation)){$studyResults .= '  '.$this->averageEffectFollowingLowCauseExplanation;}
        return $this->studyResults = $studyResults;
    }
    /**
     * @return string
     */
    private function setSignificanceExplanation(): string{
        $c = $this->getHasCorrelationCoefficient();
        if($c->getTValue() === null){
            return '
            <p>The T value and confidence interval have not yet been determined.
            </p>';
        }
        $str = '
            <p>Using a two-tailed t-test with alpha = 0.05, it was determined '.
            'that the change in '.
            $c->getEffectVariableDisplayName().
            ' is not statistically significant at a 95% confidence '.
            'interval.  This suggests that the '.
            $c->getCauseVariableDisplayName().
            ' value does not have a significant '.
            'influence on the '.
            $c->getEffectVariableDisplayName().
            ' value.
            </p>';
        if($c->getTValue() > $c->getCriticalTValue()){
            $c->significantDifference = true;
            $str = '
            <p>Using a two-tailed t-test with alpha = 0.05, it was '.
                'determined that the change in '.
                $c->effectVariableName.
                ' is statistically significant at 95% confidence interval.
            </p> ';
        }
        if($rsd = $c->getEffectBaselineRelativeStandardDeviation()){
            $str .= "
            <p>
                After treatment, a ".
                $c->getOrCalculatePercentChangeFragment(false).
                " (".
                $c->getAbsoluteFollowupChangeString().
                ")".
                " from the mean baseline ".
                $c->getMeanBaselineString().
                " was observed. The relative standard deviation at baseline was ".
                "$rsd%. The observed change was {$c->getZScore()} times the standard deviation.
            </p>".
                "
            <p>A common rule of thumb considers a change greater than twice the baseline standard deviation on ".
                "two separate pre-post experiments may be considered significant.  This occurrence would have only a 5% ".
                "likelihood of resulting from random fluctuation (a p-value < 0.05).
            </p>";
        }
        return $this->significanceExplanation = $str;
    }
    /**
     * @return string
     */
    private function setStudyLimitations(): string{
        $correlation = $this->getHasCorrelationCoefficient();
        $html = '
            <p>
                As with any human experiment, it was impossible to control for all potentially confounding variables.
                User Variable Relationship does not necessarily imply causation.  We can never know for sure if one factor is definitely the cause of an outcome.
                However, lack of correlation definitely implies the lack of a causal relationship.  Hence, we can with great
                confidence rule out non-existent relationships.  For instance, if we discover no relationship between mood
                and an antidepressant this information is just as or even more valuable than the discovery that there is a relationship.
            </p>
            <p>
                We can also take advantage of several characteristics of time series data from many subjects
                to infer the likelihood of a causal relationship if we do find a correlational relationship.
                The criteria for causation are a group of minimal conditions necessary to provide adequate
                evidence of a causal relationship between an incidence and a possible consequence.
            </p>
            <h3> Criteria For Causal Inference</h3>'.
            HtmlHelper::getParagraphWithBoldHeader('Strength (A.K.A. Effect Size)',
                'A small association does not mean that there is not a causal effect, '.
                'though the larger the association, the more likely that it is causal. '.
                $correlation->getStrengthSentence()).
            HtmlHelper::getParagraphWithBoldHeader('Consistency (A.K.A. Reproducibility)',
                'Consistent findings observed by different persons in different '.
                'places with different samples strengthens the likelihood of an effect. Furthermore, in accordance with the '.
                'law of large numbers (LLN), the predictive power and accuracy of these results will continually grow '.
                "over time.  {$correlation->getNumberOfPairs()} paired data points were used in this analysis.   Assuming that the relationship ".
                "is merely coincidental, as the participant independently modifies their ".
                $correlation->causeNameWithSuffix().
                " values, the ".
                'observed strength of the relationship will decline until it is below the threshold of significance. '.
                " To it another way, in the case that we do find a spurious correlation, suggesting that banana intake improves mood for instance, ".
                'one will likely increase their banana intake.  Due to the fact that this correlation is spurious, it is unlikely '.
                'that you will see a continued and persistent corresponding increase in mood.  So over time, the spurious correlation will '.
                'naturally dissipate. ').
            HtmlHelper::getParagraphWithBoldHeader('Specificity',
                'Causation is likely if a very specific population at a specific site and disease with '.
                'no other likely explanation. The more specific an association between a factor and an effect is, '.
                'the bigger the probability of a causal relationship.').
            HtmlHelper::getParagraphWithBoldHeader('Temporality',
                'The effect has to occur after the cause (and if there is an expected delay between '.
                'the cause and expected effect, then the effect must occur after that delay). '.
                'The confidence in a causal relationship is bolstered by the fact that time-precedence was taken into account in all calculations.').
            HtmlHelper::getParagraphWithBoldHeader('Biological Gradient',
                'Greater exposure should generally lead to greater incidence of the effect. '.
                'However, in some cases, the mere presence of the factor can trigger the effect. In other cases, '.
                'an inverse proportion is observed: greater exposure leads to lower incidence.');
        if(AppMode::isTestingOrStaging()){
            HtmlHelper::checkForMissingHtmlClosingTags($html, __FUNCTION__);
        }
        $html .= $correlation->getPlausibilitySectionHtml();
        $html .= HtmlHelper::getParagraphWithBoldHeader('Coherence',
                'Coherence between epidemiological and laboratory findings increases the likelihood of an effect. '.
                ' It will be very enlightening to aggregate this data with the data from other participants '.
                '
                with similar genetic, diseasomic, environmentomic, and demographic profiles.').
            HtmlHelper::getParagraphWithBoldHeader('Experiment',
                'All of human life can be considered a natural experiment. Occasionally, it is possible to appeal to experimental evidence.').
            HtmlHelper::getParagraphWithBoldHeader('Analogy',
                'The effect of similar factors may be considered.');
        if($err = $correlation->getUserErrorMessage()){
            $html =
                "
            <p>The accuracy of this study may be limited by the fact that ".
                $err.
                ".  A greater amount of data and more variance in the data would help to resolve this issue.
            </p>".
                $html;
        }
        if($warnings = $correlation->getWarnings()){
            $html .= '<h3>Potential Issues</h3>';
            $html .= '<p>The following issues were identified during analysis:</p>';
            $items = "";
            foreach($warnings as $warning){
                $items .= '<li>'.$warning.'</li>';
            }
            $html .= "
                <ul style='line-height: unset;'>
                $items
                </ul>
            ";
        }
        return $this->studyLimitations = $html;
    }
    /**
     * @return string
     */
    private function setDataAnalysis(): string {
        $c = $this->getHasCorrelationCoefficient();
        $cause = $this->getCauseVariable();
        $effect = $this->getEffectVariable();
        $recalculateStudyLink = null;
        $study = $this->getHasCauseAndEffect();
        if($study){$recalculateStudyLink = $study->getStudyLinks()->getRecalculateStudyUrl();}
        $text =
            $cause->getDataAnalysisParagraph($recalculateStudyLink).
            $effect->getDataAnalysisParagraph($recalculateStudyLink);
        $text .= "<h4 class=\"text-2xl font-semibold\">Predictive Analytics</h4>".
            '<p>It was assumed that '.$c->getOnsetDelayHumanString().
            " would pass before a change in {$c->getCauseVariableName()} would produce an observable ".
            "change in {$c->getEffectVariableName()}.</p>";
        $text .= '<p>It was assumed that '.
            "{$c->getCauseVariableName()} could produce an observable change in {$c->getEffectVariableName()} for as much as ".
            $c->getDurationOfActionHumanString().' after the stimulus event.  </p>'.
            $cause->getSettingsButtonHtml($recalculateStudyLink);
        if($c->typeIsIndividual()){
            $text .= $cause->getDataQuantityHTML();
        }
        if($c->typeIsIndividual()){
            $text .= $effect->getDataQuantityHTML();
        }
        return $this->dataAnalysis = $text;
    }
    /**
     * @return string
     */
    public function setStudyAbstract(): string{
        $c = $this->getHasCorrelationCoefficient();
		if(!$c){
			$c = $this->getHasCauseAndEffect();
		}
	    return $this->studyAbstract = $c->getStudyAbstract();
    }
    /**
     * @return string
     */
    private function setStudyDesign(): string{
        $c = $this->getHasCorrelationCoefficient();
        if(isset($c->userId)){
            $this->studyDesign =
                "<p>This study is based on data donated by one participant. Thus, the study".
                " design is consistent with an n=1 observational natural experiment.</p> ";
        }else{
            $this->studyDesign =
                "<p>This study is based on data donated by ".
                $c->getNumberOfUsers().
                " participants. Thus, the study design is equivalent to the ".
                "aggregation of {$c->getNumberOfUsers()} separate n=1 observational natural experiments. </p>";
        }
        return $this->studyDesign;
    }
    /**
     * @return string
     */
    private function setStudyObjective(): string{
        return $this->studyObjective =
            "The objective of this study is to determine the nature of the relationship ".
            "(if any) between ".
            $this->getCauseVariableDisplayName().
            " and ".
            $this->getEffectVariableDisplayName().
            ". Additionally, we attempt to determine the ".
            $this->getCauseVariableName().
            " values most likely to produce optimal ".
            $this->getEffectVariableDisplayName().
            " values.";
    }
    /**
     * @param string $variableName
     * @param QMDataSource $source
     * @return string
     */
    public function getDataSourcesParagraphForConnector(string $variableName, $source): ?string{
        if(!$source){
            return null;
        }
        $name = $source->displayName;
        if(empty($name)){le("No display name!");}
        $paragraph = $variableName. " data was primarily collected using $name.  ".$source->longDescription;
        return str_replace($name, $source->getLinkedDisplayNameHtml(), $paragraph);
    }
    /**
     * @return string
     */
    private function setDataSources(){
        $c = $this->getHasCorrelationCoefficient();
        if(!$c){return false;}
        $causeSource = $c->getCauseVariable()->getBestDataSource();
		$causeSource->getButtons();
        $effect = $c->getEffectVariable();
        $this->dataSourcesParagraphForCause = "<p>".$this->getDataSourcesParagraphForConnector(
			$c->getCauseVariableName(),
		        $causeSource)."</p>";
        $c->effectDataSource = QMDataSource::getAffiliatedQMDataSourceByNameOrId($c->getEffectVariable()->most_common_connector_id);
		$c->effectDataSource->getButtons();
        $this->dataSourcesParagraphForEffect = "<p>".$this->getDataSourcesParagraphForConnector($c->getEffectVariableName(),
		        $effect->getBestDataSource())."</p>";
        $paragraph = $this->dataSourcesParagraphForCause.$this->dataSourcesParagraphForEffect;
        return $this->dataSources = $paragraph;
    }
    /**
     * @return string
     */
    private function getDailyValuePredictingLowOutcomeSentence(): string {
        $c = $this->getHasCorrelationCoefficient();
        return $this->avgDailyValuePredictingLowOutcomeExplanation =
            $c->getDailyValuePredictingLowOutcomeSentence();
    }
    /**
     * @return null|string
     */
    private function setAverageEffectFollowingLowCauseExplanation(): ?string{
        $c = $this->getHasCorrelationCoefficient();
		$str = $c->getAverageEffectFollowingLowCauseExplanation();
		if(!$str){return null;}
        return $this->averageEffectFollowingLowCauseExplanation = $str;
    }
    /**
     * @return void
     */
    private function setAverageEffectFollowingHighCauseExplanation(): void {
        $c = $this->getHasCorrelationCoefficient();
        if(!$c->getAverageEffectFollowingHighCause()){
            $str = "Could not set AverageEffectFollowingHighCauseExplanation because !isset(averageEffectFollowingHighCause)";
        } else if($c->getAverageEffectFollowingHighCause() === null){
            $str = "Could not set AverageEffectFollowingHighCauseExplanation because averageEffectFollowingHighCause is null";
        } else if(!$c->getAverageEffect()){
            $str = "Could not set AverageEffectFollowingHighCauseExplanation because no averageEffect";
        }else{
            $percentChangeFromAverageEffect =
                round(($c->getAverageEffectFollowingHighCause() - $c->getAverageEffect()) / $c->getAverageEffect() * 100, 0);
            /** @noinspection TypeUnsafeComparisonInspection */
            if($percentChangeFromAverageEffect != 0){
                $percentChangeFromAverageEffectText = '('.$percentChangeFromAverageEffect.'% higher)';
                if($percentChangeFromAverageEffect < 0){
                    $percentChangeFromAverageEffectText = '('.abs($percentChangeFromAverageEffect).'% lower)';
                }
                $this->averageEffectFollowingHighCauseExplanation =
                    "{$c->getEffectVariableName()} is ".
                    $c->getAverageEffectFollowingHighCause(2).
                    " {$c->getEffectVariableCommonUnitAbbreviatedName()} $percentChangeFromAverageEffectText on average ".
                    "after days with around ".
                    $c->getAverageDailyHighCause(2).
                    " {$c->getCauseVariableCommonUnitAbbreviatedName()} {$c->getCauseVariableName()}";
                $str =
                    QMUnit::removeSpaceBeforeSlash($this->averageEffectFollowingHighCauseExplanation);
            } else {
                $str = "Could not set AverageEffectFollowingHighCauseExplanation because no percentChangeFromAverageEffect is 0";
            }
        }
        $this->averageEffectFollowingHighCauseExplanation = $str;
    }
    /**
     * @return void
     */
    private function setValueOverDurationPredictingHighOutcomeSentence(): void {
        $c = $this->getHasCorrelationCoefficient();
        $this->avgDailyValuePredictingHighOutcomeExplanation = $c->valueOverDurationPredictingHighOutcomeSentence();
    }
    /**
     * @return string
     */
    private function setStudyBackground(): string{
        return $this->studyBackground = 'In order to reduce suffering through the advancement of '.
            'human knowledge, I have '.
            "chosen to share my findings regarding the relationship between ".
            $this->getCauseVariableDisplayName().
            " and ".
            $this->getEffectVariableDisplayName().
            ".";
    }
    /**
     * @param $study
     */
    public function setHasCauseAndEffect($study){
        $this->hasCauseAndEffect = $study;
    }
    /**
     * @return HasCorrelationCoefficient
     */
    public function getHasCorrelationCoefficient(){
		if($this->hasCorrelationCoefficient){
			return $this->hasCorrelationCoefficient;
		}
        if($study = $this->hasCauseAndEffect){
            if($statistics = $this->getHasCauseAndEffect()->getHasCorrelationCoefficientIfSet()){
                if(is_string($statistics)){
                    throw new LogicException($statistics);
                }
                return $statistics;
            }
        }
        return $this->hasCorrelationCoefficient;
    }
    /**
     * @return mixed
     */
    public function getStudyObjective(): string{
        return $this->studyObjective ?: $this->setStudyObjective();
    }
    /**
     * @return string
     */
    public function getStudyLimitations(): string{
        return $this->studyLimitations ?: $this->setStudyLimitations();
    }
    /**
     * @return mixed
     */
    public function getStudyDesign(): string{
        return $this->studyDesign ?: $this->setStudyDesign();
    }
    /**
     * @return mixed
     */
    public function getSignificanceExplanation(): string{
        return $this->significanceExplanation ?: $this->setSignificanceExplanation();
    }
    /**
     * @return string
     */
    public function getDataSources(): string{
        return $this->dataSources ?: $this->setDataSources();
    }
    /**
     * @return QMStudy|HasCauseAndEffect
     */
    public function getHasCauseAndEffect(){
        return $this->hasCauseAndEffect;
    }
    /**
     * @return string
     */
    public function getStudyQuestion(): string{
        return $this->studyQuestion ?: $this->setStudyQuestion();
    }
    /**
     * @return string
     */
    private function setStudyQuestion(): string{
        $causeName = $this->getCauseVariableDisplayName();
        $effectName = $this->getEffectVariableDisplayName();
        $question = QMStr::isPlural($causeName) ? "Do " : "Does ";
        //$question .= strtolower($causeName) ." affect " . strtolower($this->getEffectVariableDisplayName()) . "?";
        $question .= $causeName." affect ".$effectName."?";
        return $this->studyQuestion = $question;
    }
    /**
     * @return string
     */
    private function getCauseVariableName(): string{
        $name = null;
        if(!$this->hasCauseAndEffect && $this->hasCorrelationCoefficient){
            $name = $this->getHasCorrelationCoefficient()->causeNameWithSuffix();
        }
        if(empty($name) && $this->getHasCauseAndEffect() && $this->getHasCauseAndEffect()->getCauseVariableName()){
            $name = $this->getHasCauseAndEffect()->getCauseVariableName();
        }
        if(empty($name) && $this->hasCorrelationCoefficient){
            $name = $this->getHasCorrelationCoefficient()->causeVariableName;
        }
        if(!$name){
            le("Could not get cause name!");
        }
        return $name;
    }
    /**
     * @return string
     */
    private function getEffectVariableName(): string{
        if(!$this->hasCauseAndEffect && $this->hasCorrelationCoefficient){
            $name = $this->getHasCorrelationCoefficient()->effectNameWithSuffix();
        }
        if(empty($name) && $this->getHasCauseAndEffect() && $this->getHasCauseAndEffect()->getEffectVariableName()){
            $name = $this->getHasCauseAndEffect()->getEffectVariableName();
        }
        if(empty($name) && $this->hasCorrelationCoefficient){
            $name = $this->getHasCorrelationCoefficient()->effectVariableName;
        }
        if(empty($name)){
            le("No getEffectVariableName");
        }
        return $name;
    }
    /**
     * @return string
     */
    private function getEffectVariableDisplayName(): string{
        return QMStr::displayName($this->getEffectVariableName());
    }
    /**
     * @return string
     */
    private function getCauseVariableDisplayName(): string{
        return QMStr::displayName($this->getCauseVariableName());
    }
    /**
     * @return string
     */
    public function getStudyInvitation(): string{
        return $this->studyInvitation ?: $this->setStudyInvitation();
    }
    /**
     * @return string
     */
    private function setStudyInvitation(): string{
        return $this->studyInvitation =
            "Donate a few seconds a day to help us discover if ".
            $this->getCauseVariableDisplayName().
            " affects ".
            $this->getEffectVariableDisplayName().
            "!";
    }
    /**
     * @param bool $arrows
     * @param bool $hyperLinkNames
     * @return string
     */
    public function getStudyTitle(bool $arrows = false): string{
        $title = $this->studyTitle;
        $c = $this->getHasCorrelationCoefficient();
        if($c && $c->getCorrelationCoefficient() !== null){
            $title = $c->getPredictorExplanationTitle($arrows, false);
        }
        if(!$title){$title = $this->getStudyQuestion();}
        $title = StudyText::formatTitle($c, $title, $arrows);
        return $this->studyTitle = $title;
    }
	/**
	 * @param bool $arrows
	 * @param bool $hyperLinkNames
	 * @return string
	 */
	public function getStudyTitleWithLinks(bool $arrows = false): string{
		$title = $this->studyTitle;
		$c = $this->getHasCorrelationCoefficient();
		if($c && $c->getCorrelationCoefficient() !== null){
			$title = $c->getPredictorExplanationTitle($arrows, true);
		}
		if(!$title){$title = $this->getStudyQuestion();}
		$title = StudyText::formatTitle($c, $title, $arrows);
		return $title;
	}
    /**
     * @return string
     */
    public function getStudyAbstract(): string{
        return $this->studyAbstract ?: $this->setStudyAbstract();
    }
    /**
     * @return string
     */
    public function getSharingTitle(): string{
	    return $this->sharingTitle = $this->getHasCauseAndEffect()->getSharingTitle();
    }
    /**
     * @return string
     */
    public function getSharingDescription(): string{
        if($this->hasCorrelationCoefficient){
	        $highConfidence = $this->getHasCorrelationCoefficient()->highConfidence();
			if($highConfidence){
				return $this->sharingDescription = $this->getHasCorrelationCoefficient()->getStudyAbstract();
			}
        }
		return $this->sharingDescription = $this->getStudyQuestion();
    }
    /**
     * @return string
     */
    public function getTagLine(): string{
        $c = $this->getHasCorrelationCoefficient();
        if($c && $c->getCorrelationCoefficient() !== null){
            try {
                return $this->tagLine = BaseUserStudyTextProperty::humanizeStudyText($c->getTagLine());
            } /** @noinspection PhpRedundantCatchClauseInspection */ catch (NotEnoughDataException $e){
                $this->logError($e->getMessage());
            }
        }
        if(!$this->tagLine){
            $this->tagLine = BaseUserStudyTextProperty::humanizeStudyText($this->getStudyInvitation());
        }
        return $this->tagLine;
    }
    /**
     * @return string
     */
    public function getParticipantInstructions(): string{
        return $this->participantInstructions ?: $this->setParticipantInstructions();
    }
    /**
     * @return string
     */
    public function setParticipantInstructions(): string{
        return $this->participantInstructions = $this->getHasCauseAndEffect()->getParticipantInstructionsHtml();
    }
    /**
     * @return bool
     */
    private function highConfidence(): bool{
        if(!$this->hasCorrelationCoefficient){
            return false;
        }
        $c = $this->getHasCorrelationCoefficient();
        return $c->highConfidence();
    }
    /**
     * @return string
     */
    public function getOptimalValueSentence(): string {
        $sentence = $this->getHasCorrelationCoefficient()->getOptimalValueWithDurationOfActionSentence();
        return $this->optimalDailyValueSentence = $sentence;
    }
    /**
     * @return StudySection[]
     */
    public function getStudySectionsArray(): array {
        $sections = [];
        $c = $this->hasCorrelationCoefficient;
        if($c){
            $sections[] = new StudySection('Abstract', $this->getStudyAbstract(), ImageUrls::STUDY);
        }
        $sections[] = new StudySection('Objective', $this->getStudyObjective(),
            ImageUrls::BUSINESS_STRATEGY_GOAL);
        $sections[] = new StudySection('Participant Instructions', $this->getParticipantInstructions(),
            ImageUrls::PEOPLE);
        //if(!$this->study && $this->correlationObject){  // What the hell is this for?
        if($c = $this->hasCorrelationCoefficient){
            $sections[] = new StudySection('Design', $this->getStudyDesign(),
                ImageUrls::DESIGN_TOOL_COLLECTION_COMPASS);
            $sections[] = new StudySection('Data Analysis', $this->setDataAnalysis(),
                ImageUrls::ANALYSIS);
            $sections[] = new StudySection('Statistical Significance', $this->getSignificanceExplanation(),
                ImageUrls::DESIGN_TOOL_COLLECTION_STATISTICS);
            $sections[] = new StudySection('Data Sources', $this->getDataSources(),
                ImageUrls::CONNECTOR_DEVICE);
            $sections[] = new StudySection('Limitations', $this->getStudyLimitations(),
                ImageUrls::ESSENTIAL_COLLECTION_WARNING);
            $sections[] = new StudySection('Plausibility', self::getPlausibilitySection($c),
                ImageUrls::SCIENCE_ATOM);
        }
        return $sections;
    }
    /**
     * @return string
     */
    public function getLogMetaDataString(): string{
        return $this->getHasCorrelationCoefficient()->getLogMetaDataString();
    }
    /**
     * @param array|null $meta
     * @return array
     */
    public function getLogMetaData(?array $meta = []): array{
        $meta = $this->getHasCorrelationCoefficient()->getLogMetaData($meta);
        return $meta;
    }
    /**
     * @return string
     */
    public function getPlainText(): string{
        $text = '';
        $sections = $this->getStudySectionsArray();
        foreach($sections as $section){
            $text .= "\n".$section->title."\n";
            $text .= "\n".$section->body."\n";
        }
        return $text;
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
        return $this->getHasCauseAndEffect()->getUrl();
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
     * @param HasCorrelationCoefficient $correlation
     * @return string
     * @noinspection PhpMissingParamTypeInspection
     */
    public static function getPlausibilitySection($correlation): string{
        $correlation->getNumberOfUpVotesButtonHtml();
        return '<p>A plausible bio-chemical mechanism between cause and effect is critical.  This is where human brains excel. '.
            'Based on our responses so far, '.
            $correlation->getNumberOfUpVotes().
            ' humans feel that there is a plausible mechanism '.
            'of action and '.
            $correlation->getNumberOfDownVotes().
            ' feel that any relationship observed between '.
            $correlation->getCauseAndEffectString().
            ' is coincidental.
            </p>';
    }
    public function getDurationOfAction(): int{
        return $this->getHasCorrelationCoefficient()->getDurationOfAction();
    }
    public function getOnsetDelay(): int{
        return $this->getHasCorrelationCoefficient()->getOnsetDelay();
    }
    /**
     * @param HasCorrelationCoefficient|QMVariableRelationship $c
     * @param string $title
     * @param bool $arrows
     * @return string
     */
    public static function formatTitle($c, string $title, bool $arrows): string {
        if($c && !$c->typeIsIndividual() && $title &&
            stripos($title, "for Population") === false){
            $title .= " for Population";
        }
        $title = BaseUserStudyTextProperty::humanizeStudyText($title);
        if(!$arrows){
            $title = str_replace(["&darr;", "&uarr;"], '', $title);
        }
        try {
            QMStr::assertStringDoesNotContain($title,
                [
                    //"(Systolic - Top Number)",
                    "Population for Population"
                ],
                __FUNCTION__);
        } catch (InvalidStringException $e) {
            le($e);
        }
        return $title;
    }
}
