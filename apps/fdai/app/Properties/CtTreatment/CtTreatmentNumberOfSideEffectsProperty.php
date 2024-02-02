<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\CtTreatment;
use App\Models\CtTreatment;
use App\Models\CtTreatmentSideEffect;
use App\Traits\PropertyTraits\CtTreatmentProperty;
use App\Properties\Base\BaseNumberOfSideEffectsProperty;
use App\Traits\PropertyTraits\IsNumberOfRelated;
class CtTreatmentNumberOfSideEffectsProperty extends BaseNumberOfSideEffectsProperty {
    use CtTreatmentProperty;
    use IsNumberOfRelated;
    public $table = CtTreatment::TABLE;
    public $parentClass = CtTreatment::class;
    public static function getForeignKey(): string{return CtTreatmentSideEffect::FIELD_TREATMENT_ID;}
    protected static function getRelatedTable(): string{return CtTreatmentSideEffect::TABLE;}
}
