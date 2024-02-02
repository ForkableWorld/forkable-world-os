<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\ShellCommands;
use App\Computers\JenkinsSlave;
class PhpUnitCommand extends DynamicCommand {
	public function __construct(string $cmd = null, bool $obfuscate = null, JenkinsSlave $executor = null){
		parent::__construct($cmd, null, null, null, 60, $obfuscate, $executor);
	}
}
