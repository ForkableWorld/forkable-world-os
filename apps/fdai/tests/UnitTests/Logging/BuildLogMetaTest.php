<?php
namespace Tests\UnitTests\Logging;
use App\Logging\BuildLogMeta;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Logging\BuildLogMeta;
 */
class BuildLogMetaTest extends UnitTestCase {
	public function testBuildLogMeta(){
		$arr = BuildLogMeta::get();
		foreach($arr as $key => $value){
			$this->assertFalse($value === false, "$key should be null not false");
		}
	}
}
