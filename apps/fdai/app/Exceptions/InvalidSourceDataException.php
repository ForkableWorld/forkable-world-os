<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Traits\QMAnalyzableTrait;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\Solution;
class InvalidSourceDataException extends AnalysisException
{
    protected $analyzable;
    /**
     * InvalidSourceDataException constructor.
     * @param string $problemTitle
     * @param string $problemDescription
     * @param QMAnalyzableTrait $analyzable
     */
    public function __construct(string $problemTitle, string $problemDescription, $analyzable){
        $this->analyzable = $analyzable;
        parent::__construct($problemTitle, $problemDescription,
            $problemDescription, $code = 500);
    }
    public function getSolutionTitle(): string{
        return $this->solutionTitle = "Review Source Data";
    }
    public function getSolutionDescription(): string{
        return $this->solutionDescription = "Review the source data and delete if necessary. ";
    }
    public function getSolution(): Solution{
        $a = $this->getAnalyzable();
        return BaseSolution::create($this->getSolutionTitle())
            ->setSolutionDescription($this->getSolutionDescription())
            ->setDocumentationLinks([
                "Source Data Index" => $a->getSourceDataUrl(),
                "View ".$a->getShortClassName() => $a->getSourceDataUrl(),
            ]);
    }
}
