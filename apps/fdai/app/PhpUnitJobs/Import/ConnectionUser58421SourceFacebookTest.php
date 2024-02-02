<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace App\PhpUnitJobs\Import;
use Tests\SlimStagingTestCase;
use App\Models\Connection;
class ConnectionUser58421SourceFacebookTest extends SlimStagingTestCase {
	public function testConnectionUser58421SourceFacebook(): void{
		Connection::find(15546)->test();
		$this->checkTestDuration(10);
		$this->checkQueryCount(5);
	}
}
