<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Variables\QMVariable;
use Facade\IgnitionContracts\Solution;
class DeleteLargeMeasurementsSolution extends AbstractSolution implements Solution {
	protected $variable;
	public function __construct($variable){
		$this->variable = $variable;
	}
	public function getSolutionTitle(): string{
		return "Delete Large Measurements";
	}
	public function getSolutionDescription(): string{
		$v = $this->getVariable();
		return "Delete Measurements Greater Than ".$v->getMaximumAllowedValueAttribute().$v->getCommonUnit()->name;
	}
	/**
	 * @return QMVariable
	 */
	public function getVariable(): QMVariable{
		return $this->variable;
	}
	public function getDocumentationLinks(): array{
		$v = $this->getVariable();
		$url = $v->getDeleteLargeMeasurementsUrl();
		return ["Delete" => $url];
	}
}
