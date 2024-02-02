<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Buttons\Admin\PHPStormButton;
use Facade\IgnitionContracts\Solution;
class EditFileSolution extends AbstractSolution implements Solution {
	public $filePath;
	public function getSolutionTitle(): string{
		return "Edit File";
	}
	public function getSolutionDescription(): string{
		return "Edit the file to fix the problem";
	}
	public function getDocumentationLinks(): array{
		return ["Open File" => PHPStormButton::redirectUrl()];
	}
}
