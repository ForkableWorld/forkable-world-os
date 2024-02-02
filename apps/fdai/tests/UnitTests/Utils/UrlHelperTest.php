<?php
namespace Tests\UnitTests\Utils;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\UrlHelper;
use Tests\UnitTestCase;
class UrlHelperTest extends UnitTestCase
{
    public function testOnLaravelAPIPath()
    {
        $this->assertFalse(AppMode::isLaravelAPIRequest());
        $this->assertEquals(Env::getAppUrl()."/js/qmLog.js", qm_api_asset('js/qmLog.js'));
        $this->assertEquals(Env::getAppUrl()."/js/qmLog.js", asset('js/qmLog.js'));
    }
	public function testGetLocalUrl(){
		$url = "https://staging.quantimo.do/api/v4/static?bucket=qm-private&path=diffs%2Ftests%2FStagingUnitTests%2FA%2FAppSettings%2FMenuTest-testAdminSearchMenu-buttons.json-SideBySide.html";
		$local = UrlHelper::getLocalUrl($url);
		$this->assertEquals("https://local.quantimo.do/api/v4/static?bucket=qm-private&path=diffs%2Ftests%2FStagingUnitTests%2FA%2FAppSettings%2FMenuTest-testAdminSearchMenu-buttons.json-SideBySide.html", $local);
	}
}
