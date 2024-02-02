<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Files;
use App\Files\TestArtifacts\ClockworkFile;
use App\Folders\ClockworkFolder;
use App\Logging\QMClockwork;
use Clockwork\Support\Laravel\Tests\UsesClockwork;
use Tests\UnitTestCase;
class ClockworkFileTest extends UnitTestCase
{
    public function testGetData(){
	    $folder = ClockworkFolder::get();
	    $newest = $folder->getNewestFile(true, ".json");
        if(!QMClockwork::enabled()){
            $this->skipTest("Clockwork not enabled");
        }
        $data = ClockworkFile::getData();
        $this->assertContains("clockwork", $data->getPath());
    }
}
