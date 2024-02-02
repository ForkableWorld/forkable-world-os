<?php /** @noinspection PhpTraditionalSyntaxArrayLiteralInspection */
namespace Tests\UnitTests\Menus\RoleBased;
use App\Menus\RoleBased\AdminMenu;
use Tests\Traits\TestsMenus;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Menus\RoleBased\AdminMenu;
 */
class AdminMenuTest extends UnitTestCase {
	use TestsMenus;
	protected function setUp(): void{
		$this->skipTest('Need to handle admin auth first');
		parent::setUp();
	}
	/**
	 * @covers \App\Menus\RoleBased\AdminMenu::get
	 */
	public function testAdminMenu(){
		$this->setAdminUser();
		$this->checkMenuButtonTitlesAndUrls(array (
			                                    0 => 'aaPanel',
			                                    1 => 'Clockwork',
			                                    2 => 'Horizon Queue Manager',
			                                    3 => 'Issues',
			                                    4 => 'Access Tokens',
			                                    5 => 'Telescope',
			                                    6 => 'Admin Search',
			                                    7 => 'Bugsnag',
			                                    8 => 'XHGUI',
		                                    ),                                  AdminMenu::class);
	}
}
