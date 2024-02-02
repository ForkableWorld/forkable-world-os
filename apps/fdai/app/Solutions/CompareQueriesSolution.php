<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Exceptions\QMFileNotFoundException;
use App\Files\TestArtifacts\TestQueryLogFile;
use App\Logging\QMLog;
use Facade\IgnitionContracts\Solution;
class CompareQueriesSolution extends AbstractSolution implements Solution {
	/**
	 * CompareQueriesSolution constructor.
	 */
	public function __construct(){ }
	public function getSolutionTitle(): string{
		return "Compare the queries";
	}
	public function getSolutionDescription(): string{
		return TestQueryLogFile::getQueryLogMarkdown();
	}
	public function getDocumentationLinks(): array{
		try {
			return ['Query Log Diff' => TestQueryLogFile::getDiffUrl()];
		} catch (QMFileNotFoundException $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
			return [$e->getMessage() => $e->getMessage()];
		}
	}
}
