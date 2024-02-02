<?php
namespace Buttons;
use App\Buttons\IonicButton;
use App\Buttons\States\OnboardingStateButton;
use App\Utils\Env;
use App\Utils\IonicHelper;
use PHPUnit\Framework\TestCase;
class IonicButtonTest extends TestCase
{
    public function testGetUrl()
    {
	    $appUrl = Env::getAppUrl();
	    $this->assertEquals($appUrl."/app/public/#/app/onboarding", OnboardingStateButton::url());
		$this->assertEquals("$appUrl/app/public", IonicHelper::ionicOrigin());
		$this->assertEquals($appUrl."/app/public", IonicButton::url());
    }
}
