<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Code;
use App\PhpUnitJobs\JobTestCase;
use App\Policies\BasePolicy;
class PoliciesJob extends JobTestCase {
	public function testGeneratePolicies(){
		BasePolicy::generatePolicies();
	}
}
