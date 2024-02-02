<?php
namespace App\Folders;
namespace Tests\UnitTests\Folders;
use App\Folders\ClockworkFolder;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Folders\ClockworkFolder;
 */
class ClockworkFolderTest extends UnitTestCase {
	/**
	 * @covers       ClockworkFolder::getPath
	 * @noinspection PhpUnitMissingTargetForTestInspection (from a trait)
	 */
	public function testGetPathClockworkFolder(){
		$clockworkFolder = new ClockworkFolder();
		$this->assertEquals("storage/clockwork", $clockworkFolder->getRelativePath());
	}
}
