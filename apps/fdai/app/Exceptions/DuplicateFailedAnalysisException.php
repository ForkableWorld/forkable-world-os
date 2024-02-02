<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Logging\SolutionButton;
use App\Traits\QMAnalyzableTrait;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Throwable;
class DuplicateFailedAnalysisException extends BaseException implements ProvidesSolution
{
    public $analyzable;
    public $analysisException;
    /**
     * DuplicateFailedAnalysisException constructor.
     * @param QMAnalyzableTrait $analyzable
     * @param AnalysisException|Throwable $analysisException
     */
    public function __construct($analyzable, Throwable $analysisException){
        $this->analysisException = $analysisException;
        parent::__construct();
        $this->analyzable = $analyzable;
        $this->solutionTitle = "Prevent The Job From Trying To Analyze This";
        $this->userErrorMessageBodyString = "We tried to analyze $analyzable and it failed again just like ".
            "before and got the same exception ".$this->getAnalysisException()->getMessage()." again.";
        $this->userErrorMessageTitle = "We tried to analyze $analyzable and it failed again just like before";
    }
    public function getSolution(): Solution{
        return BaseSolution::create("Fix Failure or Prevent from Being Analyzed Again")
            ->setSolutionDescription("Fix Failure or Prevent from Being Analyzed Again")
            ->setDocumentationLinks($this->getDocumentationLinks());
    }
    /**
     * @return QMAnalyzableTrait
     */
    public function getAnalyzable(){
        return $this->analyzable;
    }
    /**
     * @return array
     */
    public function getDocumentationLinks(): array{
	    return SolutionButton::addUrlNameArrays($this->getAnalyzable()->getUrls());
    }
    /**
     * @return BaseException
     */
    public function getAnalysisException(){
        return $this->analysisException;
    }
}
