<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Types\TimeHelper;
trait HasTime {
	abstract public function getAt(): string;
	public function getHumanAt(): string{
		return TimeHelper::humanTime($this->getAt());
	}
}
