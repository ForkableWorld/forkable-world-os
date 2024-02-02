<?php
namespace Tests\UnitTests\Types;
use App\Types\QMStr;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Types\QMStr
 */
class QMStrTest extends UnitTestCase {
	/**
	 * @covers QMStr::folderToNamespace
	 */
	public function testFolderToNamespaceQMStr(){
		$this->assertEquals("Tests\StagingUnitTests\D\Laravel", QMStr::folderToNamespace("tests/StagingUnitTests/D/Laravel"));
	}
}
