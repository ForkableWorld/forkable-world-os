<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Solutions\ViewAnalyzableDataSolution;
use Facade\IgnitionContracts\Solution;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Variables\QMUserVariable;
class NotEnoughMeasurementsForCorrelationException extends NotEnoughDataException {
    const GET_HELP_IF_YOU_HAVE_ENOUGH_DATA = "Please create a ticket at http://help.quantimo.do if you think you should already have enough data.";
    const DATA_REQUIREMENT_FOR_CORRELATIONS_STRING = "I generally need about a month of varying data that overlaps with varying data for another variable in order to perform an analysis. \n";
    const DATA_REQUIREMENT_FOR_CORRELATIONS_HTML = "
        <h4>Data Requirements for Study</h4>
        <p>
            ".self::DATA_REQUIREMENT_FOR_CORRELATIONS_STRING."
        </p>
    ";
    public $causeUserVariable;
    public $effectUserVariable;
    /**
     * @var QMUserVariableRelationship|null
     */
    public $correlation;
    /**
     * @param string $problemDetails
     * @param QMUserVariableRelationship $c
     * @param QMUserVariable|null $cause
     * @param QMUserVariable|null $effect
     */
    public function __construct(string $problemDetails,
                                QMUserVariableRelationship $c,
                                QMUserVariable $cause = null,
                                QMUserVariable $effect = null){
        if(!$cause){$cause = $c->getOrSetCauseQMVariable();}
        if(!$effect){$effect = $c->getOrSetEffectQMVariable();}
        $this->causeUserVariable = $cause;
        $this->effectUserVariable = $effect;
        $this->analyzable = $this->correlation = $c;
	    $problemDetails .= "\n\n".$cause->getMeasurementQuantitySentence(). "\n"
	                       .$effect->getMeasurementQuantitySentence();
		$problemDetails .= "\n".self::DATA_REQUIREMENT_FOR_CORRELATIONS_STRING;
        parent::__construct($c, "Not Enough Overlapping Data", $problemDetails);
    }
    /**
     * @param QMUserVariable|null $cause
     * @param QMUserVariable|null $effect
     * @param string $message
     * @param QMUserVariableRelationship|null $c
     * @return string
     */
    public static function getNotEnoughDataAndTrackingInstructionsHtml(QMUserVariable $cause = null,
                                                                       QMUserVariable $effect = null,
                                                                       string $message = '',
                                                                       QMUserVariableRelationship $c = null): string{
        $message .= "\n".self::DATA_REQUIREMENT_FOR_CORRELATIONS_HTML."\n";
        if($cause){
            $message .= "\n".$cause->getDataQuantityHTML()."\n";
            if(!$cause->getNumberOfMeasurements()){$message .= "\n".$cause->getTrackingInstructionsHtml()."\n";}
        }
        if($effect){
            $message .= "\n".$effect->getDataQuantityHTML()."\n";
            if(!$effect->getNumberOfMeasurements()){$message .= "\n".$effect->getTrackingInstructionsHtml()."\n";}
        }
        if($c){$message .= "
    <p>
        ".$c->getStudyLinkHtml()."
    </p>
";}
        $message .= "
    <p>
        ".self::GET_HELP_IF_YOU_HAVE_ENOUGH_DATA."
    </p>";
        return $message;
    }
    public function getDocumentationLinks(): array{
        $arr = [];
        $cause = $this->getCauseUserVariable();
        $arr['View '.$cause->name] = $cause->getUrl();
        $effect = $this->getEffectUserVariable();
        $arr['View '.$effect->name] = $effect->getUrl();
        $correlation = $this->getQMUserVariableRelationship();
        if($correlation){
            $arr['View Study'] = $correlation->getUrl();
        }
        return $this->links = $arr;
    }
    /**
     * @return \App\Variables\QMCommonVariable|QMUserVariable|\App\Variables\QMVariable
     */
    public function getCauseUserVariable(){
        return $this->causeUserVariable;
    }
    /**
     * @return \App\Variables\QMCommonVariable|QMUserVariable|\App\Variables\QMVariable
     */
    public function getEffectUserVariable(){
        return $this->effectUserVariable;
    }
    /**
     * @return QMUserVariableRelationship|null
     */
    public function getQMUserVariableRelationship(): ?QMUserVariableRelationship{
        return $this->correlation;
    }
    public function getSolution(): Solution{
        $s = new ViewAnalyzableDataSolution($this->getQMUserVariableRelationship());
		$s->setDocumentationLinks($this->getDocumentationLinks());
        return $s;
    }
    public function getRelatedModels(): array{
        $this->addRelatedModel($this->correlation);
        $this->addRelatedModel($this->causeUserVariable);
        $this->addRelatedModel($this->effectUserVariable);
        return parent::getRelatedModels();
    }
    public function getSolutionDescription(): string{
        $desc = NotEnoughMeasurementsForCorrelationException::getNotEnoughDataAndTrackingInstructionsHtml(
            $this->causeUserVariable, $this->effectUserVariable, "", $this->correlation);
        return $this->solutionDescription = $desc;
    }
    public function getSolutionTitle(): string{
        return "Start Tracking";
    }
}
