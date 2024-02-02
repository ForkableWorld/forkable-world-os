<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use Facade\IgnitionContracts\ProvidesSolution;
use App\Buttons\Analyzable\CreateStudyButton;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\Utils\IonicHelper;
use App\Logging\QMLog;
class NoUserVariableRelationshipsToAggregateException extends NotEnoughDataException implements ProvidesSolution {
    /**
     * @var QMGlobalVariableRelationship
     */
    public $aggregateCorrelation;
    /**
     * NoUserVariableRelationshipsException constructor.
     * @param QMGlobalVariableRelationship|GlobalVariableRelationship $correlation
     */
    public function __construct($correlation){
        $this->analyzable = $c = $this->aggregateCorrelation = $correlation;
	    $internalErrorMessage = "No user variable relationships for cause ".
		    $correlation->getCauseVariableName()." and effect ".$correlation->getEffectVariableName();
		if($correlation->hasId()){
			$show = $correlation->getDataLabShowUrl();
			$internalErrorMessage .= ".\n\tDelete at $show. \n";
		}
        parent::__construct($correlation,
            "Not Enough Shared Data",
            "There are not enough users who have collected and anonymously shared their data to ".
            "create a population study on the relationship between $c->causeVariableName and $c->effectVariableName. ".
            "See available user_variable_relationships at ".$correlation->getAstralIndexUrl(),
            $internalErrorMessage);
        $this->userErrorMessageBodyHtml = $this->getSolutionDescription().
            "\n".(new CreateStudyButton())->getLink();
    }
    public function getDocumentationLinks(): array{
        $links["Create a Study"] = IonicHelper::getStudyCreationUrl();
        return $this->links = array_merge($links, parent::getDocumentationLinks());
    }
    public function getSolutionTitle(): string{
        return $this->solutionTitle = "Create a Study";
    }
    public function getSolutionDescription(): string{
        $c = $this->getQMGlobalVariableRelationship();
        return $this->solutionDescription = "Please create a study and share it with your friends so we can collect enough data to ".
            "determine the effect of $c->causeVariableName on $c->effectVariableName.  ";
    }
    /**
     * @return GlobalVariableRelationship
     */
    public function getGlobalVariableRelationship(): GlobalVariableRelationship{
        return $this->aggregateCorrelation->l();
    }
    /**
     * @return QMGlobalVariableRelationship
     */
    public function getQMGlobalVariableRelationship(): QMGlobalVariableRelationship{
        return $this->aggregateCorrelation->getDBModel();
    }
}
