<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Storage\DB\TestDB;
use Facade\IgnitionContracts\RunnableSolution;
use Tests\SlimTests\DBTest;
class UpdateTestDBSolution extends AbstractSolution implements RunnableSolution {
	public function getDocumentationLinks(): array{
		$links = ['Update Test DB' => DBTest::getLinkToUpdateTestDB()];
		return $links;
	}
	public function getSolutionActionDescription(): string{
		return "Update test fixtures and commit to new feature branch";
	}
	public function getRunButtonText(): string{
		return "Update Fixtures";
	}
	public function run(array $parameters = []){
		TestDB::updateAndCommitTestDB();
	}
	public function getRunParameters(): array{
		return [];
	}
	public function __toString(){
		return $this->getSolutionTitle()."\n".$this->getSolutionDescription()."\n".DBTest::getLinkToUpdateTestDB();
	}
	public function getSolutionTitle(): string{
		return "Update Test DB Fixtures";
	}
	public function getSolutionDescription(): string{
		return "IF YOU CREATED MIGRATIONS, YOU NEED TO UPDATE THE TEST DB by:
                1. running \Tests\SlimTests\DatabaseTest::testUpdateTestDB
                2. committing update sql files ";
	}
}
