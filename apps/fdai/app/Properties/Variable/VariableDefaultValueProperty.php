<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseDefaultValueProperty;
use App\Traits\VariableValueTraits\VariableValueTrait;
use App\Variables\QMCommonVariable;
class VariableDefaultValueProperty extends BaseDefaultValueProperty
{
    use VariableProperty, VariableValueTrait;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    use IsCalculated;
    /**
     * @param QMCommonVariable|\App\Slim\Model\Reminders\QMTrackingReminder $model
     * @return float|null
     */
    public static function calculate($model): ?float{
        $val = self::getDefaultValueIfValid($model);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
	/**
	 * @param QMCommonVariable|\App\Slim\Model\Reminders\QMTrackingReminder $model
	 * @return null
	 */
	private static function getDefaultValueIfValid($model){
        $val = $model->defaultValue;
        if($val === null){return null;}
        $unit = $model->getCommonUnit();
        if($unit->minimumValue !== null && $val < $unit->minimumValue){return null;}
        if($unit->maximumValue !== null && $val > $unit->maximumValue){return null;}
        if($model->minimumAllowedValue !== null && $val < $model->minimumAllowedValue){return null;}
        if($model->maximumAllowedValue !== null && $val > $model->maximumAllowedValue){return null;}
        return $val;
    }
}
