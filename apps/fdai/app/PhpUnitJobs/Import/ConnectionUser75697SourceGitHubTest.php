<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace App\PhpUnitJobs\Import;
use Tests\SlimStagingTestCase;
use App\Models\Connection;
class ConnectionUser75697SourceGitHubTest extends SlimStagingTestCase {
	public function testConnectionUser75697SourceGitHub(): void{
		Connection::find(10353)->test();
		$this->checkTestDuration(10);
		$this->checkQueryCount(5);
	}
}
