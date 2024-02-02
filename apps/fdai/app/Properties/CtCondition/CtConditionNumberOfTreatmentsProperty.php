<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\CtCondition;
use App\Models\CtCondition;
use App\Models\CtConditionCause;
use App\Models\CtConditionTreatment;
use App\Traits\PropertyTraits\CtConditionProperty;
use App\Properties\Base\BaseNumberOfTreatmentsProperty;
class CtConditionNumberOfTreatmentsProperty extends BaseNumberOfTreatmentsProperty
{
    use CtConditionProperty;
    public $table = CtCondition::TABLE;
    public $parentClass = CtCondition::class;
    protected static function getRelatedTable():string{return CtConditionTreatment::TABLE;}
    public static function getForeignKey(): string{return CtConditionCause::FIELD_CONDITION_ID;}
}
