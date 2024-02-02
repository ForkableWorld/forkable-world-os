<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files;
trait HasDefinedFileOwner {
	abstract public function getDefinedOwner(): string;
	abstract public function getDefinedGroup(): string;
	abstract public function getDefinedPermissions(): string;
}
