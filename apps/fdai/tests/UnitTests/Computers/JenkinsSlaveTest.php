<?php

namespace Computers;

use App\Buttons\Admin\AAPanelButton;
use App\Computers\JenkinsSlave;
use PHPUnit\Framework\TestCase;

class JenkinsSlaveTest extends TestCase {
	/**
	 * @return void
	 * @covers \App\Computers\JenkinsSlave::getRunnerIP
	 */
	public function testGetRunnerIP(){
		$actual = JenkinsSlave::getRunnerIP('infinity-vagrant');
		$this->assertEquals(AAPanelButton::RUNNER_IPS['infinity'], $actual);
	}
}
