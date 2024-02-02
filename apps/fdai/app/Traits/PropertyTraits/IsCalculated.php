<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\BaseModel;
use App\Slim\Model\DBModel;
use App\Traits\HasCalculatedAttributes;
trait IsCalculated {
	public $isCalculated = true;
	/**
	 * @param $model
	 * @return mixed
	 */
	abstract public static function calculate($model);
	/**
	 * @param BaseModel|DBModel|HasCalculatedAttributes $model
	 * @return mixed
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function calculateAndSet($model){
		$val = self::calculate($model);
		$model->setAttribute(static::NAME, $model);
		return $val;
	}
}
