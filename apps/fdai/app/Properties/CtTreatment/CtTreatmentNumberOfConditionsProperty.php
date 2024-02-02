<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\CtTreatment;
use App\Models\CtConditionTreatment;
use App\Models\CtTreatment;
use App\Properties\Base\BaseNumberOfConditionsProperty;
use App\Traits\PropertyTraits\CtTreatmentProperty;
use App\Traits\PropertyTraits\IsNumberOfRelated;
class CtTreatmentNumberOfConditionsProperty extends BaseNumberOfConditionsProperty
{
    use CtTreatmentProperty;
    use IsNumberOfRelated;
    public $table = CtTreatment::TABLE;
    public $parentClass = CtTreatment::class;
    public static function getForeignKey(): string{return CtConditionTreatment::FIELD_TREATMENT_ID;}
    protected static function getRelatedTable(): string{return CtConditionTreatment::TABLE;}
}
