<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties;
interface IsCalculated {
	/**
	 * @param \App\Models\BaseModel|\App\Traits\AnalyzableTrait $model
	 * @return mixed
	 */
	public static function calculate($model);
}
