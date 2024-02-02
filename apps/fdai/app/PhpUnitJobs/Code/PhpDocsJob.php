<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Code;
use App\Files\PHP\BaseModelFile;
use App\PhpUnitJobs\JobTestCase;
class PhpDocsJob extends JobTestCase {
	public function testUpdatePHPDocs(){
		BaseModelFile::updatePHPDocs();
	}
}
