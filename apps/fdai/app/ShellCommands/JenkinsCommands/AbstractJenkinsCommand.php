<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\ShellCommands\JenkinsCommands;
use App\Computers\MasterComputer;
use App\DevOps\Jenkins\Jenkins;
use App\Repos\JenkinsBackupRepo;
use App\ShellCommands\AbstractCommand;
abstract class AbstractJenkinsCommand extends AbstractCommand {
	public function __construct(){
		parent::__construct($this->getCommandLine(), null, null, null, 60, false, MasterComputer::find("master"));
	}
	public function getCommandLine(): string{
		$cmd = "java -jar ".$this->jenkinsCli()." -s ".Jenkins::JENKINS_URL." -auth ".Jenkins::USER.":"
		       .Jenkins::API_TOKEN." ";
		return $this->commandLine = $cmd.$this->getDefinedCommand();
	}
	private function jenkinsCli(): string{
		return JenkinsBackupRepo::getAbsolutePath("jenkins-cli.jar");
	}
}
