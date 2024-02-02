<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
class SoftDeleteRecordSolution extends HardDeleteRecordSolution {
	public function getSolutionTitle(): string{
		return "Soft Delete ".$this->getShortClassName()." ".$this->getBaseModel()->getTitleAttribute();
	}
	public function getSolutionDescription(): string{
		return "Soft delete the record to fix the problem";
	}
	public function getSolutionActionDescription(): string{
		$class = $this->getBaseModel()->getClassNameTitle();
		return "Going to soft delete the $class from the database forever";
	}
	public function getRunButtonText(): string{
		$class = $this->getBaseModel()->getClassNameTitle();
		return "Soft Delete $class";
	}
	/**
	 * @param array $parameters
	 * @return bool|null
	 * @throws \Exception
	 */
	public function run(array $parameters = []){
		return $this->getBaseModel()->delete();
	}
}
