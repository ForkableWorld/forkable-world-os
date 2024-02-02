<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace App\PhpUnitJobs\Import;
use App\DataSources\Connectors\GithubConnector;
use Tests\SlimStagingTestCase;
class GitHubJob extends SlimStagingTestCase
{
    public function testImportRepositories(): void{
		GithubConnector::generateClassesForRepositories(230);
		$this->checkTestDuration(10);
		$this->checkQueryCount(5);
	}
}
