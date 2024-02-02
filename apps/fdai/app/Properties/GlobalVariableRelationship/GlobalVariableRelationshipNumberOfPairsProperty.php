<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BaseNumberOfPairsProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\VariableRelationships\QMGlobalVariableRelationship;
class GlobalVariableRelationshipNumberOfPairsProperty extends BaseNumberOfPairsProperty
{
    use GlobalVariableRelationshipProperty;
    use IsCalculated;
    public const MIN_PAIRS_FOR_PUBLIC = 3;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    /**
     * @param QMGlobalVariableRelationship|GlobalVariableRelationship $model
     * @return float
     * @throws \App\Exceptions\NoUserVariableRelationshipsToAggregateException
     */
    public static function calculate($model): float{
        $val = $model->summedUserVariableRelationshipValue(static::NAME);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
