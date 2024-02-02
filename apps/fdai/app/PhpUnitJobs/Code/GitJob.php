<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Code;
use App\Repos\GitRepo;
use App\DevOps\Jenkins\JenkinsQueue;
use App\DevOps\Jenkins\JenkinsView;
use App\DevOps\Jenkins\JenkinsAPI;
use App\Repos\QMAPIRepo;
use App\PhpUnitJobs\JobTestCase;
class GitJob  extends JobTestCase {
    public function testUpdateFeatureBranches(){
        QMAPIRepo::updateFeatureBranches();
    }
    public function testCancelJenkinsBuildsForCommit(){
        $jenkins = new JenkinsAPI();
        $view = JenkinsView::getView('PHPUnit');
        $queue = JenkinsQueue::getQueue();
        $branch = 'feature/calculateCorrelations';
        $builds = $view->getBuildsForBranch($branch);
        $builds = $view->cancelBuildsForBranch($branch);
    }
    public function testDeleteFeatureBranches(){
        QMAPIRepo::deleteLocalFeatureBranches();
    }
    public function testCommitEachChangedFileToABranch(){
        QMAPIRepo::configureNameEmailToken();
        QMAPIRepo::createFeatureBranchForEachModifiedFile();
    }
    public function testCloneSubModules(){
        GitRepo::cloneSubModules();
    }
    public function testCheckGithubRateLimits(){
        QMAPIRepo::checkRateLimits();
    }
}
