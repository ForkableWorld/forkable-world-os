<?php
namespace Tests\UnitTests\Http\Urls;
use App\Http\Urls\AfterLogoutUrl;
use App\Utils\Env;
use App\Utils\IonicHelper;
use Tests\UnitTestCase;
class AfterLogoutUrlTest extends UnitTestCase
{
	/**
	 * @return void
	 * @covers \App\Http\Urls\AfterLogoutUrl::url()
	 * @covers \App\Utils\IonicHelper::ionicOrigin()
	 */
	public function testAfterLogoutUrl()
    {
		$before = Env::getAppUrl();
		Env::set(Env::APP_URL, "https://feature.quantimo.do");
		try {
			$this->assertEquals('https://feature.quantimo.do/app/public', IonicHelper::ionicOrigin(null));
			$this->assertEquals('https://feature.quantimo.do/app/public/#/app/intro?logout=1', AfterLogoutUrl::url());
		} catch (\Throwable $e) {
			Env::set(Env::APP_URL, $before);
		    le($e);
		}
	    Env::set(Env::APP_URL, $before);

    }
}
