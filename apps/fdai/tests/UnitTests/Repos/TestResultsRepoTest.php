<?php

namespace Tests\UnitTests\Repos;

use App\Logging\QMLog;
use App\Repos\QMAPIRepo;
use App\Repos\TestResultsRepo;
use App\Utils\Env;
use Tests\UnitTestCase;

/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Repos\TestResultsRepo
 */
class TestResultsRepoTest extends UnitTestCase
{
    public function testGithubComment(){
		try {
			$branch = QMAPIRepo::getBranchFromMemoryOrGit();
		} catch (\Throwable $e){
		   $this->fail("Could not get branch.  Here's the ENV: ".Env::printObfuscated());
		}
		$env = $_ENV ?? [];
	    $branchEnvs = [];
		foreach ($env as $key => $value) {
			if(str_contains($key, 'BRANCH')){
				$branchEnvs[$key] = $value;
			}
		}
		QMLog::info("Branch: $branch\n Branch Envs: " . QMLog::print_r($branchEnvs));
	    $arr = QMAPIRepo::githubComment(new \Exception('testGithubComment'));
		$keys = array_keys($arr);
		$this->assertArrayEquals(array (
			                         0 => 'url',
			                         1 => 'html_url',
			                         2 => 'id',
			                         3 => 'node_id',
			                         4 => 'user',
			                         5 => 'position',
			                         6 => 'line',
			                         7 => 'path',
			                         8 => 'commit_id',
			                         9 => 'created_at',
			                         10 => 'updated_at',
			                         11 => 'author_association',
			                         12 => 'body',
			                         13 => 'reactions',
		                         ), $keys);
    }
	public function testSetFinalStatus(){
		QMAPIRepo::setFinalStatus();
	}
}
