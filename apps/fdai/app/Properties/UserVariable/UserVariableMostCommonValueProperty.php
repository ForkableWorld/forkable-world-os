<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseMostCommonValueProperty;
use App\Traits\VariableValueTraits\UserVariableValuePropertyTrait;
use App\Traits\VariableValueTraits\VariableValueTrait;
use App\Utils\Stats;
use App\Variables\QMUserVariable;
class UserVariableMostCommonValueProperty extends BaseMostCommonValueProperty
{
    use UserVariableProperty, VariableValueTrait, UserVariableValuePropertyTrait;
    use IsCalculated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @param QMUserVariable $v
     * @return float
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function calculate($v): ?float{
        if($values = $v->getValidValues()){
            $mode = Stats::mode($values);
        } else {
            $mode = null;
        }
		$min = $v->getMinimumAllowedValueAttribute();
		$minDay = $v->getMinimumAllowedDailyValue();
        $v->mostCommonValueInCommonUnit = $mode;
        $v->convertValuesToUserUnit();
        $v->setAttribute(static::NAME, $mode);
        return $mode;
    }
	protected function validateMin(): void{
		parent::validateMin();
	}
}
