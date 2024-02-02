<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Traits;
use App\Computers\JenkinsSlave;
use App\Computers\PhpUnitComputer;
use App\Files\FileHelper;
use Tests\UnitTestCase;
class HasXmlConfigTest extends UnitTestCase
{
    public function testGenerateConfigTemplateStub(){
        $this->skipTest("This is a nightmare");
        $computers = PhpUnitComputer::all();
        /** @var JenkinsSlave $one */
        $one = $computers->first();
	    FileHelper::assertExists($one->getXmlConfigPath());
        $originalFile = $one->getXmlConfigContents();
        $this->assertEquals(FileHelper::getContents($one->getXmlConfigPath()), 
            $one->getXmlConfigContents(), __METHOD__);
        $generated = $one->generateXmlConfig();
        $this->assertEquals($originalFile, $generated);
    }
}
