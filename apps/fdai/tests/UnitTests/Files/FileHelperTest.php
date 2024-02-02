<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Files;
use App\Computers\ThisComputer;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Folders\AbstractFolder;
use App\Variables\QMCommonVariable;
use Tests\UnitTestCase;
class FileHelperTest extends UnitTestCase {
	public function testWriteFilePermissionsAndOwner() {
		//$this->assertEquals(ThisComputer::DEFAULT_WEB_USER, IndexDotPhp::instance()->getGroupName());
		//$this->assertEquals(ThisComputer::USER_JENKINS, IndexDotPhp::instance()->getOwnerName());
		$path = 'storage/charts/config.json';
		FileHelper::writeByFilePath($path, "test");
		FileHelper::writeByFilePath($path, "test");
		FileHelper::delete($path);
		$folder = AbstractFolder::getCurrentTestFolder();
		$path = $folder."/".__FUNCTION__;
		FileHelper::delete($path);
		$this->assertFileDoesNotExist($path);
		FileHelper::writeByFilePath($path, __FUNCTION__);
		$this->assertFileExists($path);
        return; //Does not work on Windows
		$this->assertEquals(FileHelper::getDefaultFileOwnerName(), FileHelper::getOwnerName($path));
		$this->assertEquals(FileHelper::getDefaultGroupName(), FileHelper::getGroupName($path));
		$this->assertEquals(FileHelper::DEFAULT_FILE_PERMISSIONS, FileHelper::getPermissions($path));
		// TODO $this->assertEquals(FileHelper::DEFAULT_FOLDER_PERMISSIONS, FileHelper::getPermissions($folder));
        FileHelper::delete($path);
		$this->assertFileDoesNotExist($path);
	}
    public function testGetAllFilesInAllSubFoldersRecursively() {
        $absPath = QMCommonVariable::getHardCodedDirectory();
        $fromIterator = FileFinder::listFilesRecursively($absPath);
        $this->assertGreaterThan(100, count($fromIterator));
    }
    public function testPathToNamespace(){
        $abs = FileHelper::absPath("app/Strategies/AbstractInsiderStrategy.php");
        $this->assertEquals("App\\Strategies", FileHelper::filePathToNamespace($abs));
    }
    public function testGetFilesInFolder(){
        $files = FileFinder::getFilesInFolder('app/Solutions');
        $this->assertGreaterThan(20, count($files));
    }
    public function testRelativePath(){
    	$this->assertEquals(".", relative_path(abs_path()));
    }
	/**
	 * @covers \App\Files\FileHelper::absPath
	 */
	public function testAbsPath(){
	    $this->assertEquals(ThisComputer::home() . DIRECTORY_SEPARATOR.'.ssh'.DIRECTORY_SEPARATOR.'id_rsa',
		    abs_path('~/.ssh/id_rsa'));
	    $abs = abs_path();
	    $rel = relative_path($abs);
	    $this->assertEquals($abs, abs_path($rel));
		$path = "/www/wwwroot/qm-api/tests/UnitTests/ShellCommands/RsyncFromLocalToRemoteCommandTest.php";
		$this->assertEquals($path, abs_path($path));
    }
	public function testListFolders(){
    	$absPaths = FileFinder::listFolders(abs_path("app/Slim"));
    	$rel = [];
    	foreach($absPaths as $abs){
    		$rel[] = relative_path($abs);
	    }
    	sort($rel);
		$this->assertArrayEquals([
			0 => 'app/Slim/APIWrappers',
			1 => 'app/Slim/Configuration',
			2 => 'app/Slim/Controller',
			3 => 'app/Slim/Middleware',
			4 => 'app/Slim/Model',
			5 => 'app/Slim/Templates',
			6 => 'app/Slim/View',], $rel);
	}
}
