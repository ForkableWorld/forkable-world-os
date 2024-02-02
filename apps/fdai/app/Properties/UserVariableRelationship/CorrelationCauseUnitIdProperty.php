<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseCauseUnitIdProperty;
use App\Traits\PropertyTraits\IsCalculated;
class CorrelationCauseUnitIdProperty extends BaseCauseUnitIdProperty
{
    use CorrelationProperty;
	use IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return false;}
	/**
	 * @param UserVariableRelationship|\App\VariableRelationships\QMUserVariableRelationship $model
	 * @return int
	 */
	public static function calculate($model): int{
		$value = $model->getCauseVariable()->default_unit_id;
		$model->setAttribute(self::NAME, $value);
		return $value;
	}
}
