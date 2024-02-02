<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\VariableCategory;
use App\Models\VariableCategory;
use App\Properties\Base\BaseMedianSecondsBetweenMeasurementsProperty;
use App\Traits\PropertyTraits\VariableCategoryProperty;
class VariableCategoryMedianSecondsBetweenMeasurementsProperty extends BaseMedianSecondsBetweenMeasurementsProperty
{
    use VariableCategoryProperty;
    public $table = VariableCategory::TABLE;
    public $parentClass = VariableCategory::class;
    /**
     * @param VariableCategory $model
     * @return float
     */
    public static function calculate($model): ?float{
        $values = $model->getVariableValues(static::NAME);
        $median = $values->median();
        $model->setAttribute(static::NAME, $median);
        return $median;
    }
}
