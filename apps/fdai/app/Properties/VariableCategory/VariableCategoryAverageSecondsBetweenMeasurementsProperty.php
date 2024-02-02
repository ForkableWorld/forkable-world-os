<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\VariableCategory;
use App\Models\VariableCategory;
use App\Properties\Base\BaseAverageSecondsBetweenMeasurementsProperty;
use App\Traits\PropertyTraits\VariableCategoryProperty;
class VariableCategoryAverageSecondsBetweenMeasurementsProperty extends BaseAverageSecondsBetweenMeasurementsProperty
{
    use VariableCategoryProperty;
    public $table = VariableCategory::TABLE;
    public $parentClass = VariableCategory::class;
    /**
     * @param VariableCategory $model
     * @return float
     */
    public static function calculate($model): ?float{
        $value = $model->getAverageVariableValue(static::NAME);
        $model->setAttribute(static::NAME, $value);
        return $value;
    }
}
