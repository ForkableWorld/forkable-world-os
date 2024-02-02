<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\ShellCommands;
use App\Computers\JenkinsSlave;
use App\Files\FileHelper;
class RsyncFromLocalToRemoteCommand extends BaseRsyncCommand {
	/**
	 * @var \App\Computers\JenkinsSlave
	 */
	private JenkinsSlave $destinationComputer;
	public function __construct(string $src, string $dest, JenkinsSlave $destinationComputer){
		$this->destinationComputer = $destinationComputer;
		parent::__construct($src, $dest);
	}
	private function getRemoteDestination(): string{
		$c = $this->getDestinationComputer();
		$dest = $this->destination;
		return $c->getUser()."@".$c->getIP().":".$dest;
	}
	/**
	 * @return \App\Computers\JenkinsSlave
	 */
	public function getDestinationComputer(): JenkinsSlave{
		return $this->destinationComputer;
	}
	public function getDefinedCommand(): string{
		$c = $this->getDestinationComputer();
		return "rsync ".$this->getRsync()->getOptionsString().' -e "ssh -p '.$c->getPort().'" '.$this->getSource().
		       " ".$this->getRemoteDestination();
	}
	/**
	 * @throws \App\ShellCommands\CommandFailureException
	 */
	public function runOnExecutor(): void{
		$c = $this->getDestinationComputer();
		$destDir = FileHelper::getFolderFromPath($this->getDestination());
		$c->mkdir($destDir);
		parent::runOnExecutor();
	}
}
