<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Logging\QMLog;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseLatestMeasurementStartAtProperty;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationLatestMeasurementStartAtProperty extends BaseLatestMeasurementStartAtProperty
{
    use CorrelationProperty;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    use \App\Traits\PropertyTraits\IsCalculated;
    /**
     * @param QMUserVariableRelationship $model
     * @return float|null
     * @noinspection PhpMissingReturnTypeInspection
     */
    public static function calculate($model) {
        QMLog::info("TODO: Implement calculate in ".static::class);
        return null;
        // TODO: Implement calculate() method.
        //$model->setAttribute(static::NAME, $value);
        //return $value;
    }
}
