<?php
namespace Tests\UnitTests\Utils;
use App\Computers\ThisComputer;
use App\DevOps\Jenkins\JenkinsAPI;
use App\DevOps\XDebug;
use App\Types\BoolHelper;
use App\Utils\AppMode;
use App\Utils\Env;
use Tests\UnitTestCase;

class AppModeTest extends UnitTestCase {
    public function testConsoleAvailable(){
        $this->assertTrue(AppMode::consoleAvailable());
    }
    public function testJobTaskOrTestName(){
        $this->assertEquals(__FUNCTION__, AppMode::getJobTaskOrTestName());
    }
    public function testIsProductionApiRequest(){
        $this->assertFalse(AppMode::isProductionApiRequest());
    }
    public function testGetAppMode(){
        $this->assertEquals(Env::ENV_TESTING, AppMode::getAppMode());
    }
    public function testIsWorker(){
        $this->assertNotTrue(AppMode::isWorker());
    }
    public function testIsTravis(){
        $this->assertFalse(AppMode::isTravis());
    }
    public function testIsDialogFlowRequest(){
        $this->assertFalse(AppMode::isDialogFlowRequest());
    }
    public function testIsProductionTesting(){
        $this->assertFalse(AppMode::isStagingUnitTesting());
    }
    public function testIsPHPUnitTaskOrJob(){
        $this->assertFalse(AppMode::workingDirectoryOrArgumentStartsWithJobsOrTasksFolder());
    }
    public function testIsJenkins(){
        if(\App\Utils\Env::get(JenkinsAPI::JENKINS_HOME)){
            $this->assertTrue(AppMode::isJenkins());
        }else{
            $this->assertFalse(AppMode::isJenkins());
        }
    }
    public function testIsCI(){
        if(\App\Utils\Env::get(JenkinsAPI::JENKINS_HOME)){ // Need to updated this if we start using Heroku
            $this->assertTrue(AppMode::isCI());
        }elseif(\App\Utils\Env::get('TRAVIS')){
            $this->assertTrue(AppMode::isCI());
        }else{
            $this->assertFalse(AppMode::isCI());
        }
    }
    public function testIsStagingOrProductionApiRequest(){
        $this->assertFalse(AppMode::isStagingOrProductionApiRequest());
    }
    public function testIsUnitOrStagingUnitTest(){
        $this->assertTrue(AppMode::isAnyKindOfUnitTest());
    }
    public function testIsTestingOrDevelopment(){
        $this->assertTrue(Env::isTestingOrDevelopment());
    }
    public function testIsTravisOrHeroku(){
        if(\App\Utils\Env::get('TRAVIS')){ // Need to updated this if we start using Heroku
            $this->assertTrue(AppMode::isTravisOrHeroku());
        }else{
            $this->assertFalse(AppMode::isTravisOrHeroku());
        }
    }
    public function testIsUnitTest(){
        $relativePath = "tests/UnitTest";
        $this->assertTrue(AppMode::workingDirectoryOrArgumentStartsWith($relativePath), "scriptRelativePathMatches('tests/UnitTests')");
        $this->assertTrue(AppMode::unitTestPathOrArgs(), "Arguments: ".var_export(ThisComputer::getCommandLineArguments(), true));
    }
    public function testIsTestingOrIsTestUser(){
        $this->assertTrue(AppMode::isTestingOrIsTestUser(1));
        $this->assertTrue(AppMode::isTestingOrIsTestUser(18535));
    }
    public function testIsTesting(){
        $this->assertTrue(AppMode::isTestingOrStaging());
    }
//    public function testIsDevelopment(){
//        if(Env::getEnv('APP_ENV') !== "development"){
//            $this->assertFalse(EnvOverride::isLocal());
//        } else {
//            $this->assertTrue(EnvOverride::isLocal());
//        }
//    }
    public function testIsStaging(){
        $this->assertFalse(Env::isStaging());
    }
    public function testIsSlimHttpRequest(){
        $this->assertFalse(AppMode::isSlimHttpRequest());
    }
    public function testIsApiRequest(){
        $this->assertFalse(AppMode::isApiRequest());
    }
    public function testXDebugActive(){
        $this->assertFalse(XDebug::active());
    }
    public function testIsTestingStagingOrDevelopmentOrConsoleTask(){
        $this->assertTrue(AppMode::isTestingStagingOrDevelopmentOrConsoleTask());
    }
    public function testIsProduction(){
        $this->assertFalse(AppMode::isProduction());
    }
    public function testIsDebug(){
        BoolHelper::assertFalsey(\App\Utils\Env::get('APP_DEBUG'), Env::APP_DEBUG);
        $this->assertFalse(Env::APP_DEBUG());
    }
    public function testSetJobTaskOrTestName(){
        AppMode::setJobOrTaskName("testing");
        $this->assertEquals("testSetJobTaskOrTestName", AppMode::getJobTaskOrTestName());
        AppMode::setJobOrTaskName(null);
    }
}
