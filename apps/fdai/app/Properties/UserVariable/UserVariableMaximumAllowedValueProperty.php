<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseMaximumAllowedValueProperty;
use App\Traits\PropertyTraits\UserHyperParameterTrait;
use App\Traits\VariableValueTraits\UserVariableValuePropertyTrait;
use App\Traits\VariableValueTraits\VariableValueTrait;
use App\Variables\CommonVariables\EnvironmentCommonVariables\BarometricPressureCommonVariable;
use App\Variables\QMUserVariable;
class UserVariableMaximumAllowedValueProperty extends BaseMaximumAllowedValueProperty
{
    use UserVariableProperty, VariableValueTrait, UserVariableValuePropertyTrait, UserHyperParameterTrait;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public const SYNONYMS = [
        'user_maximum_allowed_value',
        'maximum_allowed_value',
        'user_maximum_allowed_value_in_common_unit',
        'maximum_allowed_value_in_common_unit',
    ];
    /**
     * @param QMUserVariable $uv
     * @return float|null
     */
    public static function calculate($uv): ?float{
        $inCommonUnit = $uv->maximumAllowedValueInCommonUnit ??
            $uv->getRawAttribute(Variable::FIELD_MAXIMUM_ALLOWED_VALUE);
        if($inCommonUnit === 'Infinity'){$inCommonUnit = null;}
        $commonUnit = $uv->getCommonUnit();
        $unitMax = $commonUnit->maximumValue;
        if($inCommonUnit === null || ($unitMax && $unitMax < $inCommonUnit)){$inCommonUnit = $unitMax;}
        if($commonUnit->isRating()){$inCommonUnit = $commonUnit->maximumValue;}
        $uv->maximumAllowedValueInCommonUnit = $inCommonUnit;
        return $uv->setMaximumAllowedValue($inCommonUnit);
    }
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $variableId = $this->getVariableIdAttribute();
        $max = $this->getDBValue();
        if ($variableId === BarometricPressureCommonVariable::ID) {
            $this->throwException("Why are we setting BarometricPressure max to $max");
        }
    }
}
