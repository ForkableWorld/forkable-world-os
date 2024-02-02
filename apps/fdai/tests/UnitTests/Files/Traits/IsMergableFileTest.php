<?php
namespace Tests\UnitTests\Files\Traits;
use App\Files\Bash\BashLibScriptFile;
use App\Files\Traits\IsMergableFile;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Files\Traits\IsMergableFile;
 */
class IsMergableFileTest extends UnitTestCase
{
	/**
	 * @covers IsMergableFile::merge
	 * @noinspection PhpUnitMissingTargetForTestInspection (from a trait)
	 */
    public function testMerge(){
		$this->skipTest("I don't think we need to merge");
    	$paths = BashLibScriptFile::getFilePathsToMerge();
    	$this->validatePaths($paths);
	    $this->deleteFile(BashLibScriptFile::getMergedOutputFilePath());
	    $this->assertNotEmpty(BashLibScriptFile::merge());
	    $this->assertFileExists(abs_path(BashLibScriptFile::getMergedOutputFilePath()));
    }
}
