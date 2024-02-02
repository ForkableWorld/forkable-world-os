<?php
namespace Tests\UnitTests\Files;
use App\Files\Env\EnvFile;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Files\UntypedFile;
 */
class UntypedFileTest extends UnitTestCase {
	/**
	 * @covers UntypedFile::getRelativeFilePath
	 * @noinspection PhpUnitMissingTargetForTestInspection (from a trait)
	 */
	public function testGetRelativeFilePathUntypedFile(){
        $this->skipTest('TODO');
		$envs = EnvFile::all();
		foreach($envs as $env){
			$prefix = $env->getPathPrefix();
			$this->assertEquals(abs_path()."/", $prefix);
			$path = $env->getRelativePath();
            if(stripos($path, 'scripts/ci/')) {
                $this->assertStringStartsWith("scripts/ci/", $path);
            } else {
                $this->assertStringStartsWith("configs/", $path);
            }

		}
	}
}
