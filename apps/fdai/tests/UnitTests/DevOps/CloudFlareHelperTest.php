<?php
namespace Tests\UnitTests\DevOps;
use App\DevOps\CloudFlareHelper;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\DevOps\CloudFlareHelper;
 */
class CloudFlareHelperTest extends UnitTestCase {
	/**
	 * @covers CloudFlareHelper::listLoadBalancers
	 * @noinspection PhpUnitMissingTargetForTestInspection (from a trait)
	 */
	public function testListLoadBalancersCloudFlareHelper(){
        $this->markTestSkipped(
            'This test is skipped because it requires a CloudFlare account.'
        );
		$balancers = CloudFlareHelper::listLoadBalancers();
		$this->assertEquals("app.quantimo.do", $balancers[0]->name);
	}
}
