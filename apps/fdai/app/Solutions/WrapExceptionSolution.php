<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Buttons\Admin\PHPStormExceptionButton;
use Facade\IgnitionContracts\Solution;
class WrapExceptionSolution extends AbstractSolution implements Solution {
	private $actual;
	private $wrapper;
	public function __construct($actualException, $wrapperException){
		$this->actual = $actualException;
		$this->wrapper = $wrapperException;
	}
	public function getSolutionTitle(): string{
		return "Wrap with Solvable";
	}
	public function getSolutionDescription(): string{
		return "Catch and Wrap with solvable exception: ".get_class($this->wrapper);
	}
	public function getDocumentationLinks(): array{
		return [
			"Catch and Wrap with ".
			get_class($this->wrapper) => PHPStormExceptionButton::urlForException($this->actual),
		];
	}
}
