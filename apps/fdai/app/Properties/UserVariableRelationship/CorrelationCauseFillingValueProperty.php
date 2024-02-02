<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Traits\VariableValueTraits\CauseDailyVariableValueTrait;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseCauseFillingValueProperty;
class CorrelationCauseFillingValueProperty extends BaseCauseFillingValueProperty
{
    use CorrelationProperty, CauseDailyVariableValueTrait;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    public function getExample(): ?float{
        $c = $this->getCorrelation();
        $v = $c->getCauseVariable();
        return $v->getFillingValueAttribute();
    }
}
