<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Logging\QMClockwork;
use App\Solutions\GenerateJobSolution;
use App\Solutions\GetHelpSolution;
use App\Traits\QMAnalyzableTrait;
use Facade\IgnitionContracts\Solution;
class TooSlowToAnalyzeException extends BaseException {
    /**
     * @var string
     */
    public $functionName;
    protected $analyzable;
    /**
     * TooSlowException constructor.
     * @param string $functionName
     * @param QMAnalyzableTrait $analyzable
     * @param string $message
     */
    public function __construct(string $functionName, $analyzable, $message = "") {
        $this->functionName = $functionName;
        $this->analyzable = $analyzable;
	    QMClockwork::logSlowOperation($message);
        parent::__construct($message);
    }
    public function getSolution(): Solution{
        return $this->solution = new GenerateJobSolution($this->functionName, $this->getDocumentationLinks());
    }
    public function getSolutionTitle(): string{
        return "Create a Job";
    }
    public function getSolutionDescription(): string{
        return "Create a Queuable Job and Notification";
    }
    public function getDocumentationLinks(): array{
        return $this->links = [
            'Laravel Jobs' => 'https://laravel.com/docs/5.8/queues#generating-job-classes',
            'Profile' => $this->getAnalyzable()->getProfileUrl(),
            'PHPUnit Test' => $this->getAnalyzable()->getPHPUnitTestUrl(),
            'PHPStorm' => $this->getAnalyzable()->getPHPStormUrl(),
        ];
    }
    /**
     * @return QMAnalyzableTrait
     */
    public function getAnalyzable(){
        return $this->analyzable;
    }
	public function getUserSolution(): GetHelpSolution{
		$s = new GetHelpSolution();
		$s->title = "Too Slow to Analyze";
		$s->description = "This will be done offline as it takes a while. We'll send you an email when it's done.";
		return $s;
	}
}
