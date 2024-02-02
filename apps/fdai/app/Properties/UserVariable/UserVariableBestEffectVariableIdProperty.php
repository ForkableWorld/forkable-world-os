<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariableRelationship;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseBestEffectVariableIdProperty;
use Illuminate\Database\Eloquent\Builder;
use App\Variables\QMUserVariable;
class UserVariableBestEffectVariableIdProperty extends BaseBestEffectVariableIdProperty
{
    use IsCalculated;
    use UserVariableProperty;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @param QMUserVariable|UserVariable $uv
     * @return int
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function calculate($uv): ?int{
        $best = $uv->setBestCorrelationAsCause();
        if(!$best){return null;}
        $uv->setAttribute(static::NAME, $best->effect_variable_id);
        return $best->effect_variable_id;
    }
}
