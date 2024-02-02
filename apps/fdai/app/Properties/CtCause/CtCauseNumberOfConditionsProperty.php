<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\CtCause;
use App\Models\CtCause;
use App\Models\CtConditionCause;
use App\Traits\PropertyTraits\CtCauseProperty;
use App\Properties\Base\BaseNumberOfConditionsProperty;
class CtCauseNumberOfConditionsProperty extends BaseNumberOfConditionsProperty
{
    use CtCauseProperty;
    public $table = CtCause::TABLE;
    public $parentClass = CtCause::class;
    protected static function getRelatedTable():string{return CtConditionCause::TABLE;}
    public static function getForeignKey(): string{return CtConditionCause::FIELD_CAUSE_ID;}
}
