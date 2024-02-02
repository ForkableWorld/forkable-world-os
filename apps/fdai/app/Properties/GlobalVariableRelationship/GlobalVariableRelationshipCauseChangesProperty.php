<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BaseCauseChangesProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\VariableRelationships\QMGlobalVariableRelationship;
class GlobalVariableRelationshipCauseChangesProperty extends BaseCauseChangesProperty
{
    use GlobalVariableRelationshipProperty;
    use IsCalculated;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    /**
     * @param QMGlobalVariableRelationship|GlobalVariableRelationship $model
     * @return int
     */
    public static function calculate($model): int {
        $val = $model->summedUserVariableRelationshipValue(static::NAME);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
