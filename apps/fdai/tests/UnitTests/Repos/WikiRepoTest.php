<?php
namespace Tests\UnitTests\Repos;
use App\Repos\WikiRepo;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Repos
 * @coversDefaultClass \App\Repos\WikiRepo
 */
class WikiRepoTest extends UnitTestCase {
	protected function setUp(): void{
		$this->skipTest("TODO");
	}
	public function testExportMarkdown(){
		WikiRepo::cloneOrPullIfNecessary();
		$r = new WikiRepo();
		$r->deleteOutputPath();
		$this->assertFileDoesNotExist($r->getOutputPath());
		$r->exportToMarkdown();
		$this->assertFileExists($r->getOutputPath());
	}
}
