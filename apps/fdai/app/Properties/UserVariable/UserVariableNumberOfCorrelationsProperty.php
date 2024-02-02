<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Properties\UserVariableRelationship\CorrelationCauseNumberOfProcessedDailyMeasurementsProperty;
use App\Storage\DB\QMQB;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseNumberOfCorrelationsProperty;
use App\Variables\QMUserVariable;
class UserVariableNumberOfCorrelationsProperty extends BaseNumberOfCorrelationsProperty
{
    use UserVariableProperty;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public static function whereTooBig(): QMQB{
        CorrelationCauseNumberOfProcessedDailyMeasurementsProperty::make()->minimum;
        $qb = static::whereQMQB(">", 0)
            ->where(UserVariable::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN, '<', 1);
        return $qb;
    }

    use IsCalculated;
    /**
     * @param UserVariable $model
     * @return mixed
     */
    public static function calculate($model){
        $numberOfCorrelations =
            UserVariableNumberOfUserVariableRelationshipsAsCauseProperty::calculate($model) +
            UserVariableNumberOfUserVariableRelationshipsAsEffectProperty::calculate($model);
	    $model->setAttribute(static::NAME, $numberOfCorrelations);
        return $numberOfCorrelations;
    }
}
