<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\CtgCondition;
use App\Models\CtgCondition;
use App\Traits\PropertyTraits\CtgConditionProperty;
use App\Properties\Base\BaseNctIdProperty;
class CtgConditionNctIdProperty extends BaseNctIdProperty
{
    use CtgConditionProperty;
    public $table = CtgCondition::TABLE;
    public $parentClass = CtgCondition::class;
}
