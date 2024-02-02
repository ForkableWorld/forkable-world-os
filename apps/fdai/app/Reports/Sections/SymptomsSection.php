<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Reports\Sections;
use App\Reports\RootCauseAnalysisSection;
use App\VariableCategories\SymptomsVariableCategory;

class SymptomsSection extends RootCauseAnalysisSection {
    public $title = "Correlated Symptoms";
    protected $factor = false;
    public $predictorVariableCategoryName = SymptomsVariableCategory::NAME;
    protected static $unnecessaryTableColumns = [
        'Outcome',
        'Association',
        'Change',
        'Predictive Coefficient'
    ];
    public $introductorySentence = "Very often, different symptoms are assumed to be different conditions and are treated in isolation.
            When the severity of multiple symptoms correlate with each over time,
            this suggests that they may share the same underlying root cause.
            One example is the case in which depression severity is correlated with digestive issues, acne, psoriasis, joint pain, or other
            inflammatory disorders known to originate from elevated cytokine levels produced by an overactive immune system.
            When these symptoms co-occur, this suggests that the depression may be a result of
            cytokine interference in the production of intracranial serotonin as opposed to psychological factors,
            life circumstances, a methylfolate deficiency or other potential causes.
            ";
//For instance, Mike had depression and anxiety so he was referred to a psychologist.
//He saw the psychologist weekly for 10 years.  The cost per visit was $100.
//That's $5,200 per year and $52,000 over 10 years.
    /**
     * @return string
     */
    public function getTablesSections(): string {
        $html =  $this->getIntroductorySentenceHTML();
        $valenceExplanation = "When your ".$this->getOutcomeVariableName().
            " is worse, the symptoms in the table below are generally more severe.";
        if ($this->getOutcomeVariable()->valenceIsPositive()){
            $html .= $this->negativeUpVotedTableSection("Symptoms Associated with Lower " .
                $this->getOutcomeVariableName(), $valenceExplanation);
        } else if ($this->getOutcomeVariable()->valenceIsNegative()){
            $html .= $this->positiveUpVotedTableSection("Symptoms Associated with Higher " .
                $this->getOutcomeVariableName(), $valenceExplanation);
        } else {
            $html .= $this->negativeUpVotedTableSection("Symptoms Associated with Lower " .
                $this->getOutcomeVariableName());
            $html .= $this->positiveUpVotedTableSection("Symptoms Associated with Higher " .
                $this->getOutcomeVariableName());
            $html .= $this->uncorrelatedTableSection("Symptoms Not Associated with ".$this->getOutcomeVariableName());
        }
        //$html .= $this->flaggedSection();
        return $html;
    }
}
