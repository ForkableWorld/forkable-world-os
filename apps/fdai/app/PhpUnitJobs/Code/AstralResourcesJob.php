<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Code;
use App\Models\BaseModel;
use App\PhpUnitJobs\JobTestCase;
class AstralResourcesJob extends JobTestCase {
	public function testGenerateAstralResources(){
		//MeasurementImport::generateResourceClass();
		BaseModel::generateAllAstralResources();
	}
}
