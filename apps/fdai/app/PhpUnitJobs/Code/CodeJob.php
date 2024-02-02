<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Code;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\BaseProperty;
use App\Properties\IsCalculated;
class CodeJob extends JobTestCase {

	public function testAddInterface(){
		BaseProperty::addImplementsWhereContains(IsCalculated::class, "IsCalculated");
	}
}
