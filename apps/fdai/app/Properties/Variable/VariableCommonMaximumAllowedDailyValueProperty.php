<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Traits\VariableValueTraits\DailyVariableValueTrait;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseCommonMaximumAllowedDailyValueProperty;
class VariableCommonMaximumAllowedDailyValueProperty extends BaseCommonMaximumAllowedDailyValueProperty
{
    use VariableProperty, DailyVariableValueTrait;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param Variable $v
     * @return float
     */
    public static function calculate( $v): ?float {
        $val = $v->getRawAttribute(self::NAME);
        if($val !== null){return $val;}
        $val = $v->getRawAttribute(Variable::FIELD_MAXIMUM_ALLOWED_VALUE);
        if($val !== null){return $val;}
        $unit = $v->getQMUnit();
        return $unit->maximumDailyValue;
    }
}
