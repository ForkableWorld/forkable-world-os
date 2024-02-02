<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseNumberOfSoftDeletedMeasurementsProperty;
class UserVariableNumberOfSoftDeletedMeasurementsProperty extends BaseNumberOfSoftDeletedMeasurementsProperty
{
    use UserVariableProperty;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @param UserVariable $model
     * @return int
     */
    public static function calculate($model): int {
        $val = $model
            ->l()
            ->measurements()
            ->whereNotNull(Measurement::FIELD_DELETED_AT)
            ->withTrashed()
            ->count();
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
