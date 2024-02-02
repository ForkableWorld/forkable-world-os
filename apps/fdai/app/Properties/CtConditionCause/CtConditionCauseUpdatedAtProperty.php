<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\CtConditionCause;
use App\Models\CtConditionCause;
use App\Traits\PropertyTraits\CtConditionCauseProperty;
use App\Properties\Base\BaseUpdatedAtProperty;
class CtConditionCauseUpdatedAtProperty extends BaseUpdatedAtProperty
{
    use CtConditionCauseProperty;
    public $table = CtConditionCause::TABLE;
    public $parentClass = CtConditionCause::class;
}
