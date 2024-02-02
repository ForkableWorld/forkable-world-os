<?php
namespace Tests\UnitTests\Repos;
use App\Slim\Middleware\QMAuth;
use App\Utils\QMProfile;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Repos\LiveprofRepo;
 */
class LiveprofRepoTest extends UnitTestCase {
	protected function setUp(): void{
		$this->skipIfNotLocal();
		$before = \App\Utils\Env::get('PROFILE');
		if($before){putenv("PROFILE=false");}
		parent::setUp();
		if($before){putenv("PROFILE=$before");}
	}
	public function testLiveprofProfile(){
		//$this->assertFalse((bool)\App\Utils\Env::get('PROFILE'), "Cannot test");
		$this->skipTest("Cannot test");
		QMProfile::startLiveProf();
		QMAuth::getUser();
		QMProfile::endProfile(true);
		$this->assertStringStartsWith(\App\Utils\Env::getAppUrl().'/'."profiler/tree-view.phtml?app=testing&label=testLiveprofProfile", QMProfile::getLastProfileUrl());
	}
}
