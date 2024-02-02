<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Debug;
use App\Models\User;
use App\Reports\DescriptiveOverviewReportForPhysician;
use App\Slim\Model\User\PhysicianUser;
use App\PhpUnitJobs\JobTestCase;
class PatientReportDebugTest extends JobTestCase {
    public function testDescriptiveOverviewReport(){
        $analysis = new DescriptiveOverviewReportForPhysician(User::mike(), User::mike()->getPhysicianUser());
        $analysis->generatePDF();
    }
    public function testDescriptiveOverviewForAllPatientsEmail(){
        $physician = PhysicianUser::find(230);
        $physician->sendDescriptiveOverviewForAllPatientsEmail();
    }
}
