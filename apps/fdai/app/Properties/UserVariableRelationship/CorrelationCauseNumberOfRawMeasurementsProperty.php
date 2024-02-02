<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Properties\UserVariable\UserVariableNumberOfRawMeasurementsWithTagsJoinsChildrenProperty;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseCauseNumberOfRawMeasurementsProperty;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationCauseNumberOfRawMeasurementsProperty extends BaseCauseNumberOfRawMeasurementsProperty
{
    use CorrelationProperty;
    use \App\Traits\PropertyTraits\IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param QMUserVariableRelationship $model
     * @return int
     */
    public static function calculate($model): int {
        $cause = $model->getOrSetCauseQMVariable();
        $value = UserVariableNumberOfRawMeasurementsWithTagsJoinsChildrenProperty::calculate($cause);
        $model->setAttribute(static::NAME, $value);
        return $value;
    }
}
