<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\ShellCommands;
abstract class AbstractCommand extends DynamicCommand {
	abstract protected function getDefinedCommand(): string;
	public function getCommandLine(): string{
		return $this->commandLine = $this->getDefinedCommand();
	}
	public static function exec():self{
		$c = new static();
		$c->runOnExecutor();
		return $c;
	}
}
