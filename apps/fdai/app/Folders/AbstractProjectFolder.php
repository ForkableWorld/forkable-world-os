<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Folders;
abstract class AbstractProjectFolder extends DynamicFolder {
	public function __construct(){
		$path = static::relativePath();
		parent::__construct($path); }
	abstract public static function relativePath(): string;
}
