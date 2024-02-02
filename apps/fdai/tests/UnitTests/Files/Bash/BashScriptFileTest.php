<?php
namespace Tests\UnitTests\Files\Bash;
use App\Files\Bash\BashLibScriptFile;
use App\Files\Bash\BashScriptFile;
use Tests\UnitTests\Files\AbstractFileTest;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Files\Bash\BashScriptFile
 */
class BashScriptFileTest extends AbstractFileTest {
	/**
	 * @covers \App\Files\Bash\BashScriptFile::get
	 */
	public function testGetDefaultExtension(){
		$this->skipTest("Don't need this anymore");
		$this->assertFilesHaveDefaultExtension(BashScriptFile::get(), BashScriptFile::getDefaultExtension());
	}
	/**
	 * @covers BashLibScriptFile::get
	 * @noinspection PhpUnitMissingTargetForTestInspection (from a trait)
	 */
	public function testGetBashScripts(){
		$this->skipTest("Don't need this anymore");
		$files = BashScriptFile::get();
		$this->assertGreaterThan(130, $files->count());
		$this->assertLessThan(250, $files->count());
		foreach($files as $file){
			$this->assertInstanceOf(BashScriptFile::class, $file);
		}
	}
	public function tesRelativePathGenerator(){
		$f = new BashLibScriptFile("scripts/bsfl/lib/bsfl.sh");
		$relative = $f->getRelativePath();
		$this->assertEquals("scripts/bsfl/lib/bsfl.sh", $relative);
		$this->assertEquals("bsfl.sh", $f->getFileName());
		$this->assertEquals("./../../", $f->getDotsPathToRoot());
	}
}
