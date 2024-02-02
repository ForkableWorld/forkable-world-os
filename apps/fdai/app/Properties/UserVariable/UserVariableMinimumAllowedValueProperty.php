<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Properties\Base\BaseMinimumAllowedValueProperty;
use App\Slim\Model\QMUnit;
use App\Traits\PropertyTraits\UserHyperParameterTrait;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Traits\VariableValueTraits\UserVariableValuePropertyTrait;
use App\Traits\VariableValueTraits\VariableValueTrait;
use App\Variables\QMUserVariable;
class UserVariableMinimumAllowedValueProperty extends BaseMinimumAllowedValueProperty
{
    use UserVariableProperty, VariableValueTrait, UserVariableValuePropertyTrait, UserHyperParameterTrait;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public const SYNONYMS = [
        'user_minimum_allowed_value',
        'minimum_allowed_value',
        'user_minimum_allowed_value_in_common_unit',
        'minimum_allowed_value_in_common_unit',
    ];
    public static function fixTooBig(): array{
        $fixed = [];
        foreach (QMUnit::getUnits() as $u) {
            if ($u->getMinimumValue() !== null) {
                $unitMin = $u->getMinimumValue();
                /** @var QMUserVariable[] $rows */
                $rows = QMUserVariable::readonly()
                    ->whereNotNull(UserVariable::FIELD_MINIMUM_ALLOWED_VALUE)
                    ->where(UserVariable::FIELD_MINIMUM_ALLOWED_VALUE, '>', $unitMin)
                    ->getDBModels();
                foreach ($rows as $v) {
                    $v->logInfo($v->minimumAllowedValue);
                    $fixed[] = $v;
                }
            }
        }
        return $fixed;
    }
    /**
     * @param $inUserUnit
     * @return float|null
     * @throws \App\Exceptions\IncompatibleUnitException
     * @throws \App\Exceptions\InvalidVariableValueException
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function toDBValue($inUserUnit): ?float{
        return $this->toCommonUnit($inUserUnit);
    }
}
