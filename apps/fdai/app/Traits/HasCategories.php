<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Buttons\QMButton;
trait HasCategories {
	/**
	 * @return QMButton[]
	 */
	abstract public function getCategoryButtons(): array;
}
