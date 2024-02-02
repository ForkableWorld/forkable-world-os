<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Exceptions\ExceptionHandler;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\Solution;
trait HasBaseSolution {// Make sure to add "implements \Facade\IgnitionContracts\ProvidesSolution" {
	abstract public function getDocumentationLinks(): array;
	abstract public function getSolutionDescription(): string;
	abstract public function getSolutionTitle(): string;
	public function getProblemAndSolutionString(): string{
		return ExceptionHandler::getProblemSolutionString($this);
	}
	public function getSolution(): Solution{
		return BaseSolution::create($this->getSolutionTitle())->setSolutionDescription($this->getSolutionDescription())
			->setDocumentationLinks($this->getDocumentationLinks());
	}
}
