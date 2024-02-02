<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Menus;
use App\Computers\PhpUnitComputer;
use App\Menus\Admin\DebugMenu;
use Tests\Traits\TestsMenus;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Menus
 * @coversDefaultClass \App\Menus\Admin\DebugMenu;
 */
class DebugMenuTest extends UnitTestCase {
	use TestsMenus;
	/**
	 * @covers \App\Menus\Admin\DebugMenu
	 */
	public function testLightSailDebugMenu(){
		$this->skipTest("Too slow");
//		$computers = PhpUnitComputer::all();
//		foreach($computers as $computer){
//			$m = new DebugMenu($computer);
//			// TODO $m->testUrls();
//			$m->logUrls();
//			$this->assertTrue(true);
//		}
		$c = PhpUnitComputer::first();
		$m = new DebugMenu($c);
		$ip = $c->getIP();
		$name = $c->getNameAttribute();
		$this->assertArrayEquals([
			1 => 'http://'.$ip.':7777',
			2 => 'http://'.$ip.'/astral',
			3 => 'http://'.$ip.'/pimp',
			4 => \App\Utils\Env::getAppUrl().'/'.'dev/phpstorm?line=16&file=tests%2FUnitTests%2FMenus%2FDebugMenuTest.php',
			5 => 'sftp://ubuntu;x-name='.$name.';x-publickeyfile=C%3A%5Ccode%5Cwindows-settings%5Cwsl%5Chome%5Cvagrant%5C.ssh%5Cubuntu%40qm-aws-20160528.ppk;x-considerdst=0@'.$ip.':22/home/ubuntu',
			6 => 'http://'.$ip.'/horizon/dashboard',
			7 => 'http://'.$ip.'/admin/adminer',], $m->getUrls());
	}
	public function testLocalDebugMenu(){
		$this->skipTest('Not implemented yet.');
		$this->checkMenuButtonTitlesAndUrls(array (
			0 => 'Clockwork',
			1 => 'aaPanel',
			2 => 'Astral Admin Console',
			3 => 'Server Logs',
			4 => 'Open Test',
			5 => 'WinSCP',
			6 => 'Horizon Queue Manager',
			7 => 'Adminer',
		), DebugMenu::class);
	}
	public function testRouteMenu(){
		$this->artisan('route:menu');
		$this->assertTrue(true);
	}
}
