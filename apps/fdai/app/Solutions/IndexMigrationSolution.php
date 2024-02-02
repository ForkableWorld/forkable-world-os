<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Storage\DB\Migrations;
class IndexMigrationSolution extends SlowQuerySolution {
	public function getSolutionTitle(): string{
		return "Create an index";
	}
	public function getSolutionDescription(): string{
		return "Create an index";
	}
	public function getDocumentationLinks(): array{
		return ["Create Index" => Migrations::getUrlToCreateIndexMigration($this->sql)];
	}
}
