<?php
namespace Tests\UnitTests\Files;
use App\Files\PHP\AbstractPhpFile;
use App\Logging\ConsoleLog;
use Tests\UnitTestCase;
class PHPFileTest extends UnitTestCase
{
    public function testGetTraitsAndClassesInFolder(){
	    ConsoleLog::debug("AbstractPhpFile::getTraitsAndClassesInFolder in testGetTraitsAndClassesInFolder");
	    $files = AbstractPhpFile::getTraitsAndClassesInFolder("app/Solutions");
	    ConsoleLog::debug("assertGreaterThan in testGetTraitsAndClassesInFolder");
	    $this->assertGreaterThan(20, count($files));
    }
}
