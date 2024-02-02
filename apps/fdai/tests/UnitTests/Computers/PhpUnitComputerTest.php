<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Computers;
use App\Computers\LightsailInstanceResponse;
use App\Computers\PhpUnitComputer;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Computers\PhpUnitComputer;
 */
class PhpUnitComputerTest extends UnitTestCase {
	public const DISABLED_UNTIL = "2023-04-01";
	protected function setUp(): void{
		$this->skipTest(self::DISABLED_UNTIL);
		parent::setUp();
	}
	public function testPHPUnitOnSlave(){
		$this->skipIfNotLocal("Too slow");
		$slave = LightsailInstanceResponse::findByIp("34.203.30.87");
		//$slave->updateFirewall();
		/** @var PhpUnitComputer $c */
		$c = $slave->getComputer();
		$this->assertInstanceOf(PhpUnitComputer::class, $c);
		$c->test();
	}
	public function testRebootOfflinePhpUnitComputers(){
		$this->skipIfNotLocal("Too slow");
		$all = PhpUnitComputer::all();
		foreach($all as $one){$this->assertStringContainsString(PhpUnitComputer::instancePrefix(), $one->getNameAttribute());}
		$offline = PhpUnitComputer::getSshOrWebsiteOffline();
		$this->assertLessThan(count($all), count($offline));
		foreach($offline as $one){$this->assertStringContainsString(PhpUnitComputer::instancePrefix(), $one->getNameAttribute());}
		$rebooted = PhpUnitComputer::rebootOffline();
		foreach($rebooted as $c){
			$this->assertFalse($c->sshOrWebsiteOffline(),
				"$c is still offline.  Maybe you should delete it at:\n\t".
			                                    $c->getLightsailInstance()->getDeleteUrl());
		}
	}
}
