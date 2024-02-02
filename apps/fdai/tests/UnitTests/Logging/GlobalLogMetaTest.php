<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
namespace Tests\UnitTests\Logging;
use App\Exceptions\InvalidStringException;
use App\Exceptions\InvalidUrlException;
use App\Logging\GlobalLogMeta;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Logging\GlobalLogMeta;
 */
class GlobalLogMetaTest extends UnitTestCase {
	protected function setUp(): void{
		$this->skipIfNotLocal();
		parent::setUp();
	}
	/**
	 * @covers GlobalLogMeta::get
	 * @noinspection PhpUnitMissingTargetForTestInspection (from a trait)
	 */
	public function testGetGlobalLogMeta(){
        $this->skipTest("TODO");
		$arr = GlobalLogMeta::get();
		$this->checkLinks($arr['LINKS']);
	}
	/**
	 * @param array $links
	 */
	private function checkLinks(array $links): void{
		$this->assertArrayEquals(array (
            0 => 'IGNITION',
            1 => 'IGNITION_REPORT',
            2 => 'Astral Admin Console',
            3 => 'Open Test',
            4 => 'WinSCP',
            5 => 'Horizon Queue Manager',
            6 => 'Adminer',
        ), array_keys($links));
		foreach($links as $name => $value){
			$this->assertIsUrl($value, $name);
		}
	}
}
