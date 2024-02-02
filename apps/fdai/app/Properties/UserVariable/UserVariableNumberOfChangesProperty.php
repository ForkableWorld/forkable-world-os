<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseNumberOfChangesProperty;
use App\Utils\Stats;
use App\Variables\QMCommonVariable;
class UserVariableNumberOfChangesProperty extends BaseNumberOfChangesProperty
{
    use UserVariableProperty;
    use IsCalculated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @param UserVariable $model
     * @return int
     */
    public static function calculate($model){
		if($model instanceof QMCommonVariable){
			$measurements = $model->getValidDailyMeasurementsWithTags();
			$values = collect($measurements)->pluck('value')->all();
		} else {
			$values = $model->getDailyValuesWithTagsAndFilling();
		}
        $changes = Stats::countChanges($values);
        $model->setAttribute(static::NAME, $changes);
        return $changes;
    }
}
