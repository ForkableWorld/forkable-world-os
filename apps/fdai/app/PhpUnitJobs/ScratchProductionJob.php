<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs;
use App\Reports\ResumeReport;
class ScratchProductionJob extends JobTestCase
{
    /**
     * Edit this to run ad-hoc functions using the .env in the repo root
     * It's ignored to avoid polluting git
     */
    public function testScratchProduction(){
        $r = new ResumeReport();
        $r->getBody();
    }
}
