<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace App\PhpUnitJobs\Import;
use Tests\SlimStagingTestCase;
use App\Models\Connection;
class ConnectionUser91865SourceWithingsTest extends SlimStagingTestCase {
	public function testConnectionUser91865SourceWithings(): void{
		Connection::find(16138)->test();
		$this->checkTestDuration(10);
		$this->checkQueryCount(5);
	}
}
