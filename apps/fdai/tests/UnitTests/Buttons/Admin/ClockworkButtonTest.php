<?php
namespace Buttons\Admin;
use App\Buttons\Admin\ClockworkButton;
use PHPUnit\Framework\TestCase;
class ClockworkButtonTest extends TestCase
{
	public function testClockworkButton(){
		$button = new ClockworkButton();
		$url = $button->getUrl();
		$this->assertStringContainsString('__clockwork/app', $url);
	}
}
