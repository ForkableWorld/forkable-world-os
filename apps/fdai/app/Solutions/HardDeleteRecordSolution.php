<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
class HardDeleteRecordSolution extends ModelSolution {
	public function getSolutionTitle(): string{
		return "Hard Delete ".$this->getShortClassName()." ".$this->getBaseModel()->getTitleAttribute();
	}
	public function getShortClassName(): string{
		return $this->getBaseModel()->getClassNameTitle();
	}
	public function getSolutionDescription(): string{
		return "Delete the record to fix the problem";
	}
	public function getDocumentationLinks(): array{
		$m = $this->getBaseModel();
		return $m->getDataLabUrls();
	}
	public function getSolutionActionDescription(): string{
		$class = $this->getBaseModel()->getClassNameTitle();
		return "Going to delete the $class from the database forever";
	}
	public function getRunButtonText(): string{
		$class = $this->getBaseModel()->getClassNameTitle();
		return "Hard Delete $class";
	}
	public function run(array $parameters = []){
		return $this->getBaseModel()->forceDelete();
	}
}
