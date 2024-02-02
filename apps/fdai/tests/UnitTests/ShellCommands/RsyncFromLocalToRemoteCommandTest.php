<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\ShellCommands;
use App\Computers\PhpUnitComputer;
use App\ShellCommands\RsyncFromLocalToRemoteCommand;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\ShellCommands\RsyncFromLocalToRemoteCommand;
 */
class RsyncFromLocalToRemoteCommandTest extends UnitTestCase {
	/**
	 * @covers RsyncFromLocalToRemoteCommand::getDefinedCommand
	 * @noinspection PhpUnitMissingTargetForTestInspection (from a trait)
	 */
	public function testRsyncFromLocalToRemoteCommand(){
		$this->skipTest("TODO");
		/** @var PhpUnitComputer $c */
		$c = PhpUnitComputer::first();
		$source = $this->getPathToTest();
		$sourceAbs = abs_path($source);
		$sourceRel = relative_path($source);
		$dest = "/home/".$c->getUser()."/".$this->getPathToTest();
		$c->deleteFile($dest);
		$this->assertFalse($c->fileExists($dest));
		$obj = new RsyncFromLocalToRemoteCommand($sourceRel, $dest, $c);
		$cmd = $obj->archive()->getCommandLine();
		$this->assertEquals('rsync  -arlpgo --devices --specials -e "ssh -p 22" '.
		                    $sourceAbs.' '.$c->getUser().'@'.$c->getIP().':'.$dest, $cmd);
		$obj->runOnExecutor();
		$this->assertTrue($c->fileExists($dest));
	}
}
