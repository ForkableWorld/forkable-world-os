<?php
namespace Tests\UnitTests\Http\Urls;
use App\Http\Urls\IntendedUrl;
use App\Utils\Env;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Http\Urls\IntendedUrl
 */
class IntendedUrlTest extends UnitTestCase {
	public function testGetDefaultIntendedUrl(){
		$appUrl = Env::getAppUrl();
		$intendedUrl = IntendedUrl::get();
		$this->assertNull($intendedUrl);
	}
	/**
	 * @covers IntendedUrl::get
	 */
	public function testGetIntendedUrl(){
        $this->skipTest("TODO: May fix auth if you have time. Just use this for a backend server");
		$this->assertGuest();
		$url = "/admin/ignitionReport?time=225715";
		$expected =
			"http://localhost/auth/login?intended_url=".urlencode(\App\Utils\Env::getAppUrl()).
            "%2Fadmin%2FignitionReport%3Ftime%3D225715&logout=1";
		$response = $this->get($url);
		$this->assertRedirect($response, $expected);
		$response->assertSessionHas('url.intended', \App\Utils\Env::getAppUrl()."/admin/ignitionReport?time=225715");
	}
}
