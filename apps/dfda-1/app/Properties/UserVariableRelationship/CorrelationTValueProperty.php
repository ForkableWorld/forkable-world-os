<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseTValueProperty;
class CorrelationTValueProperty extends BaseTValueProperty
{
    use CorrelationProperty;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
}