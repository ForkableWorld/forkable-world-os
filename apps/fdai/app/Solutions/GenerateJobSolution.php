<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Types\QMStr;
use Facade\IgnitionContracts\RunnableSolution;
use Illuminate\Support\Facades\Artisan;
class GenerateJobSolution extends AbstractSolution implements RunnableSolution {
	/**
	 * @var string
	 */
	public $functionName;
	public $links;
	public function __construct(string $functionName, array $links){
		$this->functionName = $functionName;
		$this->links = $links;
	}
	public function getSolutionTitle(): string{
		return 'Your app key is missing';
	}
	public function getDocumentationLinks(): array{
		return $this->links;
	}
	public function getSolutionActionDescription(): string{
		return 'Generate a job to complete the task offline.';
	}
	public function getRunButtonText(): string{
		return 'Generate job';
	}
	public function getSolutionDescription(): string{
		return '';
	}
	public function getRunParameters(): array{
		return ["job_name" => QMStr::toShortClassName($this->functionName)];
	}
	public function run(array $parameters = []){
		Artisan::call('make:job '.$parameters["job_name"]);
	}
}
