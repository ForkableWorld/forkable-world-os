<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Traits\VariableValueTraits\EffectDailyVariableValueTrait;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseEffectFillingValueProperty;
class CorrelationEffectFillingValueProperty extends BaseEffectFillingValueProperty
{
    use CorrelationProperty, EffectDailyVariableValueTrait;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    public function getExample(): ?float{
        $c = $this->getCorrelation();
        $v = $c->getEffectVariable();
        return $v->getFillingValueAttribute();
    }
}
