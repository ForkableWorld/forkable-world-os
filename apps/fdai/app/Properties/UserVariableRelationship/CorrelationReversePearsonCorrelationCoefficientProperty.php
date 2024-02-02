<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseReversePearsonCorrelationCoefficientProperty;
use App\Traits\PropertyTraits\IsCalculated;
class CorrelationReversePearsonCorrelationCoefficientProperty extends BaseReversePearsonCorrelationCoefficientProperty
{
    use CorrelationProperty;
	use IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
	/**
	 * @param QMUserVariableRelationship $model
	 * @return float
	 */
	public static function calculate($model): float {
		$c = new QMUserVariableRelationship(null, $model->getEffectQMVariable(), $model->getCauseQMVariable());
		try {
			$val = CorrelationForwardPearsonCorrelationCoefficientProperty::calculate($c);
		} catch (NotEnoughDataException|TooSlowToAnalyzeException $e) {
			le($e);
		}
		$model->setAttribute(self::NAME, $val);
		return $val;
	}
}
