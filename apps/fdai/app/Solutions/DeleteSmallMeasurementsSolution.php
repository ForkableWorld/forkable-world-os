<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Variables\QMVariable;
use Facade\IgnitionContracts\Solution;
class DeleteSmallMeasurementsSolution extends AbstractSolution implements Solution {
	protected $variable;
	public function __construct($variable){
		$this->variable = $variable;
	}
	public function getSolutionTitle(): string{
		return "Delete Small Measurements";
	}
	public function getSolutionDescription(): string{
		$v = $this->getVariable();
		return "Delete Measurements Less Than ".$v->getMinimumAllowedValueAttribute().$v->getCommonUnit()->name;
	}
	/**
	 * @return QMVariable
	 */
	public function getVariable(): QMVariable{
		return $this->variable;
	}
	public function getDocumentationLinks(): array{
		$v = $this->getVariable();
		$url = $v->getViewSmallMeasurementsUrl();
		return ["View and Delete" => $url];
	}
}
