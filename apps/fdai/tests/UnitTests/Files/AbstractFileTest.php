<?php
namespace Tests\UnitTests\Files;
abstract class AbstractFileTest extends \Tests\UnitTestCase {
	/**
	 * @param              $files
	 * @param string       $ext
	 */
	public function assertFilesHaveDefaultExtension($files, string $ext): void{
		$this->assertGreaterThan(1, $files->count());
		foreach($files as $file){
			$this->assertStringEndsWith("." . $ext, $file->getPath());
		}
	}
}
