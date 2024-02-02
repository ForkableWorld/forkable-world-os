<?php
namespace Tests\UnitTests\TestGenerators;
use App\Models\User;
use App\Notifications\TestGeneratedNotification;
use Tests\UnitTestCase;
class BasePhpUnitTestFileTest extends UnitTestCase {
	public function testTestGeneratedNotification() {
		$url = "https://local.quantimo.do/dev/phpstorm?project=cd-api&path=tests\StagingUnitTests\D\AccountTest.php&file=tests\StagingUnitTests\D\AccountTest.php&line=";
		$notification = new TestGeneratedNotification($url, "AccountTest");
		$m = User::mike();
		$m->notify($notification);
		$this->assertTrue(true);
	}
}
