<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Files\TestArtifacts\TestQueryLogFile;
use App\Logging\QMClockwork;
use App\Providers\DBQueryLogServiceProvider;
use App\Utils\AppMode;
use Facade\IgnitionContracts\Solution;
use Tests\QMBaseTestCase;
class DebugQueriesSolution extends AbstractSolution implements Solution {
	public function getSolutionTitle(): string{
		return "Debug the Source of Queries";
	}
	public function getSolutionDescription(): string{
		return TestQueryLogFile::getQueryLogCliTable();
	}
	public function getDocumentationLinks(): array{
		$links = [
			'Break Point at Query Logger' => DBQueryLogServiceProvider::getUrl(),
		];
		if(AppMode::isUnitOrStagingUnitTest()){
			$links['Run ' . \App\Utils\AppMode::getCurrentTestName()] = \App\Utils\AppMode::getPHPStormUrlStatic();
		}
		$links['Clockwork'] = QMClockwork::getAppUrl();
		return $links;
	}
}
