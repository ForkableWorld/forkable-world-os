<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\ShellCommands\JenkinsCommands;
class JenkinsReload extends AbstractJenkinsCommand {
	public function getDefinedCommand(): string{
		return "reload-configuration";
	}
}
