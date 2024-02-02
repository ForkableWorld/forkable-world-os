<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\VariableValueTraits;
trait DailyVariableValueTrait {
	use AggregatedVariableValueTrait;
	public function getDuration(): int{
		return 86400;
	}
}
