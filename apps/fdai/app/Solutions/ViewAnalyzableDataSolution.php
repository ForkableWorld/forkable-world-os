<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
class ViewAnalyzableDataSolution extends AnalyzableSolution {
	public function getSolutionTitle(): string{
		return "Debug Analysis of ".$this->getAnalyzable()->getTitleAttribute()." ".$this->getShortClassName();
	}
	public function getSolutionDescription(): string{
		return "Review the data for the source data or generate a PHPUnit test to identify the issue.  ";
	}
	public function getSolutionActionDescription(): string{
		return "Going to generate a PHPUnit test where you can debug";
	}
	public function getRunButtonText(): string{
		return "Generate PHPUnit Test";
	}
	/**
	 * @param array $parameters
	 * @return string
	 * @throws \Exception
	 */
	public function run(array $parameters = []){
		return $this->getAnalyzable()->getPHPUnitTestUrl();
	}
}
