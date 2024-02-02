<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Buttons\QMButton;
trait IonicTrait {
	abstract public function getIonicEditButton(): QMButton;
	abstract public function getUrlParams(): array;
	abstract public function getEditUrl(): string;
	abstract public function getEditButton(): QMButton;
}
