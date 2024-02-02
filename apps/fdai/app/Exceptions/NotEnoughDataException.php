<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Buttons\States\OnboardingStateButton;
use App\Logging\QMLog;
use App\Solutions\StartTrackingSolution;
use App\Traits\HasExceptions;
use App\Traits\QMAnalyzableTrait;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
class NotEnoughDataException extends AnalysisException implements ProvidesSolution {
    /**
     * @param QMAnalyzableTrait|HasExceptions|\App\Models\UserVariable $analyzable
     * @param string $problemTitle
     * @param string $problemDetailsSentence
     * @param string|null $internalErrorMessage
     */
    public function __construct($analyzable,
                                string $problemTitle,
                                string $problemDetailsSentence = "",
                                string $internalErrorMessage = null){
        /** @var QMAnalyzableTrait $analyzable */
        $this->analyzable = $analyzable;
        $analyzable->addException($this);
        parent::__construct($problemTitle, $problemDetailsSentence,
            $internalErrorMessage, 404);
    }
    public function getSolutionTitle(): string{
        if(!$this->solutionTitle){
            $this->solutionTitle = "Collect More Data";
        }
        return $this->solutionTitle;
    }
    public function getSolutionDescription(): string{
        if(!$this->solutionDescription){
            $a = $this->getAnalyzable();
            try {
                $t = $a->getTitleAttribute();
            } catch (\Throwable $e){
                QMLog::info(__METHOD__.": ".$e->getMessage());
                $t = get_class($a);
            }
            $this->solutionDescription = "Please add reminders or import more varying data for ".$t.". ";
        }
        return $this->solutionDescription;
    }
    public function getDocumentationLinks(): array{
        $links = parent::getDocumentationLinks();
        $links["Start Tracking"] = OnboardingStateButton::url();
        return $this->links = $links;
    }
    public function getUserSolution(): Solution{
        return new StartTrackingSolution($this->getSolutionDescription());
    }
    public function getSolution(): Solution{
        return $this->getUserSolution();
    }
}
