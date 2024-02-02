<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseEarliestMeasurementStartAtProperty;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationEarliestMeasurementStartAtProperty extends BaseEarliestMeasurementStartAtProperty
{
    use CorrelationProperty;
    use \App\Traits\PropertyTraits\IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param QMUserVariableRelationship $model
     * @return int
     */
    public static function calculate($model) {
        // TODO $model->setAttribute(static::NAME, $value);
        //return $value;
    }
}
