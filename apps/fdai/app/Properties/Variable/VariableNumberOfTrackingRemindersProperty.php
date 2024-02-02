<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\TrackingReminder;
use App\Models\Variable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseNumberOfTrackingRemindersProperty;
use App\Variables\QMCommonVariable;
class VariableNumberOfTrackingRemindersProperty extends BaseNumberOfTrackingRemindersProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    use IsCalculated;
    /**
     * @param Variable|QMCommonVariable $model
     * @return float
     */
    public static function calculate($model){
        $num = TrackingReminder::whereVariableId($model->getVariableIdAttribute())->count();
        $model->setAttribute(static::NAME, $num);
        return $num;
    }
}
