<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Types\QMStr;
use Throwable;
class CreateSolution extends CreateClass {
	public $exceptionWithoutSolution;
	public $exceptionClass;
	/**
	 * CreateSolutionSolution constructor.
	 * @param Throwable|null $exceptionWithoutSolution
	 */
	public function __construct(Throwable $exceptionWithoutSolution = null){
		if($exceptionWithoutSolution){
			$this->exceptionWithoutSolution = $exceptionWithoutSolution;
			$this->exceptionClass = get_class($exceptionWithoutSolution);
		}
		parent::__construct();
	}
	public function getBaseClassName(): string{ return BaseRunnableSolution::class; }
	public function getStubClassName(): string{ return RunnableSolutionStub::class; }
	public function getSolutionActionDescription(): string{
		return "Generate a new solution class for you to implement the fix for ".
		       $this->getPreviousExceptionShortClassName();
	}
	public function getNewShortClassName(): string{
		if($this->newClass){
			return parent::getNewShortClassName();
		}
		$this->newClass = str_replace('Exception', 'Solution', $this->exceptionClass);
		return QMStr::toShortClassName($this->newClass);
	}
	public function getSolutionDescription(): string{
		return "Please implement a Solution for this ".$this->getPreviousExceptionShortClassName();
	}
}
