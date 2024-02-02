<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
abstract class AppButton extends QMButton {
	public function __construct(){
		parent::__construct();
	}
	abstract public function getPath(): string;
}
