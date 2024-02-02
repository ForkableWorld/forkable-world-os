<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Logging\QMClockwork;
use App\Logging\SolutionButton;
use Facade\IgnitionContracts\Solution;
use Tests\TestGenerators\ApiTestFile;
class GenerateStagingUnitTestSolution extends AbstractSolution implements Solution {
	private static array $links = [];
	public function getSolutionTitle(): string{
		return 'Generate staging unit test';
	}
	public function getDocumentationLinks(): array{
		if(static::$links){return static::$links;}
		$links = [];
		try {
			$links["Generate ".ApiTestFile::generateNamePrefix() . " PHPUnit Test"] = ApiTestFile::generateAndGetUrl();
		} catch (\Throwable $e) {
			$links["Could not generate API test because".$e->getMessage()] = $e->getMessage();
		}
		$links['Clockwork'] = QMClockwork::getAppUrl();
		return static::$links = SolutionButton::addUrlNameArrays($links);
	}
	public function getSolutionDescription(): string{
		return 'Generate a test containing all the current request parameters.';
	}
}
