<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Exceptions\BaseException;
use App\Exceptions\ExceptionStub;
use App\Exceptions\UnspecifiedSolutionException;
use App\Types\QMStr;
class CreateException extends CreateClass {
	/**
	 * @var string
	 */
	public $urlToInstantiation;
	public $exceptionClassWithoutGetSolutionMethod;
	/**
	 * CreateException constructor.
	 * @param UnspecifiedSolutionException|\Throwable $exceptionWithoutSolution
	 */
	public function __construct($exceptionWithoutSolution = null){
		if($exceptionWithoutSolution){
			if(!$exceptionWithoutSolution instanceof UnspecifiedSolutionException){
				$exceptionWithoutSolution = new UnspecifiedSolutionException($exceptionWithoutSolution);
			}
			$this->exceptionClassWithoutGetSolutionMethod = $exceptionWithoutSolution->exceptionClassWithoutSolution;
			$this->urlToInstantiation = $exceptionWithoutSolution->getUrlToInstantiation();
		}
		parent::__construct();
	}
	public function getSolutionActionDescription(): string{
		return "Generate a new exception class for you to rename and add a solution. Then catch and replace the original exception.";
	}
	public function getNewShortClassName(): string{
		if($this->newClass){
			return parent::getNewShortClassName();
		}
		return "QM".$this->exceptionClassWithoutGetSolutionMethod;
	}
	public function getSolutionDescription(): string{
		$exceptionClass = $this->getNewFullClassName();
		return "Please implement a handler and wrap $this->exceptionClassWithoutGetSolutionMethod in a new ".
		       $exceptionClass." with a defined solution";
	}
	public function getPreviousExceptionShortClassName(): string{
		return QMStr::toShortClassName($this->exceptionClassWithoutGetSolutionMethod);
	}
	public function getBaseClassName(): string{ return BaseException::class; }
	public function getStubClassName(): string{ return ExceptionStub::class; }
	public function run(array $parameters = []): string{
		parent::run($parameters);
		return $this->createSolution();
	}
	/**
	 * @return string
	 */
	protected function createSolution(): string{
		$exceptionClass = $this->getNewFullClassName();
		$s = CreateSolution::generate(str_replace("Exception", "Solution", $exceptionClass));
		return $s;
	}
}
