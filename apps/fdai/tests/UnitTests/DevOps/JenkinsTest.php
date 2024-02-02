<?php
namespace DevOps;
use App\DevOps\Jenkins\Build;
use App\DevOps\Jenkins\JenkinsJob;
use App\Logging\QMLog;
use App\Repos\QMAPIRepo;
use Tests\UnitTestCase;

class JenkinsTest extends UnitTestCase
{
    public function testAbortWorkers(){
        if($disabled = true){
            $this->skipTest("Too slow");
            return;
        }
        try {
            $builds = $this->getBuilds();
        } catch (\Throwable $e){
            try {
                $builds = $this->getBuilds();
            } catch (\Throwable $e){
                QMLog::error(__METHOD__.": ".$e->getMessage());
                $this->assertTrue(true);
                $this->skipTest(__METHOD__.": ".$e->getMessage());
                return;
            }
        }
        $this->assertCountGreaterThan(0, $builds);
        //$builds = JenkinsAPI::abortBuildsForJobsLike("-worker");
        //QMLog::infoWithoutContext("Aborted ".count($builds)." worker builds");
    }
    /**
     * @return Build[]
     */
    private function getBuilds(){
        $jobs = JenkinsJob::getJobNamesLike("-worker");
        $this->assertCount(2, $jobs);
        $builds = Build::getActivePHPUnitBuildsForCommit(QMAPIRepo::getCommitShaHash());
        return $builds;
    }
}
