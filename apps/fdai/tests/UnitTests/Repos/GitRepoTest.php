<?php
namespace Tests\UnitTests\Repos;
use App\Computers\ThisComputer;
use App\Repos\QMAPIRepo;
use App\Utils\EnvOverride;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Repos\GitRepo;
 */
class GitRepoTest extends UnitTestCase {
	/**
	 * @covers GitRepo::getShortCommitSha
	 * @noinspection PhpUnitMissingTargetForTestInspection (from a trait)
	 */
	public function testGetShortCommitShaGitRepo(){
		$sha = QMAPIRepo::getShortCommitSha();
		$this->assertTrue(strlen($sha) === 7);
	}
	public function testCreateGithubStatus(){
		$this->skipTest('TODO: Fix this test');
		if(!EnvOverride::isLocal()){
			$this->skipTest("Not local");
		}
		$res = QMAPIRepo::setStatusPending(__FUNCTION__, "yada yada");
		$statues = QMAPIRepo::getStatuses();
		$this->assertEquals("pending", $statues[0]["state"]);
		$buildUrl = ThisComputer::getBuildUrl();
		$this->assertEquals($buildUrl, $statues[0]["target_url"]);
		$res = QMAPIRepo::setStatusSuccessful(__FUNCTION__, "yada yada");
		$this->assertEquals("success", $statues[0]["state"]);
		$this->assertEquals($buildUrl, $statues[0]["target_url"]);
//		$res = QMAPIRepo::createCheck(__FUNCTION__, QMAPIRepo::getCommitShaHash(), 
//		                       "https://local.quantimo.do", "completed");
//		$gotten = QMAPIRepo::showCheck($res['id']);
//		$this->assertEquals($res['id'], $gotten['id']);
	}
}
