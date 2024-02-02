<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A\Connectors;
use App\Models\Connection;
use Tests\SlimStagingTestCase;
class GitHubNoRepositoriesTest extends SlimStagingTestCase {
    public function testGitHubNoRepositories(): void{
        if($disabled = true){
            $this->skipTest("User token expired");
            return;
        }
		Connection::whereUserId(71800)->where(Connection::FIELD_CONNECTOR_ID, 4)->first()
			->import(__METHOD__);
		$this->checkTestDuration(12);
		$this->checkQueryCount(2);
	}
}
