<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Analytics;
use App\Exceptions\NoEmailAddressException;
use App\Mail\PatientDescriptiveOverviewEmail;
use App\Slim\Model\User\PhysicianUser;
use App\PhpUnitJobs\JobTestCase;
class PhysicianReportJobTest extends JobTestCase {
    public function testPatientDescriptiveOverviewEmailJob(){
        $physicians = PhysicianUser::getAll();
        foreach($physicians as $physician){
            try {
                $alreadySent = $physician->emailedInLast(PatientDescriptiveOverviewEmail::getType(), 7 * 24);
                if($alreadySent){continue;}
            } catch (NoEmailAddressException $e) {
                $physician->logError(__METHOD__.": ".$e->getMessage());
                continue;
            }
            $physician->sendDescriptiveOverviewForAllPatientsEmail();
        }
    }
}
