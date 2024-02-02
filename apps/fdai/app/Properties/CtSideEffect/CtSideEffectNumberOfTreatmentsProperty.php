<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\CtSideEffect;
use App\Models\CtConditionCause;
use App\Models\CtSideEffect;
use App\Models\CtTreatmentSideEffect;
use App\Traits\PropertyTraits\CtSideEffectProperty;
use App\Properties\Base\BaseNumberOfTreatmentsProperty;
class CtSideEffectNumberOfTreatmentsProperty extends BaseNumberOfTreatmentsProperty
{
    use CtSideEffectProperty;
    public $table = CtSideEffect::TABLE;
    public $parentClass = CtSideEffect::class;
    protected static function getRelatedTable():string{return CtTreatmentSideEffect::TABLE;}
    public static function getForeignKey(): string{return CtTreatmentSideEffect::FIELD_SIDE_EFFECT_ID;}
}
