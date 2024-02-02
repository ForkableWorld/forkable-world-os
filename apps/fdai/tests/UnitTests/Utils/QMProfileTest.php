<?php
namespace Tests\UnitTests\Utils;
use App\Utils\QMProfile;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Utils\QMProfile;
 */
class QMProfileTest extends UnitTestCase {
	protected function setUp(): void{
		$this->skipIfNotLocal("Can't reproduce locally");
		parent::setUp();
	}
	public function testXHGUI(){
		$this->skipTest("Not implemented");
		QMProfile::startLiveProf();
		QMProfile::endProfile();
		$url = QMProfile::getLastProfileUrl();
		$this->assertStringStartsWith(\App\Utils\Env::getAppUrl().'/profiler/tree-view.phtml?app=testing&label=testXHGUI', $url);
	}
}
