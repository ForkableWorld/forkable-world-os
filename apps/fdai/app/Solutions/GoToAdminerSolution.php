<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Logging\SolutionButton;
use App\Storage\DB\Adminer;
use App\Storage\DB\QMDB;
use App\Storage\DB\Writable;
use Facade\IgnitionContracts\Solution;
class GoToAdminerSolution extends AbstractSolution implements Solution {
	public function getSolutionTitle(): string{
		return "Inspect Database";
	}
	public function getSolutionDescription(): string{
		return "View the database ".QMDB::getDBName()." as ".Writable::getHost();
	}
	public function getDocumentationLinks(): array{
		return SolutionButton::addUrlNameArrays(['Go to Adminer' => Adminer::getAdminerUrl()]);
	}
}
