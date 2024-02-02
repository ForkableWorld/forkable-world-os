<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\BaseModel;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseNumberOfProcessedDailyMeasurementsProperty;
use App\Variables\QMUserVariable;
class UserVariableNumberOfProcessedDailyMeasurementsProperty extends BaseNumberOfProcessedDailyMeasurementsProperty
{
    use UserVariableProperty;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    use IsCalculated;
    /**
     * @param UserVariable|QMUserVariable $model
     * @return int
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function calculate($model): int{
        $dbm = $model->getDBModel();
        $measurements = $dbm->getValidDailyMeasurementsWithTagsAndFilling();
        $count = count($measurements);
        return $dbm->setAttribute(static::NAME, $count);
    }
}
