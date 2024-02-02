<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseConfidenceIntervalProperty;
use App\Traits\PropertyTraits\IsCalculated;
class CorrelationConfidenceIntervalProperty extends BaseConfidenceIntervalProperty
{
    use CorrelationProperty, IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
	/**
	 * @param \App\VariableRelationships\QMUserVariableRelationship|UserVariableRelationship $model
	 * @throws \App\Exceptions\InsufficientVarianceException
	 * @throws \App\Exceptions\NotEnoughDataException
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 */
	public static function calculate($model){
		$model->calculatePValue();
		$val = $model->getAttribute(static::NAME);
		$model->setAttribute(self::NAME, $val);
	}
}
