<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BaseDataSourceNameProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\VariableRelationships\QMGlobalVariableRelationship;
class GlobalVariableRelationshipDataSourceNameProperty extends BaseDataSourceNameProperty
{
    use GlobalVariableRelationshipProperty;
    use IsCalculated;
    public const DATA_SOURCE_NAME_MedDRA = "MedDRA";
    public const DATA_SOURCE_NAME_CURE_TOGETHER = "ct";
    public const DATA_SOURCE_NAME_USER = "user";
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    /**
     * @param QMGlobalVariableRelationship|GlobalVariableRelationship $model
     * @return string
     */
    public static function calculate($model): string {
        $names = $model->pluckFromCorrelations(static::NAME);
        $names = array_unique($names);
        $val = implode(', ',$names);
        $model->setAttribute(static::NAME, $val);
        if(empty($val)){
            $val = self::DATA_SOURCE_NAME_USER;
        }
        return $val;
    }
}
